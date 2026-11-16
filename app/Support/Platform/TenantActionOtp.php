<?php

namespace App\Support\Platform;

use App\Models\Control\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class TenantActionOtp
{
    public function send(int $adminId, string $email, string $purpose, ?int $tenantId, ?Tenant $tenant = null): void
    {
        $cooldownKey = $this->cooldownKey($adminId);
        if (Cache::has($cooldownKey)) {
            throw ValidationException::withMessages([
                'local' => '確認コードを連続では送信できません。しばらく待ってからもう一度送信してください。',
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $action = match ($purpose) {
            'destroy' => $tenant
                ? 'テナントの削除（'.$tenant->name.' / '.$tenant->code.'）'
                : 'テナントの削除',
            'support' => $tenant
                ? 'サポート入場（'.$tenant->name.' / '.$tenant->code.'）'
                : 'サポート入場',
            default => 'テナントの追加',
        };

        Mail::raw(
            "電子帳簿管理画面の確認コードです。\n\n"
            ."操作: {$action}\n"
            ."確認コード: {$code}\n\n"
            ."このコードは10分間有効です。身に覚えがない場合は破棄してください。\n",
            function ($message) use ($email) {
                $message->to($email)->subject('【電子帳簿管理画面】確認コード');
            }
        );

        Cache::put($this->challengeKey($adminId, $purpose, $tenantId), [
            'hash' => $this->hash($code),
            'attempts' => 0,
        ], now()->addSeconds((int) config('tenancy.otp.ttl_seconds', 600)));
        Cache::put($cooldownKey, 1, now()->addSeconds((int) config('tenancy.otp.resend_seconds', 30)));
    }

    public function consume(int $adminId, string $code, string $purpose, ?int $tenantId): void
    {
        $key = $this->challengeKey($adminId, $purpose, $tenantId);
        $challenge = Cache::get($key);
        if (! is_array($challenge) || empty($challenge['hash'])) {
            throw ValidationException::withMessages([
                'otp_code' => '確認コードを送信してから入力してください。有効期限が切れている場合はもう一度送信してください。',
            ]);
        }

        $attempts = (int) ($challenge['attempts'] ?? 0);
        $maxAttempts = (int) config('tenancy.otp.max_attempts', 5);
        if ($attempts >= $maxAttempts) {
            Cache::forget($key);
            throw ValidationException::withMessages([
                'otp_code' => '確認コードの入力回数が上限を超えました。もう一度送信してください。',
            ]);
        }

        $given = $this->hash($code);
        if (! hash_equals((string) $challenge['hash'], $given)) {
            $challenge['attempts'] = $attempts + 1;
            Cache::put($key, $challenge, now()->addSeconds((int) config('tenancy.otp.ttl_seconds', 600)));
            throw ValidationException::withMessages([
                'otp_code' => '確認コードが違います。',
            ]);
        }

        Cache::forget($key);
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    private function challengeKey(int $adminId, string $purpose, ?int $tenantId): string
    {
        return 'platform-otp:'.$adminId.':'.$purpose.':'.($tenantId ?? 0);
    }

    private function cooldownKey(int $adminId): string
    {
        return 'platform-otp-cooldown:'.$adminId;
    }
}
