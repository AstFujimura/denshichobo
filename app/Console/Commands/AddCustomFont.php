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
        $s3FontUrl  = 'https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/Noto.ttf';
        $localFontPath = storage_path('fonts/Noto.ttf');

        // **ディレクトリが存在しなければ作成**
        $fontDir = storage_path('fonts');
        if (!file_exists($fontDir)) {
            mkdir($fontDir, 0775, true);
        }

        // フォントをローカルに保存
        if (!file_exists($localFontPath)) {
            $this->info("Downloading font from S3...");
            $fontData = file_get_contents($s3FontUrl);

            if ($fontData === false) {
                $this->error("Failed to download font file.");
                return;
            }

            file_put_contents($localFontPath, $fontData);
        }

        // TCPDF にフォントを追加
        $fontName = TCPDF_FONTS::addTTFfont($localFontPath, 'TrueTypeUnicode', '', 96);

        if ($fontName) {
            $this->info("Font successfully added: $fontName");
        } else {
            $this->error("Failed to add font.");
        }
    }
}
