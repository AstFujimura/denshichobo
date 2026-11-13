<?php

namespace App\Jobs;

use App\Models\Control\Tenant;
use App\Models\Control\TenantAiSetting;
use App\Support\Tenancy\ProvisionDatabaseManager;
use App\Support\Tenancy\TenantFeatureSync;
use App\Support\Tenancy\TenantMigrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProvisionTenant implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 900;

    /**
     * @param  array<string, bool>  $featureMap
     */
    public function __construct(
        public int $tenantId,
        public array $featureMap,
        public string $aiProvider,
        public ?string $geminiModel
    ) {
    }

    public function handle(
        ProvisionDatabaseManager $provision,
        TenantMigrator $migrator,
        TenantFeatureSync $featureSync
    ): void {
        $tenant = Tenant::query()->findOrFail($this->tenantId);

        if ($provision->databaseExists($tenant->db_database)) {
            throw new RuntimeException(
                "Database [{$tenant->db_database}] already exists. Provisioning stopped to avoid overwriting it."
            );
        }
        $provision->createDatabase($tenant->db_database);

        $migration = $migrator->migrate(
            $tenant,
            $migrator->newBatchId(),
            'database/migrations',
            true
        );
        if (! $migration['ok']) {
            throw new RuntimeException(
                $migration['error'] ?: 'テナントDBのマイグレーションに失敗しました。'
            );
        }

        DB::connection('control')->transaction(function () use ($tenant, $featureSync) {
            $featureSync->sync($tenant, $this->featureMap);

            TenantAiSetting::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'ledger_ocr_provider' => $this->aiProvider,
                    'gemini_model' => $this->geminiModel,
                ]
            );

            $tenant->forceFill([
                'status' => Tenant::STATUS_ACTIVE,
                'provisioned_at' => now(),
            ])->save();
        });
    }
}
