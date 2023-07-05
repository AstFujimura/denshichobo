<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mater;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Common\Type;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class DriveTestController extends Controller
{
    public function test()
    {
        return view('test.test');
    }
    public function test2()
    {
        return view('test.test2');
    }
    public function testpost2(Request $request)
    {

            // アップロードされたExcelファイルを取得
            $excelFile = $request->file('excel_file');

            // ファイルの一時保存とパスの取得
            $filePath = $excelFile->store('temp');

                    // Readerの作成
                    $reader = ReaderEntityFactory::createReaderFromFile(storage_path('app/' . $filePath));
                    $reader->open(storage_path('app/' . $filePath));
                    
            // 行ごとの処理
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                        $rowdata = $row->toArray();


                            $model = new Mater();
                            $model->年月 = $rowdata[0];
                            $model->月初 = $rowdata[1];
                            $model->月末 = $rowdata[2];
                            $model->走行距離 = $rowdata[3];
                            $model->save();                       
                    
                }
                
            }
            $reader->close();

            return redirect()->route('topGet');

        }
    }
