<?php

namespace App\Http\Controllers;

use App\Contracts\AiOcrLedgerProvider;
use App\Support\AiOcrLedgerPromptBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AiOcrController extends Controller
{
    public function __construct(private readonly AiOcrLedgerProvider $provider)
    {
    }

    public function ledger(Request $request): JsonResponse
    {
        $traceId = (string) Str::uuid();
        $providerName = (string) config('ai_ocr.provider', 'gemini');
        $trace = (bool) config('ai_ocr.trace', false);

        $maxFileKb = (int) config('ai_ocr.max_file_kb', 20480);
        $request->validate([
            'file' => ['required', 'file', 'max:' . $maxFileKb],
            'teisyutu' => ['nullable', 'string', Rule::in([
                AiOcrLedgerPromptBuilder::TEISYUTU_JYURYO,
                AiOcrLedgerPromptBuilder::TEISYUTU_TEISHUTSU,
            ])],
        ]);

        $file = $request->file('file');
        $teisyutu = AiOcrLedgerPromptBuilder::normalizeTeisyutu($request->input('teisyutu'));

        Log::info('ai_ocr.ledger.start', [
            'trace_id' => $traceId,
            'step' => 'controller.validated',
            'provider' => $providerName,
            'teisyutu' => $teisyutu,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        $prompt = AiOcrLedgerPromptBuilder::build($teisyutu);
        if ($prompt === '') {
            Log::warning('ai_ocr.ledger.failed', [
                'trace_id' => $traceId,
                'step' => 'controller.prompt_missing',
            ]);

            return response()->json([
                'ok' => false,
                'step' => 'controller.prompt_missing',
                'error' => 'AI OCR prompt is not configured',
                'trace_id' => $traceId,
                'provider' => $providerName,
            ], 500);
        }

        $result = $this->provider->ledgerOcr($file, $prompt);
        $data = $result->toArray();
        $ok = $result->hasAnyField();

        Log::info('ai_ocr.ledger.done', [
            'trace_id' => $traceId,
            'step' => $result->step,
            'ok' => $ok,
            'provider' => $result->provider ?? $providerName,
            'teisyutu' => $teisyutu,
            'error' => $result->error,
            'has_hiduke' => $data['hiduke'] !== null,
            'has_kinngaku' => $data['kinngaku'] !== null,
            'has_torihikisaki' => $data['torihikisaki'] !== null,
        ]);

        $payload = [
            'ok' => $ok,
            'step' => $result->step,
            'error' => $result->error,
            'trace_id' => $traceId,
            'teisyutu' => $teisyutu,
            'prompt' => $prompt,
            'data' => $data,
            'provider' => $result->provider ?? $providerName,
        ];

        if ($trace || !$ok) {
            $payload['raw'] = $result->raw;
        }

        return response()->json($payload);
    }
}
