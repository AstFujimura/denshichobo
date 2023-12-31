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
            $date = $request->input('date');
            $torihikisaki = $request->input('torihikisaki');
            $kinngaku = $request->input('kinngaku');
            $syorui = $request->input('syorui');
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = Config::get('custom.file_upload_path');
            $filepath = $date . '_' . $syorui . '_' . $torihikisaki . "." . $extension  ;
            copy($file->getRealPath(),$filename . "\\" .$filepath);
            
            $file = new File();
            $file->日付 = $date;
            $file->取引先 = $torihikisaki;
            $file->金額 = $kinngaku;
            $file->書類 = $syorui;
            $file->保存者ID = Auth::user()->id;
            $file->ファイルパス = $filepath;
            $file->save();
            return redirect()->route('topGet');
        }



}
