<?php

namespace App\Services\AiOcr\Providers;

use App\Contracts\AiOcrLedgerProvider;
use App\Data\LedgerOcrResult;
use App\Support\AiOcrKinngakuBreakdownNormalizer;
use App\Support\AiOcrPdfToJpegConverter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InternalHttpLedgerOcrProvider implements AiOcrLedgerProvider
{
    private function normalizeBaseUrl(string $baseUrl): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        if ($baseUrl === '') {
            return '';
        }
        if (!preg_match('#^https?://#i', $baseUrl)) {
            $baseUrl = 'http://' . $baseUrl;
        }

        return $baseUrl;
    }

    private function formatHttpErrorMessage(int $status, mixed $body): string
    {
        $message = '社内 OCR API が HTTP ' . $status . ' を返しました';

        if (is_array($body)) {
            if (isset($body['detail'])) {
                $detail = $body['detail'];
                if (is_string($detail)) {
                    return $message . ': ' . $detail;
                }
                if (is_array($detail)) {
                    $parts = [];
                    foreach ($detail as $item) {
                        if (is_array($item) && isset($item['msg'])) {
                            $parts[] = (string) $item['msg'];
                        }
                    }
                    if ($parts !== []) {
                        return $message . ': ' . implode(' / ', $parts);
                    }
                }
            }
            if (isset($body['error']) && is_string($body['error'])) {
                return $message . ': ' . $body['error'];
            }
        } elseif (is_string($body) && $body !== '') {
            return $message . ': ' . mb_substr($body, 0, 200);
        }

        return $message;
    }

    public function ledgerOcr(UploadedFile $file, string $prompt, array $options = []): LedgerOcrResult
    {
        $sumAmounts = !empty($options['sum_amounts']);
        $baseUrl = $this->normalizeBaseUrl((string) config('ai_ocr.internal.base_url', ''));
        $path = (string) config('ai_ocr.internal.ledger_path', '/ocr/ledger');
        $token = (string) config('ai_ocr.internal.token', '');
        $timeout = (int) config('ai_ocr.internal.timeout_seconds', 60);

        if ($baseUrl === '') {
            Log::warning('ai_ocr.internal.config_missing');

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: ['ok' => false],
                provider: 'internal',
                step: 'internal.config_missing',
                error: 'INTERNAL_AI_OCR_BASE_URL が未設定です',
            );
        }

        $url = $baseUrl . $path;

        Log::info('ai_ocr.internal.request', [
            'step' => 'internal.requesting',
            'url' => $url,
            'timeout' => $timeout,
            'file_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $req = Http::timeout($timeout);
        if ($token !== '') {
            $req = $req->withToken($token);
        }

        $images = AiOcrPdfToJpegConverter::toInlineImages($file);
        if ($images === []) {
            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: ['ok' => false],
                provider: 'internal',
                step: 'internal.file_read_failed',
                error: 'OCR 用ファイルの読み込みに失敗しました',
            );
        }

        $primary = $images[0];
        $attachName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.jpg';
        if (($primary['mime'] ?? '') === 'application/pdf') {
            $attachName = $file->getClientOriginalName();
        }

        try {
            $resp = $req->attach(
                name: 'file',
                contents: base64_decode($primary['base64'], true) ?: file_get_contents($primary['path']),
                filename: $attachName,
                headers: ['Content-Type' => $primary['mime']],
            )->post($url, [
                'prompt' => $prompt,
                'task' => 'ledger',
            ]);
        } catch (\Throwable $e) {
            AiOcrPdfToJpegConverter::cleanup($images);
            Log::error('ai_ocr.internal.exception', [
                'step' => 'internal.connection_failed',
                'message' => $e->getMessage(),
            ]);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: ['ok' => false, 'exception' => $e->getMessage()],
                provider: 'internal',
                step: 'internal.connection_failed',
                error: '社内 OCR サーバーへ接続できません: ' . $e->getMessage(),
            );
        }

        AiOcrPdfToJpegConverter::cleanup($images);

        if (!$resp->successful()) {
            $body = $resp->json() ?? $resp->body();
            Log::warning('ai_ocr.internal.http_error', [
                'step' => 'internal.http_error',
                'status' => $resp->status(),
                'body' => $body,
            ]);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: [
                    'ok' => false,
                    'status' => $resp->status(),
                    'body' => $body,
                ],
                provider: 'internal',
                step: 'internal.http_error',
                error: $this->formatHttpErrorMessage($resp->status(), $body),
            );
        }

        $json = $resp->json();
        if (!is_array($json)) {
            Log::warning('ai_ocr.internal.invalid_json', [
                'step' => 'internal.invalid_response_json',
                'body_preview' => mb_substr((string) $resp->body(), 0, 200),
            ]);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: [
                    'ok' => false,
                    'body' => $resp->body(),
                ],
                provider: 'internal',
                step: 'internal.invalid_response_json',
                error: '社内 OCR API のレスポンスが JSON ではありません',
            );
        }

        $breakdown = $sumAmounts ? AiOcrKinngakuBreakdownNormalizer::fromDecoded($json) : [];

        $result = new LedgerOcrResult(
            hiduke: isset($json['hiduke']) ? (string) $json['hiduke'] : null,
            kinngaku: isset($json['kinngaku']) ? (string) $json['kinngaku'] : null,
            torihikisaki: isset($json['torihikisaki']) ? (string) $json['torihikisaki'] : null,
            raw: $json,
            provider: 'internal',
            step: 'internal.completed',
            error: null,
            kinngakuBreakdown: $breakdown,
        );

        if (!$result->hasAnyField()) {
            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: $json,
                provider: 'internal',
                step: 'internal.empty_result',
                error: '社内 OCR は成功したが hiduke/kinngaku/torihikisaki がすべて空です',
            );
        }

        return $result;
    }
}
