<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

interface TenantFileStorage
{
    /**
     * @param  string|resource|UploadedFile  $contents
     */
    public function put(string $logicalPath, string $ext, $contents): void;

    /**
     * @return string|resource
     */
    public function get(string $logicalPath, string $ext);

    public function exists(string $logicalPath, string $ext): bool;

    public function delete(string $logicalPath, string $ext): void;

    public function downloadResponse(string $logicalPath, string $ext, ?string $downloadName = null): Response;

    public function inlineResponse(string $logicalPath, string $ext, string $contentType): Response;

    /**
     * Cloud: signed PUT URL. On-prem: null (use server multipart upload).
     */
    public function temporaryUploadUrl(string $logicalPath, string $ext, int $ttlSeconds = 3600): ?string;

    /**
     * Cloud: signed GET URL. On-prem: null.
     */
    public function temporaryDownloadUrl(string $logicalPath, string $ext, int $ttlSeconds = 3600): ?string;

    /**
     * @return array{path:string,cleanup:bool}
     */
    public function materialize(string $logicalPath, string $ext): array;

    /**
     * @return 'cloud'|'onpre'
     */
    public function mode(): string;

    /**
     * Resolved object key / absolute filesystem path (for logs / debugging).
     */
    public function absoluteKey(string $logicalPath, string $ext): string;
}
