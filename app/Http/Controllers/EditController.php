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
                

        $hiduke = $file->日付;
        $hiduke = substr_replace($hiduke,'/',4,0);
        $hiduke = substr_replace($hiduke,'/',7,0);
        $syoruikubunn =$file->書類;
        $hozonn =$file->保存;
        $data = [
            'file' => $file,
            'hiduke'=>$hiduke ,
            'seikyusyo' => "",
            'nohinnsyo' => "",
            'keiyakusyo' => "",
            'mitumorisyo' => "",
            'dennshi' => "",
            'scan' => "",
        ];

        if ($syoruikubunn == "請求書"){
            $data['seikyusyo'] = "selected";
        }
        else if ($syoruikubunn == "納品書"){
            $data['nohinnsyo'] = "selected";
        }
        else if ($syoruikubunn == "契約書"){
            $data['keiyakusyo'] = "selected";
        }
        else if ($syoruikubunn == "見積書"){
            $data['mitumorisyo'] = "selected";
        };

        if ($hozonn == "電子保存"){
            $data['dennshi'] = "selected";
        }
        else if ($hozonn == "スキャナ保存"){
            $data['scan'] = "selected";
        };


        

        return view('information.editpage',$data);
    }

    public function editPost(Request $request,$path)
    {
        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');
        //過去データIDが一致するファイルが何件あるかを格納
        $historycount = Filemodel::where('過去データID',$path)->get()->count();

        //ファイルをインスタンス化
        $newfile = new Filemodel();


        
        $date = $request->input('hiduke');
        $date = str_replace('/','',$date);
        $torihikisaki = $request->input('torihikisaki');
        $kinngaku = $request->input('kinngaku');
        $kinngaku = str_replace(',','',$kinngaku);
        $syorui = $request->input('syorui');
        $hozonn = $request->input('hozonn');
        $kennsakuword = $request->input('kennsakuword');
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
        $newfile->備考 = $kennsakuword;
        $newfile->保存 = $hozonn;
        $newfile->save();
        
        return redirect()->route('topGet');

    }
    public function convert($int)
    {
        if (strlen($int) == 1) {
            $int = '0' . $int;
        }
        return $int;
    }
    public function deleteGet($path)
    {
        $deletedata = Filemodel::where('id',$path)->get();
        foreach($deletedata as $data){
            $data->削除フラグ = "済";
            $data->save();
        }
        return redirect()->route("topGet");
    }


}
