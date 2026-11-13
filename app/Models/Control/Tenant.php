<?php

namespace App\Models\Control;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends ControlModel
{
    public const STATUS_PROVISIONING = 'provisioning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'code',
        'name',
        'prefix',
        'db_database',
        'status',
        'storage_mode',
        'storage_prefix',
        'file_upload_path',
        'notes',
        'provisioned_at',
        'suspended_at',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function features(): HasMany
    {
        return $this->hasMany(TenantFeature::class);
    }

    public function aiSettings(): HasOne
    {
        return $this->hasOne(TenantAiSetting::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function migrationRuns(): HasMany
    {
        return $this->hasMany(TenantMigrationRun::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function featureEnabled(string $featureKey): bool
    {
        $feature = $this->relationLoaded('features')
            ? $this->features->firstWhere('feature_key', $featureKey)
            : $this->features()->where('feature_key', $featureKey)->first();

        if ($feature) {
            return (bool) $feature->enabled;
        }

        $defaults = config('tenancy.features', []);

        return (bool) ($defaults[$featureKey]['default'] ?? false);
    }
}
