<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\File;

class RegistController extends Controller
{
    public function registGet()
        {
            return view('information.resistpage');
        }
    public function registPost(Request $request)
        {
            $now = Carbon::now();
            $currentTime = $now->format('YmdHis');
            
            $date = $request->input('year') . $this->convert($request->input('month')) . $this->convert($request->input('day'));
            $torihikisaki = $request->input('torihikisaki');
            $kinngaku = $request->input('kinngaku');
            $syorui = $request->input('syorui');
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = Config::get('custom.file_upload_path');
            $filepath = $currentTime . '_' . $kinngaku . '_' . $torihikisaki;
            copy($file->getRealPath(),$filename . "\\" .$filepath. '.' .$extension);
            
            $file = new File();
            $file->日付 = $date;
            $file->取引先 = $torihikisaki;
            $file->金額 = $kinngaku;
            $file->書類 = $syorui;
            $file->保存者ID = Auth::user()->id;
            $file->ファイルパス = $filepath;
            $file->過去データID = $this->generateRandomCode();
            $file->ファイル形式 = $extension;
            //バージョンはデフォルトで1になるのでここでは記載しない変更の時には記述
            $file->save();
            return redirect()->route('topGet');
        }
    public function convert($int)
        {
            if (strlen($int) == 1) {
                $int = '0' . $int;
            }
            return $int;
        }

            //ランダムな6桁のstring型の数値を出力
            private function generateRandomCode()
            {
                $code = mt_rand(100000, 999999);
                
                while($this->isCompanyCodeExists($code)){
                    $code = mt_rand(100000, 999999);
                }
                return $code;
    
            }
            private function isCompanyCodeExists($code)
            {
                return File::where('過去データID', $code)->exists();
            }




}
