<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('platform')->user();

        if (! $admin || ! $admin->is_active) {
            Auth::guard('platform')->logout();

            return redirect()->route('platform.login');
        }

        return $next($request);
    }
}
