<?php

namespace App\Support;

use App\Models\Document;

class AiOcrLedgerPromptBuilder
{
    public const TEISYUTU_JYURYO = '受領';

    public const TEISYUTU_TEISHUTSU = '提出';

    public static function normalizeTeisyutu(?string $teisyutu): string
    {
        $teisyutu = trim((string) $teisyutu);

        return $teisyutu === self::TEISYUTU_TEISHUTSU
            ? self::TEISYUTU_TEISHUTSU
            : self::TEISYUTU_JYURYO;
    }

    /**
     * @param  int|null  $documentId  書類区分（documents.id）。OCR 設定を反映する。
     * @param  bool|null  $sumAmounts  null のとき書類マスタの設定
     * @param  bool|null  $taxIncluded  null のとき書類マスタの設定
     */
    public static function build(
        string $teisyutu,
        ?int $documentId = null,
        ?bool $sumAmounts = null,
        ?bool $taxIncluded = null,
    ): string {
        $template = (string) config('ai_ocr.ledger_prompt', '');
        if ($template === '') {
            return '';
        }

        $teisyutu = self::normalizeTeisyutu($teisyutu);
        $document = self::findDocument($documentId);

        $contextBlock = self::buildContextBlock($teisyutu, $document);

        $sumAmounts = $sumAmounts ?? ($document ? $document->ocrSumAmountsEnabled() : false);
        $taxIncluded = $taxIncluded ?? ($document ? $document->ocrTaxIncluded() : false);

        if ($sumAmounts) {
            $sumContextKey = $taxIncluded ? 'sum_amounts_context_included' : 'sum_amounts_context_excluded';
            $sumContext = trim((string) config('ai_ocr.' . $sumContextKey, ''));
            if ($sumContext === '') {
                $sumContext = trim((string) config('ai_ocr.sum_amounts_context', ''));
            }
            if ($sumContext !== '') {
                $contextBlock = trim($contextBlock . "\n" . $sumContext);
            }
            $jsonFormat = trim((string) config('ai_ocr.sum_amounts_json_format', ''));
            if ($jsonFormat !== '') {
                $contextBlock = trim($contextBlock . "\n" . $jsonFormat);
            }
            $outputExample = trim((string) config('ai_ocr.sum_amounts_output_example', ''));
            if ($outputExample !== '') {
                $contextBlock = trim($contextBlock . "\n" . $outputExample);
            }
        }

        $taxContextKey = $taxIncluded ? 'tax_context_included_target' : 'tax_context_excluded_target';
        $taxContext = trim((string) config('ai_ocr.' . $taxContextKey, ''));
        if ($taxContext !== '') {
            $contextBlock = trim($contextBlock . "\n" . $taxContext);
        }

        $torihikisakiLine = $teisyutu === self::TEISYUTU_TEISHUTSU
            ? (string) config('ai_ocr.torihikisaki_line_teishutsu', '')
            : (string) config('ai_ocr.torihikisaki_line_jyuryo', '');

        $kinngakuLine = self::resolveKinngakuLine($sumAmounts, $taxIncluded);

        return str_replace(
            ['{context_block}', '{torihikisaki_line}', '{kinngaku_line}'],
            [$contextBlock, $torihikisakiLine, $kinngakuLine],
            $template
        );
    }

    public static function resolveSumAmounts(?int $documentId): bool
    {
        $document = self::findDocument($documentId);

        return $document ? $document->ocrSumAmountsEnabled() : false;
    }

    public static function resolveTaxIncluded(?int $documentId): bool
    {
        $document = self::findDocument($documentId);

        return $document ? $document->ocrTaxIncluded() : false;
    }

    private static function resolveKinngakuLine(bool $sumAmounts, bool $taxIncluded): string
    {
        if ($sumAmounts) {
            $key = $taxIncluded ? 'kinngaku_line_sum_amounts_included' : 'kinngaku_line_sum_amounts_excluded';
            $line = (string) config('ai_ocr.' . $key, '');
            if ($line !== '') {
                return $line;
            }

            return (string) config('ai_ocr.kinngaku_line_sum_amounts', '');
        }

        $key = $taxIncluded ? 'kinngaku_line_default_included' : 'kinngaku_line_default_excluded';
        $line = (string) config('ai_ocr.' . $key, '');
        if ($line !== '') {
            return $line;
        }

        return (string) config('ai_ocr.kinngaku_line_default', '');
    }

    private static function findDocument(?int $documentId): ?Document
    {
        if (!$documentId || $documentId <= 0) {
            return null;
        }

        return Document::find($documentId);
    }

    private static function buildContextBlock(string $teisyutu, ?Document $document): string
    {
        $documentName = $document ? trim((string) $document->書類) : '';
        $documentName = str_replace(["\r", "\n"], '', $documentName);

        if ($documentName !== '') {
            $documentLine = str_replace(
                '{document_name}',
                $documentName,
                (string) config('ai_ocr.document_context_named', '')
            );
        } else {
            $documentLine = (string) config('ai_ocr.document_context_generic', '');
        }

        $teisyutuLine = $teisyutu === self::TEISYUTU_TEISHUTSU
            ? (string) config('ai_ocr.teisyutu_context_teishutsu', '')
            : (string) config('ai_ocr.teisyutu_context_jyuryo', '');

        return trim($documentLine . "\n" . $teisyutuLine);
    }
}
