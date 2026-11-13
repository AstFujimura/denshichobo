<?php

namespace App\Support\Tenancy;

use App\Models\Control\Tenant;
use App\Models\Control\TenantFeature;

class TenantFeatureSync
{
    /**
     * Ensure all known feature keys exist for the tenant.
     *
     * @param  array<string, bool>|null  $enabledMap  feature_key => enabled
     */
    public function sync(Tenant $tenant, ?array $enabledMap = null): void
    {
        foreach (config('tenancy.features', []) as $key => $meta) {
            $enabled = $enabledMap[$key] ?? (bool) ($meta['default'] ?? false);

            TenantFeature::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'feature_key' => $key,
                ],
                [
                    'enabled' => $enabled,
                ]
            );
        }
    }
}
