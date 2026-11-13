<?php

namespace App\Jobs;

use App\Models\Control\Tenant;
use App\Models\Control\TenantAiSetting;
use App\Models\Control\TenantMigrationRun;
use App\Support\Tenancy\ProvisionDatabaseManager;
use App\Support\Tenancy\TenantFeatureSync;
use App\Support\Tenancy\TenantMigrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

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
        ignore_user_abort(true);
        set_time_limit(0);

        try {
            $this->provision($provision, $migrator, $featureSync);
        } catch (Throwable $e) {
            Log::error('Tenant provision failed', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
            ]);
            $this->recordFailure($e);
        }
    }

    protected function provision(
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

    protected function recordFailure(Throwable $e): void
    {
        $tenant = Tenant::query()->find($this->tenantId);
        if (! $tenant) {
            return;
        }

        $alreadyRecorded = TenantMigrationRun::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subMinute())
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        TenantMigrationRun::query()->create([
            'tenant_id' => $tenant->id,
            'batch_id' => (string) Str::uuid(),
            'status' => 'failed',
            'attempts' => 1,
            'started_at' => now(),
            'finished_at' => now(),
            'error_message' => Str::limit($e->getMessage(), 2000),
        ]);
    }
}
