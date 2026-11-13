<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureTenantSessionIsolation;
use App\Models\Control\PlatformAdmin;
use App\Models\Control\Tenant;
use App\Support\Tenancy\SupportEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportAccessController extends Controller
{
    public function callback(Request $request, SupportEntryService $support)
    {
        $plain = (string) $request->query('token', '');
        $tenant = tenant();
        if (! $tenant || $plain === '') {
            abort(404);
        }

        try {
            $result = $support->consumeToken($plain, $tenant);
        } catch (\Throwable $e) {
            abort(403, $e->getMessage());
        }

        Auth::guard('web')->login($result['user']);
        $request->session()->regenerate();
        $request->session()->put(
            EnsureTenantSessionIsolation::TENANT_ID_KEY,
            $tenant->getKey()
        );

        $request->session()->put('platform_support', [
            'active' => true,
            'mode' => $result['mode'],
            'platform_admin_id' => $result['admin']->id,
            'platform_admin_name' => $result['admin']->display_name ?: $result['admin']->name,
            'tenant_id' => $tenant->id,
            'target_user_id' => $result['target_user_id'],
            'started_at' => now()->toDateTimeString(),
        ]);

        if (feature_enabled('card') && ! feature_enabled('tameru')) {
            return redirect()->route('cardviewget');
        }

        return redirect()->route('topGet');
    }

    public function exit(Request $request, SupportEntryService $support)
    {
        $state = $request->session()->get('platform_support', []);
        $tenantId = $state['tenant_id'] ?? tenant()?->id;
        $adminId = $state['platform_admin_id'] ?? null;
        $targetUserId = $state['target_user_id'] ?? null;

        $admin = $adminId ? PlatformAdmin::query()->find($adminId) : null;
        $tenant = $tenantId ? Tenant::query()->find($tenantId) : tenant();

        $support->audit(
            $admin,
            $tenant,
            ($state['mode'] ?? '') === 'l2' ? 'impersonate_end' : 'tenant_exit',
            $targetUserId,
            $request
        );

        Auth::guard('web')->logout();
        $request->session()->forget([
            EnsureTenantSessionIsolation::TENANT_ID_KEY,
            'platform_support',
        ]);

        if ($tenant) {
            return redirect()->route('platform.tenants.show', $tenant);
        }

        return redirect()->route('platform.tenants.index');
    }
}
