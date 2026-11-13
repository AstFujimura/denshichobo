<?php

namespace App\Support\Tenancy;

use App\Models\Control\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantResolver
{
    public function findByPrefix(string $prefix): ?Tenant
    {
        $cacheKey = 'tenant:prefix:' . $prefix;

        $tenantId = Cache::remember($cacheKey, 120, function () use ($prefix) {
            return Tenant::query()->where('prefix', $prefix)->value('id');
        });

        if (! $tenantId) {
            return null;
        }

        return Tenant::query()
            ->with(['features', 'aiSettings'])
            ->find($tenantId);
    }

    public function forgetPrefix(string $prefix): void
    {
        Cache::forget('tenant:prefix:' . $prefix);
    }
}
