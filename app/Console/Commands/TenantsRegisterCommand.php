<?php

namespace App\Console\Commands;

use App\Models\Control\Tenant;
use App\Models\Control\TenantAiSetting;
use App\Support\Tenancy\TenantFeatureSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantsRegisterCommand extends Command
{
    protected $signature = 'tenants:register
                            {--code= : Tenant code (default: prefix or local)}
                            {--name= : Display name}
                            {--prefix= : URL prefix (required unless config prefix is set)}
                            {--db= : Existing tenant database name (default: DB_DATABASE)}
                            {--storage= : cloud|onpre (default: config prefix.server)}
                            {--upload-path= : onpre file_upload_path}
                            {--features= : Comma-separated enabled feature keys}
                            {--status=active : Tenant status}
                            {--force : Update if code already exists}';

    protected $description = 'Register an existing database as a control-plane tenant';

    public function handle(TenantFeatureSync $featureSync): int
    {
        $prefix = $this->option('prefix');
        if ($prefix === null || $prefix === '') {
            $prefix = (string) config('prefix.prefix', '');
        }

        if ($prefix === '') {
            $this->error('prefix is required. Pass --prefix= or set config/prefix.php.');

            return self::FAILURE;
        }

        $code = $this->option('code') ?: $prefix;
        $db = $this->option('db') ?: (string) config('database.connections.mysql.database');
        $storage = $this->option('storage') ?: (string) config('prefix.server', 'cloud');
        $name = $this->option('name') ?: $code;
        $uploadPath = $this->option('upload-path') ?: config('custom.file_upload_path');
        $status = $this->option('status') ?: Tenant::STATUS_ACTIVE;

        if (! in_array($storage, ['cloud', 'onpre'], true)) {
            $this->error('--storage must be cloud or onpre.');

            return self::FAILURE;
        }

        if (! $this->databaseExists($db)) {
            $this->error("Database [{$db}] does not exist on the MySQL server.");

            return self::FAILURE;
        }

        $existing = Tenant::query()->where('code', $code)->first();
        if ($existing && ! $this->option('force')) {
            $this->error("Tenant code [{$code}] already exists. Use --force to update.");

            return self::FAILURE;
        }

        $payload = [
            'name' => $name,
            'prefix' => $prefix,
            'db_database' => $db,
            'status' => $status,
            'storage_mode' => $storage,
            'storage_prefix' => $prefix,
            'file_upload_path' => $storage === 'onpre' ? $uploadPath : null,
            'provisioned_at' => now(),
        ];

        if ($existing) {
            $existing->fill($payload)->save();
            $tenant = $existing;
            $this->info("Updated tenant [{$code}].");
        } else {
            $tenant = Tenant::query()->create(array_merge(['code' => $code], $payload));
            $this->info("Registered tenant [{$code}] -> DB [{$db}].");
        }

        $enabled = [];
        if ($this->option('features')) {
            foreach (explode(',', (string) $this->option('features')) as $key) {
                $key = trim($key);
                if ($key !== '') {
                    $enabled[$key] = true;
                }
            }
        }

        $featureSync->sync($tenant, $enabled === [] ? null : $enabled);

        TenantAiSetting::query()->firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'ledger_ocr_provider' => (string) config('ai_ocr.provider', 'gemini'),
            ]
        );

        $this->table(
            ['id', 'code', 'prefix', 'db_database', 'status', 'storage_mode'],
            [[$tenant->id, $tenant->code, $tenant->prefix, $tenant->db_database, $tenant->status, $tenant->storage_mode]]
        );

        return self::SUCCESS;
    }

    protected function databaseExists(string $database): bool
    {
        $row = DB::connection('control')->selectOne(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$database]
        );

        return $row !== null;
    }
}
