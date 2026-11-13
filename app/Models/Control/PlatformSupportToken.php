<?php

namespace App\Models\Control;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformSupportToken extends ControlModel
{
    protected $fillable = [
        'token_hash',
        'tenant_id',
        'platform_admin_id',
        'target_user_id',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function platformAdmin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class);
    }
}
