<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Validator;
use App\Contracts\AiOcrLedgerProvider;
use App\Services\AiOcr\Providers\GeminiLedgerOcrProvider;
use App\Services\AiOcr\Providers\InternalHttpLedgerOcrProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(AiOcrLedgerProvider::class, function () {
            $provider = (string) config('ai_ocr.provider', 'gemini');

            return match ($provider) {
                'internal' => new InternalHttpLedgerOcrProvider(),
                default => new GeminiLedgerOcrProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . storage_path('app/json/vision_api_key.json'));

        if (App::environment('production', 'staging')) {
            URL::forceScheme('https');
        }
        // 4バイト文字のバリデーションルールを追加
        Validator::extend('not_four_byte_chars', function ($attribute, $value, $parameters, $validator) {
            return !preg_match('/[\x{10000}-\x{10FFFF}]/u', $value);
        });

        \Blade::directive('server', function ($expression) {

            return '<input type="hidden" id="server" value="' . config('prefix.server') . '">';
        });
        \Blade::directive('prefix', function ($expression) {

            return '<input type="hidden" id="prefix" value="' . config('prefix.prefix') . '">';
        });
    }
}
