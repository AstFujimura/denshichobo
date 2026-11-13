<?php

namespace App\Support\Storage;

use App\Contracts\TenantFileStorage;
use App\Support\Storage\Concerns\ResolvesTenantObjectPath;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class CloudObjectStorage implements TenantFileStorage
{
    use ResolvesTenantObjectPath;

    public function __construct(
        protected Filesystem $disk,
        string $storagePrefix,
        protected string $diskName = 's3'
    ) {
        $this->storagePrefix = $storagePrefix;
    }

    public function put(string $logicalPath, string $ext, $contents): void
    {
        $key = $this->preferredKey($logicalPath, $ext);

        if ($contents instanceof UploadedFile) {
            $stream = fopen($contents->getRealPath(), 'r');
            if ($stream === false) {
                throw new RuntimeException('Unable to read uploaded file.');
            }
            $this->disk->put($key, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            return;
        }

        $this->disk->put($key, $contents);
    }

    public function get(string $logicalPath, string $ext)
    {
        $key = $this->resolveExistingKey($logicalPath, $ext);

        return $this->disk->get($key);
    }

    public function exists(string $logicalPath, string $ext): bool
    {
        return $this->resolveExistingKey($logicalPath, $ext) !== null;
    }

    public function delete(string $logicalPath, string $ext): void
    {
        $key = $this->resolveExistingKey($logicalPath, $ext);
        if ($key !== null) {
            $this->disk->delete($key);
        }
    }

    public function downloadResponse(string $logicalPath, string $ext, ?string $downloadName = null): Response
    {
        $key = $this->resolveExistingKey($logicalPath, $ext);
        if ($key === null) {
            abort(404);
        }

        $filename = $downloadName ?: basename($key);
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response($this->disk->get($key), 200, $headers);
    }

    public function inlineResponse(string $logicalPath, string $ext, string $contentType): Response
    {
        $key = $this->resolveExistingKey($logicalPath, $ext);
        if ($key === null) {
            abort(404);
        }

        return response($this->disk->get($key), 200, [
            'Content-Type' => $contentType,
        ]);
    }

    public function temporaryUploadUrl(string $logicalPath, string $ext, int $ttlSeconds = 3600): ?string
    {
        $key = $this->preferredKey($logicalPath, $ext);
        $expiration = now()->addSeconds(max(60, $ttlSeconds));

        /** @var \Illuminate\Filesystem\FilesystemAdapter $adapter */
        $adapter = Storage::disk($this->diskName);
        $result = $adapter->temporaryUploadUrl($key, $expiration);

        if (is_array($result)) {
            return isset($result['url']) ? (string) $result['url'] : null;
        }

        return $result ? (string) $result : null;
    }

    public function temporaryDownloadUrl(string $logicalPath, string $ext, int $ttlSeconds = 3600): ?string
    {
        $key = $this->resolveExistingKey($logicalPath, $ext);
        if ($key === null) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $adapter */
        $adapter = Storage::disk($this->diskName);

        return (string) $adapter->temporaryUrl($key, now()->addSeconds(max(60, $ttlSeconds)));
    }

    public function materialize(string $logicalPath, string $ext): array
    {
        $key = $this->resolveExistingKey($logicalPath, $ext);
        if ($key === null) {
            throw new RuntimeException('File not found.');
        }

        $suffix = pathinfo($key, PATHINFO_EXTENSION);
        $path = tempnam(sys_get_temp_dir(), 'tenant-storage-');
        if ($path === false) {
            throw new RuntimeException('Unable to create temporary file.');
        }

        if ($suffix !== '') {
            $renamedPath = $path . '.' . $suffix;
            if (! rename($path, $renamedPath)) {
                @unlink($path);
                throw new RuntimeException('Unable to prepare temporary file.');
            }
            $path = $renamedPath;
        }

        try {
            $written = file_put_contents($path, $this->disk->get($key));
        } catch (\Throwable $e) {
            @unlink($path);
            throw $e;
        }

        if ($written === false) {
            @unlink($path);
            throw new RuntimeException('Unable to materialize stored file.');
        }

        return ['path' => $path, 'cleanup' => true];
    }

    public function mode(): string
    {
        return 'cloud';
    }

    public function absoluteKey(string $logicalPath, string $ext): string
    {
        return $this->resolveExistingKey($logicalPath, $ext) ?? $this->preferredKey($logicalPath, $ext);
    }

    protected function preferredKey(string $logicalPath, string $ext): string
    {
        return $this->buildRelativeKey($logicalPath, $ext);
    }

    protected function resolveExistingKey(string $logicalPath, string $ext): ?string
    {
        $preferred = $this->preferredKey($logicalPath, $ext);
        if ($this->disk->exists($preferred)) {
            return $preferred;
        }

        $legacy = $this->withExtension($this->normalizeLogicalPath($logicalPath), $ext);
        if ($legacy !== $preferred && $this->disk->exists($legacy)) {
            return $legacy;
        }

        return null;
    }
}
