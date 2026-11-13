<?php

use App\Contracts\TenantFileStorage;
use App\Models\Control\Tenant;
use App\Models\Version;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Schema;

if (! function_exists('tenant')) {
    function tenant(): ?Tenant
    {
        return app(TenantContext::class)->tenant();
    }
}

if (! function_exists('tenant_prefix')) {
    function tenant_prefix(): ?string
    {
        return tenant()?->prefix ?? (config('prefix.prefix') ?: null);
    }
}

if (! function_exists('tenant_path_prefix')) {
    /**
     * Leading-slash prefix for app links / JS (#prefix), e.g. "/local" or "".
     */
    function tenant_path_prefix(): string
    {
        $prefix = trim((string) (tenant_prefix() ?? ''), '/');

        return $prefix === '' ? '' : '/' . $prefix;
    }
}

if (! function_exists('tenant_asset')) {
    /**
     * Public asset URL under /{tenant_prefix}/... (AssetController routes).
     */
    function tenant_asset(string $path): string
    {
        $path = ltrim($path, '/');
        $prefix = trim((string) (tenant_prefix() ?? ''), '/');

        if ($prefix === '') {
            return asset($path);
        }

        return asset($prefix . '/' . $path);
    }
}

if (! function_exists('tenant_storage')) {
    function tenant_storage(): TenantFileStorage
    {
        return app(TenantFileStorage::class);
    }
}

if (! function_exists('feature_enabled')) {
    /**
     * Prefer control-plane tenant_features; fall back to legacy versions row.
     */
    function feature_enabled(string $featureKey): bool
    {
        $context = app(TenantContext::class);
        if ($context->check()) {
            return $context->featureEnabled($featureKey);
        }

        $legacy = config("tenancy.features.{$featureKey}.legacy_column");
        if (! $legacy) {
            return (bool) (config("tenancy.features.{$featureKey}.default") ?? false);
        }

        try {
            if (! Schema::hasTable('versions') || ! Schema::hasColumn('versions', $legacy)) {
                return (bool) (config("tenancy.features.{$featureKey}.default") ?? false);
            }

            // スケジュール was historically a string column.
            return Version::query()->where($legacy, true)->exists()
                || Version::query()->where($legacy, '1')->exists()
                || Version::query()->where($legacy, 1)->exists();
        } catch (\Throwable $e) {
            return (bool) (config("tenancy.features.{$featureKey}.default") ?? false);
        }
    }
}
