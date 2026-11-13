<?php

namespace App\Console\Commands;

use App\Support\Tenancy\SupportEntryService;
use App\Support\Tenancy\TenantMigrator;
use Illuminate\Console\Command;

class TenantsEnsureSupportUserCommand extends Command
{
    protected $signature = 'tenants:ensure-support-user
                            {--tenant= : Tenant code or prefix}
                            {--status=active,provisioning : Target statuses when --tenant omitted}';

    protected $description = 'Ensure platform_support user exists in tenant databases';

    public function handle(TenantMigrator $migrator, SupportEntryService $support): int
    {
        $statuses = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('status')))));
        $tenants = $migrator->resolveTenants($this->option('tenant'), $statuses);

        foreach ($tenants as $tenant) {
            $user = $support->ensureSupportUser($tenant);
            $this->info("{$tenant->code}: support user id={$user->id} name={$user->name}");
        }

        return self::SUCCESS;
    }
}
