<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if ($request->is('platform') || $request->is('platform/*')) {
                return route('platform.login');
            }

            $prefix = $request->route('tenant_prefix') ?? tenant_prefix() ?? config('prefix.prefix');

            return route('loginGet', array_filter([
                'tenant_prefix' => $prefix ?: null,
            ]));
        }
    }
}
