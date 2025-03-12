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
        $fontDir = storage_path('fonts');

        if (!file_exists($fontDir)) {
            mkdir($fontDir, 0775, true);
        }

        // 通常フォント
        $regularFontPath = storage_path('fonts/Noto-Regular.ttf');
        $mediumFontPath = storage_path('fonts/Noto-Medium.ttf');
        $boldFontPath = storage_path('fonts/Noto-Bold.ttf');

        // S3 からダウンロード（既にある場合はスキップ）
        $this->downloadFont('https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/Noto_Sans_JP/NotoSansJP-Regular.ttf', $regularFontPath);
        $this->downloadFont('https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/Noto_Sans_JP/NotoSansJP-Medium.ttf', $mediumFontPath);
        $this->downloadFont('https://astdocs-public.s3.ap-northeast-1.amazonaws.com/font/Noto_Sans_JP/NotoSansJP-Bold.ttf', $boldFontPath);

        // フォントを TCPDF に登録
        $fontNameRegular = TCPDF_FONTS::addTTFfont($regularFontPath, 'TrueTypeUnicode', '', 96);
        $fontNameMedium = TCPDF_FONTS::addTTFfont($mediumFontPath, 'TrueTypeUnicode', '', 96);
        $fontNameBold = TCPDF_FONTS::addTTFfont($boldFontPath, 'TrueTypeUnicode', '', 96);

        $this->info("Regular font added: $fontNameRegular");
        $this->info("Medium font added: $fontNameMedium");
        $this->info("Bold font added: $fontNameBold");
    }

    private function downloadFont($url, $path)
    {
        if (!file_exists($path)) {
            $this->info("Downloading font: $url");
            $fontData = file_get_contents($url);
            if ($fontData === false) {
                $this->error("Failed to download font: $url");
                return;
            }
            file_put_contents($path, $fontData);
        }
    }
}
