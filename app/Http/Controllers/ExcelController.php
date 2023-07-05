<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Reader\ReaderFactory;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelController extends Controller
{
    public function excel()
    {
        // テンプレートファイルのパス
$templatePath = public_path('files/運転日報1.xlsx');

// 出力ファイルのパス
// $outputFilePath = public_path('files/運転日報2.xltx');

 // テンプレートファイルを読み込む
 $spreadsheet = IOFactory::load($templatePath);

 // 必要に応じてスプレッドシートを編集する
 // ...

 // ファイルのダウンロードに適切なヘッダーを設定する
 $headers = [
     'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
     'Content-Disposition' => 'attachment; filename="template.xlsx"',
 ];

 // 変更されたスプレッドシートを一時ファイルに保存する
 $tempFile = tempnam(sys_get_temp_dir(), 'template');
 $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
 $writer->save($tempFile);

 // ダウンロードレスポンスを返す
 return response()->download($tempFile, 'template.xlsx', $headers)->deleteFileAfterSend(true);

}
}
