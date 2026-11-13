<?php

namespace App\Http\Middleware;

use App\Models\Control\Tenant;
use App\Support\Tenancy\TenantAiConfigApplier;
use App\Support\Tenancy\TenantContext;
use App\Support\Tenancy\TenantDatabaseManager;
use App\Support\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class InitializeTenancyByPrefix
{
    public function __construct(
        protected TenantResolver $resolver,
        protected TenantContext $context,
        protected TenantDatabaseManager $databaseManager,
        protected TenantAiConfigApplier $aiConfigApplier
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $prefix = (string) $request->route('tenant_prefix');

        if ($prefix === '') {
            abort(404);
        }

        $tenant = $this->resolver->findByPrefix($prefix);

        if (! $tenant) {
            abort(404);
        }

        if ($tenant->status === Tenant::STATUS_PROVISIONING) {
            abort(503, 'Tenant is provisioning.');
        }

        if ($tenant->status === Tenant::STATUS_SUSPENDED) {
            abort(403, 'Tenant is suspended.');
        }

        if ($tenant->status === Tenant::STATUS_ARCHIVED || $tenant->status !== Tenant::STATUS_ACTIVE) {
            abort(404);
        }

        $features = [];
        foreach ($tenant->features as $feature) {
            $features[$feature->feature_key] = (bool) $feature->enabled;
        }

        $this->context->set($tenant, $features);
        $this->databaseManager->connect($tenant, true);
        $this->databaseManager->applyCompatConfig($tenant);
        $this->aiConfigApplier->apply($tenant);

        // Keep defaults for the whole request (route() helpers / auth redirects).
        URL::defaults(['tenant_prefix' => $tenant->prefix]);

        // Blade / JS compatibility: $prefix is "/{tenant}" (or ""), $server is storage mode.
        view()->share('prefix', tenant_path_prefix());
        view()->share('server', $tenant->storage_mode);

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
