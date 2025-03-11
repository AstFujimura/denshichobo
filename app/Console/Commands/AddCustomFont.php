<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TCPDF_FONTS;

class AddCustomFont extends Command
{
    protected $signature = 'tcpdf:add-font';
    protected $description = 'Add a custom font to TCPDF';

    public function handle()
    {
        // Laravelの `public/font/Noto.ttf` を取得
        $fontFile = 'https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/Noto.ttf';

        if (!file_exists($fontFile)) {
            $this->error("Font file not found: $fontFile");
            return;
        }

        // TCPDF フォントフォルダに登録
        $fontName = TCPDF_FONTS::addTTFfont($fontFile, 'TrueTypeUnicode', '', 96);

        if ($fontName) {
            $this->info("Font successfully added: $fontName");
        } else {
            $this->error("Failed to add font.");
        }
    }
}
