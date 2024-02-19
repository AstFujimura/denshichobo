<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use App\Models\Document;
use App\Models\File;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\M_approval;
use App\Models\M_flow;
use App\Models\M_flow_group;
use App\Models\M_flow_point;
use App\Models\M_next_flow_point;
use App\Models\T_flow;
use App\Models\T_flow_point;
use App\Models\T_approval;
use App\Models\T_flow_draft;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class FlowController extends Controller
{
    public function flowuserlist(Request $request)
    {
        $searchtext = $request->input("search");
        $duplicationarray = $request->input("duplicationarray");

        if (!$duplicationarray) {
            $users = User::where('name', 'like', '%' . $searchtext . '%')
                ->where('削除', "")
                ->get();
        } else {
            $users = User::where('name', 'like', '%' . $searchtext . '%')
                ->whereNotIn('name', $duplicationarray)
                ->where('削除', "")
                ->get();
        }

        return response()->json($users);
    }
    public function flowusercheck(Request $request)
    {
        $userarray = $request->input("userarray");
        // 個人名入力がある場合
        if ($userarray) {
            // 重複を除いた新しい配列を作成
            $uniqueArray = array_unique($userarray);

            // 重複がないか確認
            if (count($userarray) === count($uniqueArray)) {
                $uniqueerror = false;
            } else {
                $uniqueerror = true;
            }

            $noneuserarray = [];
            foreach ($userarray as $user) {
                $exsistinguser = User::where("name", $user)->first();
                if (!$exsistinguser) {
                    $noneuserarray[] = $user;
                }
            }
            if (count($noneuserarray) == 0) {
                $noneusererror = false;
            } else {
                $noneusererror = true;
            }
        }
        // グループだけの場合
        else {
            $uniqueerror = false;
            $noneusererror = false;
            $noneuserarray = [];
        }


        return response()->json([$uniqueerror, $noneusererror, $noneuserarray]);
    }

    public function viewonlyworkflow($id)
    {
        $flow_points = M_flow_point::where("フローマスタID", $id)
            ->get();
        $flow_point_object = [];
        $maxcolumn = 1;
        $maxrow = 1;
        foreach ($flow_points as $flow_point) {
            $point = $flow_point->フロントエンド表示ポイント;
            // アンダースコア（_）をデリミタとして文字列を分割
            $parts = explode("_", $point);
            if ($maxcolumn < $parts[0]) {
                $maxcolumn = $parts[0];
            }
            if ($maxrow < $parts[1]) {
                $maxrow = $parts[1];
            }

            $flow_point_object[] = [
                'column' => $parts[0],
                'row' => $parts[1],
                'required' => $flow_point->承認ポイント,
                'parameter' => $flow_point->母数,
                'person_group' => $flow_point->個人グループ,
                // 他の属性があれば追加
            ];
        }

        $next_flow_points = DB::table("m_next_flow_points")
            ->select("m_next_flow_points.*", "m_flow_points.フロントエンド表示ポイント")
            ->leftJoin("m_flow_points", "m_next_flow_points.現フロー地点ID", "=", "m_flow_points.id")
            ->where("m_next_flow_points.フローマスタID", $id)
            ->get();


        $line_object = [];
        foreach ($next_flow_points as $next_flow_point) {
            $startpoint = $next_flow_point->フロントエンド表示ポイント;
            $endpoint = $next_flow_point->次フロントエンド表示ポイント;
            // アンダースコア（_）をデリミタとして文字列を分割
            $startparts = explode("_", $startpoint);
            $endparts = explode("_", $endpoint);

            $line_object[] = [
                'startcolumn' => $startparts[0],
                'startrow' => $startparts[1],
                'endcolumn' => $endparts[0],
                'endrow' => $endparts[1],
            ];
        }


        $approvals = DB::table("m_approvals")
            ->select("m_approvals.*", "m_flow_points.*",  "users.name", "groups.グループ名","positions.役職",)
            ->leftJoin("m_flow_points", "m_approvals.フロー地点ID", "=", "m_flow_points.id")
            ->leftJoin("users", "m_approvals.ユーザーID", "=", "users.id")
            ->leftJoin("groups", "m_approvals.グループID", "=", "groups.id")
            ->leftJoin("positions", "m_approvals.役職ID", "=", "positions.id")
            ->where("m_approvals.フローマスタID", $id)
            ->get();
        $approval_object = [];
        foreach ($approvals as $approval) {
            // アンダースコア（_）をデリミタとして文字列を分割
            $parts = explode("_", $approval->フロントエンド表示ポイント);
            $approval_object[] = [
                'column' => $parts[0],
                'row' => $parts[1],
                'person_group' => $approval->個人グループ,
                'user' => $approval->name,
                'group' => $approval->グループ名,
                'groupid' => $approval->グループID,
                'position' => $approval->役職,
                'id' => $approval->フローマスタID,
            ];
        }


        return response()->json([$flow_point_object, $maxcolumn, $maxrow, $line_object, $approval_object]);
    }
    public function flowgrouplist($groupid)
    {
        $groups = DB::table("group_user")
        ->select("group_user.*","users.*")
        ->leftJoin("users","group_user.ユーザーID","=","users.id")
        ->where("グループID",$groupid)
        ->get();

        $group_object = [];
        foreach ($groups as $group) {
            $group_object[] = [
                'id' => $group->グループID,
                'name' => $group->name,
            ];
        }
        return response()->json($group_object);
    }


    public function flowobject($id)
    {
        $object = json_decode(M_flow::find($id)->フロントエンドオブジェクト, true);
        return response()->json($object);
    }



    public function workflowregistget()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $groups = Group::where('id', ">", 100000)
            ->get();
        foreach ($groups as $group) {
            $count = Group_User::where("グループID", $group->id)
                ->count();
            $group->count = $count;
        }
        $positions = Position::all();
        // 役職についている人が何人いるかを中間テーブルから取得して新たなカラムとして付与
        foreach ($positions as $position) {
            $positioncount = Group_User::where("役職ID", $position->id)
                ->count();
            $position->count = $positioncount;
        }
        return view('flow.workflowregist', compact("prefix", "server", "groups", "positions"));
    }
    public function workflowregistpost(Request $request)
    {
        // 編集か新規登録かを判断する
        $regist_edit = $request->input("edit");
        $flow_master_id = $request->input("flow_master_id");
        $pastid = false;
        if ($regist_edit == "edit") {
            $t_flow = T_flow::where("フローマスタID", $flow_master_id)
                ->first();
            // フローのトランザクションデータがあった場合は古いデータの削除フラグを立てる
            if ($t_flow) {
                $m_flow = M_flow::find($flow_master_id);
                $m_flow->削除フラグ = true;
                $m_flow->save();
            }
            // フローのトランザクションデータがない場合はマスタデータの削除
            else {
                $pastid = $flow_master_id;
                $m_flow = M_flow::find($flow_master_id)->delete();
            }
        }

        // -------------フローマスタ-----------------------------
        $flow_name = $request->input("flow_name");
        $flow_groups = $request->input("flow_group");
        $start_flow_price = $request->input("start_flow_price");
        $end_flow_price = $request->input("end_flow_price");
        $arrays = $request->input("arrays");

        // 分岐の数
        $arraycount = $request->input("arraycount");
        // 決裁地点数
        $lastelementcount = $request->input("lastelementcount");

        $flow_master = new M_flow();
        if ($pastid) {
            $flow_master->id = $pastid;
        }


        $flow_master->フロー名 = $flow_name;
        if (!$flow_master) {
            $flow_groups->グループ条件 = false;
        }
        // 金額に指定があった場合
        // デフォルトでは下限0　上限20億に設定している
        if ($start_flow_price) {
            $flow_master->金額下限条件 = $start_flow_price;
        }
        if ($end_flow_price) {
            $flow_master->金額上限条件 = $end_flow_price;
        }
        $flow_master->決裁地点数 = $lastelementcount;
        $flow_master->フロントエンドオブジェクト = $arrays;

        // フローマスタを登録
        $flow_master->save();
        // -----------------------------------------------------




        // -------------フローグループ条件マスタ-------------------


        // フローグループ条件マスタをそれぞれ登録
        foreach ($flow_groups as $groupid) {
            $flow_group_master = new M_flow_group();
            $flow_group_master->フローマスタID = $flow_master->id;
            $flow_group_master->グループiD = $groupid;
            $flow_group_master->save();
        }

        // // -----------------------------------------------------



        // -------------フロー地点マスタ-------------------
        $elements = json_decode($request->input("elements"), true);


        foreach ($elements as $key => $element) {

            $flow_point = new M_flow_point();
            if ($key == "10000") {
                $flow_point->フローマスタID = $flow_master->id;
                $flow_point->承認移行ポイント = 0;
                $flow_point->承認ポイント = 0;
                $flow_point->個人グループ = 0;
                $flow_point->承認可能状態 = true;
                $flow_point->フロントエンド表示ポイント = "1_1";
            } else {
                $flow_point->フローマスタID = $flow_master->id;
                $flow_point->承認移行ポイント = 0;
                $flow_point->承認ポイント = $element["required_number"];
                $flow_point->承認可能状態 = $element["approvable"];
                $flow_point->フロントエンド表示ポイント = $element["point"];
                $flow_point->母数 = $element["parameter"];
                if ($element["authorizer"] == "person") {
                    $flow_point->個人グループ = 1;
                } else if ($element["authorizer"] == "group") {
                    if ($element["select_method"] == "nolimit") {
                        $flow_point->個人グループ = 2;
                    } else if ($element["select_method"] == "byapplicant") {
                        $flow_point->個人グループ = 3;

                        $flow_point->申請者選択数 = $element["group_choice_number"];
                    } else if ($element["select_method"] == "postchoice") {
                        $flow_point->個人グループ = 4;
                    }
                }
            }

            $flow_point->save();


            // -----------------------------


            // ------承認マスタ-----------------------

            // 承認_個人の場合
            if ($flow_point->個人グループ == 1) {
                foreach ($element["person_name"] as $person) {
                    // ユーザーテーブルから名前でレコードを取得
                    $user = User::where("name", $person)->first();

                    // 存在する場合、承認マスタを作成
                    if ($user) {
                        $userid = $user->id;
                        $approval = new M_approval();
                        $approval->フローマスタID = $flow_master->id;
                        $approval->フロー地点ID = $flow_point->id;
                        $approval->ユーザーID = $userid;
                        $approval->save();
                    }
                }
            }
            // グループ(限定無し)の場合
            else if ($flow_point->個人グループ == 2) {

                $approval = new M_approval();
                $approval->フローマスタID = $flow_master->id;
                $approval->フロー地点ID = $flow_point->id;
                $approval->グループID = $element["group_id"];
                $approval->save();
            }
            // グループ(申請者が選択)の場合
            else if ($flow_point->個人グループ == 3) {

                $approval = new M_approval();
                $approval->フローマスタID = $flow_master->id;
                $approval->フロー地点ID = $flow_point->id;
                $approval->グループID = $element["group_id"];
                $approval->save();
            }
            // グループ(役職から選択)の場合
            else if ($flow_point->個人グループ == 4) {
                foreach ($element["position"] as $position_id) {
                    $approval = new M_approval();
                    $approval->フローマスタID = $flow_master->id;
                    $approval->フロー地点ID = $flow_point->id;
                    $approval->グループID = $element["group_id"];
                    $approval->役職ID = $position_id;
                    $approval->save();
                }
            }
        }

        // -----------------------------------------------------

        // -------------次フロー地点マスタ-------------------

        $arraycount = $request->input("arraycount");

        for ($i = 1; $i < $arraycount + 1; $i++) {
            $array = $request->input("array" . $i);
            // 配列の要素数を取得
            $arrayLength = count($array);
            for ($j = 0; $j < $arrayLength; $j++) {
                // 配列が最後ではない場合
                if ($j + 1 < $arrayLength) {
                    // フロー地点マスタが存在するか確認
                    $flow_point = M_flow_point::where("フローマスタID", $flow_master->id)
                        ->where("フロントエンド表示ポイント", $array[$j])
                        ->first();
                    // 次フロー地点マスタに同じものが過去にあるかを確認する
                    $existing_next_flow_point = M_next_flow_point::where("現フロー地点ID", $flow_point)
                        ->where("次フロントエンド表示ポイント", $array[$j + 1])
                        ->first();
                    if ($flow_point && !$existing_next_flow_point) {
                        $next_flow_point = new M_next_flow_point();
                        $next_flow_point->フローマスタID = $flow_master->id;
                        $next_flow_point->現フロー地点ID = $flow_point->id;
                        $next_flow_point->次フロントエンド表示ポイント = $array[$j + 1];
                        $next_flow_point->save();

                        // 次のフロー地点の承認移行ポイントを1あげる
                        $flow_point_next = M_flow_point::where("フローマスタID", $flow_master->id)
                            ->where("フロントエンド表示ポイント", $next_flow_point->次フロントエンド表示ポイント)
                            ->first();
                        $flow_point_next->承認移行ポイント += 1;
                        $flow_point_next->save();
                    }
                }
                // 配列の最後である場合
                else {
                    $flow_point = M_flow_point::where("フローマスタID", $flow_master->id)
                        ->where("フロントエンド表示ポイント", $array[$j])
                        ->first();
                    $flow_point->決裁地点 = true;
                    $flow_point->save();
                }
            }
        }


        // -----------------------------------------------------



    }
    // ワークフローメインメニュー
    public function workflow(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('flow.workflow', compact("prefix", "server"));
    }
    // ワークフローマスタ一覧
    public function workflowmaster(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $flow_master = M_flow::where("削除フラグ", false)
            ->get();
        $flow_groups = DB::table("m_flow_groups")
            ->select("m_flow_groups.*", "groups.グループ名")
            ->leftJoin("groups", "groups.id", "=", "m_flow_groups.グループID")
            ->get();

        return view('flow.workflowmaster', compact("prefix", "server", "flow_master", "flow_groups"));
    }
    // ワークフロー詳細
    public function workflowmasterdetail(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('flow.workflowmasterdetail', compact("prefix", "server"));
    }
    // ワークフロー編集
    public function workfloweditget($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $groups = Group::where('id', ">", 100000)
            ->get();
        // チェックされているグループを確認して値を入れる
        foreach ($groups as $group) {
            $flow_group = M_flow_group::where("フローマスタID", $id)
                ->where("グループID", $group->id)
                ->first();
            if ($flow_group) {
                $group->checked = "checked";
            } else {
                $group->checked = "";
            }
        }

        $flow_master = M_flow::find($id);
        $flow_points = M_flow_point::where("フローマスタID", $id)
            ->get();

        $flow_approvals = DB::table('m_approvals')
            ->select("m_approvals.*", "users.name", "groups.グループ名", "positions.役職")
            ->leftJoin("users", "m_approvals.ユーザーID", "=", "users.id")
            ->leftJoin("groups", "m_approvals.グループID", "=", "groups.id")
            ->leftJoin("positions", "m_approvals.役職ID", "=", "positions.id")
            ->where("フローマスタID", $id)
            ->get();

        $selectmethod = ["nolimit", "nolimit", "nolimit", "byapplicant", "postchoice"];

        if ($flow_master) {

            // ------------フロー地点-----------------
            foreach ($flow_points as $flow_point) {
                $point = $flow_point->フロントエンド表示ポイント;

                // アンダースコア（_）をデリミタとして文字列を分割
                $parts = explode("_", $point);

                // 分割された部分をそれぞれ変数に代入
                $flow_point->column = $parts[0];
                $flow_point->row = $parts[1];
                if ($flow_point->column == 1 && $flow_point->row == 1) {
                    $flow_point->id = 10000;
                }
                // 個人の場合
                if ($flow_point->個人グループ == 1) {
                    $flow_point->person_group = "person";
                    $flow_point->person_parameter = $flow_point->母数;
                    $flow_point->group_parameter = 0;
                    $flow_point->person_required = $flow_point->承認ポイント;
                    $flow_point->group_required = 0;
                }
                // グループの場合 
                else if ($flow_point->個人グループ >= 2) {
                    $flow_point->person_group = "group";
                    $flow_point->person_parameter = 0;
                    $flow_point->group_parameter = $flow_point->母数;
                    $flow_point->group_required = $flow_point->承認ポイント;
                    $flow_point->person_required = 0;
                }
                // 申請者の場合
                else {
                    $flow_point->person_group = 0;
                    $flow_point->person_parameter = 0;
                    $flow_point->group_parameter = 0;
                    $flow_point->group_required = 0;
                    $flow_point->person_required = 0;
                }
                $flow_point->select_method = $selectmethod[$flow_point->個人グループ];
            }

            // -----------------------------

            // ------------承認マスタ-----------------

            // 承認マスタのがグループIDの場合、役職IDにも値がある可能性があるため
            // nowgroupに新しいグループIDを入れていき既存のグループIDであれば新たに作成しない
            $nowgroup = 0;
            foreach ($flow_approvals as $flow_approval) {
                if ($flow_approval->ユーザーID) {
                    $flow_approval->newgroup = "person";
                } else if ($flow_approval->グループID) {
                    // nowgroupと該当のグループIDが一致した場合(２つ目以降の役職のレコードの場合)
                    if ($nowgroup == $flow_approval->グループID) {
                        $flow_approval->newgroup = "none";
                    }
                    // 新しいグループIDかつ役職IDがない場合(限定無し、申請者が選択の場合)
                    else if ($nowgroup != $flow_approval->グループID && $flow_approval->役職ID) {
                        $flow_approval->newgroup = "newgroup_post";
                    }
                    // 新しいグループかつ役職IDがある場合(1つ目の役職のレコードの場合)
                    else {
                        $flow_approval->newgroup = "newgroup_none_post";
                    }
                    $nowgroup = $flow_approval->グループID;
                }
                if ($flow_approval->役職ID) {
                }
            }

            // -----------------------------


            // ------------次フロー地点マスタ-----------------

            $next_flow_points = DB::table("m_next_flow_points")
                ->select("m_next_flow_points.*", "m_flow_points.フロントエンド表示ポイント")
                ->leftJoin("m_flow_points", "m_next_flow_points.現フロー地点ID", "=", "m_flow_points.id")
                ->where("m_next_flow_points.フローマスタID", $id)
                ->get();

            foreach ($next_flow_points as $next_flow_point) {
                $nowpoint = $next_flow_point->フロントエンド表示ポイント;
                $nextpoint = $next_flow_point->次フロントエンド表示ポイント;

                // アンダースコア（_）をデリミタとして文字列を分割
                $nowparts = explode("_", $nowpoint);
                $nextparts = explode("_", $nextpoint);

                // 分割された部分をそれぞれ変数に代入
                $next_flow_point->startcolumn = $nowparts[0];
                $next_flow_point->startrow = $nowparts[1];
                $next_flow_point->endcolumn = $nextparts[0];
                $next_flow_point->endrow = $nextparts[1];
            }

            // -----------------------------

            // -----------グループユーザーマスタ------------------

            // グループのselectボックスに入れる値を格納する
            foreach ($groups as $group) {
                $count = Group_User::where("グループID", $group->id)
                    ->count();
                $group->count = $count;
            }
            $positions = Position::all();

            // -----------------------------

            // ---------------役職マスタ--------------

            // 役職についている人が何人いるかを中間テーブルから取得して新たなカラムとして付与
            foreach ($positions as $position) {
                $positioncount = Group_User::where("役職ID", $position->id)
                    ->count();
                $position->count = $positioncount;
            }

            // -----------------------------


            return view('flow.workflowedit', compact("prefix", "server", "groups", "positions", "flow_master", "flow_points", "flow_approvals", "next_flow_points"));
        } else {
            return view('flow.notfoundworkflow', compact("prefix", "server"));
        }
    }
    public function workflowapplicationget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('flow.workflowapplication', compact("prefix", "server"));
    }

    public function workflowapplicationpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $title = $request->input('title');
        $company = $request->input('company');
        $date = $request->input('date');
        $price = $request->input('price');
        $comment = $request->input('comment');
        $file = $request->file('file');
        $pastID = $this->generateRandomCode();

        $extension = $file->getClientOriginalExtension();

        $filename = Config::get('custom.file_upload_path');

        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');
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

        // 同じ申請者による下書きは古いものは削除する
        T_flow_draft::where("申請者ID", Auth::user()->id)->delete();

        $new_t_flow = new T_flow_draft();
        $new_t_flow->標題 = $title;
        $new_t_flow->コメント = $comment;
        $new_t_flow->ファイルパス = $filepath;
        $new_t_flow->取引先 = $company;
        $new_t_flow->金額 = $price;
        $new_t_flow->日付 = $date;
        $new_t_flow->申請者ID = Auth::user()->id;
        $new_t_flow->過去データID = $pastID;
        $new_t_flow->ファイル形式 = $extension;

        $new_t_flow->save();




        return redirect()->route('workflowchoiceget', ["id" => $new_t_flow->id]);
    }

    public function workflowchoiceget($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        // $t_flow_draft = T_flow_draft::find($id);
        $m_flows =  M_flow::all();
        $server = config('prefix.server');
        return view('flow.workflowchoice', compact("prefix", "server", "m_flows"));
    }





    public function workflowapproval(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('flow.workflowapproval', compact("prefix", "server"));
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
