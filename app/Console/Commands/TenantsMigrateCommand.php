<?php

namespace App\Console\Commands;

use App\Support\Tenancy\TenantMigrator;
use Illuminate\Console\Command;

class TenantsMigrateCommand extends Command
{
    protected $signature = 'tenants:migrate
                            {--tenant= : Tenant code or prefix}
                            {--status=active,provisioning : Comma-separated tenant statuses}
                            {--batch= : Batch id (UUID). Auto-generated when omitted}
                            {--path=database/migrations : Tenant migrations path}
                            {--force : Force the operation to run when in production}
                            {--dry-run : List tenants / pending counts without migrating}
                            {--stop-on-error : Stop after the first failure}';

    protected $description = 'Run pending migrations for one or all tenants';

    public function handle(TenantMigrator $migrator): int
    {
        $statuses = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('status')))));
        $path = (string) $this->option('path');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force') || ! app()->environment('production');
        $batchId = $this->option('batch') ?: $migrator->newBatchId();

        $tenants = $migrator->resolveTenants($this->option('tenant'), $statuses);

        if ($tenants === []) {
            $this->warn('No tenants matched.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . 'batch_id=' . $batchId);
        $this->info('tenants=' . count($tenants) . ' path=' . $path);

        $ok = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            $label = "{$tenant->code} ({$tenant->db_database})";

            if ($dryRun) {
                try {
                    $pending = $migrator->pendingMigrationCount($tenant, $path);
                    $latest = $migrator->latestAppliedMigration($tenant);
                    $this->line("[dry-run] {$label} pending={$pending} latest=" . ($latest ?: '-'));
                    $ok++;
                } catch (\Throwable $e) {
                    $this->error("[dry-run] {$label} ERROR: " . $e->getMessage());
                    $failed++;
                    if ($this->option('stop-on-error')) {
                        break;
                    }
                }
                continue;
            }

            $this->line("Migrating {$label} ...");
            $result = $migrator->migrate($tenant, $batchId, $path, $force, false);

            if ($result['ok']) {
                $this->info("OK {$label} {$result['from']} -> {$result['to']}");
                $ok++;
            } else {
                $this->error("FAIL {$label}: {$result['error']}");
                $failed++;
                if ($this->option('stop-on-error')) {
                    break;
                }
            }
        }

        $this->newLine();
        $this->info("done ok={$ok} failed={$failed} batch_id={$batchId}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
