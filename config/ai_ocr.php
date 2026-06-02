<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Provider 選択
    |--------------------------------------------------------------------------
    |
    | 現状は gemini。将来 internal に切り替えるだけで投げ先を変更できるようにします。
    |
    */
    // TAMERU（帳簿保存）専用の切替。名刺管理など他機能には影響させない。
    'provider' => env('TAMERU_AI_OCR_PROVIDER', env('AI_OCR_PROVIDER', 'gemini')),

    /*
    |--------------------------------------------------------------------------
    | 社内AIサーバー設定（将来用）
    |--------------------------------------------------------------------------
    |
    */
    'internal' => [
        'base_url' => env('INTERNAL_AI_OCR_BASE_URL', ''),
        'ledger_path' => env('INTERNAL_AI_OCR_LEDGER_PATH', '/ocr/ledger'),
        'token' => env('INTERNAL_AI_OCR_TOKEN', ''),
        'timeout_seconds' => env('INTERNAL_AI_OCR_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | 帳簿保存/変更: OCR 抽出プロンプト
    |--------------------------------------------------------------------------
    |
    | {torihikisaki_line} は受領・提出の選択に応じて AiOcrLedgerPromptBuilder が差し替えます。
    | vLLM（ローカルLLM）向けに構造を明確化しています。
    |
    */
    'ledger_prompt' => <<<'PROMPT'
# 指示
添付された画像（請求書・領収書等）から、以下の3項目のみを抽出してJSON形式で出力してください。解説やMarkdownの装飾（```json 等）は一切不要です。必ずJSONオブジェクトのみを返してください。

# 抽出項目
1. hiduke: 取引日（形式: YYYY/MM/DD）
2. kinngaku: 税込合計金額（数値のみ、カンマや通貨記号は除外）
3. torihikisaki: {torihikisaki_line}

# 出力フォーマット
{"hiduke":"YYYY/MM/DD","kinngaku":12345,"torihikisaki":"..."}
PROMPT,

    // 【受領した書類用】取引先 ＝ 「書類の発行元（請求元）」
    'torihikisaki_line_jyuryo' => '取引先名（必ず「書類の発行元・請求元・店舗名」の会社名を抽出してください。※宛名側にある自社名は絶対に抽出しないでください）',
    
    // 【提出する書類用】取引先 ＝ 「提出先（宛名・請求先）」
    'torihikisaki_line_teishutsu' => '取引先名（必ず「宛名・請求先・提出先（御中や様が付いている側）」の会社名を抽出してください。敬称は除外。※差出人側にある自社名は絶対に抽出しないでください）',

    // アップロード上限（KB）。Laravel の validation max は KB 単位。
    'max_file_kb' => 20480,

    // true: laravel.log に詳細ログ、API レスポンスに raw を含める、画面で step を alert
    'trace' => env('TAMERU_AI_OCR_TRACE', false),
];