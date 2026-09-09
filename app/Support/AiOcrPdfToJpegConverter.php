<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OCR 用: PDF を Ghostscript で JPEG 化する（Linux 本番は `gs` を想定）。
 */
class AiOcrPdfToJpegConverter
{
    /**
     * @return list<array{mime: string, path: string, base64: string, cleanup: bool}>
     */
    public static function toInlineImages(UploadedFile $file): array
    {
        $path = (string) $file->getRealPath();
        $mime = (string) ($file->getMimeType() ?? '');
        $isPdf = str_contains($mime, 'pdf')
            || strtolower((string) $file->getClientOriginalExtension()) === 'pdf';

        if (!$isPdf || !(bool) config('ai_ocr.pdf_to_image.enabled', true)) {
            return self::asInline($path, $mime !== '' ? $mime : 'application/octet-stream', false);
        }

        $jpegs = self::pdfToJpegPaths($path);
        if ($jpegs === []) {
            Log::warning('ai_ocr.pdf_to_jpeg.fallback_original', [
                'file' => $file->getClientOriginalName(),
            ]);

            return self::asInline($path, 'application/pdf', false);
        }

        $images = [];
        foreach ($jpegs as $jpeg) {
            $part = self::asInline($jpeg, 'image/jpeg', true);
            if ($part !== []) {
                $images[] = $part[0];
            }
        }

        Log::info('ai_ocr.pdf_to_jpeg.done', [
            'file' => $file->getClientOriginalName(),
            'pages' => count($images),
        ]);

        return $images;
    }

    /**
     * @param  list<array{path?: string, cleanup?: bool}>  $images
     */
    public static function cleanup(array $images): void
    {
        foreach ($images as $image) {
            if (!empty($image['cleanup']) && !empty($image['path']) && is_file($image['path'])) {
                @unlink($image['path']);
            }
        }
    }

    /**
     * @return list<array{mime: string, path: string, base64: string, cleanup: bool}>
     */
    private static function asInline(string $path, string $mime, bool $cleanup): array
    {
        $binary = @file_get_contents($path);
        if ($binary === false) {
            return [];
        }

        return [[
            'mime' => $mime,
            'path' => $path,
            'base64' => base64_encode($binary),
            'cleanup' => $cleanup,
        ]];
    }

    /**
     * @return list<string>
     */
    private static function pdfToJpegPaths(string $pdfPath): array
    {
        $gs = self::ghostscriptBinary();
        if ($gs === null) {
            Log::warning('ai_ocr.pdf_to_jpeg.gs_missing');

            return [];
        }

        $dir = storage_path('app/ai_ocr_tmp');
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return [];
        }

        $prefix = $dir . DIRECTORY_SEPARATOR . 'ocr_' . Str::uuid()->toString();
        $outputPattern = $prefix . '_%03d.jpg';
        $dpi = max(72, (int) config('ai_ocr.pdf_to_image.density', 200));
        $quality = max(40, min(100, (int) config('ai_ocr.pdf_to_image.quality', 90)));
        $maxPages = max(1, (int) config('ai_ocr.pdf_to_image.max_pages', 20));

        // 配列指定の proc_open なら Windows でも %03d が壊れない
        $cmd = [
            $gs,
            '-dSAFER',
            '-dBATCH',
            '-dNOPAUSE',
            '-dQUIET',
            '-sDEVICE=jpeg',
            '-dJPEGQ=' . $quality,
            '-r' . $dpi,
            '-sOutputFile=' . $outputPattern,
            $pdfPath,
        ];

        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($cmd, $descriptors, $pipes, null, null, ['bypass_shell' => true]);
        $stderr = '';
        $code = -1;
        if (is_resource($process)) {
            fclose($pipes[1]);
            $stderr = stream_get_contents($pipes[2]) ?: '';
            fclose($pipes[2]);
            $code = proc_close($process);
        }

        $files = glob($prefix . '_*.jpg') ?: [];
        $files = array_values(array_filter($files, 'is_file'));
        sort($files, SORT_NATURAL);

        if ($code !== 0 || $files === []) {
            Log::warning('ai_ocr.pdf_to_jpeg.gs_failed', [
                'code' => $code,
                'output' => mb_substr($stderr, 0, 300),
            ]);
            foreach ($files as $file) {
                @unlink($file);
            }

            return [];
        }

        return array_slice($files, 0, $maxPages);
    }

    private static function ghostscriptBinary(): ?string
    {
        $configured = trim((string) config('ai_ocr.pdf_to_image.gs_path', ''));
        if ($configured !== '') {
            return is_file($configured) || self::onPath($configured) ? $configured : null;
        }

        // Linux 本番: gs / ローカル Windows: gswin64c
        foreach (['gs', 'gswin64c'] as $bin) {
            if (self::onPath($bin)) {
                return $bin;
            }
        }

        return null;
    }

    private static function onPath(string $bin): bool
    {
        if (str_contains($bin, DIRECTORY_SEPARATOR) || str_contains($bin, '/')) {
            return is_file($bin);
        }

        $cmd = PHP_OS_FAMILY === 'Windows'
            ? 'where ' . escapeshellarg($bin)
            : 'command -v ' . escapeshellarg($bin);
        exec($cmd . ' 2>&1', $out, $code);

        return $code === 0 && $out !== [];
    }
}
