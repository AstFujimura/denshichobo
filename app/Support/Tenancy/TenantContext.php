<?php

namespace App\Support\Tenancy;

use App\Models\Control\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    /** @var array<string, bool> */
    protected array $features = [];

    public function set(Tenant $tenant, array $features = []): void
    {
        $this->tenant = $tenant;
        $this->features = $features;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->features = [];
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function featureEnabled(string $featureKey): bool
    {
        if (array_key_exists($featureKey, $this->features)) {
            return $this->features[$featureKey];
        }

        if ($this->tenant) {
            return $this->tenant->featureEnabled($featureKey);
        }

        return (bool) (config("tenancy.features.{$featureKey}.default") ?? false);
    }
}
