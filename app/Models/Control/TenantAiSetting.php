<?php

namespace App\Models\Control;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAiSetting extends ControlModel
{
    protected $fillable = [
        'tenant_id',
        'ledger_ocr_provider',
        'gemini_model',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
