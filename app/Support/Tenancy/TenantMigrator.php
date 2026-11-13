<?php

namespace App\Support\Tenancy;

use App\Models\Control\Tenant;
use App\Models\Control\TenantMigrationRun;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TenantMigrator
{
    public function __construct(protected TenantDatabaseManager $databaseManager)
    {
    }

    /**
     * @param  array<int, string>  $statuses
     * @return array<int, Tenant>
     */
    public function resolveTenants(?string $tenantOption, array $statuses): array
    {
        $query = Tenant::query()->orderBy('id');

        if ($tenantOption) {
            $query->where(function ($q) use ($tenantOption) {
                $q->where('code', $tenantOption)
                    ->orWhere('prefix', $tenantOption);
            });
        } else {
            $query->whereIn('status', $statuses);
        }

        return $query->get()->all();
    }

    public function latestAppliedMigration(Tenant $tenant): ?string
    {
        $this->databaseManager->connect($tenant, false);

        try {
            if (! $this->tenantMigrationsTableExists()) {
                return null;
            }

            return DB::connection('tenant')
                ->table('migrations')
                ->orderByDesc('id')
                ->value('migration');
        } finally {
            $this->databaseManager->disconnect(true);
        }
    }

    public function pendingMigrationCount(Tenant $tenant, string $path): int
    {
        $this->databaseManager->connect($tenant, false);

        try {
            $files = $this->migrationFiles($path);
            if ($files === []) {
                return 0;
            }

            $ran = [];
            if ($this->tenantMigrationsTableExists()) {
                $ran = DB::connection('tenant')
                    ->table('migrations')
                    ->pluck('migration')
                    ->all();
            }

            return count(array_diff($files, $ran));
        } finally {
            $this->databaseManager->disconnect(true);
        }
    }

    /**
     * @return array{ok: bool, from: ?string, to: ?string, error: ?string, run: TenantMigrationRun|null}
     */
    public function migrate(
        Tenant $tenant,
        string $batchId,
        string $path,
        bool $force = false,
        bool $dryRun = false
    ): array {
        $from = null;
        $run = null;

        try {
            $this->databaseManager->connect($tenant, false);
            $from = $this->tenantMigrationsTableExists()
                ? DB::connection('tenant')->table('migrations')->orderByDesc('id')->value('migration')
                : null;

            if ($dryRun) {
                $pending = $this->pendingMigrationCountConnected($path);
                $this->databaseManager->disconnect(true);

                return [
                    'ok' => true,
                    'from' => $from,
                    'to' => $from,
                    'error' => null,
                    'run' => null,
                    'pending' => $pending,
                ];
            }

            $run = TenantMigrationRun::query()->create([
                'tenant_id' => $tenant->id,
                'batch_id' => $batchId,
                'target_migration' => null,
                'status' => 'running',
                'from_migration' => $from,
                'to_migration' => null,
                'attempts' => 1,
                'started_at' => now(),
            ]);

            $params = [
                '--database' => 'tenant',
                '--path' => $path,
            ];
            if ($force) {
                $params['--force'] = true;
            }

            $exit = Artisan::call('migrate', $params);
            $output = Artisan::output();

            $to = $this->tenantMigrationsTableExists()
                ? DB::connection('tenant')->table('migrations')->orderByDesc('id')->value('migration')
                : null;

            if ($exit !== 0) {
                throw new \RuntimeException(trim($output) !== '' ? trim($output) : "migrate exited with code {$exit}");
            }

            $run->fill([
                'status' => 'succeeded',
                'to_migration' => $to,
                'finished_at' => now(),
                'error_message' => null,
            ])->save();

            $this->writeLog('info', 'Tenant migrate succeeded', [
                'tenant_code' => $tenant->code,
                'batch_id' => $batchId,
                'from' => $from,
                'to' => $to,
            ]);

            $this->databaseManager->disconnect(true);

            return [
                'ok' => true,
                'from' => $from,
                'to' => $to,
                'error' => null,
                'run' => $run,
            ];
        } catch (Throwable $e) {
            if ($run) {
                $run->fill([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'error_message' => Str::limit($e->getMessage(), 2000),
                ])->save();
            }

            $this->writeLog('error', 'Tenant migrate failed', [
                'tenant_code' => $tenant->code,
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);

            try {
                $this->databaseManager->disconnect(true);
            } catch (Throwable $ignored) {
                //
            }

            return [
                'ok' => false,
                'from' => $from,
                'to' => null,
                'error' => $e->getMessage(),
                'run' => $run,
            ];
        }
    }

    public function newBatchId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * @return array<int, string>
     */
    protected function migrationFiles(string $path): array
    {
        $absolute = base_path($path);
        if (! is_dir($absolute)) {
            return [];
        }

        $files = glob($absolute . DIRECTORY_SEPARATOR . '*_*.php') ?: [];
        $names = [];
        foreach ($files as $file) {
            $names[] = basename($file, '.php');
        }
        sort($names);

        return $names;
    }

    protected function pendingMigrationCountConnected(string $path): int
    {
        $files = $this->migrationFiles($path);
        if ($files === []) {
            return 0;
        }

        $ran = [];
        if ($this->tenantMigrationsTableExists()) {
            $ran = DB::connection('tenant')
                ->table('migrations')
                ->pluck('migration')
                ->all();
        }

        return count(array_diff($files, $ran));
    }

    protected function tenantMigrationsTableExists(): bool
    {
        return DB::connection('tenant')->getSchemaBuilder()->hasTable('migrations');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function writeLog(string $level, string $message, array $context = []): void
    {
        try {
            Log::log($level, $message, $context);
        } catch (Throwable $e) {
            // ログへ書けなくても、マイグレーション結果は変えない。
        }
    }
}
