<?php

namespace App\Support;

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

    public static function build(string $teisyutu): string
    {
        $base = (string) config('ai_ocr.ledger_prompt', '');
        $teisyutu = self::normalizeTeisyutu($teisyutu);

        $torihikisakiRule = $teisyutu === self::TEISYUTU_TEISHUTSU
            ? (string) config('ai_ocr.ledger_prompt_torihikisaki_teishutsu', '')
            : (string) config('ai_ocr.ledger_prompt_torihikisaki_jyuryo', '');

        if ($torihikisakiRule === '') {
            return $base;
        }

        return rtrim($base) . "\n\n" . trim($torihikisakiRule);
    }
}
