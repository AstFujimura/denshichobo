<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
            if (App::environment('production','staging')) {
                URL::forceScheme('https');
        }
        // 4バイト文字のバリデーションルールを追加
        Validator::extend('not_four_byte_chars', function ($attribute, $value, $parameters, $validator) {
            return !preg_match('/[\x{10000}-\x{10FFFF}]/u', $value);
        });

        \Blade::directive('server', function ($expression) {

            return '<input type="hidden" id="server" value="'.config('prefix.server').'">';
        });
        \Blade::directive('prefix', function ($expression) {

            return '<input type="hidden" id="prefix" value="'.config('prefix.prefix').'">';
        });
    }
}
