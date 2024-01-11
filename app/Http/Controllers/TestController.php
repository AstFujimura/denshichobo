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
use App\Models\Client;
use App\Models\Group;
use App\Models\Group_User;
use GuzzleHttp\Client as GuzzleClient;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestController extends Controller
{
    public function testGet($num)
    {
        if (Auth::id() == 1) {
            if ($num > 0) {
                for ($i = 0; $i < $num; $i++) {
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
            } else if ($num == -999) {
                // "備考カラム"が"aaa"のデータを取得
                File::where('備考', 'DLを押さないでください')->delete();
            } else if ($num == -10) {
                $files = File::all();
                foreach ($files as $file) {
                    $client = Client::where("取引先", $file->取引先)->first();
                    if ($client) {
                        $file->取引先 = $client->id;
                        $file->save();
                    } else {
                        if (!(Client::where("id", $file->取引先)->first())) {
                            $client = new Client();
                            $client->取引先 = $file->取引先;
                            $client->save();
                            $file->取引先 = $client->id;
                        }
                    }
                }
            } else if ($num == -11) {
                $files = File::all();
                foreach ($files as $file) {
                    $file->更新者ID = $file->保存者ID;
                    $file->save();
                }
            } else if ($num == -123) {

                File::query()->delete();
            }
            //グループ機能追加前に使用しているアカウントに対して保存者IDを一時的にグループIDとして保存 
            else if ($num == -1123) {
                $users = User::all();
                foreach ($users as $user) {
                    $newgroup = new Group();
                    $newgroup->id = $user->id;
                    $newgroup->グループ名 = $user->name . "(固有グループ名ghdF4ol)";
                    $newgroup->save();
                    $newGroupUser = new Group_User();
                    //中間テーブルに追加(今回は同じIDとなるが)
                    $newGroupUser->グループID = $newgroup->id;
                    $newGroupUser->ユーザーID = $user->id;
                    $newGroupUser->save();
                }


                $files = File::all();
                foreach ($files as $file) {
                    //デフォルトの100000のグループIDを保存者IDと同じにする。
                    $file->グループID = $file->保存者ID;
                    $file->save();
                }
            } else if ($num == -1156) {
                $files = File::all();
                foreach ($files as $file){
                    if ($file->グループID == 100000){
                        $file->グループID = $file->保存者ID;
                        $file->save();
                    }
                }
            }else if ($num == -5486) {

                if (Auth::id() == 1) {
                    return view("test.userexcel");
                }
            } else if ($num == -1923) {

                if (Auth::id() == 1) {
                    $deletegroups = Group::where("グループ名", "like", "%" . "(固有グループ名excel1923)")
                        ->get();
                    foreach ($deletegroups as $deletegroup) {
                        $id = $deletegroup->id;
                        Group_User::where("グループID", $id)->delete();
                        User::where("id", $id)->delete();
                        $deletegroup->delete();
                    }
                }
            }
            else if ($num == -853) {

                if (Auth::id() == 1) {
                    $users = User::all();
                    foreach ($users as $user){
                        $user->ニュース番号 = 0;
                        $user->save();
                    }
                }
            }
            else if ($num == -515) {

                if (Auth::id() == 1) {
                    if (!Group::find(1)){
                        $newgroup = new Group();
                        $newgroup->id = 1;
                        $newgroup->グループ名 = "astec(固有グループ名ghdF4ol)";
                        $newgroup->save();

                        $newGroupUser = new Group_User();
                        $newGroupUser->グループID = 1;
                        $newGroupUser->ユーザーID = 1;
                    }
                    if (!Group::find(2)){
                        $newgroup = new Group();
                        $newgroup->id = 2;
                        $newgroup->グループ名 = "管理者(固有グループ名ghdF4ol)";
                        $newgroup->save();

                        $newGroupUser = new Group_User();
                        $newGroupUser->グループID = 2;
                        $newGroupUser->ユーザーID = 2;
                    }
                }
            }
        }



        return redirect()->route("topGet");
    }
    public function userExcel(Request $request)
    {
        if (Auth::id() == 1) {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            foreach ($sheetData as $row) {
                $newuser = new User();
                $newuser->name = $row["B"];
                $newuser->password = Hash::make($row["A"]);
                $newuser->email = $row["C"];
                $newuser->管理 = "一般";
                $newuser->削除 = "";
                $newuser->パスワードリセット時 = "2000-01-01 00:00:00";
                $newuser->save();

                $newgroup = new Group();
                $newgroup->id = $newuser->id;
                $newgroup->グループ名 = $row["B"] . "(固有グループ名excel1923)";
                $newgroup->save();

                $newGroupUser = new Group_User();
                $newGroupUser->グループID = $newuser->id;
                $newGroupUser->ユーザーID = $newuser->id;
                $newGroupUser->save();
            }

            return back()->with('success', 'Excelファイルをインポートしました。');
        }
    }

    public function groupsetting()
    {
        $users = User::all();
        foreach ($users as $user) {
            $newgroup = new Group();
            $newgroup->id = $user->id;
            $newgroup->グループ名 = $user->name . "(固有グループ名ghdF4ol)";
            $newgroup->save();
            $newGroupUser = new Group_User();
            //中間テーブルに追加(今回は同じIDとなるが)
            $newGroupUser->グループID = $newgroup->id;
            $newGroupUser->ユーザーID = $user->id;
            $newGroupUser->save();
        }

        $files = File::all();
        foreach ($files as $file) {
            //デフォルトの100000のグループIDを保存者IDと同じにする。
            $file->グループID = $file->保存者ID;
            $file->save();
        }
    }
}
