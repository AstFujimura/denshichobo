<?php

namespace App\Http\Controllers;

use App\Contracts\AiOcrLedgerProvider;
use App\Data\LedgerOcrResult;
use App\Support\AiOcrKinngakuTaxConverter;
use App\Support\AiOcrLedgerPromptBuilder;
use App\Support\AiOcrPdfToJpegConverter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
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
            'syorui' => ['nullable', 'integer', 'exists:documents,id'],
            'ocr_sum_amounts' => ['nullable', 'boolean'],
            'ocr_tax_included' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $teisyutu = AiOcrLedgerPromptBuilder::normalizeTeisyutu($request->input('teisyutu'));
        $documentId = $request->filled('syorui') ? (int) $request->input('syorui') : null;
        $sumAmountsOcr = $request->has('ocr_sum_amounts')
            ? $request->boolean('ocr_sum_amounts')
            : AiOcrLedgerPromptBuilder::resolveSumAmounts($documentId);
        $taxIncludedOcr = $request->has('ocr_tax_included')
            ? $request->boolean('ocr_tax_included')
            : AiOcrLedgerPromptBuilder::resolveTaxIncluded($documentId);

        Log::info('ai_ocr.ledger.start', [
            'trace_id' => $traceId,
            'step' => 'controller.validated',
            'provider' => $providerName,
            'teisyutu' => $teisyutu,
            'document_id' => $documentId,
            'ocr_sum_amounts' => $sumAmountsOcr,
            'ocr_tax_included' => $taxIncludedOcr,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        $prompt = AiOcrLedgerPromptBuilder::build($teisyutu, $documentId, $sumAmountsOcr, $taxIncludedOcr);
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

        $result = $this->provider->ledgerOcr($file, $prompt, [
            'sum_amounts' => $sumAmountsOcr,
        ]);

        // PDF で欠落があるときだけ JPEG 再試行し、足りない項目だけ埋める
        $result = $this->retryWithJpegIfNeeded(
            $file,
            $prompt,
            $result,
            $sumAmountsOcr,
            $traceId,
        );

        if ($result->hasAnyField()) {
            $result = AiOcrKinngakuTaxConverter::normalizeResultForTargetTaxMode(
                $result,
                $taxIncludedOcr,
            );
        }

        $data = $result->toArray();
        $ok = $result->hasAnyField();

        Log::info('ai_ocr.ledger.done', [
            'trace_id' => $traceId,
            'step' => $result->step,
            'ok' => $ok,
            'provider' => $result->provider ?? $providerName,
            'teisyutu' => $teisyutu,
            'document_id' => $documentId,
            'ocr_sum_amounts' => $sumAmountsOcr,
            'ocr_tax_included' => $taxIncludedOcr,
            'error' => $result->error,
            'has_hiduke' => filled($data['hiduke']),
            'has_kinngaku' => filled($data['kinngaku']),
            'has_torihikisaki' => filled($data['torihikisaki']),
            'kinngaku_breakdown_count' => count($data['kinngaku_breakdown'] ?? []),
        ]);

        $payload = [
            'ok' => $ok,
            'step' => $result->step,
            'error' => $result->error,
            'trace_id' => $traceId,
            'teisyutu' => $teisyutu,
            'document_id' => $documentId,
            'ocr_sum_amounts' => $sumAmountsOcr,
            'ocr_tax_included' => $taxIncludedOcr,
            'prompt' => $prompt,
            'data' => $data,
            'provider' => $result->provider ?? $providerName,
        ];

        if ($trace || !$ok) {
            $payload['raw'] = $result->raw;
        }

        return response()->json($payload);
    }

    private function retryWithJpegIfNeeded(
        UploadedFile $file,
        string $prompt,
        LedgerOcrResult $primary,
        bool $sumAmounts,
        string $traceId,
    ): LedgerOcrResult {
        if (!AiOcrPdfToJpegConverter::retryEnabled()) {
            return $primary;
        }
        if (!AiOcrPdfToJpegConverter::isPdf($file)) {
            return $primary;
        }
        if (!$primary->hasMissingCoreFields()) {
            return $primary;
        }

        $maxPages = $sumAmounts
            ? max(1, (int) config('ai_ocr.pdf_to_image.max_pages', 3))
            : 1;

        Log::info('ai_ocr.ledger.jpeg_retry', [
            'trace_id' => $traceId,
            'step' => 'controller.jpeg_retry',
            'missing_hiduke' => !$primary->hiduke,
            'missing_kinngaku' => !$primary->kinngaku,
            'missing_torihikisaki' => !$primary->torihikisaki,
            'max_pages' => $maxPages,
        ]);

        $images = AiOcrPdfToJpegConverter::toInlineImages($file, ['max_pages' => $maxPages]);
        if ($images === []) {
            Log::warning('ai_ocr.ledger.jpeg_retry_skipped', [
                'trace_id' => $traceId,
                'reason' => 'convert_failed',
            ]);

            return $primary;
        }

        try {
            $retry = $this->provider->ledgerOcr($file, $prompt, [
                'sum_amounts' => $sumAmounts,
                'inline_images' => $images,
            ]);

            if (!$retry->hasAnyField()) {
                return $primary;
            }

            return LedgerOcrResult::mergePreferPrimary($primary, $retry);
        } finally {
            AiOcrPdfToJpegConverter::cleanup($images);
        }
    }
}
