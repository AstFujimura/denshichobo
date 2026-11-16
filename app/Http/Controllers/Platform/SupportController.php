<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Control\Tenant;
use App\Support\Platform\TenantActionOtp;
use App\Support\Tenancy\SupportEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SupportController extends Controller
{
    public function enter(Request $request, Tenant $tenant, SupportEntryService $support, TenantActionOtp $otp)
    {
        $admin = Auth::guard('platform')->user();
        $targetUserId = $request->input('user_id');
        $targetUserId = $targetUserId !== null && $targetUserId !== '' ? (int) $targetUserId : null;

        if ($tenant->status !== Tenant::STATUS_ACTIVE) {
            return back()->with('error', '利用中のテナントだけ入れます。');
        }

        if ($targetUserId !== null && ! $admin->can_impersonate) {
            return back()->with('error', '顧客ユーザーとして開く権限がありません。');
        }

        try {
            $request->validate([
                'otp_code' => ['required', 'digits:6'],
            ], [
                'otp_code.required' => '確認コードを入力してください。',
                'otp_code.digits' => '確認コードは6桁の数字です。',
            ]);
            $otp->consume((int) $admin->id, (string) $request->input('otp_code'), 'support', $tenant->id);
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('otp_modal', 'support');
        }

        try {
            $issued = $support->issueToken($tenant, $admin, $targetUserId, $request);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->away($issued['url']);
    }
}
