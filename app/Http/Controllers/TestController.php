<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\File;

class TestController extends Controller
{
    public function testGet($num)
    {

        if ($num > 0){
            for ($i = 0; $i < $num; $i++){
                $file = new File();
                $file->日付 = "19800101";
                $file->取引先 = "ダミー";
                $file->金額 = "999999";
                $file->書類ID = 1;
                $file->提出 = "提出";
                $file->保存者ID = Auth::user()->id;
                $file->ファイルパス = "example";
                $file->過去データID = strval(1000000 + $num);
                $file->ファイル形式 = "ddd";
                $file->保存 = "ダミー";
                $file->備考 = "DLを押さないでください";
                $file->save();
    
            }
        }
        else if($num == -999){
            // "備考カラム"が"aaa"のデータを取得
          File::where('備考', 'DLを押さないでください')->delete();
        }


        return redirect()->route("topGet");

    }
}
