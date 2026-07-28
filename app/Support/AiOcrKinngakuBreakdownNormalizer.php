<?php

namespace App\Support;

final class AiOcrKinngakuBreakdownNormalizer
{
    /**
     * @return list<array{amount: int, label: string}>
     */
    public static function fromDecoded(array $decoded): array
    {
        $items = $decoded['kinngaku_items'] ?? null;
        if (!is_array($items) || $items === []) {
            return [];
        }

        $items = array_values($items);
        $out = [];

        foreach ($items as $item) {
            $parsed = self::parseItem($item);
            if ($parsed === null) {
                continue;
            }

            if ($parsed['label'] === '') {
                $parsed['label'] = '項目' . (count($out) + 1);
            }

            $out[] = $parsed;
        }

        return self::reconcileWithKinngaku($out, $decoded['kinngaku'] ?? null);
    }

    /**
     * @return array{amount: int, label: string}|null
     */
    private static function parseItem(mixed $item): ?array
    {
        $amount = null;
        $label = '';

        if (is_numeric($item)) {
            $amount = (float) $item;
        } elseif (is_array($item)) {
            $rawAmount = $item['amount'] ?? $item['kinngaku'] ?? $item['kingaku'] ?? null;
            if ($rawAmount !== null && is_numeric($rawAmount)) {
                $amount = (float) $rawAmount;
            }
            $label = trim((string) ($item['label'] ?? $item['memo'] ?? $item['page'] ?? ''));
        }

        if ($amount === null || abs($amount) < 0.0001) {
            return null;
        }

        if ($label === '') {
            return [
                'amount' => (int) round($amount),
                'label' => '',
            ];
        }

        return [
            'amount' => (int) round($amount),
            'label' => $label,
        ];
    }

    /**
     * @param  list<array{amount: int, label: string}>  $breakdown
     * @return list<array{amount: int, label: string}>
     */
    private static function reconcileWithKinngaku(array $breakdown, mixed $kinngakuRaw): array
    {
        if ($breakdown === []) {
            return [];
        }

        $expected = self::parseAmount($kinngakuRaw);
        if ($expected === null) {
            return $breakdown;
        }

        $sum = array_sum(array_column($breakdown, 'amount'));
        if ($sum === $expected) {
            return $breakdown;
        }

        $diff = $expected - $sum;

        array_unshift($breakdown, [
            'amount' => (int) round($diff),
            'label' => self::missingFirstPageLabel($breakdown),
        ]);

        return $breakdown;
    }

    /**
     * @param  list<array{amount: int, label: string}>  $breakdown
     */
    private static function missingFirstPageLabel(array $breakdown): string
    {
        foreach ($breakdown as $row) {
            if (preg_match('/1\s*[枚页ページ]/u', $row['label'])) {
                return '不足分（1枚目相当）';
            }
        }

        if (self::labelsSuggestMissingFirstPage($breakdown)) {
            return '1枚目';
        }

        return '1枚目（OCR内訳に未記載の金額）';
    }

    /**
     * @param  list<array{amount: int, label: string}>  $breakdown
     */
    private static function labelsSuggestMissingFirstPage(array $breakdown): bool
    {
        $hasFirst = false;
        $hasSecondOrLater = false;

        foreach ($breakdown as $row) {
            $label = $row['label'];
            if (preg_match('/(?:^|[^\d])1\s*[枚页ページ]/u', $label)) {
                $hasFirst = true;
            }
            if (preg_match('/[2-9]\s*[枚页ページ]/u', $label)) {
                $hasSecondOrLater = true;
            }
        }

        return $hasSecondOrLater && !$hasFirst;
    }

    private static function parseAmount(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        $str = preg_replace('/[,\s￥¥]/u', '', (string) $value);
        $str = preg_replace('/[^\d.-]/', '', $str ?? '');
        if ($str === '' || !is_numeric($str)) {
            return null;
        }

        return (int) round((float) $str);
    }
}
