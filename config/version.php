<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 画面表示用アプリケーションバージョン
    |--------------------------------------------------------------------------
    |
    | 各レイアウトのヘッダーに「ver.x.x.x」として表示されます。
    | .env の APP_DISPLAY_VERSION で上書きできます。
    |
    */

    'display' => env('APP_DISPLAY_VERSION', '7.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Gemini API モデル
    |--------------------------------------------------------------------------
    |
    | generateContent エンドポイントで使用するモデル名。
    | .env の GEMINI_MODEL で上書きできます。
    |
    */

    'gemini_model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),

    'gemini_api_base' => env('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta'),

];
