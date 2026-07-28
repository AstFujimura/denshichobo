<?php

namespace App\Support;

use App\Data\LedgerOcrResult;

final class AiOcrKinngakuTaxConverter
{
    public const BASIS_INCLUDED = 'included';

    public const BASIS_EXCLUDED = 'excluded';

    public const BASIS_UNKNOWN = 'unknown';

    public static function consumptionTaxRate(): float
    {
        $rate = (float) config('ai_ocr.consumption_tax_rate', 0.10);

        return $rate > 0 ? $rate : 0.10;
    }

    public static function toTaxIncludedAmount(int $taxExcludedAmount): int
    {
        if ($taxExcludedAmount === 0) {
            return 0;
        }

        $rate = self::consumptionTaxRate();
        $sign = $taxExcludedAmount < 0 ? -1 : 1;
        $abs = abs($taxExcludedAmount);

        return $sign * (int) round($abs * (1 + $rate));
    }

    public static function toTaxExcludedAmount(int $taxIncludedAmount): int
    {
        if ($taxIncludedAmount === 0) {
            return 0;
        }

        $rate = self::consumptionTaxRate();
        $sign = $taxIncludedAmount < 0 ? -1 : 1;
        $abs = abs($taxIncludedAmount);

        return $sign * (int) round($abs / (1 + $rate));
    }

    /**
     * 書類管理の税区分設定に合わせ、kinngaku / 内訳を正規化する。
     *
     * @param  bool  $targetTaxIncluded  true=金額欄は税込、false=税抜
     */
    public static function normalizeResultForTargetTaxMode(LedgerOcrResult $result, bool $targetTaxIncluded): LedgerOcrResult
    {
        $basis = self::resolveTaxBasis($result->raw);
        $kinngaku = self::normalizeAmountString($result->kinngaku, $basis, $targetTaxIncluded);

        $breakdown = [];
        foreach ($result->kinngakuBreakdown as $item) {
            if (!is_array($item)) {
                continue;
            }
            $amount = isset($item['amount']) && is_numeric($item['amount'])
                ? (int) $item['amount']
                : 0;
            $breakdown[] = [
                'amount' => self::convertAmount($amount, $basis, $targetTaxIncluded),
                'label' => (string) ($item['label'] ?? ''),
            ];
        }

        return new LedgerOcrResult(
            hiduke: $result->hiduke,
            kinngaku: $kinngaku,
            torihikisaki: $result->torihikisaki,
            raw: $result->raw,
            provider: $result->provider,
            step: $result->step,
            error: $result->error,
            kinngakuBreakdown: $breakdown,
        );
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    public static function resolveTaxBasis(array $raw): string
    {
        $value = $raw['kinngaku_tax_basis'] ?? null;
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['included', 'zeikomi', 'tax_included', '税込'], true)) {
                return self::BASIS_INCLUDED;
            }
            if (in_array($normalized, ['excluded', 'zeinuki', 'tax_excluded', '税抜'], true)) {
                return self::BASIS_EXCLUDED;
            }
        }

        if (array_key_exists('kinngaku_is_tax_included', $raw)) {
            return !empty($raw['kinngaku_is_tax_included'])
                ? self::BASIS_INCLUDED
                : self::BASIS_EXCLUDED;
        }

        return self::BASIS_UNKNOWN;
    }

    public static function convertAmount(int $amount, string $basis, bool $targetTaxIncluded): int
    {
        if ($amount === 0 || $basis === self::BASIS_UNKNOWN) {
            return $amount;
        }

        if ($targetTaxIncluded && $basis === self::BASIS_EXCLUDED) {
            return self::toTaxIncludedAmount($amount);
        }

        if (!$targetTaxIncluded && $basis === self::BASIS_INCLUDED) {
            return self::toTaxExcludedAmount($amount);
        }

        return $amount;
    }

    private static function normalizeAmountString(?string $value, string $basis, bool $targetTaxIncluded): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        $normalized = preg_replace('/[,\s￥¥]/u', '', $value);
        $normalized = preg_replace('/[^\d.-]/', '', $normalized ?? '');
        if ($normalized === '' || !is_numeric($normalized)) {
            return $value;
        }

        $amount = (int) round((float) $normalized);

        return (string) self::convertAmount($amount, $basis, $targetTaxIncluded);
    }
}
