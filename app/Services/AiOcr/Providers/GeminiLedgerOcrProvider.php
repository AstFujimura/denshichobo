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
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 512,
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'hiduke' => ['type' => 'string'],
                        'kinngaku' => ['type' => 'number'],
                        'torihikisaki' => ['type' => 'string'],
                    ],
                    'required' => ['hiduke', 'kinngaku', 'torihikisaki'],
                ],
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

        $body = $resp->json();
        $text = $this->extractTextFromResponse($body);
        if ($text === null || $text === '') {
            Log::warning('ai_ocr.gemini.empty_text', ['step' => 'gemini.empty_response']);

            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: [
                    'ok' => false,
                    'body' => $body,
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
                'finish_reason' => data_get($body, 'candidates.0.finishReason'),
                'raw_preview' => mb_substr($text, 0, 200),
                'raw_length' => mb_strlen($text),
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

    private function extractTextFromResponse(array $body): ?string
    {
        $parts = data_get($body, 'candidates.0.content.parts', []);
        if (!is_array($parts)) {
            return null;
        }

        $texts = [];
        foreach ($parts as $part) {
            if (!is_array($part)) {
                continue;
            }

            $partText = $part['text'] ?? null;
            if (is_string($partText) && $partText !== '') {
                $texts[] = $partText;
            }
        }

        if ($texts === []) {
            return null;
        }

        return $this->normalizeJsonText(implode('', $texts));
    }

    private function normalizeJsonText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^```json\s*/i', '', $text) ?? $text;
        $text = preg_replace('/```\s*$/', '', $text) ?? $text;

        return trim($text);
    }
}
