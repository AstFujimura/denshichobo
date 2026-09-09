<?php

namespace App\Services\AiOcr\Providers;

use App\Contracts\AiOcrLedgerProvider;
use App\Data\LedgerOcrResult;
use App\Support\AiOcrKinngakuBreakdownNormalizer;
use App\Support\GeminiApi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiLedgerOcrProvider implements AiOcrLedgerProvider
{
    public function ledgerOcr(UploadedFile $file, string $prompt, array $options = []): LedgerOcrResult
    {
        $sumAmounts = !empty($options['sum_amounts']);
        $maxTokens = max(256, (int) config('ai_ocr.gemini.max_output_tokens', 2048));
        $retryTokens = max($maxTokens, (int) config('ai_ocr.gemini.max_output_tokens_retry', 8192));
        $tokenLimits = array_values(array_unique([$maxTokens, $retryTokens]));

        $lastResult = null;

        foreach ($tokenLimits as $attemptIndex => $maxOutputTokens) {
            if ($attemptIndex > 0) {
                Log::info('ai_ocr.gemini.retry_max_tokens', [
                    'step' => 'gemini.retry_max_tokens',
                    'attempt' => $attemptIndex + 1,
                    'max_output_tokens' => $maxOutputTokens,
                ]);
            }

            $httpResult = $this->requestGemini($file, $prompt, $maxOutputTokens, $sumAmounts, $options);
            if ($httpResult instanceof LedgerOcrResult) {
                return $httpResult;
            }

            [$body, $text, $finishReason] = $httpResult;
            $decoded = json_decode($text, true);

            if (is_array($decoded)) {
                return $this->buildSuccessResult($decoded, $sumAmounts);
            }

            $lastResult = $this->buildJsonDecodeErrorResult($text, $body, $finishReason, $maxOutputTokens);

            if ($finishReason !== 'MAX_TOKENS' || $attemptIndex >= count($tokenLimits) - 1) {
                return $lastResult;
            }
        }

        return $lastResult ?? new LedgerOcrResult(
            hiduke: null,
            kinngaku: null,
            torihikisaki: null,
            raw: ['ok' => false],
            provider: 'gemini',
            step: 'gemini.json_decode_error',
            error: 'Gemini の応答を JSON として解釈できませんでした',
        );
    }

    /**
     * @param  array<string, mixed>  $options
     * @return LedgerOcrResult|array{0: array, 1: string, 2: ?string}
     */
    private function requestGemini(
        UploadedFile $file,
        string $prompt,
        int $maxOutputTokens,
        bool $sumAmounts,
        array $options = [],
    ): LedgerOcrResult|array {
        $inlineImages = $options['inline_images'] ?? null;
        $parts = [['text' => $prompt]];

        if (is_array($inlineImages) && $inlineImages !== []) {
            foreach ($inlineImages as $image) {
                if (empty($image['mime']) || empty($image['base64'])) {
                    continue;
                }
                $parts[] = [
                    'inlineData' => [
                        'mimeType' => $image['mime'],
                        'data' => $image['base64'],
                    ],
                ];
            }
            $mimeType = (string) ($inlineImages[0]['mime'] ?? 'image/jpeg');
            $inlineCount = count($inlineImages);
        } else {
            $mimeType = $file->getMimeType() ?? 'application/octet-stream';
            $binary = file_get_contents($file->getRealPath());
            if ($binary === false) {
                return new LedgerOcrResult(
                    hiduke: null,
                    kinngaku: null,
                    torihikisaki: null,
                    raw: ['ok' => false],
                    provider: 'gemini',
                    step: 'gemini.file_read_failed',
                    error: 'OCR 用ファイルの読み込みに失敗しました',
                );
            }
            $parts[] = [
                'inlineData' => [
                    'mimeType' => $mimeType,
                    'data' => base64_encode($binary),
                ],
            ];
            $inlineCount = 1;
        }

        if (count($parts) < 2) {
            return new LedgerOcrResult(
                hiduke: null,
                kinngaku: null,
                torihikisaki: null,
                raw: ['ok' => false],
                provider: 'gemini',
                step: 'gemini.file_read_failed',
                error: 'OCR 用ファイルの読み込みに失敗しました',
            );
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => $this->buildGenerationConfig($maxOutputTokens, $sumAmounts),
        ];

        $url = GeminiApi::generateContentUrl();

        Log::info('ai_ocr.gemini.request', [
            'step' => 'gemini.requesting',
            'file_name' => $file->getClientOriginalName(),
            'mime' => $mimeType,
            'inline_parts' => $inlineCount,
            'max_output_tokens' => $maxOutputTokens,
            'thinking_budget' => (int) config('ai_ocr.gemini.thinking_budget', 0),
        ]);

        try {
            $resp = Http::timeout(90)->post($url, $payload);
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

        $finishReason = data_get($body, 'candidates.0.finishReason');

        return [$body, $text, is_string($finishReason) ? $finishReason : null];
    }

    private function buildGenerationConfig(int $maxOutputTokens, bool $sumAmounts): array
    {
        $properties = [
            'hiduke' => ['type' => 'string'],
            'kinngaku' => ['type' => 'number'],
            'kinngaku_tax_basis' => [
                'type' => 'string',
                'enum' => ['included', 'excluded'],
            ],
            'torihikisaki' => ['type' => 'string'],
        ];
        $required = ['hiduke', 'kinngaku', 'kinngaku_tax_basis', 'torihikisaki'];

        if ($sumAmounts) {
            $properties['kinngaku_items'] = [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'amount' => ['type' => 'number'],
                        'label' => ['type' => 'string'],
                    ],
                    'required' => ['amount'],
                ],
            ];
            $required[] = 'kinngaku_items';
        }

        $config = [
            'temperature' => 0.2,
            'maxOutputTokens' => $maxOutputTokens,
            'responseMimeType' => 'application/json',
            'responseSchema' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
            ],
        ];

        $thinkingBudget = (int) config('ai_ocr.gemini.thinking_budget', 0);
        if ($thinkingBudget >= 0) {
            $config['thinkingConfig'] = [
                'thinkingBudget' => $thinkingBudget,
            ];
        }

        return $config;
    }

    private function buildSuccessResult(array $decoded, bool $sumAmounts): LedgerOcrResult
    {
        $breakdown = $sumAmounts ? AiOcrKinngakuBreakdownNormalizer::fromDecoded($decoded) : [];

        $result = new LedgerOcrResult(
            hiduke: LedgerOcrResult::nullableString($decoded['hiduke'] ?? null),
            kinngaku: LedgerOcrResult::nullableString($decoded['kinngaku'] ?? null),
            torihikisaki: LedgerOcrResult::nullableString($decoded['torihikisaki'] ?? null),
            raw: $decoded,
            provider: 'gemini',
            step: 'gemini.completed',
            error: null,
            kinngakuBreakdown: $breakdown,
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

    private function buildJsonDecodeErrorResult(
        string $text,
        array $body,
        ?string $finishReason,
        int $maxOutputTokens,
    ): LedgerOcrResult {
        Log::warning('ai_ocr.gemini.json_decode_error', [
            'step' => 'gemini.json_decode_error',
            'finish_reason' => $finishReason,
            'max_output_tokens' => $maxOutputTokens,
            'raw_preview' => mb_substr($text, 0, 200),
            'raw_length' => mb_strlen($text),
        ]);

        $error = 'Gemini の応答を JSON として解釈できませんでした';
        if ($finishReason === 'MAX_TOKENS') {
            $error = 'Gemini の出力がトークン上限（MAX_TOKENS）で途中切断されました。'
                . ' config/ai_ocr.php の gemini.max_output_tokens を確認してください。';
        }

        return new LedgerOcrResult(
            hiduke: null,
            kinngaku: null,
            torihikisaki: null,
            raw: [
                'ok' => false,
                'raw' => $text,
                'finish_reason' => $finishReason,
                'max_output_tokens' => $maxOutputTokens,
            ],
            provider: 'gemini',
            step: 'gemini.json_decode_error',
            error: $error,
        );
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
