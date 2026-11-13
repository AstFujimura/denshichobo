<?php

namespace App\Models\Control;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAuditLog extends ControlModel
{
    public $timestamps = false;

    protected $fillable = [
        'platform_admin_id',
        'tenant_id',
        'action',
        'target_user_id',
        'ip',
        'user_agent',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function platformAdmin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
