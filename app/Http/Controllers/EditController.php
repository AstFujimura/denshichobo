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
        //過去データIDが一致するファイルが何件あるかを格納
        $historycount = Filemodel::where('過去データID',$path)->get()->count();

        //ファイルをインスタンス化
        $newfile = new Filemodel();


        
        $date = $request->input('year') . $this->convert($request->input('month')) . $this->convert($request->input('day'));
        $torihikisaki = $request->input('torihikisaki');
        $kinngaku = $request->input('kinngaku');
        $syorui = $request->input('syorui');
        $version = $historycount + 1;

        //ファイルに変更がある場合とない場合でわける
        if (!empty($request ->file('file'))){
            $file = $request->file('file');
            //ファイル形式を取得
            $extension = $file->getClientOriginalExtension();
            //既定のフォルダのパスを取得
            $filename = Config::get('custom.file_upload_path');
            //拡張子を除くファイル名
            $filepath = $currentTime . '_' . $kinngaku . '_' . $torihikisaki;
            //ファイルを保存
            copy($file->getRealPath(),$filename . "\\" .$filepath. '.' .$extension);

            $newfile->ファイル形式 = $extension;
            $newfile->ファイルパス = $filepath;
            $newfile->ファイル変更 = "あり";

        }
        else{
            //最新のデータからファイルパスを取得して格納する
            $latestdata = Filemodel::where('過去データID',$path)
                                ->where('バージョン',$historycount)
                                ->first();
            $newfile->ファイルパス = $latestdata->ファイルパス;
            $newfile->ファイル形式 = $latestdata->ファイル形式;
        }

        
        $newfile->日付 = $date;
        $newfile->取引先 = $torihikisaki;
        $newfile->金額 = $kinngaku;
        $newfile->書類 = $syorui;
        $newfile->保存者ID = Auth::user()->id;
        $newfile->バージョン = $version;
        $newfile->過去データID = $path;
        $newfile->save();
        return redirect()->route('detail',['id'=>$newfile->過去データID]);

    }
    public function convert($int)
    {
        if (strlen($int) == 1) {
            $int = '0' . $int;
        }
        return $int;
    }


}
