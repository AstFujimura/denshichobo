<?php

namespace App\Support\Storage\Concerns;

trait ResolvesTenantObjectPath
{
    protected string $storagePrefix;

    protected function normalizeLogicalPath(string $logicalPath): string
    {
        $path = str_replace('\\', '/', trim($logicalPath));

        return ltrim($path, '/');
    }

    protected function withExtension(string $path, string $ext): string
    {
        $ext = ltrim(trim($ext), '.');
        if ($ext === '') {
            return $path;
        }

        $suffix = '.' . $ext;
        if (str_ends_with(strtolower($path), strtolower($suffix))) {
            return $path;
        }

        return $path . $suffix;
    }

    /**
     * Build preferred object key / relative path under the tenant root.
     * Accepts legacy DB values that already include storage_prefix.
     */
    protected function buildRelativeKey(string $logicalPath, string $ext): string
    {
        $path = $this->normalizeLogicalPath($logicalPath);
        $prefix = trim(str_replace('\\', '/', $this->storagePrefix), '/');

        if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix . '/'))) {
            return $this->withExtension($path, $ext);
        }

        $relative = $prefix !== '' ? ($prefix . '/' . $path) : $path;

        return $this->withExtension($relative, $ext);
    }

    /**
     * Logical path to persist in DB for new writes (no storage_prefix).
     */
    public function logicalPathForDb(string $logicalPath): string
    {
        $path = $this->normalizeLogicalPath($logicalPath);
        $prefix = trim(str_replace('\\', '/', $this->storagePrefix), '/');

        if ($prefix !== '' && str_starts_with($path, $prefix . '/')) {
            return substr($path, strlen($prefix) + 1);
        }

        if ($prefix !== '' && $path === $prefix) {
            return '';
        }

        return $path;
    }
}
