<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;

class EnsureTenantFeature
{
    public function __construct(protected TenantContext $context)
    {
    }

    public function handle(Request $request, Closure $next, string $featureKey)
    {
        if (! $this->context->check() || ! $this->context->featureEnabled($featureKey)) {
            abort(404);
        }

        return $next($request);
    }
}
