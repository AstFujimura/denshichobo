<?php

namespace App\Models\Control;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMigrationRun extends ControlModel
{
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'target_migration',
        'status',
        'from_migration',
        'to_migration',
        'attempts',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
