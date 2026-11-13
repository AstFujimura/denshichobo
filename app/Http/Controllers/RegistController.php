<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\File;
use App\Models\T_flow;
use App\Models\Document;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\Version;

class RegistController extends Controller
{
    public function registGet(Request $request)
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

        if (Auth::user()->管理 == "一般") {
            // 中間テーブルからログインユーザーが含まれる グループID のリストを取得
            $grouparray = Group_User::where("ユーザーID", $userid)
                ->where('グループID', '>', 100000)
                ->pluck('グループID') // グループID のみを取得
                ->toArray(); // コレクションを配列に変換
        }
        //管理の場合はすべてのグループを選択することができる
        else if (Auth::user()->管理 == "管理") {
            // 中間テーブルからログインユーザーが含まれる グループID のリストを取得
            $grouparray = Group_User::where('グループID', '>', 100000)
                ->pluck('グループID') // グループID のみを取得
                ->toArray(); // コレクションを配列に変換
        }


        //該当するグループの情報を取得
        $groups = Group::whereIn("id", $grouparray)->get();

        $banbanEnabled = feature_enabled('banban');
        $ichifujiEnabled = feature_enabled('ichifuji');

        $ledgerRegistMode = $request->query('mode') === 'bulk' ? 'bulk' : 'normal';
        if ($ledgerRegistMode === 'bulk' && !$banbanEnabled) {
            $ledgerRegistMode = 'normal';
        }

        return view('information.resistpage', compact('documents', 'prefix', 'server', 'groups', 'banbanEnabled', 'ichifujiEnabled', 'ledgerRegistMode'));
    }

    public function registURL(Request $request)
    {
        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');

        $method = $request->input('method');
        $extension = (string) $request->input('extension', '');
        $ID = $request->input('pastID');

        if ($method == 'post') {
            $pastID = $this->generateRandomCode();
        } elseif ($method == 'edit') {
            $pastID = $ID;
        } else {
            abort(400, 'Invalid method.');
        }

        $logicalPath = $currentTime . '_' . $pastID;
        $storage = tenant_storage();
        $signedUrl = $storage->temporaryUploadUrl($logicalPath, $extension, 3600);

        // storage.js (cloud) 互換: 当面 filepass は prefix 付きを維持（既存経路）。
        // resolve は prefix 有無の両方を扱える。
        $filepass = $logicalPath;
        if ($storage->mode() === 'cloud') {
            $prefix = trim((string) (tenant()?->storage_prefix ?: config('prefix.prefix', '')), '/');
            if ($prefix !== '') {
                $filepass = $prefix . '/' . $logicalPath;
            }
        }

        return response()->json([
            'url' => $signedUrl,
            'pastID' => $pastID,
            'filepass' => $filepass,
            'logical_path' => $logicalPath,
            'strategy' => $signedUrl ? 'direct_s3' : 'server',
        ]);
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
        $extension = (string) $file->getClientOriginalExtension();
        $filepath = $currentTime . '_' . $pastID;
        if ($extension === '') {
            $extension = '';
        }

        tenant_storage()->put($filepath, $extension, $file);

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

        return redirect()->route('registGet')->with('success', '帳簿を登録しました。');
    }

    /**
     * BANBAN: 帳簿保存の一括取込
     */
    public function registBulkPost(Request $request)
    {
        if (! feature_enabled('banban')) {
            abort(404);
        }

        $maxFileKb = 51200; // 50MB
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:' . $maxFileKb],
            'hiduke' => ['required', 'array'],
            'kinngaku' => ['required', 'array'],
            'torihikisaki' => ['required', 'array'],
            'syorui' => ['required', 'array'],
            'teisyutu' => ['required', 'array'],
            'hozonn' => ['required', 'array'],
            'group' => ['required', 'array'],
            'kennsakuword' => ['nullable', 'array'],
        ]);

        $files = $request->file('files');
        $count = count($files);

        foreach (['hiduke', 'kinngaku', 'torihikisaki', 'syorui', 'teisyutu', 'hozonn', 'group'] as $key) {
            if (count($request->input($key, [])) !== $count) {
                return redirect()->back()->with('error', '一括取込の入力データが不正です。');
            }
        }

        for ($i = 0; $i < $count; $i++) {
            $fileUpload = $files[$i];

            $request->validate([
                "torihikisaki.$i" => 'string|not_four_byte_chars',
                "kennsakuword.$i" => 'nullable|not_four_byte_chars',
            ], [
                "torihikisaki.$i.not_four_byte_chars" => '環境依存文字は使用しないでください。',
                "kennsakuword.$i.not_four_byte_chars" => '環境依存文字は使用しないでください。',
            ]);

            $now = Carbon::now();
            $currentTime = $now->format('YmdHis');

            $date = (string) $request->input("hiduke.$i");
            $date = str_replace('/', '', $date);
            $torihikisaki = (string) $request->input("torihikisaki.$i");
            $kinngaku = (string) $request->input("kinngaku.$i");
            $kinngaku = str_replace(',', '', $kinngaku);
            $syorui = $request->input("syorui.$i");
            $teisyutu = (string) $request->input("teisyutu.$i");
            $hozonn = (string) $request->input("hozonn.$i");
            $kennsaku = (string) ($request->input("kennsakuword.$i") ?? '');
            $group = $request->input("group.$i");

            $extension = (string) $fileUpload->getClientOriginalExtension();

            // 一括取込は重複判定せず、各行を新規登録する（通常取込と同様）
            $pastID = $this->generateRandomCode();
            $filepath = $currentTime . '_' . $pastID;

            try {
                tenant_storage()->put($filepath, $extension, $fileUpload);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'ファイルの保存に失敗しました: ' . $e->getMessage());
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
            $file->save();
        }

        return redirect()->route('registGet', ['mode' => 'bulk'])->with('success', '帳簿を一括登録しました。');
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

        if ($filechange === "") {
            session()->flash('success', '帳簿を登録しました。');

            return route('registGet');
        }

        session()->flash('success', '帳簿を変更しました。');

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
        $t_flow = T_flow::where('過去データID', $code)->exists();
        $tameru = File::where('過去データID', $code)->exists();
        return $t_flow || $tameru;
    }
}
