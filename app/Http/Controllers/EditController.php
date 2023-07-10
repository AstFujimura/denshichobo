<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\File as Filemodel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class EditController extends Controller
{


    public function editGet($path)
    {
        //過去データIDに一致する一番最新のものを取得
        $file = Filemodel::where('過去データID',$path)
                ->orderby('バージョン','desc')
                ->first();
                

        $dateString = $file->日付;

        $year_data = substr($dateString, 0, 4);
        $month_data = ltrim(substr($dateString, 4, 2), '0');
        $date_data = ltrim(substr($dateString, 6, 2), '0');
        

        return view('information.editpage',['file' => $file,'year_data' =>$year_data,'month_data' =>$month_data,'date_data' =>$date_data]);
    }

    public function editPost(Request $request,$path)
    {
        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');
        $historycount = Filemodel::where('過去データID',$path)->get()->count();
        
        
        $date = $request->input('year') . $this->convert($request->input('month')) . $this->convert($request->input('day'));
        $torihikisaki = $request->input('torihikisaki');
        $kinngaku = $request->input('kinngaku');
        $syorui = $request->input('syorui');
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = Config::get('custom.file_upload_path');
        $version = $historycount + 1;
        $filepath = $currentTime . '_' . $kinngaku . '_' . $torihikisaki;
        copy($file->getRealPath(),$filename . "\\" .$filepath .'_' . $version. '.' .$extension);
        
        $file = new Filemodel();
        $file->日付 = $date;
        $file->取引先 = $torihikisaki;
        $file->金額 = $kinngaku;
        $file->書類 = $syorui;
        $file->保存者ID = Auth::user()->id;
        $file->ファイルパス = $filepath;
        $file->ファイル形式 = $extension;
        $file->バージョン = $version;
        $file->過去データID = $path;
        $file->save();
        return redirect()->route('detail',['id'=>$file->過去データID]);

    }
    public function convert($int)
    {
        if (strlen($int) == 1) {
            $int = '0' . $int;
        }
        return $int;
    }


}
