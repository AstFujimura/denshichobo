<?php

namespace App\Support\Storage;

use App\Contracts\TenantFileStorage;
use App\Support\Storage\Concerns\ResolvesTenantObjectPath;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class LocalPathStorage implements TenantFileStorage
{
    use ResolvesTenantObjectPath;

    public function __construct(
        protected string $uploadRoot,
        string $storagePrefix,
        protected bool $flat = true
    ) {
        $this->storagePrefix = $storagePrefix;
        $this->uploadRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uploadRoot), DIRECTORY_SEPARATOR);

        if ($this->uploadRoot === '') {
            throw new RuntimeException('file_upload_path is not configured for on-prem storage.');
        }
    }

    public function put(string $logicalPath, string $ext, $contents): void
    {
        $path = $this->preferredPath($logicalPath, $ext);
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('Unable to create storage directory: ' . $dir);
        }

        if ($contents instanceof UploadedFile) {
            if (! @copy($contents->getRealPath(), $path)) {
                throw new RuntimeException('Unable to store uploaded file.');
            }

            return;
        }

        if (is_resource($contents)) {
            $target = fopen($path, 'w');
            if ($target === false) {
                throw new RuntimeException('Unable to open storage path for writing.');
            }
            stream_copy_to_stream($contents, $target);
            fclose($target);

            return;
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Unable to write storage file.');
        }
    }

    public function get(string $logicalPath, string $ext)
    {
        $path = $this->resolveExistingPath($logicalPath, $ext);
        if ($path === null) {
            throw new RuntimeException('File not found.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read storage file.');
        }

        return $contents;
    }

    public function exists(string $logicalPath, string $ext): bool
    {
        return $this->resolveExistingPath($logicalPath, $ext) !== null;
    }

    public function delete(string $logicalPath, string $ext): void
    {
        $path = $this->resolveExistingPath($logicalPath, $ext);
        if ($path !== null && is_file($path)) {
            @unlink($path);
        }
    }

    public function downloadResponse(string $logicalPath, string $ext, ?string $downloadName = null): Response
    {
        $path = $this->resolveExistingPath($logicalPath, $ext);
        if ($path === null) {
            abort(404);
        }

        return response()->download($path, $downloadName);
    }

    public function inlineResponse(string $logicalPath, string $ext, string $contentType): Response
    {
        $path = $this->resolveExistingPath($logicalPath, $ext);
        if ($path === null) {
            abort(404);
        }

        return response()->file($path, ['Content-Type' => $contentType]);
    }

    public function temporaryUploadUrl(string $logicalPath, string $ext, int $ttlSeconds = 3600): ?string
    {
        return null;
    }

    public function temporaryDownloadUrl(string $logicalPath, string $ext, int $ttlSeconds = 3600): ?string
    {
        return null;
    }

    public function materialize(string $logicalPath, string $ext): array
    {
        $path = $this->resolveExistingPath($logicalPath, $ext);
        if ($path === null) {
            throw new RuntimeException('File not found.');
        }

        return ['path' => $path, 'cleanup' => false];
    }

    public function mode(): string
    {
        return 'onpre';
    }

    public function absoluteKey(string $logicalPath, string $ext): string
    {
        return $this->resolveExistingPath($logicalPath, $ext) ?? $this->preferredPath($logicalPath, $ext);
    }

    protected function preferredPath(string $logicalPath, string $ext): string
    {
        return $this->toAbsolutePath($this->relativeUnderRoot($logicalPath, $ext, $this->flat));
    }

    protected function resolveExistingPath(string $logicalPath, string $ext): ?string
    {
        // Prefer configured layout, then fall back for legacy paths.
        $candidates = $this->flat
            ? [
                $this->toAbsolutePath($this->relativeUnderRoot($logicalPath, $ext, true)),
                $this->toAbsolutePath($this->relativeUnderRoot($logicalPath, $ext, false)),
            ]
            : [
                $this->toAbsolutePath($this->relativeUnderRoot($logicalPath, $ext, false)),
                $this->toAbsolutePath($this->relativeUnderRoot($logicalPath, $ext, true)),
            ];

        foreach (array_values(array_unique($candidates)) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function relativeUnderRoot(string $logicalPath, string $ext, bool $flat): string
    {
        $path = $this->normalizeLogicalPath($logicalPath);
        $prefix = trim(str_replace('\\', '/', $this->storagePrefix), '/');

        if ($prefix !== '' && str_starts_with($path, $prefix . '/')) {
            $path = substr($path, strlen($prefix) + 1);
        } elseif ($prefix !== '' && $path === $prefix) {
            $path = '';
        }

        if (! $flat && $prefix !== '') {
            $path = $path === '' ? $prefix : ($prefix . '/' . $path);
        }

        return $this->withExtension($path, $ext);
    }

    protected function toAbsolutePath(string $relative): string
    {
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relative, '/\\'));

        return $this->uploadRoot . DIRECTORY_SEPARATOR . $relative;
    }
}
