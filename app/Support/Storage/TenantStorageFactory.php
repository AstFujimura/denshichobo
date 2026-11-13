<?php

namespace App\Support\Storage;

use App\Contracts\TenantFileStorage;
use App\Models\Control\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TenantStorageFactory
{
    public function __construct(protected TenantContext $context)
    {
    }

    public function forCurrent(): TenantFileStorage
    {
        $tenant = $this->context->tenant();
        if (! $tenant) {
            throw new RuntimeException('No tenant is bound in the current context.');
        }

        return $this->for($tenant);
    }

    public function for(Tenant $tenant): TenantFileStorage
    {
        $mode = strtolower((string) $tenant->storage_mode);
        $prefix = (string) ($tenant->storage_prefix ?: $tenant->prefix);
        $flat = $this->isFlatLayout($tenant);

        if ($mode === 'onpre') {
            $root = (string) ($tenant->file_upload_path ?: config('custom.file_upload_path'));

            return new LocalPathStorage($root, $prefix, $flat);
        }

        if ($mode !== 'cloud') {
            throw new RuntimeException('Unsupported storage_mode: ' . $tenant->storage_mode);
        }

        $diskName = (string) config('tenancy.storage.cloud_disk', 's3');

        return new CloudObjectStorage(Storage::disk($diskName), $prefix, $diskName);
    }

    protected function isFlatLayout(Tenant $tenant): bool
    {
        $default = (bool) config('tenancy.storage.onpre_flat_default', true);

        try {
            $raw = $tenant->settings()
                ->where('key', 'storage_flat')
                ->value('value');
        } catch (\Throwable $e) {
            return $default;
        }

        if ($raw === null) {
            return $default;
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }
}
