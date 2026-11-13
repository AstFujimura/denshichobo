<?php

namespace App\Console\Commands;

use App\Models\Control\TenantMigrationRun;
use App\Support\Tenancy\TenantMigrator;
use Illuminate\Console\Command;

class TenantsMigrateStatusCommand extends Command
{
    protected $signature = 'tenants:migrate-status
                            {--tenant= : Tenant code or prefix}
                            {--status=active,provisioning,suspended : Comma-separated tenant statuses}
                            {--path=database/migrations : Tenant migrations path}';

    protected $description = 'Show migration status across tenants';

    public function handle(TenantMigrator $migrator): int
    {
        $statuses = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('status')))));
        $path = (string) $this->option('path');
        $tenants = $migrator->resolveTenants($this->option('tenant'), $statuses);

        if ($tenants === []) {
            $this->warn('No tenants matched.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($tenants as $tenant) {
            $latest = null;
            $pending = null;
            $error = null;

            try {
                $latest = $migrator->latestAppliedMigration($tenant);
                $pending = $migrator->pendingMigrationCount($tenant, $path);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }

            $lastRun = TenantMigrationRun::query()
                ->where('tenant_id', $tenant->id)
                ->orderByDesc('id')
                ->first();

            $rows[] = [
                $tenant->code,
                $tenant->db_database,
                $tenant->status,
                $latest ?: ($error ? 'ERROR' : '-'),
                $pending === null ? '-' : (string) $pending,
                $lastRun?->status ?: '-',
                $lastRun?->finished_at?->diffForHumans() ?: ($lastRun?->started_at?->diffForHumans() ?: '-'),
            ];
        }

        $this->table(
            ['code', 'db_database', 'tenant_status', 'last_migration', 'pending', 'last_run', 'when'],
            $rows
        );

        return self::SUCCESS;
    }
}
