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
use Aws\S3\S3Client;

class RegistController extends Controller
{
    public function registGet()
    {
        $documents = Document::where("check", "check")
            ->orderBy('order', 'asc')
            ->get();

        return view('information.resistpage', compact('documents'));
    }

    public function registURl()
    {

        // S3クライアントのインスタンスを作成
        $s3 = new S3Client([
            'region' => 'us-east-1',
            'version' => 'latest',
        ]);

        // 署名付きURLの生成
        $bucket = 'astdocs';
        $objectKey = 'astec/test';
        $expires = '+1 hour'; // URLの有効期限

        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $objectKey,
        ]);
        $request = $s3->createPresignedRequest($cmd, $expires);

        $signedUrl = (string) $request->getUri();
        dd($signedUrl);

        return response()->json(['signed_url' => $signedUrl]);
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
        $file->ファイルパス = $filepath;
        $file->ファイル形式 = $extension;
        $file->過去データID = $pastID;
        $file->保存 = $hozonn;
        $file->提出 = $teisyutu;
        $file->備考 = $kennsaku;
        //バージョンはデフォルトで1になるのでここでは記載しない。変更の時には記述
        //最新フラグはデフォルトで最新になるのでここでは記載しない。変更の時に過去データの最新フラグを外す
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
