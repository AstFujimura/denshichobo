<?php

namespace App\Console\Commands;

use App\Support\Tenancy\ProvisionDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenancyDbCheckCommand extends Command
{
    protected $signature = 'tenancy:db-check
        {--create-probe : Create and leave a probe database named binder_provision_probe (no DROP)}';

    protected $description = 'Verify app vs provision DB credentials and CREATE DATABASE capability';

    public function handle(ProvisionDatabaseManager $provision): int
    {
        $this->info('=== Tenancy DB connections ===');

        $rows = [
            ['mysql (default/app)', config('database.connections.mysql.username'), config('database.connections.mysql.host'), config('database.connections.mysql.database')],
            ['control', config('database.connections.control.username'), config('database.connections.control.host'), config('database.connections.control.database')],
            ['tenant', config('database.connections.tenant.username'), config('database.connections.tenant.host'), config('database.connections.tenant.database')],
            ['provision', config('database.connections.provision.username'), config('database.connections.provision.host'), config('database.connections.provision.database')],
        ];

        $this->table(['connection', 'username', 'host', 'database'], $rows);

        $ok = true;

        foreach (['mysql', 'control', 'tenant', 'provision'] as $name) {
            try {
                DB::connection($name)->select('select 1 as ok');
                $this->line("<info>OK</info>  ping {$name}");
            } catch (\Throwable $e) {
                $ok = false;
                $this->error("FAIL ping {$name}: " . $e->getMessage());
            }
        }

        if ($provision->usesSeparateCredentials()) {
            $this->info('Credentials: provision user is distinct from tenant/app user.');
        } else {
            $this->warn('Credentials: provision user matches app/tenant user.');
            $this->warn('Split PROVISION_DB_USERNAME / PROVISION_DB_PASSWORD from DB_USERNAME for production-like setup.');
            if ((bool) config('tenancy.storage.require_separate_provision_user', false)
                || (bool) config('tenancy.db.require_separate_provision_user', false)) {
                $ok = false;
                $this->error('tenancy.db.require_separate_provision_user=true but users are not separated.');
            }
        }

        try {
            $grants = $provision->showGrants();
            $this->line('Provision SHOW GRANTS:');
            foreach ($grants as $grant) {
                $this->line('  - ' . $grant);
            }
            if ($provision->canCreateDatabase()) {
                $this->info('CREATE DATABASE privilege: likely YES (from SHOW GRANTS).');
            } else {
                $this->warn('CREATE DATABASE privilege: not clearly present in SHOW GRANTS.');
            }
        } catch (\Throwable $e) {
            $ok = false;
            $this->error('SHOW GRANTS failed: ' . $e->getMessage());
        }

        if ($this->option('create-probe')) {
            $probe = 'binder_provision_probe';
            try {
                $existed = $provision->databaseExists($probe);
                $provision->createDatabase($probe);
                $this->info($existed
                    ? "Probe database already existed: {$probe}"
                    : "Created probe database: {$probe}");
                $this->comment('Note: this command never DROP DATABASE. Remove the probe DB manually if needed.');
            } catch (\Throwable $e) {
                $ok = false;
                $this->error('CREATE DATABASE probe failed: ' . $e->getMessage());
            }
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
