<?php

namespace App\Support;

class GeminiApi
{
    /**
     * Gemini generateContent API の URL を生成する
     */
    public static function generateContentUrl(?string $apiKey = null): string
    {
        $apiKey = $apiKey ?? config('gemini.api_key');
        $base = rtrim(config('version.gemini_api_base'), '/');
        $model = config('version.gemini_model');

        return "{$base}/models/{$model}:generateContent?key={$apiKey}";
    }
}
