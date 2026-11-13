<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Control\Tenant;
use App\Support\Tenancy\SupportEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function enter(Request $request, Tenant $tenant, SupportEntryService $support)
    {
        $admin = Auth::guard('platform')->user();
        $targetUserId = $request->input('user_id');
        $targetUserId = $targetUserId !== null && $targetUserId !== '' ? (int) $targetUserId : null;

        try {
            $issued = $support->issueToken($tenant, $admin, $targetUserId, $request);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->away($issued['url']);
    }
}
