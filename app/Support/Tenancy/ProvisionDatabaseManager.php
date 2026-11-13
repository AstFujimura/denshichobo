<?php

namespace App\Support\Tenancy;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Privileged MySQL operations (CREATE DATABASE).
 * Call only from trusted Artisan or provisioning queue jobs.
 */
class ProvisionDatabaseManager
{
    public const CONNECTION = 'provision';

    public function connectionName(): string
    {
        return self::CONNECTION;
    }

    public function username(): string
    {
        return (string) config('database.connections.provision.username');
    }

    public function appUsername(): string
    {
        return (string) config('database.connections.tenant.username');
    }

    public function usesSeparateCredentials(): bool
    {
        $provisionUser = $this->username();
        $appUser = $this->appUsername();

        if ($provisionUser === '' || $appUser === '') {
            return false;
        }

        if ($provisionUser !== $appUser) {
            return true;
        }

        $provisionPass = (string) config('database.connections.provision.password');
        $appPass = (string) config('database.connections.tenant.password');

        return $provisionPass !== $appPass;
    }

    public function ping(): void
    {
        DB::connection(self::CONNECTION)->select('select 1 as ok');
    }

    /**
     * @return list<string>
     */
    public function showGrants(): array
    {
        $rows = DB::connection(self::CONNECTION)->select('SHOW GRANTS');
        $grants = [];
        foreach ($rows as $row) {
            $values = array_values((array) $row);
            if (isset($values[0])) {
                $grants[] = (string) $values[0];
            }
        }

        return $grants;
    }

    public function canCreateDatabase(): bool
    {
        foreach ($this->showGrants() as $grant) {
            $upper = strtoupper($grant);
            if (str_contains($upper, 'ALL PRIVILEGES') && str_contains($upper, ' ON *.*')) {
                return true;
            }
            if (preg_match('/\bCREATE\b/', $upper) && str_contains($upper, ' ON *.*')) {
                return true;
            }
        }

        return false;
    }

    public function assertSafeDatabaseName(string $database): string
    {
        $database = trim($database);
        if ($database === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            throw new InvalidArgumentException('Invalid database name. Use only letters, numbers, underscore.');
        }

        return $database;
    }

    public function databaseExists(string $database): bool
    {
        $database = $this->assertSafeDatabaseName($database);
        $row = DB::connection(self::CONNECTION)->selectOne(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$database]
        );

        return $row !== null;
    }

    /**
     * Drop a database created for a cancelled provision.
     * Refuses the control database, the default app database, and system schemas.
     */
    public function dropDatabase(string $database): void
    {
        $database = $this->assertSafeDatabaseName($database);
        $this->assertDroppableDatabase($database);

        try {
            DB::connection(self::CONNECTION)->statement(
                sprintf('DROP DATABASE IF EXISTS `%s`', $database)
            );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'DROP DATABASE failed via provision connection. The provision user needs the DROP privilege.',
                0,
                $e
            );
        }
    }

    /**
     * CREATE DATABASE IF NOT EXISTS. Does not DROP.
     */
    public function createDatabase(string $database, string $charset = 'utf8', string $collation = 'utf8_general_ci'): void
    {
        $database = $this->assertSafeDatabaseName($database);
        $charset = $this->assertSafeIdentifier($charset, 'charset');
        $collation = $this->assertSafeIdentifier($collation, 'collation');

        try {
            DB::connection(self::CONNECTION)->statement(
                sprintf(
                    'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                    $database,
                    $charset,
                    $collation
                )
            );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'CREATE DATABASE failed via provision connection. Check PROVISION_DB_* credentials and grants.',
                0,
                $e
            );
        }
    }

    protected function assertDroppableDatabase(string $database): void
    {
        $protected = [
            (string) config('database.connections.control.database'),
            (string) config('database.connections.mysql.database'),
            'mysql',
            'information_schema',
            'performance_schema',
            'sys',
        ];

        if (in_array($database, array_filter($protected), true)) {
            throw new RuntimeException("Database [{$database}] cannot be dropped.");
        }
    }

    protected function assertSafeIdentifier(string $value, string $label): string
    {
        $value = trim($value);
        if ($value === '' || ! preg_match('/^[A-Za-z0-9_]+$/', $value)) {
            throw new InvalidArgumentException("Invalid {$label}.");
        }

        return $value;
    }
}
