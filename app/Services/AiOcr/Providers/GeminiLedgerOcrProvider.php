<?php

namespace App\Services\AiOcr\Providers;

use App\Contracts\AiOcrLedgerProvider;
use App\Data\LedgerOcrResult;
use App\Support\GeminiApi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiLedgerOcrProvider implements AiOcrLedgerProvider
{
    public function ledgerOcr(UploadedFile $file, string $prompt): LedgerOcrResult
    {
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json',
            ],
        ];

        $url = GeminiApi::generateContentUrl();

        Log::info('ai_ocr.gemini.request', [
            'step' => 'gemini.requesting',
            'file_name' => $file->getClientOriginalName(),
            'mime' => $mimeType,
        ]);

        try {
            $resp = Http::timeout(60)->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('ai_ocr.gemini.exception', [
                'step' => 'gemini.connection_failed',
                'message' => $e->getMessage(),
            ]);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: ['ok' => false, 'exception' => $e->getMessage()],
                provider: 'gemini',
                step: 'gemini.connection_failed',
                error: 'Gemini API へ接続できません: ' . $e->getMessage(),
            );
        }

        if (!$resp->successful()) {
            Log::warning('ai_ocr.gemini.http_error', [
                'step' => 'gemini.api_error',
                'status' => $resp->status(),
            ]);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: [
                    'ok' => false,
                    'status' => $resp->status(),
                    'body' => $resp->json(),
                ],
                provider: 'gemini',
                step: 'gemini.api_error',
                error: 'Gemini API が HTTP ' . $resp->status() . ' を返しました',
            );
        }

        $text = data_get($resp->json(), 'candidates.0.content.parts.0.text');
        if (!is_string($text) || $text === '') {
            Log::warning('ai_ocr.gemini.empty_text', ['step' => 'gemini.empty_response']);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: [
                    'ok' => false,
                    'body' => $resp->json(),
                ],
                provider: 'gemini',
                step: 'gemini.empty_response',
                error: 'Gemini からテキスト応答が取得できませんでした',
            );
        }

        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            Log::warning('ai_ocr.gemini.json_decode_error', [
                'step' => 'gemini.json_decode_error',
                'raw_preview' => mb_substr($text, 0, 200),
            ]);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: [
                    'ok' => false,
                    'raw' => $text,
                ],
                provider: 'gemini',
                step: 'gemini.json_decode_error',
                error: 'Gemini の応答を JSON として解釈できませんでした',
            );
        }

        $result = new LedgerOcrResult(
            hiduke: isset($decoded['hiduke']) ? (string) $decoded['hiduke'] : null,
            kinngaku: isset($decoded['kinngaku']) ? (string) $decoded['kinngaku'] : null,
            torihikisaki: isset($decoded['torihikisaki']) ? (string) $decoded['torihikisaki'] : null,
            raw: $decoded,
            provider: 'gemini',
            step: 'gemini.completed',
            error: null,
        );

        if (!$result->hasAnyField()) {
            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: $decoded,
                provider: 'gemini',
                step: 'gemini.empty_result',
                error: 'Gemini は応答したが hiduke/kinngaku/torihikisaki がすべて空です',
            );
        }

        return $result;
    }
}
