<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\Version;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminController extends Controller
{

    //管理者画面に進む時
    public function adminGet()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        //管理者ユーザーとしてログイン状態かどうかを確認して管理者ユーザー出なければトップページにリダイレクト
        if (Auth::user()->管理 == "管理") {
            //astecユーザーを表示しないため
            $users = User::where('id', '>=', 2)
                ->where('削除', '')
                ->get();
            return view('admin.adminpage', compact('users', 'prefix', 'server'));
        } else {
            return redirect()->route('topGet');
        }
    }

    // public function admintop()
    // {
    //     return redirect()->route('topGet');
    // }

    public function adminregistGet()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $groups = Group::where("id", ">", 100000)->get();

        if (Auth::user()->管理 == "管理") {
            return view('admin.adminregist', compact('prefix', 'server', 'groups'));
        } else {
            return redirect()->route('topGet');
        }
    }


    public function adminregistPost(Request $request)
    {
        if (Auth::user()->管理 == "管理") {

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string',
            ]);



            $user = new User();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->password = Hash::make($validatedData['password']);
            $user->管理 = $request->input('admin');
            $user->save();

            $grouparray = $request->input('grouparray', []);

            //固有グループ名を追加する
            $newgroup = new Group();
            $newgroup->id = $user->id;
            $newgroup->グループ名 = $user->name . "(固有グループ名ghdF4ol)";
            $newgroup->save();

            //中間テーブルにも追加する
            $newGroupUser = new Group_User();
            $newGroupUser->グループID = $user->id;
            $newGroupUser->ユーザーID = $user->id;
            $newGroupUser->save();

            //送信されたグループをそれぞれ中間テーブルに追加していく
            foreach ($grouparray as $group) {
                $newGroupUser = new Group_User();
                $newGroupUser->グループID = $group;
                $newGroupUser->ユーザーID = $user->id;
                $newGroupUser->save();
            }


            return redirect()->route('adminGet');
        } else {
            return redirect()->route('topGet');
        }
    }


    public function admineditGet($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        if (Auth::user()->管理 == "管理") {
            $user = User::where('id', '=', $id)->first();
            if (!$user) {
                return response()->json(['message' => 'ユーザーが見つかりません'], 404);
            }
            $groups = Group::where("id", ">", 100000)->get();

            foreach ($groups as $group) {
                //中間テーブルからユーザーが属しているグループを検索してくる
                $includedGroup = Group_User::where("ユーザーID", $id)
                    ->where('グループID', $group->id)
                    ->first();
                if ($includedGroup) {
                    $group->checked = "checked";
                } else {
                    $group->checked = "";
                }
            }


            $data = [
                'user' => $user,
                'admin' => "",
                'normal' => "",
                'prefix' => $prefix,
                'server' => $server,
                'groups' => $groups,
            ];
            if ($user->管理 == "管理") {
                $data['admin'] = "selected";
            } else if ($user->管理 == "一般") {
                $data['normal'] = "selected";
            }

            return view('admin.adminedit', $data);
        } else {
            return redirect()->route('topGet');
        }
    }

    public function admineditPut(Request $request, $id)
    {
        if (Auth::user()->管理 == "管理") {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'ユーザーが見つかりません'], 404);
            }


            // 取得したユーザー情報を利用する処理
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->管理 = $request->input('admin');

            $user->save();

            $grouparray = $request->input('grouparray', []);

            // //固有グループ名を追加する
            // $newgroup = new Group();
            // $newgroup->id = $user->id;
            // $newgroup->グループ名 = $user->name . "(固有グループ名ghdF4ol)";
            // $newgroup->save();

            // //中間テーブルにも追加する
            // $newGroupUser = new Group_User();
            // $newGroupUser->グループID = $user->id;
            // $newGroupUser->ユーザーID = $user->id;
            // $newGroupUser->save();

            //固有グループ名を除くグループIDとユーザーIDの組み合わせのレコードを一旦消去する
            Group_User::where("グループID", ">", 100000)
                ->where('ユーザーID', $user->id)
                ->delete();

            //送信されたグループをそれぞれ中間テーブルに追加していく
            foreach ($grouparray as $group) {

                $newGroupUser = new Group_User();
                $newGroupUser->グループID = $group;
                $newGroupUser->ユーザーID = $user->id;
                $newGroupUser->save();
            }

            return redirect()->route('adminGet');
        } else {
            return redirect()->route('topGet');
        }
    }

    public function adminresetPost($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }
        // 更新時にはパスワードをリセットしない
        if ($user->パスワードリセット時->diffInMinutes(now()) > 1) {
            $password = $this->generateRandomStr(8);
            $user->password = Hash::make($password);
            $user->パスワードリセット時 = Carbon::now();
            $user->save();
            return view('admin.adminreset', compact('password', 'prefix', 'server'));
        } else {
            return redirect()->route("errorGet", ['code' => 'P127262']);
        }
    }

    public function adminDelete($id)
    {
        if (Auth::user()->管理 == "管理") {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'ユーザーが見つかりません'], 404);
            } else if (User::where('管理', '管理')->get()->count() == 1 && $user->管理 = "管理") {
                abort(404);
            }
            $user->削除 = "削除";
            $user->name = $user->name . "(削除ユーザー)";
            $user->password = Hash::make($this->generateRandomStr(16));
            $user->save();

            //該当するユーザーIDを含む中間テーブルのレコードを削除する
            Group_User::where("ユーザーID", $id)->delete();

            if (Auth::user()->id == $id) {
                Auth::logout();
            }
            return redirect()->route('adminGet');
        } else {
            return redirect()->route('topGet');
        }
    }

    //$idは変更・削除しようとするuserのidであり変更するものを除いて管理ユーザーが何人いるかを返すAPI
    //これを受けとったjs側で変更・削除をするか否かの処理を行う
    public function admincheck($id)
    {
        $count = User::where("管理", "管理")
            ->where("削除", "")
            ->where("id", "not like", $id)
            ->where("id", "not like", 1)
            ->get()
            ->count();
        return $count;
    }

    public function documentcheck($id)
    {
        $document = File::where("書類ID", $id)
            ->first();
        if (!$document) {
            $record = Document::find($id);
            $record->delete();
        }
        return $document;
    }


    //ランダムな6桁のstring型の数値を出力
    private function generateRandomStr($digit)
    {
        $randomString = Str::random($digit); // 10文字のランダムな文字列を生成
        return $randomString;
    }

    public function admindocumentGet()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $documents = Document::orderBy('order', 'asc')->get();
        return view("admin.admindocument", compact("documents", 'prefix', 'server'));
    }

    public function admindocumentPost(Request $request)
    {

        $docuarray = json_decode($request->getContent());

        foreach ($docuarray as $document) {
            if ($document->past == "past") {
                $pastdocument = Document::where("id", $document->id)->first();
                $pastdocument->check = $document->check;
                $pastdocument->書類 = $document->document;
                $pastdocument->order = $document->order;
                $pastdocument->save();
            } else if ($document->past == "new") {
                $docu = Document::where("書類", $document->document)->first();
                if (!$docu) {
                    $newdocument = new Document();
                    $newdocument->check = $document->check;
                    $newdocument->書類 = $document->document;
                    $newdocument->order = $document->order;
                    $newdocument->save();
                }
            }
        }
        return response()->json("成功");
    }

    public function groupcheck($id)
    {
        $document = File::where("グループID", $id)
            ->first();
        //  グループに属している帳簿がなければ、削除
        // グループに紐づくユーザーの関係を削除
        // グループに属している役職の削除
        if (!$document) {
            $group_users = Group_User::where("グループID", $id)->delete();

            $positions = Position::where("グループID", $id)->delete();

            $record = Group::find($id);
            $record->delete();
        }
        return $document;
    }

    public function admingroupregistGet()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $groups = Group::where('id', '>', 100000)->get();
        $version = Version::find(1)->フロー;

        return view("admin.admingroup", compact("groups", 'version', 'prefix', 'server'));
    }

    public function admingroupregistPost(Request $request)
    {
        $grarray = json_decode($request->getContent());

        foreach ($grarray as $group) {
            if ($group->past == "past") {
                $pastgroup = Group::where("id", $group->id)->first();
                $pastgroup->グループ名 = $group->group;
                $pastgroup->save();
            } else if ($group->past == "new") {
                $gr = Group::where("グループ名", $group->group)->first();
                if (!$gr) {
                    $newgroup = new Group();
                    $newgroup->グループ名 = $group->group;
                    $newgroup->save();
                }
            }
        }
        return response()->json("成功");
    }

    public function admingroupuserGet($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $server = config('prefix.server');

        $group = Group::find($id);
        $users = DB::table("group_user")
            ->select('users.*', 'group_user.役職ID')
            ->leftJoin('users', 'group_user.ユーザーID', '=', 'users.id')
            ->where('group_user.グループID', $id)
            ->get();

        $count = $users->count();


        $allusers = User::where("id", ">", 1)->get();
        $positions = Position::where("グループID", $id)->get();
        return view("admin.admingroupuser", compact("group", "users", "allusers", "count", "positions", 'prefix', 'server'));
    }
    public function admingroupuserPost(Request $request, $id)
    {
        $groupuser_count = $request->input("groupusercount");
        Group_User::where("グループID",$id)->delete();
        for ($i = 1; $i <= $groupuser_count; $i++) {
            $userid = $request->input("user" . $i);
            $positionid = $request->input("position" . $i);
            // ユーザー名が空欄でなかった場合(フロントエンドで制御するが万が一漏れていた場合の保険)
            if ($userid) {
                // 役職が指定されたければ役職IDを0にする
                if (!$positionid) {
                    $positionid = 0;
                }
                $newgroupuser = new Group_User();
                $newgroupuser->グループID = $id;
                $newgroupuser->ユーザーID = $userid;
                $newgroupuser->役職ID = $positionid;
                $newgroupuser->save();
            }
        }
        return redirect()->route("admingroupuserGet", ['id' => $id]);
    }
    public function admingrouppositionGet($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $group = Group::find($id);
        // グループIDが該当する役職の一覧
        $positions = Position::where("グループID", $id)->get();
        return view("admin.admingroupposition", compact("group", 'prefix', 'server', 'positions'));
    }

    public function admingrouppositionPost(Request $request, $id)
    {

        $positionarray = json_decode($request->getContent());

        foreach ($positionarray as $position) {
            if ($position->past == "past") {
                $pastposition = Position::where("id", $position->id)->first();
                $pastposition->役職 = $position->position;
                $pastposition->グループID = $id;
                $pastposition->save();
            } else if ($position->past == "new" && $position->position != "") {
                $existposition = Position::where("役職", $position->position)
                    ->where("グループID", $id)
                    ->first();
                if (!$existposition) {
                    $newposition = new Position();
                    $newposition->役職 = $position->position;
                    $newposition->グループID = $id;
                    $newposition->save();
                }
            }
        }
        return response()->json("成功");
    }

    public function admingrouppositiondeleteGet($id)
    {

        // 指定の役職を消去する
        $position = Position::find($id);
        $position->delete();

        // グループユーザーテーブルから該当の役職があれば0にしてそのユーザーの役職をなしにする
        $group_users = Group_User::where("役職ID", $id)->get();
        foreach ($group_users as $group_user) {
            $group_user->役職ID = 0;
            $group_user->save();
        }


        return response()->json("成功");
    }
}
