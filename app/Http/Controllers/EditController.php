<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Document;
use App\Models\File as Filemodel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use App\Models\Group;
use App\Models\Group_User;

class EditController extends Controller
{


    public function editGet($path)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        //過去データIDに一致する一番最新のものを取得
        $file = Filemodel::where('過去データID', $path)
            ->orderby('バージョン', 'desc')
            ->first();

        $userid = Auth::id();

        // 中間テーブルからログインユーザーが含まれる グループID のリストを取得
        //エラーページを出すかの判定用
        $grouparray = Group_User::where("ユーザーID", $userid)
            ->pluck('グループID') // グループID のみを取得
            ->toArray(); // コレクションを配列に変換
        //ファイルがDBに存在しなければエラーページ
        if (!$file) {
            return redirect()->route("errorGet", ['code' => 'N173647']);
        }
        //一般ユーザーで他のユーザーのファイルを変更しようとしたときはエラーページ
        if (!(in_array($file->グループID, $grouparray)) && Auth::user()->管理 == "一般") {
            return redirect()->route("errorGet", ['code' => 'E183728']);
        }
        //チェックはしてないが過去のデータをいじりたい時に選択肢に既存の書類区分がないとデフォルトで違うものになってしまう。
        //2023/08/17 芝田さん仕様
        $documents = Document::where("check", "check")
            ->orWhere('id', $file->書類ID)
            ->orderBy('order', 'asc')
            ->get();

        $hiduke = $file->日付;
        $hiduke = substr_replace($hiduke, '/', 4, 0);
        $hiduke = substr_replace($hiduke, '/', 7, 0);
        $syoruikubunn = $file->書類ID;
        $hozonn = $file->保存;
        $teisyutu = $file->提出;

        foreach ($documents as $document) {

            //新たにcheckedというカラムを追加する（一時的に）
            //チェックされたユーザーが一致した場合値はcheckedを付与する
            $document->selected = ($document->id == $syoruikubunn) ? 'selected' : '';
        }

        if (Auth::user()->管理 == "一般") {
            // 中間テーブルからログインユーザーが含まれる グループID のリストを取得
            $grouparray = Group_User::where("ユーザーID", $userid)
                ->where('グループID', '>', 100000)
                ->pluck('グループID') // グループID のみを取得
                ->toArray(); // コレクションを配列に変換

            
        } 
        //管理の場合はすべてのグループを選択することができる
        else if (Auth::user()->管理 == "管理") {
            // グループテーブルからログインユーザーが含まれる グループID のリストを取得
            //管理の場合は中間テーブルではなくグループテーブルから取得する。
            //一度作成したグループとユーザーとの関係を切ったときに中間テーブルを参照するとグループが表示されなくなるため

            $grouparray = Group::where('id', '>', 100000)
                ->pluck('id') // グループID のみを取得
                ->toArray(); // コレクションを配列に変換
        }

        //該当するグループの情報を取得
        $groups = Group::whereIn("id", $grouparray)->get();

        //指定されたグループがあるかどうかのステータス。デフォルトは「指定なし」
        $selectstatus = "selected";
        foreach ($groups as $group){
            if ($group->id == $file->グループID){
                $group->selected = "selected";
                $selectstatus = "";
            }
        }
        
        $data = [
            'file' => $file,
            'documents' => $documents,
            'hiduke' => $hiduke,
            'dennshi' => "",
            'scan' => "",
            'teisyutu' => "",
            'jyuryo' => "",
            'prefix' => $prefix,
            'server' => $server,
            'groups' => $groups,
            'selectstatus' => $selectstatus,
        ];



        if ($hozonn == "電子保存") {
            $data['dennshi'] = "selected";
        } else if ($hozonn == "スキャナ保存") {
            $data['scan'] = "selected";
        };


        if ($teisyutu == "提出") {
            $data['teisyutu'] = "selected";
        } else if ($teisyutu == "受領") {
            $data['jyuryo'] = "selected";
        };








        if ($file->削除フラグ == "済") {
            return redirect()->route("topGet");
        } else {
            return view('information.editpage', $data);
        }
    }

    public function editPost(Request $request, $path)
    {
        $request->validate([
            'torihikisaki' => 'string|not_four_byte_chars',
            'kennsakuword' => 'not_four_byte_chars',
        ], [

            'torihikisaki.not_four_byte_chars' => '環境依存文字は使用しないでください。',

            'kennsakuword.not_four_byte_chars' => '環境依存文字は使用しないでください。',
        ]);
        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');




        //過去データIDが一致するファイルが何件あるかを格納
        $history = Filemodel::where('過去データID', $path)->get();
        $historycount = $history->count();

        //最新のデータからファイルパスを取得して格納する
        $latestdata = Filemodel::where('過去データID', $path)
            ->where('バージョン', $historycount)
            ->first();



        foreach ($history as $pastdata) {
            $pastdata->最新フラグ = "";
            $pastdata->save();
        }

        //ファイルをインスタンス化
        $newfile = new Filemodel();


        $date = $request->input('hiduke');
        $date = str_replace('/', '', $date);
        $torihikisaki = $request->input('torihikisaki');
        $kinngaku = $request->input('kinngaku');
        $kinngaku = str_replace(',', '', $kinngaku);
        $syorui = $request->input('syorui');
        $teisyutu = $request->input('teisyutu');
        $hozonn = $request->input('hozonn');
        $kennsakuword = $request->input('kennsakuword');
        $group = $request->input('group');


        //グループ番号を確認して管理者かつ指定なし(グループIDが10万以下)の場合は
        //グループIDを保存者IDと一緒にしておく
        //(管理者の「指定なし」にすると一般ユーザーから見れなくなるため)
        if ((Auth::user()->管理 == "管理")&& ($group < 100000)){
            $group = $latestdata->保存者ID;
        }

        //値が入っていないときはnullを入れない
        if (!$kennsakuword) {
            $kennsakuword = "";
        }
        //バージョンを1あげる
        $version = $historycount + 1;

        //ファイルに変更がある場合とない場合でわける
        if (!empty($request->file('file'))) {
            $file = $request->file('file');
            //ファイル形式を取得
            $extension = $file->getClientOriginalExtension();
            //既定のフォルダのパスを取得
            $filename = Config::get('custom.file_upload_path');
            //拡張子を除くファイル名
            $filepath = $currentTime . '_' . $path;

            //アップロードされたファイルに拡張子がない場合
            if (!$extension) {
                copy($file->getRealPath(), $filename . "\\" . $filepath);
                $extension = "";
            } else {
                copy($file->getRealPath(), $filename . "\\" . $filepath . '.' . $extension);
            }


            $newfile->ファイル形式 = $extension;
            $newfile->ファイルパス = $filepath;
            $newfile->ファイル変更 = "あり";
        } else {

            $newfile->ファイルパス = $latestdata->ファイルパス;
            $newfile->ファイル形式 = $latestdata->ファイル形式;
        }


        $newfile->日付 = $date;
        $newfile->取引先 = $torihikisaki;
        $newfile->金額 = $kinngaku;
        $newfile->書類ID = $syorui;
        $newfile->提出 = $teisyutu;
        $newfile->保存者ID = $latestdata->保存者ID;
        $newfile->更新者ID = Auth::user()->id;
        $newfile->バージョン = $version;
        $newfile->過去データID = $path;
        $newfile->備考 = $kennsakuword;
        $newfile->保存 = $hozonn;
        $newfile->グループID = $group;
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
        $deletedata = Filemodel::where('過去データID', $path)->get();
        if (!$deletedata) {
            return redirect()->route("errorGet", ['code' => 'N173647']);
        }
        foreach ($deletedata as $data) {
            $data->削除フラグ = "済";
            $data->最新フラグ = "";
            $data->save();
        }

        $file = Filemodel::where('過去データID', $path)
            ->orderby('バージョン', 'desc')
            ->first();
        $newdeletefile = new Filemodel();
        $newdeletefile->日付 = $file->日付;
        $newdeletefile->取引先 = $file->取引先;
        $newdeletefile->金額 = $file->金額;
        $newdeletefile->書類ID = $file->書類ID;
        $newdeletefile->提出 = $file->提出;
        $newdeletefile->保存者ID = $file->保存者ID;
        $newdeletefile->更新者ID = Auth::user()->id;
        $newdeletefile->バージョン = 9999;
        $newdeletefile->過去データID = $file->過去データID;
        $newdeletefile->備考 = $file->備考;
        $newdeletefile->保存 = $file->保存;
        $newdeletefile->ファイルパス = $file->ファイルパス;
        $newdeletefile->ファイル形式 = $file->ファイル形式;
        $newdeletefile->削除フラグ = "済";
        $newdeletefile->最新フラグ = "最新";
        $newdeletefile->save();
        return redirect()->route("topGet");
    }
}
