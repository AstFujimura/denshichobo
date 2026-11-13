<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class EnsureTenantSessionIsolation
{
    public const TENANT_ID_KEY = 'auth_tenant_id';

    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();
        if (! $tenant) {
            abort(404);
        }

        $guard = Auth::guard('web');
        $hasAuthentication = $request->session()->has($guard->getName())
            || $request->cookies->has($guard->getRecallerName());

        if (! $hasAuthentication) {
            $request->session()->forget([
                self::TENANT_ID_KEY,
                'platform_support',
            ]);

            return $next($request);
        }

        $authenticatedTenantId = $request->session()->get(self::TENANT_ID_KEY);
        if ((string) $authenticatedTenantId === (string) $tenant->getKey()) {
            return $next($request);
        }

        // Fail closed without resolving the session's user ID against the
        // current tenant DB. Calling Auth::logout() here could load and mutate
        // an unrelated user that happens to have the same primary key.
        $request->session()->forget([
            $guard->getName(),
            self::TENANT_ID_KEY,
            'platform_support',
        ]);
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        Cookie::queue(Cookie::forget($guard->getRecallerName()));

        $request->session()->flash(
            'error',
            '別テナントのログイン情報を検出したため、再ログインが必要です。'
        );

        return redirect()->route('loginGet', [
            'tenant_prefix' => $tenant->prefix,
        ]);
    }
}
