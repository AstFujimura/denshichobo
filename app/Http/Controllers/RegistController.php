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
use App\Models\Document;
use App\Models\Group;
use App\Models\Group_User;
use Aws\S3\S3Client;

class RegistController extends Controller
{
    public function registGet()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $documents = Document::where("check", "check")
            ->orderBy('order', 'asc')
            ->get();
        $userid = Auth::id();

        // 中間テーブルからログインユーザーが含まれる グループID のリストを取得
        $grouparray = Group_User::where("ユーザーID", $userid)
            ->where('グループID', '>', 100000)
            ->pluck('グループID') // グループID のみを取得
            ->toArray(); // コレクションを配列に変換
        //該当するグループの情報を取得
        $groups = Group::whereIn("id", $grouparray)->get();

        return view('information.resistpage', compact('documents', 'prefix', 'server', 'groups'));
    }

    public function registURL(Request $request)
    {
        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');


        //s3バケットを扱うときはprefixに/は入れない
        $prefix = config('prefix.prefix');
        $server = config('prefix.server');

        $method = $request->input("method");
        $extension = $request->input("extension");
        $ID = $request->input("pastID");

        if ($method == "post") {
            $pastID = $this->generateRandomCode();
        } else if ($method == "edit") {
            $pastID = $ID;
        }



        // S3バケットの情報
        $bucket = 'astdocs.com';
        $key = $prefix . "/" . $currentTime . "_" . $pastID . "." . $extension; // S3オブジェクトのキー
        $expiration = '+1 hour'; // 有効期限

        $s3Client = new S3Client([
            'region' => 'ap-northeast-1',
            'version' => 'latest',
        ]);


        $command = $s3Client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $key,
        ]);


        $signedUrl = $s3Client->createPresignedRequest($command, $expiration)->getUri();


        $data = [
            'url' => $signedUrl,
            'pastID' => $pastID,
            'filepass' => $prefix . "/" . $currentTime . "_" . $pastID,
        ];
        return response()->json($data);







        return view('information.test', ['signedUrl' => $signedUrl]);
    }



    public function registPost(Request $request)
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




        $date = $request->input('hiduke');
        $date = str_replace('/', '', $date);
        $torihikisaki = $request->input('torihikisaki');
        $kinngaku = $request->input('kinngaku');
        $kinngaku = str_replace(',', '', $kinngaku);
        $syorui = $request->input('syorui');
        $teisyutu = $request->input('teisyutu');
        $file = $request->file('file');
        $hozonn = $request->input('hozonn');
        $kennsaku = $request->input('kennsakuword');
        $group = $request->input('group');
        $pastID = $this->generateRandomCode();


        //値が入っていないときはnullを入れない
        if (!$kennsaku) {
            $kennsaku = "";
        }
        $extension = $file->getClientOriginalExtension();

        $filename = Config::get('custom.file_upload_path');
        $filepath = $currentTime . '_' . $pastID;
        //アップロードされたファイルに拡張子がない場合
        if (!$extension) {
            if (config('app.env') == 'production') {
                // 本番環境用の設定
            } else {
                // 開発環境用の設定
                copy($file->getRealPath(), $filename . "\\" . $filepath);
            }
            //extensionがnullになっているためエラー回避
            $extension = "";
        } else {
            if (config('app.env') == 'production') {
                // 本番環境用の設定
            } else {
                // 開発環境用の設定
                copy($file->getRealPath(), $filename . "\\" . $filepath . '.' . $extension);
            }
        }

        $file = new File();
        $file->日付 = $date;
        $file->取引先 = $torihikisaki;
        $file->金額 = $kinngaku;
        $file->書類ID = $syorui;
        $file->保存者ID = Auth::user()->id;
        $file->更新者ID = Auth::user()->id;
        $file->ファイルパス = $filepath;
        $file->ファイル形式 = $extension;
        $file->過去データID = $pastID;
        $file->保存 = $hozonn;
        $file->提出 = $teisyutu;
        $file->備考 = $kennsaku;
        $file->グループID = $group;
        //バージョンはデフォルトで1になるのでここでは記載しない。変更の時には記述
        //最新フラグはデフォルトで最新になるのでここでは記載しない。変更の時に過去データの最新フラグを外す
        $file->save();
        return redirect()->route('topGet');
    }

    //クラウドでjqueryから直接アップロードされる場合の機能
    public function registcloudPost(Request $request)
    {

        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');
        //デフォルトでバージョンを1にしておく
        $version = 1;

        //デフォルトで保存者idをログインユーザーのidにしておく。
        //変更の場合は過去の保存者idを後で代入する
        $creater = Auth::user()->id;




        $date = $request->get('hiduke');
        $date = str_replace('/', '', $date);
        $torihikisaki = $request->input('torihikisaki');
        $kinngaku = $request->input('kinngaku');
        $kinngaku = str_replace(',', '', $kinngaku);
        $syorui = $request->input('syorui');
        $teisyutu = $request->input('teisyutu');
        $hozonn = $request->input('hozonn');
        $kennsaku = $request->input('kennsakuword');
        $group = $request->input('group');
        $filepass = $request->input('filepass');
        $pastID = $request->input('pastID');
        $extension = $request->input('extension');


        //値が入っていないときはnullを入れない
        if (!$kennsaku) {
            $kennsaku = "";
        }

        if (File::where("過去データID", $pastID)->first()) {
            //最新のデータからファイルパスを取得して格納する
            $latestdata = File::where('過去データID', $pastID)
                ->orderBy('バージョン', "desc")
                ->first();
            $latestdata->最新フラグ = "";
            $latestdata->save();
            $version = $latestdata->バージョン + 1;
            $creater = $latestdata->保存者ID;
            //ファイル変更あり(過去データがあるため)
            $filechange = "あり";
        } else {
            //ファイル変更なし(過去データがない→新規の登録であるため)
            $filechange = "";
        }


        $file = new File();
        $file->日付 = $date;
        $file->取引先 = $torihikisaki;
        $file->金額 = $kinngaku;
        $file->書類ID = $syorui;
        $file->保存者ID = $creater;
        $file->更新者ID = Auth::user()->id;
        $file->バージョン = $version;
        $file->ファイルパス = $filepass;
        $file->ファイル形式 = $extension;
        $file->ファイル変更 = $filechange;
        $file->過去データID = $pastID;
        $file->保存 = $hozonn;
        $file->提出 = $teisyutu;
        $file->備考 = $kennsaku;
        $file->グループID = $group;
        //最新フラグはデフォルトで最新になるのでここでは記載しない。
        $file->save();
        return route('topGet');
    }
    public function convert($int)
    {
        if (strlen($int) == 1) {
            $int = '0' . $int;
        }
        return $int;
    }

    //ランダムな8桁のstring型の数値を出力
    private function generateRandomCode()
    {
        $code = mt_rand(10000000, 99999999);

        while ($this->isCompanyCodeExists($code)) {
            $code = mt_rand(10000000, 99999999);
        }
        return $code;
    }
    private function isCompanyCodeExists($code)
    {
        return File::where('過去データID', $code)->exists();
    }
}
