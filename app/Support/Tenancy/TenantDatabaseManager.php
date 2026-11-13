<?php

namespace App\Support\Tenancy;

use App\Models\Control\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TenantDatabaseManager
{
    public function connect(Tenant $tenant, bool $setAsDefault = true): void
    {
        if ($tenant->db_database === null || $tenant->db_database === '') {
            throw new InvalidArgumentException('Tenant db_database is empty.');
        }

        Config::set('database.connections.tenant.database', $tenant->db_database);

        DB::purge('tenant');
        DB::reconnect('tenant');

        if ($setAsDefault) {
            DB::setDefaultConnection('tenant');
        }
    }

    public function disconnect(bool $restoreDefault = true): void
    {
        DB::purge('tenant');

        if ($restoreDefault) {
            DB::setDefaultConnection(config('database.default'));
        }
    }

    public function applyCompatConfig(Tenant $tenant): void
    {
        Config::set('prefix.prefix', $tenant->prefix);
        Config::set('prefix.server', $tenant->storage_mode);
        Config::set('custom.file_upload_path', $tenant->file_upload_path);
    }
}
