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
    | {context_block} … 選択された書類区分名と受領/提出の前提（AiOcrLedgerPromptBuilder が組み立て）
    | {torihikisaki_line} … 受領・提出に応じた取引先の抽出ルール
    | {kinngaku_line} … 書類管理の OCR 設定「複数ページ・複数セットの金額を合算する」に応じて差し替え
    | vLLM（ローカルLLM）向けに構造を明確化しています。
    |
    */
    'ledger_prompt' => <<<'PROMPT'
# 前提
{context_block}

# 指示
添付された画像から、以下の3項目のみを抽出してJSON形式で出力してください。解説やMarkdownの装飾（```json 等）は一切不要です。必ずJSONオブジェクトのみを返してください。

# 抽出項目
1. hiduke: 取引日（形式: YYYY/MM/DD）
2. kinngaku: {kinngaku_line}
3. torihikisaki: {torihikisaki_line}

# 出力フォーマット
{"hiduke":"YYYY/MM/DD","kinngaku":12345,"kinngaku_tax_basis":"included","torihikisaki":"..."}
PROMPT,

    // {context_block} の内訳（Builder が連結）
    'document_context_named' => 'この書類は「{document_name}」です。画像はその種類の帳票として読み取り、当該書類で一般的に使われる日付・金額・取引先の表記（請求日・発行日・合計請求額・御中 等）を踏まえて抽出してください。',

    'document_context_generic' => '書類区分は指定されていません。画像の内容から請求書・領収書・納品書など該当する帳票として読み取り、記載から項目を抽出してください。',

    'teisyutu_context_jyuryo' => '受領・提出の区分は「受領」です。自社が相手方からこの書類を受け取った取引として扱います。hiduke は取引日・請求日・発行日など当該書類の基準日を優先して選んでください。',

    'teisyutu_context_teishutsu' => '受領・提出の区分は「提出」です。自社が相手方へこの書類を送付・提出した取引として扱います。hiduke は取引日・請求日・発行日など当該書類の基準日を優先して選んでください。',

    // 【受領した書類用】取引先 ＝ 「書類の発行元（請求元）」
    'torihikisaki_line_jyuryo' => '取引先名（必ず「書類の発行元・請求元・店舗名」の会社名を抽出してください。※宛名側にある自社名は絶対に抽出しないでください）',

    // 【提出する書類用】取引先 ＝ 「提出先（宛名・請求先）」
    'torihikisaki_line_teishutsu' => '取引先名（必ず「宛名・請求先・提出先（御中や様が付いている側）」の会社名を抽出してください。敬称は除外。※差出人側にある自社名は絶対に抽出しないでください）',

    // kinngaku 行（{kinngaku_line}）。書類管理 OCR 設定の税区分・複数合算で差し替え。
    'kinngaku_line_default' => '税抜合計金額（数値のみ、カンマや通貨記号は除外）',

    'kinngaku_line_default_excluded' => '税抜合計金額（数値のみ、カンマや通貨記号は除外。帳票の税抜・本体価格合計を優先）',

    'kinngaku_line_default_included' => '税込合計金額（数値のみ、カンマや通貨記号は除外。帳票の税込・合計請求額を優先）',

    'kinngaku_line_sum_amounts' => '税込の取引金額（数値のみ、カンマ・通貨記号は除外）。1ファイル（PDF・画像）に複数ページがあり、各ページまたは複数セット分の同種帳票（例：納品書が連続して配置されている）が含まれる場合は、各セットごとの税込合計・請求金額・合計欄など読み取れる金額をすべて足し合わせ、その合算値を kinngaku として1つだけ出力すること。1セットのみの場合はその合計金額を出力すること。',

    'kinngaku_line_sum_amounts_included' => '税込の取引金額（数値のみ、カンマ・通貨記号は除外）。1ファイル（PDF・画像）に複数ページがあり、各ページまたは複数セット分の同種帳票が含まれる場合は、各セットごとの税込合計・請求金額・合計欄などをすべて足し合わせ、その合算値を kinngaku として1つだけ出力すること。1セットのみの場合はそのセットの税込合計金額を出力すること。',

    'kinngaku_line_sum_amounts_excluded' => '税抜の取引金額（数値のみ、カンマ・通貨記号は除外）。1ファイル（PDF・画像）に複数ページがあり、各ページまたは複数セット分の同種帳票が含まれる場合は、各セットごとの税抜合計・本体価格合計などをすべて足し合わせ、その合算値を kinngaku として1つだけ出力すること。1セットのみの場合はそのセットの税抜合計金額を出力すること。',

    // OCR 読み取り金額の税区分（kinngaku_tax_basis）と、書類管理の税区分設定
    'consumption_tax_rate' => 0.10,

    'tax_context_included_target' => '金額の税区分（この書類区分の設定は税込）：kinngaku および kinngaku_items の amount は、帳票に記載されている数値をそのまま出力し、kinngaku_tax_basis でその数値が税込か税抜かを必ず示すこと。帳票に税込合計・請求総額（税込）があればその数値を出力し kinngaku_tax_basis は "included"。税抜・本体価格合計しか読み取れない場合はその税抜数値を出力し kinngaku_tax_basis は "excluded"（システムが税込に換算する）。',

    'tax_context_excluded_target' => '金額の税区分（この書類区分の設定は税抜）：kinngaku および kinngaku_items の amount は、帳票に記載されている数値をそのまま出力し、kinngaku_tax_basis でその数値が税込か税抜かを必ず示すこと。帳票に税抜・本体価格合計があれば kinngaku_tax_basis は "excluded"。税込合計しか読み取れない場合はその税込数値を出力し kinngaku_tax_basis は "included"（システムが税抜に換算する）。',

    // {context_block} に追記（書類管理で「金額を合算する」が ON のとき）
    'sum_amounts_context' => '金額（kinngaku）について：この書類区分では、1ファイル内に複数ページへ同種の帳票が複数セット並んでいるケース（納品書の連続添付など）を想定しています。各セットの合計金額をすべて読み取り、それらを加算した値を kinngaku として返してください。1セットだけのときはそのセットの合計金額を返してください。',

    'sum_amounts_context_excluded' => '金額（kinngaku）について：各セットの税抜合計・本体価格合計を読み取り、それらを加算した税抜合計を kinngaku として返してください。',

    'sum_amounts_context_included' => '金額（kinngaku）について：各セットの税込合計・合計請求額を読み取り、それらを加算した税込合計を kinngaku として返してください。',

    'sum_amounts_json_format' => '4. kinngaku_items: 合算に含めた各セットの合計金額の配列。各要素は {"amount":数値,"label":"1枚目・2枚目…のようにページ順の短い説明"} とし、kinngaku は kinngaku_items の amount の合計と一致させること（返品・値引き・マイナス請求などで amount が負の数になる場合もそのまま記載）。PDF・画像の1枚目（1ページ目）から最終ページまで、すべてのセットを kinngaku_items に漏れなく含めること（1枚目だけ kinngaku に入れて kinngaku_items から省略してはならない）。1セットのみのときも kinngaku_items は1要素の配列として返すこと。',

    'sum_amounts_output_example' => '出力例: {"hiduke":"2024/01/15","kinngaku":17000,"kinngaku_tax_basis":"included","torihikisaki":"...","kinngaku_items":[{"amount":8000,"label":"1枚目"},{"amount":6500,"label":"2枚目"},{"amount":2500,"label":"3枚目"}]}',

    // アップロード上限（KB）。Laravel の validation max は KB 単位。
    'max_file_kb' => 20480,

    // true: laravel.log に詳細ログ、API レスポンスに raw を含める、画面で step を alert
    'trace' => env('TAMERU_AI_OCR_TRACE', false),

    /*
    |--------------------------------------------------------------------------
    | Gemini（帳簿 OCR）生成設定
    |--------------------------------------------------------------------------
    |
    | max_output_tokens / max_output_tokens_retry … config/ai_ocr.php の gemini で変更（Git 管理）
    | thinking_budget … 思考モデル用。0 で思考を抑え JSON 出力を優先
    |
    */
    'gemini' => [
        'max_output_tokens' => 2048,
        'max_output_tokens_retry' => 8192,
        'thinking_budget' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF → JPEG（OCR 前）
    |--------------------------------------------------------------------------
    |
    | Ghostscript のみ使用。本番 Linux は `sudo apt install ghostscript` 等で gs を入れる。
    | パスを固定したい場合だけ GHOSTSCRIPT_PATH を指定。
    |
    */
    'pdf_to_image' => [
        'enabled' => env('TAMERU_AI_OCR_PDF_TO_IMAGE', true),
        'gs_path' => env('GHOSTSCRIPT_PATH', ''), // 空なら PATH の gs / gswin64c
        'density' => (int) env('TAMERU_AI_OCR_PDF_DENSITY', 200),
        'quality' => (int) env('TAMERU_AI_OCR_PDF_QUALITY', 90),
        'max_pages' => (int) env('TAMERU_AI_OCR_PDF_MAX_PAGES', 20),
    ],
];
