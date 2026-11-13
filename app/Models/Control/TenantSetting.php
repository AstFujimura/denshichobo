<?php

namespace App\Models\Control;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSetting extends ControlModel
{
    protected $fillable = [
        'tenant_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
