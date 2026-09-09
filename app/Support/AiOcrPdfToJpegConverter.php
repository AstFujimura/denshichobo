<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OCR 向けに PDF を JPEG へラスタライズする。
 * ImageMagick (magick) 優先、失敗時は Ghostscript。
 */
class AiOcrPdfToJpegConverter
{
    /**
     * @return list<array{mime: string, path: string, base64: string, cleanup: bool}>
     */
    public static function toInlineImages(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $mime = (string) ($file->getMimeType() ?? '');
        $ext = strtolower((string) $file->getClientOriginalExtension());

        $isPdf = str_contains($mime, 'pdf') || $ext === 'pdf';
        if (!$isPdf || !self::enabled()) {
            $binary = file_get_contents($path);
            if ($binary === false) {
                return [];
            }

            return [[
                'mime' => $mime !== '' ? $mime : 'application/octet-stream',
                'path' => $path,
                'base64' => base64_encode($binary),
                'cleanup' => false,
            ]];
        }

        $converted = self::convertPdfToJpegFiles($path);
        if ($converted === []) {
            Log::warning('ai_ocr.pdf_to_jpeg.fallback_original', [
                'file' => $file->getClientOriginalName(),
            ]);
            $binary = file_get_contents($path);
            if ($binary === false) {
                return [];
            }

            return [[
                'mime' => 'application/pdf',
                'path' => $path,
                'base64' => base64_encode($binary),
                'cleanup' => false,
            ]];
        }

        $images = [];
        foreach ($converted as $jpegPath) {
            $binary = file_get_contents($jpegPath);
            if ($binary === false) {
                @unlink($jpegPath);
                continue;
            }
            $images[] = [
                'mime' => 'image/jpeg',
                'path' => $jpegPath,
                'base64' => base64_encode($binary),
                'cleanup' => true,
            ];
        }

        Log::info('ai_ocr.pdf_to_jpeg.done', [
            'file' => $file->getClientOriginalName(),
            'pages' => count($images),
        ]);

        return $images;
    }

    /**
     * @param  list<array{path: string, cleanup?: bool}>  $images
     */
    public static function cleanup(array $images): void
    {
        foreach ($images as $image) {
            if (empty($image['cleanup']) || empty($image['path'])) {
                continue;
            }
            if (is_file($image['path'])) {
                @unlink($image['path']);
            }
        }
    }

    public static function enabled(): bool
    {
        return (bool) config('ai_ocr.pdf_to_image.enabled', true);
    }

    /**
     * @return list<string> JPEG ファイルパス
     */
    private static function convertPdfToJpegFiles(string $pdfPath): array
    {
        $dir = storage_path('app/ai_ocr_tmp');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $prefix = $dir . DIRECTORY_SEPARATOR . 'ocr_' . Str::uuid()->toString();
        $density = max(72, (int) config('ai_ocr.pdf_to_image.density', 200));
        $quality = max(40, min(100, (int) config('ai_ocr.pdf_to_image.quality', 90)));
        $maxPages = max(1, (int) config('ai_ocr.pdf_to_image.max_pages', 20));

        $files = self::convertWithMagick($pdfPath, $prefix, $density, $quality);
        if ($files === []) {
            $files = self::convertWithGhostscript($pdfPath, $prefix, $density, $quality);
        }

        if ($files === []) {
            return [];
        }

        sort($files, SORT_NATURAL);

        return array_slice($files, 0, $maxPages);
    }

    /**
     * @return list<string>
     */
    private static function convertWithMagick(string $pdfPath, string $prefix, int $density, int $quality): array
    {
        $magick = self::resolveMagickPath();
        if ($magick === null) {
            return [];
        }

        $pattern = $prefix . '_%03d.jpg';
        $cmd = escapeshellarg($magick)
            . ' -density ' . $density
            . ' ' . escapeshellarg($pdfPath)
            . ' -quality ' . $quality
            . ' -background white -alpha remove'
            . ' ' . escapeshellarg($pattern);

        $output = [];
        $code = 0;
        exec($cmd . ' 2>&1', $output, $code);

        $files = self::globConverted($prefix);
        if ($code !== 0 || $files === []) {
            Log::warning('ai_ocr.pdf_to_jpeg.magick_failed', [
                'code' => $code,
                'output' => mb_substr(implode("\n", $output), 0, 500),
            ]);
            self::cleanupGlob($prefix);

            return [];
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    private static function convertWithGhostscript(string $pdfPath, string $prefix, int $density, int $quality): array
    {
        $gs = self::resolveGhostscriptPath();
        if ($gs === null) {
            return [];
        }

        $pattern = $prefix . '_%03d.jpg';
        $cmd = escapeshellarg($gs)
            . ' -dSAFER -dBATCH -dNOPAUSE -dQUIET'
            . ' -sDEVICE=jpeg'
            . ' -dJPEGQ=' . $quality
            . ' -r' . $density
            . ' -sOutputFile=' . escapeshellarg($pattern)
            . ' ' . escapeshellarg($pdfPath);

        $output = [];
        $code = 0;
        exec($cmd . ' 2>&1', $output, $code);

        $files = self::globConverted($prefix);
        if ($code !== 0 || $files === []) {
            Log::warning('ai_ocr.pdf_to_jpeg.gs_failed', [
                'code' => $code,
                'output' => mb_substr(implode("\n", $output), 0, 500),
            ]);
            self::cleanupGlob($prefix);

            return [];
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    private static function globConverted(string $prefix): array
    {
        $matched = glob($prefix . '_*.jpg') ?: [];

        return array_values(array_filter($matched, 'is_file'));
    }

    private static function cleanupGlob(string $prefix): void
    {
        foreach (self::globConverted($prefix) as $file) {
            @unlink($file);
        }
    }

    private static function resolveMagickPath(): ?string
    {
        $configured = trim((string) config('ai_ocr.pdf_to_image.magick_path', ''));
        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        $candidates = [
            'C:\\xampp\\ImageMagick-7.1.0-18-vc15-x64\\bin\\magick.exe',
            'C:\\Program Files\\ImageMagick-7.1.1-Q16-HDRI\\magick.exe',
            'magick',
        ];
        foreach ($candidates as $candidate) {
            if ($candidate === 'magick') {
                return self::commandExists('magick') ? 'magick' : null;
            }
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function resolveGhostscriptPath(): ?string
    {
        $configured = trim((string) config('ai_ocr.pdf_to_image.gs_path', ''));
        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        $candidates = [
            'C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe',
            'C:\\Program Files\\gs\\gs10.03.1\\bin\\gswin64c.exe',
            'gswin64c',
            'gs',
        ];
        foreach ($candidates as $candidate) {
            if (in_array($candidate, ['gswin64c', 'gs'], true)) {
                if (self::commandExists($candidate)) {
                    return $candidate;
                }
                continue;
            }
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function commandExists(string $command): bool
    {
        $check = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'where ' . escapeshellarg($command)
            : 'command -v ' . escapeshellarg($command);
        exec($check . ' 2>NUL', $output, $code);

        return $code === 0 && $output !== [];
    }
}
