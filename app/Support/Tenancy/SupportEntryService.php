<?php

namespace App\Support\Tenancy;

use App\Models\Control\PlatformAdmin;
use App\Models\Control\PlatformAuditLog;
use App\Models\Control\PlatformSupportToken;
use App\Models\Control\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class SupportEntryService
{
    public function __construct(protected TenantDatabaseManager $databaseManager)
    {
    }

    public function supportUserName(): string
    {
        return (string) config('tenancy.support_user_name', 'platform_support');
    }

    /**
     * @return array{plain: string, url: string, token: PlatformSupportToken}
     */
    public function issueToken(
        Tenant $tenant,
        PlatformAdmin $admin,
        ?int $targetUserId,
        Request $request,
        int $ttlSeconds = 180
    ): array {
        if ($tenant->status !== Tenant::STATUS_ACTIVE) {
            throw new RuntimeException('Tenant is not active.');
        }

        if ($targetUserId !== null && ! $admin->can_impersonate) {
            throw new RuntimeException('Impersonation is not allowed for this admin.');
        }

        if ($targetUserId !== null) {
            $this->usingTenant($tenant, function () use ($targetUserId) {
                $exists = User::query()
                    ->where('id', $targetUserId)
                    ->where('削除', '')
                    ->where('name', '!=', $this->supportUserName())
                    ->exists();
                if (! $exists) {
                    throw new RuntimeException('Target user not found.');
                }
            });
        }

        $plain = Str::random(64);
        $token = PlatformSupportToken::query()->create([
            'token_hash' => hash('sha256', $plain),
            'tenant_id' => $tenant->id,
            'platform_admin_id' => $admin->id,
            'target_user_id' => $targetUserId,
            'expires_at' => now()->addSeconds($ttlSeconds),
            'used_at' => null,
        ]);

        $this->audit($admin, $tenant, $targetUserId ? 'impersonate_start' : 'tenant_enter', $targetUserId, $request);

        $url = url('/' . $tenant->prefix . '/support/callback?token=' . urlencode($plain));

        return [
            'plain' => $plain,
            'url' => $url,
            'token' => $token,
        ];
    }

    /**
     * Assumes current request already connected to the tenant DB (tenant.resolve).
     *
     * @return array{admin: PlatformAdmin, user: User, mode: string, target_user_id: ?int}
     */
    public function consumeToken(string $plain, Tenant $tenant): array
    {
        $hash = hash('sha256', $plain);
        $token = PlatformSupportToken::query()
            ->where('token_hash', $hash)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (! $token) {
            throw new RuntimeException('Invalid support token.');
        }
        if ($token->used_at) {
            throw new RuntimeException('Support token already used.');
        }
        if ($token->expires_at->isPast()) {
            throw new RuntimeException('Support token expired.');
        }

        $token->forceFill(['used_at' => now()])->save();

        $admin = PlatformAdmin::query()->find($token->platform_admin_id);
        if (! $admin || ! $admin->is_active) {
            throw new RuntimeException('Platform admin is inactive.');
        }

        $user = $token->target_user_id
            ? $this->findTargetUser($token->target_user_id)
            : $this->ensureSupportUserConnected();

        return [
            'admin' => $admin,
            'user' => $user,
            'mode' => $token->target_user_id ? 'l2' : 'l1',
            'target_user_id' => $token->target_user_id,
        ];
    }

    public function ensureSupportUser(Tenant $tenant): User
    {
        return $this->usingTenant($tenant, function () {
            return $this->ensureSupportUserConnected();
        });
    }

    public function listTenantUsers(Tenant $tenant, int $limit = 200)
    {
        return $this->usingTenant($tenant, function () use ($limit) {
            return User::query()
                ->where('削除', '')
                ->where('name', '!=', $this->supportUserName())
                ->orderBy('id')
                ->limit($limit)
                ->get(['id', 'name', '表示名', '管理', 'email']);
        });
    }

    public function audit(
        ?PlatformAdmin $admin,
        ?Tenant $tenant,
        string $action,
        ?int $targetUserId,
        Request $request,
        array $meta = []
    ): void {
        PlatformAuditLog::query()->create([
            'platform_admin_id' => $admin?->id,
            'tenant_id' => $tenant?->id,
            'action' => $action,
            'target_user_id' => $targetUserId,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }

    protected function ensureSupportUserConnected(): User
    {
        $name = $this->supportUserName();
        $user = User::query()->whereRaw('BINARY name = ?', [$name])->first();
        if ($user) {
            if (($user->削除 ?? '') !== '') {
                $user->削除 = '';
                $user->save();
            }

            return $user;
        }

        $user = new User();
        $user->name = $name;
        $user->表示名 = (string) config('tenancy.support_user_display_name', 'サポート（platform）');
        $user->email = $name . '@platform.invalid';
        $user->password = Hash::make(Str::random(40));
        $user->管理 = '管理';
        $user->削除 = '';
        $user->save();

        return $user;
    }

    protected function findTargetUser(int $targetUserId): User
    {
        $user = User::query()
            ->where('id', $targetUserId)
            ->where('削除', '')
            ->where('name', '!=', $this->supportUserName())
            ->first();

        if (! $user) {
            throw new RuntimeException('Target user not found.');
        }

        return $user;
    }

    /**
     * @template T
     * @param  callable(): T  $callback
     * @return T
     */
    protected function usingTenant(Tenant $tenant, callable $callback)
    {
        $this->databaseManager->connect($tenant, true);
        try {
            return $callback();
        } finally {
            $this->databaseManager->disconnect(true);
        }
    }
}
