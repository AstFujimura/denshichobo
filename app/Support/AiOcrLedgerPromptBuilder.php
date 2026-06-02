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
        $template = (string) config('ai_ocr.ledger_prompt', '');
        if ($template === '') {
            return '';
        }

        $teisyutu = self::normalizeTeisyutu($teisyutu);
        $torihikisakiLine = $teisyutu === self::TEISYUTU_TEISHUTSU
            ? (string) config('ai_ocr.torihikisaki_line_teishutsu', '')
            : (string) config('ai_ocr.torihikisaki_line_jyuryo', '');

        return str_replace('{torihikisaki_line}', $torihikisakiLine, $template);
    }
}
