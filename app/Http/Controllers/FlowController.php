<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\File;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\M_approval;
use App\Models\M_category;
use App\Models\M_flow;
use App\Models\M_flow_group;
use App\Models\M_flow_point;
use App\Models\M_mail;
use App\Models\M_next_flow_point;
use App\Models\M_optional;
use App\Models\M_pointer;
use App\Models\M_basic_pointer;
use App\Models\M_stamp;
use App\Models\M_stamp_char;
use App\Models\T_flow;
use App\Models\T_flow_point;
use App\Models\T_approval;
use App\Models\T_flow_draft;
use App\Models\Position;
use App\Models\T_optional;
use App\Models\M_flow_view_group;
use App\Models\M_tameru_regist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use TCPDF;
use setasign\Fpdi\TcpdfFpdi;
use \TCPDF_FONTS;

use Carbon\Carbon;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Tcpdf\Fpdi;

class FlowController extends Controller
{
    public function flowuserlist(Request $request)
    {
        $searchtext = $request->input("search");
        $duplicationarray = $request->input("duplicationarray");

        if (!$duplicationarray) {
            $users = User::where('name', 'like', '%' . $searchtext . '%')
                ->where('id', '!=', 1)
                ->where('削除', "")
                ->get();
        } else {
            $users = User::where('name', 'like', '%' . $searchtext . '%')
                ->where('id', '!=', 1)
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
    public function viewonlymetaworkflow($id)
    {
        $m_flow = M_flow::find($id);
        $flow_object = [
            'startprice' => $m_flow->金額下限条件,
            'endprice' => $m_flow->金額上限条件,
        ];
        $m_flow_groups = DB::table("m_flow_groups")
            ->select('groups.グループ名')
            ->leftJoin('groups', 'm_flow_groups.グループID', "groups.id")
            ->where("フローマスタID", $id)
            ->get();

        $group_object = [];
        foreach ($m_flow_groups as $m_flow_group) {
            $group_object[] = $m_flow_group->グループ名;
        }
        $flow_object['group_objects'] = $group_object;
        return response()->json($flow_object);
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
            ->select("m_approvals.*", "m_flow_points.*",  "users.name", "groups.グループ名", "positions.役職",)
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
            ->select("group_user.*", "users.*")
            ->leftJoin("users", "group_user.ユーザーID", "=", "users.id")
            ->where("グループID", $groupid)
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
        $categories = M_category::all();
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
        return view('flow.workflowregist', compact("prefix", "server", "categories", "groups", "positions"));
    }
    public function workflowregistpost(Request $request)
    {
        // 編集か新規登録かを判断する
        $regist_edit = $request->input("edit");
        $flow_master_id = $request->input("flow_master_id");
        // 編集する際トランザクションデータの有無によってマスタを削除するかを判定する
        // もしあった場合は削除フラグをたてるが、ない場合はそのマスタのIDを取得してから
        // 削除を行う。(これにより外部キー接続していたレコードも消去される)
        // そして取得したIDを再度使用して新たなレコードを作成する。
        // それを格納するための変数: pastid
        $pastid = false;
        if ($regist_edit == "edit") {
            $t_flow = T_flow::where("フローマスタID", $flow_master_id)
                ->first();
            // $t_flow_draft = T_flow_draft::where("フローマスタID", $flow_master_id)
            //     ->first();

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
        $category_id = $request->input("flow_category");
        $flow_name = $request->input("flow_name");
        $flow_groups = $request->input("flow_group");
        $flow_view_groups = $request->input("flow_view_group") ?? [];
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

        $flow_master->カテゴリマスタID = $category_id;
        $m_category = M_category::find($category_id);
        $flow_master->項目順 = $m_category->項目順;
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


        $m_flow_group = M_flow_group::where("フローマスタID", $flow_master->id)->delete();
        // フローグループ条件マスタをそれぞれ登録
        foreach ($flow_groups as $groupid) {
            $flow_group_master = new M_flow_group();
            $flow_group_master->フローマスタID = $flow_master->id;
            $flow_group_master->グループiD = $groupid;
            $flow_group_master->save();
        }

        // // -----------------------------------------------------

        // -------------閲覧グループ条件マスタ-------------------

        $m_flow_view_group = M_flow_view_group::where("フローマスタID", $flow_master->id)->delete();
        // 閲覧グループ条件マスタをそれぞれ登録
        foreach ($flow_view_groups as $groupid) {
            $flow_view_group_master = new M_flow_view_group();
            $flow_view_group_master->フローマスタID = $flow_master->id;
            $flow_view_group_master->グループiD = $groupid;
            $flow_view_group_master->save();
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
                    }
                    //  else if ($element["select_method"] == "byapplicant") {
                    //     $flow_point->個人グループ = 3;

                    //     $flow_point->申請者選択数 = $element["group_choice_number"];
                    // } 
                    else if ($element["select_method"] == "postchoice") {
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
            // // グループ(申請者が選択)の場合
            // else if ($flow_point->個人グループ == 3) {

            //     $approval = new M_approval();
            //     $approval->フローマスタID = $flow_master->id;
            //     $approval->フロー地点ID = $flow_point->id;
            //     $approval->グループID = $element["group_id"];
            //     $approval->save();
            // }
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
        $t_approval_count = T_approval::where('ユーザーID', Auth::id())
            ->where('ステータス', 2)
            ->count();
        $t_reapplication_count = T_approval::where('ユーザーID', Auth::id())
            ->where('ステータス', 7)
            ->count();
        return view('flow.workflow', compact("prefix", "server", "t_approval_count", "t_reapplication_count"));
    }
    // ワークフローマスタ一覧
    public function workflowmaster(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        //管理者ユーザーとしてログイン状態かどうかを確認して管理者ユーザー出なければトップページにリダイレクト
        if (Auth::user()->管理 == "管理") {

            $server = config('prefix.server');
            $flow_master = DB::table("m_flows")
                ->select("m_flows.*", "m_categories.カテゴリ名")
                ->leftJoin("m_categories", "m_categories.id", "=", "m_flows.カテゴリマスタID")
                ->where("m_flows.削除フラグ", false)
                ->where("m_categories.削除フラグ", false)
                ->orderBy("カテゴリマスタID")
                ->get();
            $flow_groups = DB::table("m_flow_groups")
                ->select("m_flow_groups.*", "groups.グループ名")
                ->leftJoin("groups", "groups.id", "=", "m_flow_groups.グループID")
                ->get();

            return view('flow.workflowmaster', compact("prefix", "server", "flow_master", "flow_groups"));
        } else {
            return redirect()->route('workflow');
        }
    }
    // ワークフロー詳細
    public function workflowmasterdetail(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == "管理") {
            return view('flow.workflowmasterdetail', compact("prefix", "server"));
        } else {
            return redirect()->route('workflow');
        }
    }
    // ワークフロー編集
    public function workfloweditget($id)
    {

        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        if (Auth::user()->管理 == "管理") {


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
                $view_group = M_flow_view_group::where("フローマスタID", $id)
                    ->where("グループID", $group->id)
                    ->first();
                if ($view_group) {
                    $group->view_checked = "checked";
                } else {
                    $group->view_checked = "";
                }
            }

            $flow_master = M_flow::find($id);
            $categories = M_category::all();
            foreach ($categories as $category) {
                if ($flow_master->カテゴリマスタID == $category->id || $category->削除フラグ == false) {
                    if ($category->id == $flow_master->カテゴリマスタID) {
                        $category->selected = "selected";
                    } else {
                        $category->selected = "";
                    }
                } else {
                    $categories = $categories->except($category->id);
                }
            }
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
                            $flow_approval->newgroup = "newgroup_post";
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


                return view('flow.workflowedit', compact("prefix", "server", "categories", "groups", "positions", "flow_master", "flow_points", "flow_approvals", "next_flow_points"));
            } else {
                return view('flow.notfoundworkflow', compact("prefix", "server"));
            }
        } else {
            return redirect()->route('workflow');
        }
    }
    // ワークフロー削除
    public function workflowdeleteget($id)
    {
        if (Auth::user()->管理 == "管理") {
            $t_flow = T_flow::where("フローマスタID", $id)
                ->first();
            // $t_flow_draft = T_flow_draft::where("フローマスタID", $id)
            //     ->first();
            $m_flow = M_flow::find($id);
            // フローのトランザクションデータが存在する場合は
            // レコードを削除せず削除フラグをたてる
            if ($t_flow) {
                $m_flow->削除フラグ = true;
                $m_flow->save();
            } else {
                $m_flow->delete();
            }

            return redirect()->route('workflowmaster');
        } else {
            return redirect()->route('workflow');
        }
    }

    // メール設定
    public function mailsettingget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        //管理者ユーザーとしてログイン状態かどうかを確認して管理者ユーザー出なければトップページにリダイレクト
        if (Auth::user()->管理 == "管理") {

            $M_mail = M_mail::first();

            return view('mail.mailsetting', compact("prefix", "server", "M_mail"));
        } else {
            return redirect()->route('workflow');
        }
    }
    // メール送信
    public function mailsettingpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        //管理者ユーザーとしてログイン状態かどうかを確認して管理者ユーザー出なければトップページにリダイレクト
        if (Auth::user()->管理 == "管理") {
            $name = $request->input("name");
            $mail = $request->input("mail");
            $host = $request->input("host");
            $port = $request->input("port");
            $username = $request->input("username");
            $password = $request->input("password");
            $encryptedPassword = Crypt::encryptString($password);
            $test_mail = $request->input("test_mail");

            $M_mail = M_mail::first();
            // メール設定がすでにされている場合(変更時)
            if ($M_mail) {
                $M_mail->name = $name;
                $M_mail->mail = $mail;
                $M_mail->host = $host;
                $M_mail->port = $port;
                $M_mail->username = $username;
                $M_mail->password = $encryptedPassword;
                $M_mail->test_mail = $test_mail;
                $M_mail->save();
            }
            // メール設定が初めての場合
            else {
                $new_M_mail = new M_mail();
                $new_M_mail->name = $name;
                $new_M_mail->mail = $mail;
                $new_M_mail->host = $host;
                $new_M_mail->port = $port;
                $new_M_mail->username = $username;
                $new_M_mail->password = $encryptedPassword;
                $new_M_mail->test_mail = $test_mail;
                $new_M_mail->save();
            }
            return redirect()->route('workflow');
        }
    }
    // テストメール送信
    public function mailsettingtestsend(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == "管理") {
            $name = $request->input("name");
            $mail = $request->input("mail");
            $host = $request->input("host");
            $port = $request->input("port");
            $username = $request->input("username");
            $password = $request->input("password");
            $test_mail = $request->input("test_mail");
            if (!$password) {
                $m_mail_password = M_mail::first()->password;
                $password = Crypt::decryptString($m_mail_password);
            }

            $mailConfig = [
                'driver' => 'smtp',
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password,
                'encryption' => 'tls',
            ];

            config(['mail' => $mailConfig]);
            try {
                Mail::send('mail.testmail', [], function ($message) use ($test_mail, $mail, $name) {
                    $message->to($test_mail)
                        ->subject('Rapidからのテスト送信')
                        ->from($mail, $name);
                });
                return response()->json('送信しました');
                // return redirect()->route('workflow');
            } catch (\Exception) {
                return response()->json('送信できませんでした');
            }
        }
    }

    // カテゴリ設定
    public function categoryget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == "管理") {
            $m_categories = M_category::where("削除フラグ", false)->get();

            return view('flow.workflowcategory', compact("prefix", "server", "m_categories"));
        } else {
            return redirect()->route('workflow');
        }
    }
    // カテゴリ名変更非同期通信API
    public function categorychangeget($id, $value)
    {
        if (Auth::user()->管理 == "管理") {
            try {
                $m_category = M_category::find($id);
                $m_category->カテゴリ名 = $value;
                $m_category->save();
                return response()->json('変更');
            } catch (\Exception) {
                return response()->json('エラーが発生しました');
            }
        } else {
            return response()->json('権限がありません');
        }
    }
    // カテゴリ詳細変更
    public function categorydetailget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == "管理") {
            $m_category = M_category::find($id);

            $order = $m_category->項目順;
            $annotation = $m_category->注釈;

            // アンダースコア（_）をデリミタとして文字列を分割
            $parts = explode("_", $order);

            $items = array();
            $tameru_condition = [
                "file" => false,
                "date" => false,
                "price" => false,
                "company" => false
            ];
            foreach ($parts as $part) {
                $columns = array();
                $columns["id"] = $part;

                $m_optional = M_optional::find($part);
                $columns["項目名"] = $m_optional->項目名;
                $columns["型"] = $m_optional->型;
                $columns["最大"] = $m_optional->最大;
                $columns["必須項目"] = $m_optional->必須;
                $columns["デフォルト"] = $m_optional->デフォルト;
                $columns["金額条件"] = ($m_optional->金額条件 !== 0) ? "checked" : null;

                $items[] = $columns;
                if ($m_optional->型 == 4) {
                    $tameru_condition["file"] = true;
                }
                if ($m_optional->型 == 3) {
                    $tameru_condition["date"] = true;
                }
                if ($m_optional->型 == 2) {
                    $tameru_condition["price"] = true;
                }
                if ($m_optional->型 == 1 && $m_optional->デフォルト == false) {
                    $tameru_condition["company"] = true;
                }
            }
            if ($tameru_condition["file"] == true && $tameru_condition["date"] == true && $tameru_condition["price"] == true && $tameru_condition["company"] == true) {
                $tameru_condition = true;
            } else {
                $tameru_condition = false;
            }

            return view('flow.workflowcategorydetail', compact("prefix", "server", "m_category", "items", "order", "annotation", "id", "tameru_condition"));
        } else {
            return redirect()->route('workflow');
        }
    }

    // カテゴリTAMERU設定
    public function categorytamerusettingget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == "管理") {
            $m_category = M_category::find($id);

            // カテゴリの項目順を取得してその項目のみを取得する
            $optionals = explode("_", $m_category->項目順);

            // 型が4のもの(ファイル型)を取得する
            $m_optionals = M_optional::where("カテゴリマスタID", $id)
                ->whereIn("id", $optionals)
                ->where("型", 4)
                ->get();
            foreach ($m_optionals as $m_optional) {
                $m_tameru_regist = M_tameru_regist::where("ファイル", $m_optional->id)->first();
                if ($m_tameru_regist) {
                    $m_optional->checked = 'checked';
                    $m_optional->date_optional = $m_tameru_regist->取引日;
                    $m_optional->price_optional = $m_tameru_regist->金額;
                    $m_optional->company_optional = $m_tameru_regist->取引先;
                    $m_optional->document_optional = $m_tameru_regist->書類区分;
                    $m_optional->submit_optional = $m_tameru_regist->提出;
                    $m_optional->save_optional = $m_tameru_regist->保存方法;
                    $m_optional->search_optional = $m_tameru_regist->検索ワード;
                } else {
                    $m_optional->date_optional = "";
                    $m_optional->price_optional = "";
                    $m_optional->company_optional = "";
                    $m_optional->document_optional = "";
                    $m_optional->submit_optional = "";
                    $m_optional->save_optional = "";
                    $m_optional->search_optional = "";
                }
            }

            $date_optionals = M_optional::where("カテゴリマスタID", $id)
                ->whereIn("id", $optionals)
                ->where("型", 3)
                ->where("必須", true)
                ->get();
            $price_optionals = M_optional::where("カテゴリマスタID", $id)
                ->whereIn("id", $optionals)
                ->where("型", 2)
                ->where("必須", true)
                ->get();
            $company_optionals = M_optional::where("カテゴリマスタID", $id)
                ->whereIn("id", $optionals)
                ->where("型", 1)
                ->where("デフォルト", false)
                ->get();

            $documents = Document::all();
            return view('flow.workflowtamerusetting', compact("prefix", "server", "id", "m_category", "m_optionals", "documents", "date_optionals", "price_optionals", "company_optionals"));
        } else {
            return redirect()->route('workflow');
        }
    }
    // カテゴリTAMERU設定ポスト
    public function categorytamerusettingpost(Request $request)
    {
        if (Auth::user()->管理 == "管理") {
            $category_id = $request->input('category_id');
            $m_optionals = M_optional::where("カテゴリマスタID", $category_id)
                ->where("型", 4)
                ->get();
            foreach ($m_optionals as $m_optional) {
                if ($request->input("optional" . $m_optional->id)) {
                    $m_tameru_regist = M_tameru_regist::updateOrCreate(
                        ['ファイル' => $m_optional->id],
                        [
                            'カテゴリマスタID' => $category_id,
                            '取引日' => $request->input("date_optional" . $m_optional->id),
                            '金額' => $request->input("price_optional" . $m_optional->id),
                            '取引先' => $request->input("company_optional" . $m_optional->id),
                            '書類区分' => $request->input("document_optional" . $m_optional->id),
                            '提出' => $request->input("submit_optional" . $m_optional->id),
                            '保存方法' => $request->input("save_optional" . $m_optional->id),
                            '検索ワード' => $request->input("search_optional" . $m_optional->id) ?? ''
                        ]
                    );
                } else {
                    // チェックボックスが外された場合は削除
                    M_tameru_regist::where('ファイル', $m_optional->id)->delete();
                }
            }
            return redirect()->route('categorytamerusettingget', ["id" => $category_id]);
        } else {
            return redirect()->route('workflow');
        }
    }

    // カテゴリ詳細変更ポスト
    public function categorydetailpost(Request $request)
    {

        if (Auth::user()->管理 == "管理") {
            $category_id = $request->input('id');
            $m_category = M_category::find($category_id);
            $order = $request->input('order');
            // 新規追加された場合項目のidが仮の形(50000以上)のものになっているため
            // DBに登録してそのidを取得したものを使用して正式なものをofficial_orderに格納する
            $official_order = "";
            // アンダースコア（_）をデリミタとして文字列を分割
            $parts = explode("_", $order);
            // 金額条件を一旦すべてnullにしておく
            M_optional::where('カテゴリマスタID', $category_id)->update(['金額条件' => false]);
            foreach ($parts as $part) {
                // 新規に追加された項目
                // 新規に追加されたものは50000以上としている
                if ($part >= 50000) {
                    $new_m_optional = new M_optional();
                    $new_m_optional->カテゴリマスタID = $category_id;
                    $new_m_optional->項目名 = $request->input("name_" . $part . "");
                    $new_m_optional->型 = $request->input("type_" . $part . "");
                    $new_m_optional->最大 = $request->input("max_" . $part . "");
                    $new_m_optional->必須 = $request->input("required_" . $part . "");
                    // 金額条件に合致するものはtrueにする
                    if ($request->input('price') == $part) {
                        $new_m_optional->金額条件 = true;
                    }
                    $new_m_optional->save();
                    $part = $new_m_optional->id;
                }
                // 既存の項目
                else {
                    $m_optional = M_optional::find($part);
                    if ($m_optional) {
                        $m_optional->項目名 = $request->input("name_" . $part . "");
                        // 項目の標題はdisableとなっておりinputに値が入っていないため
                        $m_optional->型 = $request->input("type_" . $part . "") ?? 1;
                        $m_optional->最大 = $request->input("max_" . $part . "") ?? 30;
                        $m_optional->必須 = $request->input("required_" . $part . "") ?? 1;
                        // 金額条件に合致するものはtrueにする
                        if ($request->input('price') == $part) {
                            $m_optional->金額条件 = true;
                        }
                        $m_optional->save();
                    }
                }
                // 最初
                if ($official_order == "") {
                    $official_order = $part;
                }
                // 二番目以降
                else {
                    $official_order = $official_order . "_" . $part;
                }
                $m_category->項目順 = $official_order;
                $m_category->注釈 = $request->input('annotation');
                $m_category->save();
            }
            return redirect()->route('categorydetailget', ["id" => $category_id]);
        } else {
            return redirect()->route('workflow');
        }
    }

    // カテゴリを追加
    public function categoryregistget()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == "管理") {
            return view('flow.workflowcategoryregist', compact("prefix", "server"));
        } else {
            return redirect()->route('workflow');
        }
    }
    public function categoryregistpost(Request $request)
    {
        if (Auth::user()->管理 == "管理") {
            $new_m_category = new M_category();
            $order = $request->input('order');
            $new_m_category->カテゴリ名 = $request->input('category_name');
            $new_m_category->注釈 = $request->input('annotation');
            // 仮の項目順を入れておく
            $new_m_category->項目順 = $order;
            $new_m_category->save();
            // 新規追加された場合項目のidが仮の形(50000以上)のものになっているため
            // DBに登録してそのidを取得したものを使用して正式なものをofficial_orderに格納する
            $official_order = "";
            // アンダースコア（_）をデリミタとして文字列を分割
            $parts = explode("_", $order);
            foreach ($parts as $part) {
                // 新規に追加された項目
                // 新規に追加されたものは50000以上としている
                if ($part == 50000) {
                    $new_m_optional = new M_optional();
                    $new_m_optional->カテゴリマスタID = $new_m_category->id;
                    $new_m_optional->項目名 = $request->input("name_" . $part . "");
                    $new_m_optional->型 = 1;
                    $new_m_optional->最大 = 30;
                    $new_m_optional->必須 = true;
                    $new_m_optional->デフォルト = true;
                    $new_m_optional->save();
                    $part = $new_m_optional->id;
                } else if ($part > 50000) {
                    $new_m_optional = new M_optional();
                    $new_m_optional->カテゴリマスタID = $new_m_category->id;
                    $new_m_optional->項目名 = $request->input("name_" . $part . "");
                    $new_m_optional->型 = $request->input("type_" . $part . "");
                    $new_m_optional->最大 = $request->input("max_" . $part . "");
                    $new_m_optional->必須 = $request->input("required_" . $part . "");
                    // 金額条件に合致するものはtrueにする
                    if ($request->input('price') == $part) {
                        $new_m_optional->金額条件 = true;
                    }
                    $new_m_optional->save();
                    $part = $new_m_optional->id;
                }

                // 最初
                if ($official_order == "") {
                    $official_order = $part;
                }
                // 二番目以降
                else {
                    $official_order = $official_order . "_" . $part;
                }
                $new_m_category->項目順 = $official_order;
                $new_m_category->save();
            }
            return redirect()->route('categorydetailget', ["id" => $new_m_category->id]);
        } else {
            return redirect()->route('workflow');
        }
    }
    // カテゴリ削除
    public function categorydeletepost(Request $request)
    {
        if (Auth::user()->管理 == "管理") {
            $category_id = $request->input('category_id');
            $m_category = M_category::find($category_id);

            $existing_m_flow = M_flow::where('カテゴリマスタID', $category_id)->first();
            if ($existing_m_flow) {
                $m_category->削除フラグ = true;
                $m_category->save();
            } else {
                $m_category->delete();
            }
            return redirect()->route('categoryget');
        } else {
            return redirect()->route('workflow');
        }
    }

    // カテゴリ情報の非同期通信API
    public function categoryinfoget(Request $request, $id)
    {
        $category_object = [];
        $m_category = M_category::find($id);
        $m_optionals = M_optional::where("カテゴリマスタID", $id);
        $order = $m_category->項目順;
        $parts = explode("_", $order);
        foreach ($parts as $part) {
            $m_optional = M_optional::find($part);
            $item = [
                "id" => $part,
                "項目名" => $m_optional->項目名,
                "型" => $m_optional->型,
                "最大" => $m_optional->最大,
                "必須" => $m_optional->必須
            ];

            $category_object[] = $item;
        }
        $each_time_issue = $m_category->発行;
        return response()->json([$m_category->注釈, $category_object, $each_time_issue]);
    }

    // カテゴリ承認設定
    public function categoryapprovalsettingget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        if (Auth::user()->管理 == "管理") {
            // 項目の情報を取得
            $m_optionals = [];
            $m_category = M_category::find($id);
            $m_category->status = ($m_category->発行 == 1) ? "none_change" : "empty";
            $order = $m_category->項目順;
            // アンダースコア（_）をデリミタとして文字列を分割
            $parts = explode("_", $order);
            foreach ($parts as $part) {
                $m_optional = M_optional::find($part);
                $m_pointers = M_pointer::where('カテゴリマスタID', $id)
                    ->where('任意項目マスタID', $part)
                    ->get();
                if ($m_optional->型 != 4) {
                    if ($m_optional->型 == 1) {
                        $m_pointers = M_pointer::where('カテゴリマスタID', $id)
                            ->where('任意項目マスタID', $part)
                            ->get();
                    }
                    $item = [
                        "id" => $part,
                        "項目名" => $m_optional->項目名,
                        "型" => $m_optional->型,
                        "pointers" => $m_pointers,
                    ];

                    $m_optionals[] = $item;
                }
            }
            // 基本情報の情報を取得
            $basic_users = [];
            $basic_dates = [];
            $m_basic_pointers = M_basic_pointer::where("カテゴリマスタID", $id)->get();
            foreach ($m_basic_pointers as $m_basic_pointer) {
                if ($m_basic_pointer->基本情報 == 1) {
                    $basic_users[] = $m_basic_pointer;
                } else if ($m_basic_pointer->基本情報 == 2) {
                    $basic_dates[] = $m_basic_pointer;
                }
            }

            return view('flow.workflowcategory_approval', compact("prefix", "server", "id", "m_category", "m_optionals", "basic_users", "basic_dates"));
        }
    }

    // カテゴリ承認設定ポスト
    public function categoryapprovalsettingpost(Request $request)
    {
        $prefix = config('prefix.prefix');

        $category_id = $request->input('category_id');
        $m_category = M_category::find($category_id);
        $approval_setting = $request->input('approval_setting');
        // 承認用紙を発行しない
        if ($approval_setting  == 0) {

            $m_category->発行 = 0;
            $m_category->ファイルパス = null;
            $m_category->縦 = 0;
            $m_category->横 = 0;
            $m_category->縦横 = null;
            $m_category->承認印 = false;
            $m_category->申請印 = false;
        }
        // 申請のたびに承認用紙を発行する
        else if ($approval_setting == 2) {
            $approval_stamp = $request->input('approval_stamp');
            $application_stamp = $request->input('application_stamp');
            if ($approval_stamp) {
                $m_category->承認印 = true;
            } else {
                $m_category->承認印 = false;
            }
            if ($application_stamp) {
                $m_category->申請印 = true;
            } else {
                $m_category->申請印 = false;
            }

            $m_category->発行 = 2;
            $m_category->ファイルパス = null;
            $m_category->縦 = 0;
            $m_category->横 = 0;
            $m_category->縦横 = null;
        }
        // 承認用紙を登録しておく
        else if ($approval_setting == 1) {
            $pointers = $request->pointer_array;
            $approval_stamp = $request->input('approval_stamp');
            $application_stamp = $request->input('application_stamp');
            if ($approval_stamp) {
                $m_category->承認印 = true;
            } else {
                $m_category->承認印 = false;
            }
            if ($application_stamp) {
                $m_category->申請印 = true;
            } else {
                $m_category->申請印 = false;
            }
            M_pointer::where("カテゴリマスタID", $category_id)->delete();
            M_basic_pointer::where("カテゴリマスタID", $category_id)->delete();
            if ($pointers) {
                foreach ($pointers as $pointer) {
                    // 基本情報の場合
                    if ($request->input('basic_info' . $pointer) > 0) {
                        $new_m_basic_pointer = new M_basic_pointer();
                        if ($pointer < 100000) {
                            $new_m_basic_pointer->id = $pointer;
                        }
                        $new_m_basic_pointer->カテゴリマスタID = $category_id;
                        $new_m_basic_pointer->基本情報 = $request->input('basic_info' . $pointer);
                        $new_m_basic_pointer->top = $request->input('top' . $pointer);
                        $new_m_basic_pointer->left = $request->input('left' . $pointer);
                        $new_m_basic_pointer->フォントサイズ = $request->input('font_size' . $pointer);
                        $new_m_basic_pointer->ページ = $request->input('page' . $pointer);

                        $new_m_basic_pointer->save();
                    } else {
                        // 任意項目の場合
                        $new_m_pointer = new M_pointer();
                        if ($pointer < 100000) {
                            // 過去に登録してあったIDは明示的にidを振る
                            // 新規のものは自動インクリメントのため明示しない
                            $new_m_pointer->id = $pointer;
                        }
                        if ($request->input('comma' . $pointer)) {
                            $new_m_pointer->桁区切り = true;
                        }
                        $new_m_pointer->カテゴリマスタID = $category_id;
                        $new_m_pointer->任意項目マスタID = $request->input('optional_id' . $pointer);
                        $new_m_pointer->top = $request->input('top' . $pointer);
                        $new_m_pointer->left = $request->input('left' . $pointer);
                        $new_m_pointer->フォントサイズ = $request->input('font_size' . $pointer);
                        // $new_m_pointer->フォント = $request->input('font'.$pointer);
                        $new_m_pointer->ページ = $request->input('page' . $pointer);
                        $new_m_pointer->save();
                    }
                }
            }



            if ($request->input("status") == "change") {

                // 入力されたPDFデータを取得
                $pdfData = $request->file('pdf');

                $width = $request->input('width');

                $height = $request->input('height');
                // 縦横の情報
                $p_l = $request->input('p_l');


                $now = Carbon::now();
                $currentTime = $now->format('YmdHis');
                $randomID = $this->generateRandomCode();

                $originalPath = $pdfData->getRealPath();
                $pdfVersion = $this->getPdfVersion($originalPath);

                $tempDir = storage_path('app/pdf/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $tempPath = storage_path("app/pdf/temp/{$currentTime}temp.pdf");


                if ($pdfVersion && $pdfVersion >= 1.5) {
                    // PDFバージョンが1.5以上なら1.4に変換
                    $convertedPath = $this->downgradePdf($originalPath, $tempPath);
                    if (!$convertedPath) {
                        return response()->json(['error' => 'PDFの変換に失敗しました'], 500);
                    }
                    $finalPath = $convertedPath;
                } else {
                    // そのまま使用
                    $finalPath = $originalPath;
                }

                if (config('prefix.server') == 'cloud') {
                    $pdffilename = 'flow/application/' . $currentTime . '_' . $randomID . '.pdf';
                    $pdfpath = $prefix . '/' . $pdffilename;
                    Storage::disk('s3')->put($pdfpath, file_get_contents($finalPath));
                } else {
                    $pdffilename = 'flow\\application\\' . $currentTime . '_' . $randomID . '.pdf';
                    $pdfpath = Config::get('custom.file_upload_path') . '\\' . $pdffilename;
                    copy($finalPath, $pdfpath);
                }

                if ($pdfVersion && $pdfVersion >= 1.5) {
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                }


                $m_category->ファイルパス = $pdffilename;
                $m_category->発行 = 1;
                $m_category->縦 = $width;
                $m_category->横 = $height;
                $m_category->縦横 = $p_l;
            }
        }
        $m_category->save();

        return redirect()->route('categoryapprovalsettingget', ['id' => $category_id])->with('success', '承認用紙の設定を更新しました。');

        // -----------以降はテストで作成したコードのため後で消す-----------------

        // TCPDFでPDFを作成し、画像を追加する
        $pdf = new Fpdi();


        $pdf->AddPage($p_l, [$width, $height]);


        // 入力されたPDFデータを新しいPDFに結合
        $pdf->setSourceFile($pdfpath);
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);
        $pdf->useTemplate($tplIdx); // サイズ調整が必要かもしれません

        // // 画像データを一時的なファイルに保存する（任意）
        // $imagePath = public_path('images/temp_image.png');
        // if (!file_exists(dirname($imagePath))) { // ディレクトリが存在しない場合は作成
        //     mkdir(dirname($imagePath), 0777, true);
        // }
        // file_put_contents($imagePath, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)));

        // dd(($left*0.0847));
        // $pdf->Image($imagePath, , $top,100,30); // 適切な座標とサイズに調整する
        // $pdf->Image($imgpath, $left * 0.35264, $top * 0.35264, 0, 0, '', '', '', false);
        // TTFファイルのパスを指定して追加
        // dd(asset($prefix.'/font/HGR教科書体.TTC'));


        $pdf->SetFont('msungstdlight', '', 14); // フォントを設定
        $pdf->Text(0, 0, '標題');
        // PDFをダウンロードまたは表示する
        // $pdf->Output('output.pdf', 'D'); // ダウンロード
        $pdf->Output('output.pdf', 'I'); // ブラウザに表示
    }

    // private function pdfsize($pdfpath)
    // {
    //     $pdf = new Fpdi();
    //     $pdf->setSourceFile($pdfpath);
    //     $tplIdx = $pdf->importPage(1);
    //     $size = $pdf->getTemplateSize($tplIdx);
    //     $pdfsize = [$size["width"] > $size["height"] ? "L" : "P",[$size["width"],$size["height"]]]; 
    //     return $pdfsize;
    // }

    public function getPdfVersion($filePath)
    {
        $fp = fopen($filePath, 'rb');
        if (!$fp) return false;

        $firstLine = fgets($fp, 20); // 最初の行を取得
        fclose($fp);

        if (preg_match('/%PDF-(\d+\.\d+)/', $firstLine, $matches)) {
            return (float)$matches[1]; // バージョンを小数として返す
        }

        return false;
    }

    function downgradePdf($inputPath, $outputPath)
    {
        $gsCommand = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            ? '"C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe"'  // Windows用
            : 'gs';  // Linux用

        $command = $gsCommand . " -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -o " .
            escapeshellarg($outputPath) . " -f " . escapeshellarg($inputPath);

        exec($command, $output, $returnVar);
        return $returnVar === 0 ? $outputPath : false;
    }



    // ワークフロー申請
    public function workflowapplicationget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $m_categories = M_category::all();
        $m_pointers = M_pointer::all();


        return view('flow.workflowapplication', compact("prefix", "server", "m_categories", "m_pointers"));
    }
    // ワークフロー申請ポスト
    public function workflowapplicationpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        // if ($prefix !== "") {
        //     $prefix = "/" . $prefix;
        // }
        $server = config('prefix.server');







        // フローテーブルを作成 (ステータスはデフォルトで0→下書き状態)
        $new_t_flow = new T_flow();
        $new_t_flow->申請者ID = Auth::id();
        $new_t_flow->カテゴリマスタID = $request->input("category");
        $pastID = $this->generateRandomCode();
        $new_t_flow->過去データID = $pastID;



        $category_id = $request->input('category');
        $m_category = M_category::find($category_id);

        if ($m_category->承認印) {
            $new_t_flow->承認印 = true;
        }
        if ($m_category->申請印) {
            $new_t_flow->申請印 = true;
        }
        // 項目順を取得
        $order = $m_category->項目順;
        // アンダースコア（_）をデリミタとして文字列を分割
        $parts = explode("_", $order);


        $new_t_flow->発行 = $m_category->発行;

        // partsの一つ目の要素が標題であるためそれを格納
        $new_t_flow->標題 = $request->input("item" . $parts[0]);

        $new_t_flow->save();

        // 承認用紙を発行する場合
        if ($m_category->発行 == 1) {
            // pdfに挿入する
            $pdf = new Fpdi();
            $pdf->setPrintHeader(false);
            $pdf->AddPage($m_category->縦横, [$m_category->横, $m_category->縦]);
            if (config('prefix.server') == "cloud") {
                // // S3からPDFを一時的にダウンロード
                $prefix = str_replace('/', '', $prefix);
                $s3Path = $prefix . '/' . $m_category->ファイルパス; // S3のファイルパス
                $now = Carbon::now();
                $currentTime = $now->format('YmdHis');
                $tempDir = storage_path('app/application/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $tempPath = storage_path("app/application/temp/{$currentTime}_flow.pdf");

                $s3Client = Storage::disk('s3')->getClient(); // S3 クライアントを取得

                $s3Client->getObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key'    => $s3Path,
                    'SaveAs' => $tempPath
                ]);
                $pdfpath = $tempPath;
            } else if (config('prefix.server') == "onpre") {
                $pdfpath = Config::get('custom.file_upload_path') . '\\' . $m_category->ファイルパス;
            }
            // 入力されたPDFデータを新しいPDFに結合
            $pdf->setSourceFile($pdfpath);
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx);
        }


        foreach ($parts as $part) {
            $m_optional = M_optional::find($part);
            $t_optional = new T_optional();
            $t_optional->フローテーブルID = $new_t_flow->id;
            $t_optional->任意項目マスタID = $m_optional->id;
            $t_optional->金額条件 = $m_optional->金額条件;
            if ($m_optional->型 == 1) {
                $t_optional->文字列 = $request->input("item" . $part);
                $value = $t_optional->文字列;
            } else if ($m_optional->型 == 2) {
                $t_optional->数値 = $request->input("item" . $part);
                $value = $t_optional->数値;
            } else if ($m_optional->型 == 3) {
                $t_optional->日付 = $request->input("item" . $part);
                $value = $t_optional->日付;
            } else if ($m_optional->型 == 4) {
                $file = $request->file("item" . $part);
                if ($file) {
                    $pastID = $this->generateRandomCode();
                    $extension = $file->getClientOriginalExtension();
                    $root = Config::get('custom.file_upload_path');
                    $now = Carbon::now();
                    $currentTime = $now->format('YmdHis');
                    $filepath = $currentTime . '_' . $pastID;
                    if (config('prefix.server') == 'cloud') {
                        $filepath = 'flow/attachment/' . $filepath;
                        // S3にアップロード
                        $s3Path = $prefix . '/' . $filepath . ($extension ? '.' . $extension : '');
                        Storage::disk('s3')->put($s3Path, file_get_contents($file->getRealPath()));
                    } else if (config('prefix.server') == 'onpre') {
                        $filepath = 'flow\\attachment\\' . $filepath;
                        // 開発環境: ローカルに保存
                        copy($file->getRealPath(), $root . "\\" . $filepath . ($extension ? '.' . $extension : ''));
                    }

                    $t_optional->ファイルパス = $filepath;
                    $t_optional->ファイル形式 = $extension;
                }
            }
            $t_optional->save();

            if ($m_category->発行 == 1) {
                // 項目位置マスタに項目マスタのIDが含まれていた場合
                $m_pointers = M_pointer::where('任意項目マスタID', $m_optional->id)
                    ->where('カテゴリマスタID', $m_category->id)
                    ->get();
                foreach ($m_pointers as $m_pointer) {
                    $pdf->SetFont('NotoSansJP-Regular', '', $m_pointer->フォントサイズ . "pt");
                    $pdf->SetXY($m_pointer->left, $m_pointer->top);  // (x, y)座標を指定
                    if ($m_pointer->桁区切り) {
                        $modified_value = number_format(intval($value));
                    } else {
                        $modified_value = $value;
                    }
                    $pdf->Write(0, $modified_value);
                }
            }
        }
        if ($m_category->発行 == 1) {
            // 基本情報のデータを取得
            $m_basic_pointers = M_basic_pointer::where('カテゴリマスタID', $m_category->id)
                ->get();
            // 基本情報のデータをpdfに記述
            foreach ($m_basic_pointers as $m_basic_pointer) {
                $pdf->SetFont('NotoSansJP-Regular', '', $m_basic_pointer->フォントサイズ . "pt");
                $pdf->SetXY($m_basic_pointer->left, $m_basic_pointer->top);  // (x, y)座標を指定
                if ($m_basic_pointer->基本情報 == 1) {
                    $value = Auth::user()->name;
                } else if ($m_basic_pointer->基本情報 == 2) {
                    $value = Carbon::now()->format('Y/m/d');
                }
                $pdf->Write(0, $value);
            }


            $now = Carbon::now();
            $currentTime = $now->format('YmdHis');
            $randomID = $this->generateRandomCode();
            $root = Config::get('custom.file_upload_path');
            if (config('prefix.server') == 'cloud') {
                $new_pdf_name =  'flow/application/' . $currentTime . '_' . $randomID . '.pdf';
                $new_pdf_path = $prefix . '/' . $new_pdf_name;
                $pdfcontent = $pdf->Output('', 'S');
                Storage::disk('s3')->put($new_pdf_path, $pdfcontent);
            } else if (config('prefix.server') == 'onpre') {
                $new_pdf_name = 'flow\\application\\' . $currentTime . '_' . $randomID . '.pdf';
                $new_pdf_path = $root . '\\' . $new_pdf_name;
                $pdfcontent = $pdf->Output('', 'S');
                file_put_contents($new_pdf_path, $pdfcontent);
            }


            $new_t_flow->変更前承認ファイルパス = $new_pdf_name;
            // 申請印を押さない場合は変更後承認ファイルパスへの記述がないため
            // ここで値を格納する
            if (!$m_category->申請印) {
                $new_t_flow->変更後承認ファイルパス = $new_pdf_name;
            }
            $new_t_flow->save();
        } else if ($m_category->発行 == 2) {
            $file = $request->file("approval_document");

            if ($file) {
                $randomID = $this->generateRandomCode();
                $extension = $file->getClientOriginalExtension();
                $root = Config::get('custom.file_upload_path');
                $now = Carbon::now();
                $currentTime = $now->format('YmdHis');

                $originalPath = $file->getRealPath();
                $pdfVersion = $this->getPdfVersion($originalPath);

                $tempDir = storage_path('app/pdf/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $tempPath = storage_path("app/pdf/temp/{$currentTime}temp.pdf");


                if ($pdfVersion && $pdfVersion >= 1.5) {
                    // PDFバージョンが1.5以上なら1.4に変換
                    $convertedPath = $this->downgradePdf($originalPath, $tempPath);
                    if (!$convertedPath) {
                        return response()->json(['error' => 'PDFの変換に失敗しました'], 500);
                    }
                    $finalPath = $convertedPath;
                } else {
                    // そのまま使用
                    $finalPath = $originalPath;
                }

                if (config('prefix.server') == 'cloud') {
                    $filepath = 'flow/attachment/application/' . $currentTime . '_' . $randomID . ($extension ? '.' . $extension : '');
                    $s3Path = $prefix . '/' . $filepath;
                    Storage::disk('s3')->put($s3Path, file_get_contents($finalPath));
                } else if (config('prefix.server') == 'onpre') {
                    // 開発環境: ローカルに保存
                    $filepath = 'flow\\attachment\\application\\' . $currentTime . '_' . $randomID . ($extension ? '.' . $extension : '');
                    copy($finalPath, $root . '\\' . $filepath);
                }
                if ($pdfVersion && $pdfVersion >= 1.5) {
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                }



                // pdfの縦と横のサイズを取得
                $width = $request->input("width");
                $height = $request->input("height");
                if ($width > $height) {
                    $new_t_flow->縦横 = "L";
                } else {
                    $new_t_flow->縦横 = "P";
                }
                $new_t_flow->横 = $width;
                $new_t_flow->縦 = $height;

                $new_t_flow->変更前承認ファイルパス = $filepath;
                if (!$m_category->申請印) {
                    $new_t_flow->変更後承認ファイルパス = $filepath;
                }
                $new_t_flow->save();
            }
        }

        return redirect()->route('workflowchoiceget', ["id" => $new_t_flow->id]);
    }

    // ワークフロー再申請
    public function workflowreapplyget($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $t_flow = T_flow::find($id);
        $now_reapply_number = $t_flow->再承認番号;
        $t_optionals = $this->application_items($id, $now_reapply_number);
        $m_pointers = M_pointer::all();

        return view('flow.workflowreapplication', compact("prefix", "server", "t_flow", "t_optionals", "m_pointers"));
    }

    // ワークフロー再申請ポスト
    public function workflowreapplypost(Request $request)
    {
        $prefix = config('prefix.prefix');

        $t_flow_id = $request->input("t_flow_id");
        $t_flow = T_flow::find($t_flow_id);
        $t_approval = T_approval::where("フローテーブルID", $t_flow_id)
            ->where("ステータス", 0)
            ->first();
        $now_reapply_number = $t_flow->再承認番号;
        $next_reapply_number = intval($now_reapply_number) + 1;
        $t_optionals = T_optional::where("フローテーブルID", $t_flow_id)
            ->where("再承認番号", $now_reapply_number)
            ->get();
        // 一度仮登録した再申請の任意項目テーブルの値があれば
        // それを削除する
        $next_t_optionals = T_optional::where("フローテーブルID", $t_flow_id)
            ->where("再承認番号", $next_reapply_number)
            ->delete();
        // 承認用紙を発行する場合
        if ($t_flow->発行 == 1) {
            // pdfに挿入する
            $pdf = new Fpdi();
            $pdf->setPrintHeader(false);
            $pdf->AddPage($t_flow->縦横, [$t_flow->横, $t_flow->縦]);

            if (config('prefix.server') == "cloud") {
                // // S3からPDFを一時的にダウンロード
                $s3Path = $prefix . '/' . $t_approval->承認ファイルパス; // S3のファイルパス
                $now = Carbon::now();
                $currentTime = $now->format('YmdHis');
                $tempDir = storage_path('app/application/temp');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $tempPath = storage_path("app/application/temp/{$currentTime}_flow.pdf");

                $s3Client = Storage::disk('s3')->getClient(); // S3 クライアントを取得

                $s3Client->getObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key'    => $s3Path,
                    'SaveAs' => $tempPath
                ]);
                $pdfpath = $tempPath;
            } else if (config('prefix.server') == "onpre") {
                $pdfpath = Config::get('custom.file_upload_path') . '\\' . $t_approval->承認ファイルパス;
            }
            // 入力されたPDFデータを新しいPDFに結合
            $pdf->setSourceFile($pdfpath);
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx);
        }
        foreach ($t_optionals as $t_optional) {
            $m_optional = M_optional::find($t_optional->任意項目マスタID);
            $new_t_optional = new T_optional();
            $new_t_optional->フローテーブルID = $t_flow_id;
            $new_t_optional->任意項目マスタID = $t_optional->任意項目マスタID;
            $new_t_optional->再承認番号 = $next_reapply_number;
            if ($m_optional->型 == 1) {
                $new_t_optional->文字列 = $request->input("application_form_input" . $t_optional->id);
                $value = $new_t_optional->文字列;
            } else if ($m_optional->型 == 2) {
                $new_t_optional->数値 = $request->input("application_form_input" . $t_optional->id);
                $value = $new_t_optional->数値;
            } else if ($m_optional->型 == 3) {
                $new_t_optional->日付 = $request->input("application_form_input" . $t_optional->id);
                $value = $new_t_optional->日付;
            } else if ($m_optional->型 == 4) {
                $file = $request->file("application_form_input" . $t_optional->id);
                if ($file) {
                    $pastID = $this->generateRandomCode();
                    $extension = $file->getClientOriginalExtension();
                    $root = Config::get('custom.file_upload_path');
                    $now = Carbon::now();
                    $currentTime = $now->format('YmdHis');
                    $filepath = $currentTime . '_' . $pastID;

                    if (config('prefix.server') == 'cloud') {
                        $filepath = 'flow/attachment/' . $filepath;
                        // S3にアップロード
                        $s3Path = $prefix . '/' . $filepath . ($extension ? '.' . $extension : '');
                        Storage::disk('s3')->put($s3Path, file_get_contents($file->getRealPath()));
                    } else if (config('prefix.server') == 'onpre') {
                        $filepath = 'flow\\attachment\\' . $filepath;
                        // 開発環境: ローカルに保存
                        copy($file->getRealPath(), $root . "\\" . $filepath . ($extension ? '.' . $extension : ''));
                    }


                    $new_t_optional->ファイルパス = $filepath;
                    $new_t_optional->ファイル形式 = $extension;
                }
            }
            if ($t_flow->発行 == 1) {
                // 項目位置マスタに項目マスタのIDが含まれていた場合
                $m_pointer = M_pointer::where('任意項目マスタID', $m_optional->id)
                    ->first();
                if ($m_pointer) {
                    $pdf->SetFont('NotoSansJP-Regular', '', $m_pointer->フォントサイズ . "pt");
                    $pdf->SetXY($m_pointer->left, $m_pointer->top);  // (x, y)座標を指定
                    $pdf->Write(0, $value);
                }
            }

            $new_t_optional->save();
        }
        if ($t_flow->発行 == 1) {
            $now = Carbon::now();
            $currentTime = $now->format('YmdHis');
            $randomID = $this->generateRandomCode();

            $new_pdf_name = $currentTime . '_' . $randomID . '.pdf';

            $root = Config::get('custom.file_upload_path');
            if (config('prefix.server') == 'cloud') {
                $new_pdf_name =  'flow/application/' . $new_pdf_name;
                $new_pdf_path = $prefix . '/' . $new_pdf_name;
                $pdfcontent = $pdf->Output('', 'S');
                Storage::disk('s3')->put($new_pdf_path, $pdfcontent);
            } else if (config('prefix.server') == 'onpre') {
                $new_pdf_path = $root . '\\' . $new_pdf_name;
                $pdfcontent = $pdf->Output('', 'S');
                file_put_contents($new_pdf_path, $pdfcontent);
            }



            $t_flow->変更前承認ファイルパス = $new_pdf_name;
            // 申請印を押さない場合は変更後承認ファイルパスへの記述がないため
            // ここで値を格納する
            if (!$t_flow->申請印) {
                $t_flow->変更後承認ファイルパス = $new_pdf_name;
            }
        }
        $t_flow->save();

        if (config('prefix.server') == "cloud") {
            Storage::delete("application/temp/{$currentTime}_flow.pdf");
        }

        if ($t_flow->申請印 == 1) {
            return redirect()->route('workflowreapplicationstampget', ["id" => $t_flow_id]);
        } else {
            return redirect()->route('workflowreapplicationdetailget', ["id" => $t_flow_id]);
        }
    }

    // ワークフロー経路選択
    public function workflowchoiceget($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }

        // 申請確認画面から戻る場合もあるためワークフロー経路選択時に
        // 申請印の有無のステータスを振る
        $t_flow = T_flow::find($id);
        $m_category = M_category::find($t_flow->カテゴリマスタID);
        // 申請印が必須の場合はステータスを-1に(申請印未押印)
        // if ($m_category->申請印) {
        //     $t_flow->ステータス = -1;
        //     $t_flow->save();
        // }



        $t_optional = T_optional::where("金額条件", true)->first();
        $groupIds = Group_User::where('ユーザーID', Auth::id())->pluck('グループID')->toArray();
        $m_flows = DB::table('m_flows')
            ->leftJoin('m_flow_groups', 'm_flows.id', '=', 'm_flow_groups.フローマスタID')
            ->whereIn('m_flow_groups.グループID', $groupIds)
            ->where('金額下限条件', '<=', $t_optional->数値 ?? 2000000000)
            ->where('金額上限条件', '>=', $t_optional->数値 ?? 0)
            ->where('カテゴリマスタID', $m_category->id)
            ->where('削除フラグ', false)
            ->select('m_flows.*')
            ->distinct() // 重複を取り除く
            ->get();

        $server = config('prefix.server');

        return view('flow.workflowchoice', compact("prefix", "server", "m_flows", "id"));
    }

    public function workflowchoicepost(Request $request)
    {

        $t_flow_id = $request->input("id");
        $m_flow_id = $request->input("m_flow_id");
        $t_flow = T_flow::find($t_flow_id);
        if ($t_flow) {
            $t_flow->フローマスタID = $m_flow_id;
            $t_flow->save();
            if ($t_flow->申請印 == 1) {
                return redirect()->route('workflowapplicationstampget', ["id" => $t_flow_id]);
            } else if ($t_flow->ステータス == 0) {
                return redirect()->route('workflowconfirmget', ["id" => $t_flow_id]);
            } else {
                return redirect()->route('workflowerrorGet', ["code" => "A546815"]);;
            }
        } else {
            return redirect()->route('workflowerrorGet', ["code" => "A546815"]);;
        }
    }

    // idはt_flowのid
    // 申請印
    public function workflowapplicationstampget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $t_flow = T_flow::find($id);
        $category_id = M_flow::find($t_flow->フローマスタID)->カテゴリマスタID;
        $user_id = Auth::id();
        $m_stamp = M_stamp::where('ユーザーID', $user_id)->first();

        return view('flow.workflowapplicationstamp', compact("prefix", "server", "category_id", "user_id", "t_flow", "m_stamp"));
    }
    // 申請印ポスト
    public function workflowapplicationstamppost(Request $request)
    {
        $prefix = config('prefix.prefix');

        $t_flow_id = $request->input("t_flow_id");
        $t_flow = T_flow::find($t_flow_id);
        // すでに申請済みの場合
        if ($t_flow->ステータス != 0) {
            return redirect()->route('workflowerrorGet', ["code" => "A546815"]);
        }
        // TCPDFでPDFを作成し、画像を追加する
        $pdf = new Fpdi();
        $m_category = M_category::find($request->input("category_id"));
        $pdf->setPrintHeader(false);
        // 承認用紙を事前に登録してある場合
        if ($m_category->発行 == 1) {
            $pdf->AddPage($m_category->縦横, [$m_category->横, $m_category->縦]);
        } else {
            // 承認用紙を事前に登録していない場合
            $pdf->AddPage($t_flow->縦横, [$t_flow->横, $t_flow->縦]);
        }

        $top = $request->input("top");
        $left = $request->input("left");

        $m_stamp = M_stamp::where('ユーザーID', Auth::id())->first();
        if (config('prefix.server') == "cloud") {
            // // S3からPDFを一時的にダウンロード
            $s3imgpath = $prefix . '/' . $m_stamp->ファイルパス;
            $s3pdfpath = $prefix . '/' . $t_flow->変更前承認ファイルパス;
            $now = Carbon::now();
            $currentTime = $now->format('YmdHis');
            $tempDir = storage_path('app/application/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $tempimgPath = storage_path("app/application/temp/{$currentTime}_img.png");
            $temppdfPath = storage_path("app/application/temp/{$currentTime}_pdf.pdf");

            $s3Client = Storage::disk('s3')->getClient(); // S3 クライアントを取得

            $s3Client->getObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $s3imgpath,
                'SaveAs' => $tempimgPath
            ]);
            $s3Client->getObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $s3pdfpath,
                'SaveAs' => $temppdfPath
            ]);
            $imgpath = $tempimgPath;
            $pdfpath = $temppdfPath;
        } else if (config('prefix.server') == "onpre") {
            $imgpath = Config::get('custom.file_upload_path') . '\\' . $m_stamp->ファイルパス;
            $pdfpath = Config::get('custom.file_upload_path') . '\\' . $t_flow->変更前承認ファイルパス;
        }

        // 入力されたPDFデータを新しいPDFに結合
        $pdf->setSourceFile($pdfpath);
        $tplIdx = $pdf->importPage(1);
        // $size = $pdf->getTemplateSize($tplIdx);
        $pdf->useTemplate($tplIdx); // サイズ調整が必要かもしれません

        $pdf->Image($imgpath, $left, $top, 9.5, 0, '', '', '', false);

        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');
        $randomID = $this->generateRandomCode();

        $new_pdf_name = $currentTime . '_' . $randomID . '.pdf';
        $root = Config::get('custom.file_upload_path');
        if (config('prefix.server') == 'cloud') {
            $new_pdf_name =  'flow/application/' . $new_pdf_name;
            $new_pdf_path = $prefix . '/' . $new_pdf_name;
            $pdfcontent = $pdf->Output('', 'S');
            Storage::disk('s3')->put($new_pdf_path, $pdfcontent);
        } else if (config('prefix.server') == 'onpre') {
            $new_pdf_path = $root . '\\' . $new_pdf_name;
            $pdfcontent = $pdf->Output('', 'S');
            file_put_contents($new_pdf_path, $pdfcontent);
        }


        $t_flow->ステータス = 0;
        $t_flow->変更後承認ファイルパス = $new_pdf_name;
        $t_flow->save();

        if (config('prefix.server') == "cloud") {
            Storage::delete("application/temp/{$currentTime}_img.png");
            Storage::delete("application/temp/{$currentTime}_pdf.pdf");
        }

        return redirect()->route('workflowconfirmget', ["id" => $t_flow_id]);
    }

    // 再申請印
    public function workflowreapplicationstampget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $t_flow = T_flow::find($id);
        $category_id = M_flow::find($t_flow->フローマスタID)->カテゴリマスタID;
        $user_id = Auth::id();
        $m_stamp = M_stamp::where('ユーザーID', $user_id)->first();

        return view('flow.workflowreapplicationstamp', compact("prefix", "server", "category_id", "user_id", "t_flow", "m_stamp"));
    }
    // 再申請印ポスト
    public function workflowreapplicationstamppost(Request $request)
    {
        $prefix = config('prefix.prefix');

        $t_flow_id = $request->input("t_flow_id");
        $t_flow = T_flow::find($t_flow_id);
        $t_approval = T_approval::where("フローテーブルID", $t_flow_id)
            ->where("ステータス", 0)
            ->first();
        // すでに申請済みの場合
        if ($t_flow->ステータス != 0 && $t_flow->ステータス != -1 && $t_flow->ステータス != 5) {
            return redirect()->route('workflowerrorGet', ["code" => "A546815"]);
        }
        // TCPDFでPDFを作成し、画像を追加する
        $pdf = new Fpdi();
        $m_category = M_category::find($request->input("category_id"));
        $pdf->setPrintHeader(false);
        // 承認用紙を事前に登録してある場合
        if ($m_category->発行 == 1) {
            $pdf->AddPage($m_category->縦横, [$m_category->横, $m_category->縦]);
        } else {
            // 承認用紙を事前に登録していない場合
            $pdf->AddPage($t_flow->縦横, [$t_flow->横, $t_flow->縦]);
        }

        $top = $request->input("top");
        $left = $request->input("left");

        $m_stamp = M_stamp::where('ユーザーID', Auth::id())->first();

        if (config('prefix.server') == "cloud") {
            // // S3からPDFを一時的にダウンロード
            $s3imgpath = $prefix . '/' . $m_stamp->ファイルパス;
            $s3pdfpath = $prefix . '/' . $t_flow->変更前承認ファイルパス;
            $now = Carbon::now();
            $currentTime = $now->format('YmdHis');
            $tempDir = storage_path('app/application/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $tempimgPath = storage_path("app/application/temp/{$currentTime}_img.png");
            $temppdfPath = storage_path("app/application/temp/{$currentTime}_pdf.pdf");

            $s3Client = Storage::disk('s3')->getClient(); // S3 クライアントを取得

            $s3Client->getObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $s3imgpath,
                'SaveAs' => $tempimgPath
            ]);
            $s3Client->getObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $s3pdfpath,
                'SaveAs' => $temppdfPath
            ]);
            $imgpath = $tempimgPath;
            $pdfpath = $temppdfPath;
        } else if (config('prefix.server') == "onpre") {
            $imgpath = Config::get('custom.file_upload_path') . '\\' . $m_stamp->ファイルパス;
            $pdfpath = Config::get('custom.file_upload_path') . '\\' . $t_flow->変更前承認ファイルパス;
        }


        // 入力されたPDFデータを新しいPDFに結合
        $pdf->setSourceFile($pdfpath);
        $tplIdx = $pdf->importPage(1);
        // $size = $pdf->getTemplateSize($tplIdx);
        $pdf->useTemplate($tplIdx); // サイズ調整が必要かもしれません

        $pdf->Image($imgpath, $left, $top, 9.5, 0, '', '', '', false);

        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');
        $randomID = $this->generateRandomCode();

        $new_pdf_name = $currentTime . '_' . $randomID . '.pdf';
        $root = Config::get('custom.file_upload_path');
        if (config('prefix.server') == 'cloud') {
            $new_pdf_name =  'flow/application/' . $new_pdf_name;
            $new_pdf_path = $prefix . '/' . $new_pdf_name;
            $pdfcontent = $pdf->Output('', 'S');
            Storage::disk('s3')->put($new_pdf_path, $pdfcontent);
        } else if (config('prefix.server') == 'onpre') {
            $new_pdf_path = $root . '\\' . $new_pdf_name;
            $pdfcontent = $pdf->Output('', 'S');
            file_put_contents($new_pdf_path, $pdfcontent);
        }


        // $t_flow->ステータス = 0;
        $t_flow->変更後承認ファイルパス = $new_pdf_name;
        $t_flow->save();

        if (config('prefix.server') == "cloud") {
            Storage::delete("application/temp/{$currentTime}_img.png");
            Storage::delete("application/temp/{$currentTime}_pdf.pdf");
        }

        return redirect()->route('workflowconfirmget', ["id" => $t_flow_id]);
    }


    // 申請確認画面(再申請も含む)
    public function workflowconfirmget($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $t_flow = T_flow::find($id);
        $m_flow = M_flow::find($t_flow->フローマスタID);
        $m_category = M_category::find($m_flow->カテゴリマスタID);

        // 初期申請の場合
        if ($t_flow->ステータス == 0) {
            $t_optionals = $this->application_items($id);
        }
        // 再申請の場合
        else {
            $next_reapply_number = intval($t_flow->再承認番号) + 1;
            $t_optionals = $this->application_items($id, $next_reapply_number);
        }


        return view('flow.workflowconfirm', compact("prefix", "server", "id", "t_flow", "t_optionals", "m_category"));
    }
    // フローテーブルを引数として項目の情報のレコードを返す
    private function application_items($id, $reapply_number = 1)
    {
        $t_optionals = T_optional::where("フローテーブルID", $id)
            ->where("再承認番号", $reapply_number)
            ->get();

        foreach ($t_optionals as $t_optional) {
            $m_optional = M_optional::find($t_optional->任意項目マスタID);
            $t_optional->項目名 = $m_optional->項目名;
            $t_optional->型 = $m_optional->型;
            if ($m_optional->型 == 1) {
                $t_optional->値 = $t_optional->文字列;
            } else if ($m_optional->型 == 2) {
                $t_optional->値 = $t_optional->数値;
            } else if ($m_optional->型 == 3) {
                $t_optional->値 = $t_optional->日付;
            } else if ($m_optional->型 == 4 && $t_optional->ファイルパス) {
                // viewでファイルの場合は違う表示をするので以下の値を入れておいてviewでif文の分岐
                $t_optional->値 = "file_regist_2545198";
            } else if ($m_optional->型 == 4 && !$t_optional->ファイルパス) {
                // viewでファイルの場合は違う表示をするので以下の値を入れておいてviewでif文の分岐
                $t_optional->値 = "file_none_246851";
            } else if ($m_optional->型 == 5) {
                $t_optional->値 = $t_optional->bool;
            }

            if ($m_optional->必須 == 1) {
                $t_optional->必須 = 1;
            } else {
                $t_optional->必須 = 0;
            }
        }
        return $t_optionals;
    }
    // ワークフロー最終申請ポスト
    public function workflowconfirmpost(Request $request)
    {
        $t_flow_id = $request->input("t_flow_id");
        $t_flow = T_flow::find($t_flow_id);
        $m_flow = M_flow::find($t_flow->フローマスタID);
        $m_category = M_category::find($m_flow->カテゴリマスタID);
        // すでに申請済みの場合
        if ($t_flow->ステータス != 0 && $t_flow->ステータス != 5) {
            return redirect()->route('workflowerrorGet', ["code" => "A546815"]);
        }
        // 初期申請の場合
        if ($t_flow->ステータス == 0) {
            // フローのトランザクションテーブルを作成

            $t_flow->ステータス = 1;
            $t_flow->決裁地点数 = $m_flow->決裁地点数;
            // 再申請時の時のためのファイルパス
            $application_file_path = $m_category->ファイルパス;
            // 承認用紙のファイルパスを確定させる
            $t_flow->変更前承認ファイルパス = $t_flow->変更後承認ファイルパス;


            // -------------古いファイルパスを消去するかどうかは今後考える-----------


            $t_flow->save();

            $m_flow_points = M_flow_point::where("フローマスタID", $t_flow->フローマスタID)
                ->get();
            foreach ($m_flow_points as $m_flow_point) {
                // フロー地点のトランザクションテーブルを作成
                $new_t_flow_point = new T_flow_point();
                $new_t_flow_point->フローテーブルID = $t_flow->id;
                $new_t_flow_point->フロー地点ID = $m_flow_point->id;
                $new_t_flow_point->承認移行ステータス = - ($m_flow_point->承認移行ポイント);
                $new_t_flow_point->承認ステータス = - ($m_flow_point->承認ポイント);
                $new_t_flow_point->save();

                // 個人の場合
                if ($m_flow_point->個人グループ == 1) {
                    $m_approvals = M_approval::where("フロー地点ID", $m_flow_point->id)
                        ->get();

                    foreach ($m_approvals as $m_approval) {

                        $t_approval = new T_approval();
                        $t_approval->フローテーブルID = $t_flow->id;
                        $t_approval->フロー地点テーブルID = $new_t_flow_point->id;
                        $t_approval->ステータス = 1;
                        $t_approval->ユーザーID = $m_approval->ユーザーID;
                        $t_approval->save();
                    }
                }
                // グループ限定無しの場合
                else if ($m_flow_point->個人グループ == 2) {
                    $m_approvals = M_approval::where("フロー地点ID", $m_flow_point->id)
                        ->get();

                    foreach ($m_approvals as $m_approval) {
                        $group_users = Group_User::where("グループID", $m_approval->グループID)
                            ->get();

                        foreach ($group_users as $group_user) {
                            $t_approval = new T_approval();
                            $t_approval->フローテーブルID = $t_flow->id;
                            $t_approval->フロー地点テーブルID = $new_t_flow_point->id;
                            $t_approval->ステータス = 1;
                            $t_approval->ユーザーID = $group_user->ユーザーID;
                            $t_approval->save();
                        }
                    }
                }
                // 役職から選択の場合
                else if ($m_flow_point->個人グループ == 4) {
                    $m_approvals = M_approval::where("フロー地点ID", $m_flow_point->id)
                        ->get();

                    foreach ($m_approvals as $m_approval) {
                        $group_users = Group_User::where("グループID", $m_approval->グループID)
                            ->where("役職ID", $m_approval->役職ID)
                            ->get();

                        foreach ($group_users as $group_user) {
                            $t_approval = new T_approval();
                            $t_approval->フローテーブルID = $t_flow->id;
                            $t_approval->フロー地点テーブルID = $new_t_flow_point->id;
                            $t_approval->ステータス = 1;
                            $t_approval->ユーザーID = $group_user->ユーザーID;
                            $t_approval->save();
                        }
                    }
                }
                // 申請者の場合
                else if ($m_flow_point->個人グループ == 0) {
                    $m_approval = M_approval::where("フロー地点ID", $m_flow_point->id)->first();

                    $t_approval = new T_approval();
                    $t_approval->フローテーブルID = $t_flow->id;
                    $t_approval->フロー地点テーブルID = $new_t_flow_point->id;
                    $t_approval->ステータス = 0;
                    $t_approval->ユーザーID = Auth::id();
                    $t_approval->承認ファイルパス = $application_file_path;
                    $t_approval->save();
                    $applicant_flow_point_id = $m_flow_point->id;
                }
            }

            $next_flow_points = M_next_flow_point::where("現フロー地点ID", $applicant_flow_point_id)
                ->get();
            foreach ($next_flow_points as $next_flow_point) {
                //　申請者から初めの承認ポイントを取得
                $t_flow_point = DB::table('t_flow_points')
                    ->select('t_flow_points.id as t_flow_point_id')
                    ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
                    ->where('m_flow_points.フロントエンド表示ポイント', $next_flow_point->次フロントエンド表示ポイント)
                    ->where('フローテーブルID', $t_flow->id)
                    ->first();
                // 保存
                DB::table('t_flow_points')
                    ->where('id', $t_flow_point->t_flow_point_id)
                    ->update(['承認移行ステータス' => 0]);
                // dd($t_flow_point->t_flow_point_id);
                $t_approvals = T_approval::where('フロー地点テーブルID', $t_flow_point->t_flow_point_id)
                    ->get();
                foreach ($t_approvals as $t_approval) {
                    $t_approval->ステータス = 2;
                    $t_approval->save();
                    $this->workflowmailpost($t_approval->ユーザーID, 'approval', $t_approval->id);
                }
            }
        }
        // 再申請の場合
        else if ($t_flow->ステータス == 5) {
            $t_flow->ステータス = 1;
            $t_flow->変更前承認ファイルパス = $t_flow->変更後承認ファイルパス;
            $next_reapply_number = intval($t_flow->再承認番号) + 1;
            $t_flow->再承認番号 = $next_reapply_number;
            // 標題を取得(再承認番号が該当するもののうち一番最初のものが標題)
            $t_flow_title = T_optional::where("フローテーブルID", $t_flow->id)
                ->where("再承認番号", $next_reapply_number)
                ->first();
            $t_flow->標題 = $t_flow_title->文字列;
            $t_flow->save();
            $t_approval_application = T_approval::where("フローテーブルID", $t_flow->id)
                ->where("ステータス", 7)
                ->where("再承認番号", $next_reapply_number)
                ->first();

            $t_flow_point = T_flow_point::find($t_approval_application->フロー地点テーブルID);
            $m_flow_point = M_flow_point::find($t_flow_point->フロー地点ID);

            $next_flow_points = M_next_flow_point::where("現フロー地点ID", $m_flow_point->id)
                ->get();
            // 申請者の直後のフロー地点の承認移行ステータスを0に
            foreach ($next_flow_points as $next_flow_point) {
                $t_flow_point = DB::table('t_flow_points')
                    ->select('t_flow_points.id as t_flow_point_id')
                    ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
                    ->where('m_flow_points.フロントエンド表示ポイント', $next_flow_point->次フロントエンド表示ポイント)
                    ->where('フローテーブルID', $t_flow->id)
                    ->first();
                if ($t_flow_point) {
                    DB::table('t_flow_points')
                        ->where('id', $t_flow_point->t_flow_point_id)
                        ->update(['承認移行ステータス' => 0]);

                    $t_approvals = T_approval::where('フロー地点テーブルID', $t_flow_point->t_flow_point_id)
                        ->where('再承認番号', $next_reapply_number)
                        ->get();

                    foreach ($t_approvals as $t_approval) {
                        $t_approval->ステータス = 2;
                        $t_approval->save();
                        $this->workflowmailpost($t_approval->ユーザーID, 'approval', $t_approval->id);
                    }
                }
            }
            // 再申請待ちから再申請済みにする
            $t_approval_application->ステータス = 8;
            $t_approval_application->save();
        }
        return redirect()->route('workflow');
    }
    private function workflowmailpost($user_id, $content, $content_id)
    {
        $m_mail = M_mail::first();
        if ($m_mail) {
            $name = $m_mail->name;
            $mail = $m_mail->mail;
            $host = $m_mail->host;
            $port = $m_mail->port;
            $username = $m_mail->username;
            $password = Crypt::decryptString($m_mail->password);

            $user = User::find($user_id);
            if ($user->メール許可) {
                $recipient = $user->email;

                $mailConfig = [
                    'driver' => 'smtp',
                    'host' => $host,
                    'port' => $port,
                    'username' => $username,
                    'password' => $password,
                    'encryption' => 'tls',
                ];
                if ($content == "approval") {
                    $url = route('workflowapprovalget', ['id' => $content_id]);
                    $subject = '【Rapid】承認依頼';
                    $t_flow = T_flow::find(T_approval::find($content_id)->フローテーブルID);
                    $applicant_name = User::find($t_flow->申請者ID)->name;
                    $parameter = compact('url', 'applicant_name');
                } else if ($content == "reject") {
                    $url = route('workflowapplicationdetailget', ['id' => $content_id]);
                    $subject = '【Rapid】申請却下';
                    $parameter = compact('url');
                } else if ($content == "completion") {
                    $url = route('workflowapplicationdetailget', ['id' => $content_id]);
                    $subject = '【Rapid】決裁';
                    $parameter = compact('url');
                }
                config(['mail' => $mailConfig]);
                try {
                    Mail::send('mail.mail_' . $content, $parameter, function ($message) use ($recipient, $mail, $name, $subject) {
                        $message->to($recipient)
                            ->subject($subject)
                            ->from($mail, $name);
                    });
                    return response()->json('送信しました');
                    // return redirect()->route('workflow');
                } catch (\Exception) {
                    return response()->json('送信できませんでした');
                }
            }
        }
    }


    // 承認一覧
    public function workflowapprovalview(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }

        $m_categories = M_category::all();
        $users = DB::table('t_flows')
            ->select('users.id as user_id', 'users.name')
            ->leftJoin('users', 't_flows.申請者ID', '=', 'users.id')
            ->distinct()
            ->get();



        $title = $request->input('title');
        $category = $request->input('category');
        $user = $request->input('user');
        $start_day = $request->input('start_day');
        $end_day = $request->input('end_day');
        $status = $request->input('status') ? $request->input('status') : "approvable_tab";

        $server = config('prefix.server');
        $approvables =  DB::table('t_approvals')
            ->select('t_flows.標題', 't_flows.created_at as flow_created_at', 'users.name', 'm_categories.カテゴリ名', 't_approvals.id as approval_id')
            ->leftJoin('t_flows', 't_approvals.フローテーブルID', '=', 't_flows.id')
            ->leftJoin('users', 't_flows.申請者ID', '=', 'users.id')
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where('ユーザーID', Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where('t_approvals.ステータス', 2)
            ->where('t_approvals.再承認番号', '=', DB::raw('t_flows.再承認番号'))
            ->get();

        $approveds =  DB::table('t_approvals')
            ->select('t_flows.標題', 't_flows.created_at as flow_created_at', 'users.name', 'm_categories.カテゴリ名', 't_approvals.id as approval_id')
            ->leftJoin('t_flows', 't_approvals.フローテーブルID', '=', 't_flows.id')
            ->leftJoin('users', 't_flows.申請者ID', '=', 'users.id')
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where('ユーザーID', Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where('t_approvals.ステータス', 4)
            ->where('t_approvals.再承認番号', '=', DB::raw('t_flows.再承認番号'))
            ->get();
        $rejecteds =  DB::table('t_approvals')
            ->select('t_flows.標題', 't_flows.created_at as flow_created_at', 'users.name', 'm_categories.カテゴリ名', 't_approvals.id as approval_id')
            ->leftJoin('t_flows', 't_approvals.フローテーブルID', '=', 't_flows.id')
            ->leftJoin('users', 't_flows.申請者ID', '=', 'users.id')
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where('ユーザーID', Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where('t_approvals.ステータス', 5)
            ->where('t_approvals.再承認番号', '=', DB::raw('t_flows.再承認番号'))
            ->get();

        return view('flow.workflowapprovalview', compact("prefix", "server", "users", "m_categories", "status", "title", "category", "user", "start_day", "end_day", "approvables", "approveds", "rejecteds"));
    }
    // 承認
    // idは承認テーブルのid
    public function workflowapprovalget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        // $t_approval = T_approval::find($id);
        $t_approval = DB::table('t_approvals')
            ->select('t_approvals.*', 'users.name', "m_flow_points.フロントエンド表示ポイント")
            ->leftJoin('t_flow_points', 't_approvals.フロー地点テーブルID', '=', 't_flow_points.id')
            ->leftJoin('users', 't_approvals.ユーザーID', '=', 'users.id')
            ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
            ->where("t_approvals.id", $id)
            ->first();
        $t_flow = T_flow::find($t_approval->フローテーブルID);
        $t_optionals =  $this->application_items($t_approval->フローテーブルID, $t_flow->再承認番号);
        $t_flow = T_flow::find($t_approval->フローテーブルID);
        $m_flow = M_flow::find($t_flow->フローマスタID);
        $m_category = M_category::find($m_flow->カテゴリマスタID);
        if (isset($t_approval->承認ファイルパス)) {
            $stamp_status = "true";
        }
        // 承認印を押す必要があるがまだ押印されていない場合
        else if ($t_flow->承認印) {
            $stamp_status = "false";
        }
        // 承認印を押す必要がない場合
        else {
            $stamp_status = "none";
        }


        $user = User::find($t_flow->申請者ID);

        $past_approvals = DB::table('t_approvals')
            ->select('t_approvals.ステータス', 't_approvals.updated_at as 承認日', 'users.name', "m_flow_points.フロントエンド表示ポイント", "t_flow_points.承認ステータス", "t_approvals.コメント", "t_approvals.再承認番号")
            ->leftJoin('t_flow_points', 't_approvals.フロー地点テーブルID', '=', 't_flow_points.id')
            ->leftJoin('users', 't_approvals.ユーザーID', '=', 'users.id')
            ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
            ->where(function ($query) {
                $query->where('ステータス', 0)
                    ->orWhere('ステータス', 4)
                    ->orWhere('ステータス', 5)
                    ->orWhere('ステータス', 6)
                    ->orWhere('ステータス', 8);
            })
            ->where('t_flow_points.フローテーブルID', $t_flow->id)
            ->orderBy('承認日', 'asc')
            ->get();
        $comment = $request->input('comment');

        $approval = $request->input('approval');
        return view('flow.workflowapproval', compact("prefix", "server", "t_flow", "user", "t_optionals", "t_approval", "past_approvals", "stamp_status", "comment", "approval", "m_category"));
    }
    // 申請印や承認印のpdfを取得
    public function approvalsettingpdf(Request $request, $id)
    {
        $prefix = config('prefix.prefix');

        if ($request->input("status") == "application") {
            $t_flow = T_flow::find($id);
            $filepath = $t_flow->変更前承認ファイルパス;
        } else if ($request->input("status") == "approve") {
            $t_flow = T_flow::find($id);
            $filepath = $t_flow->変更前承認ファイルパス;
        } else if ($request->input("status") == "reapplication") {
            $t_flow = T_flow::find($id);
            $filepath = $t_flow->変更前承認ファイルパス;
        } else {
            $img = M_category::find($id);
            $filepath = $img->ファイルパス;
        }



        if (config('prefix.server') == "cloud") {
            // S3バケットの情報
            $bucket = 'astdocs.com';
            $key = $prefix . '/' . $filepath;

            $s3Client = new S3Client([
                'region' => 'ap-northeast-1',
                'version' => 'latest',
            ]);
            // S3からファイルを取得
            try {
                $result = $s3Client->getObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                ]);

                // ファイルのコンテンツ（Body）を取得
                $fileContent = $result['Body'];

                // ストリームを文字列として返す
                if (is_resource($fileContent)) {
                    $fileContent = stream_get_contents($fileContent);
                }
                // ファイルデータをBlobとして返す
                return response($fileContent, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="file.pdf"');
            } catch (S3Exception $e) {
                return response()->json(['error' => 'S3 Error: ' . $e->getMessage()], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => 'General Error: ' . $e->getMessage()], 500);
            }
        } else {
            $path = Config::get('custom.file_upload_path') . "\\" . $filepath;
            return response()->file($path, ['Content-Type' => 'application/pdf']);
        }
    }
    // 承認ポスト
    public function workflowapprovalpost(Request $request)
    {
        $prefix = config('prefix.prefix');

        $approval_id = $request->input("approval_id");
        $result = $request->input("approval");
        $approvecomment = $request->input("approvecomment");


        $t_approval = T_approval::find($approval_id);


        $t_flow_point = T_flow_point::find($t_approval->フロー地点テーブルID);

        $t_flow = T_flow::find($t_flow_point->フローテーブルID);

        // 承認権限がない場合
        if ($t_approval->ステータス != 2) {
            return redirect()->route('workflowerrorGet', ["code" => "B546254"]);
        }

        if ($t_flow_point->承認ステータス < 0) {
            // 承認の場合
            if ($result == "approve") {
                // dd($t_flow_point->承認ステータス);
                // 承認テーブルの承認可能状態から承認済ステータスに変更
                $t_approval->ステータス = 4;
                $t_approval->コメント = $approvecomment;
                $t_flow_point->承認ステータス += 1;

                $t_approval->save();
                $t_flow_point->save();

                if (isset($t_approval->承認ファイルパス))
                    $t_flow->変更前承認ファイルパス = $t_approval->承認ファイルパス;


                // そのフロー地点での承認が完了していた場合
                if ($t_flow_point->承認ステータス == 0) {
                    // そのフロー地点で承認処理をしていないユーザー
                    $loss_t_approvals = T_approval::where('フロー地点テーブルID', $t_flow_point->id)
                        ->where('ステータス', 2)
                        ->get();
                    foreach ($loss_t_approvals as $loss_t_approval) {
                        // 承認資格喪失
                        $loss_t_approval->ステータス = 3;
                        $loss_t_approval->save();
                    }


                    $last_flow_point = DB::table('t_flow_points')
                        ->select('t_flow_points.*')
                        ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
                        ->where('決裁地点', true)
                        ->first();

                    // 最終決裁点だった場合の処理
                    if ($last_flow_point) {
                        $t_flow->決裁数 += 1;
                        if ($t_flow->決裁数 == $t_flow->決裁地点数) {
                            $t_flow->ステータス = 3;
                            $this->workflowmailpost($t_flow->申請者ID, 'completion', $t_flow->id);
                        }
                        $t_flow->save();
                        // TAMERUに保存
                        $category_id = $t_flow->カテゴリマスタID;
                        $m_tameru = M_tameru_regist::where('カテゴリマスタID', $category_id)->get();
                        if ($m_tameru->count() > 0) {
                            foreach ($m_tameru as $m_tameru) {
                                $t_optional = T_optional::where('フローテーブルID', $t_flow->id)
                                    ->where('任意項目マスタID', $m_tameru->ファイル)
                                    ->where('ファイルパス', '!=', null)
                                    ->first();

                                if ($t_optional) {
                                    // 新規にTAMERUファイルを作成
                                    $file = new File();

                                    // フローマスタから申請のグループを取得
                                    $flow_group = M_flow_group::where('フローマスタID', $t_flow->フローマスタID)->pluck('グループID');
                                    $application_user_id = $t_flow->申請者ID;
                                    // 申請者が属しているグループかつフローマスタに登録されているグループの中から
                                    // グループIDを取得(最初に取得したものを採用)
                                    $group_user = Group_user::where('ユーザーID', $application_user_id)
                                        ->whereIn('グループID', $flow_group)
                                        ->first();
                                    if ($group_user) {
                                        $file->グループID = $group_user->グループID;
                                    }
                                    // フローマスタからすでにグループが外されていた場合
                                    // 個人として保存
                                    else {
                                        $group_user = Group_user::where('ユーザーID', $application_user_id)
                                            ->where('グループID', '<', 100000)
                                            ->first();
                                        $file->グループID = $group_user->グループID;
                                    }

                                    // 任意項目から取引日、金額、取引先を取得
                                    $date_optional = T_optional::where('フローテーブルID', $t_flow->id)
                                        ->where('任意項目マスタID', $m_tameru->取引日)
                                        ->first();
                                    $price_optional = T_optional::where('フローテーブルID', $t_flow->id)
                                        ->where('任意項目マスタID', $m_tameru->金額)
                                        ->first();
                                    $company_optional = T_optional::where('フローテーブルID', $t_flow->id)
                                        ->where('任意項目マスタID', $m_tameru->取引先)
                                        ->first();
                                    $file->日付 = Carbon::parse($date_optional->日付)->format('Ymd');
                                    $file->金額 = $price_optional->数値;
                                    $file->取引先 = $company_optional->文字列;
                                    $file->提出 = $m_tameru->提出 == 1 ? '提出' : '受領';
                                    $file->保存 = $m_tameru->保存方法 == 1 ? '電子保存' : 'スキャナ保存';
                                    $file->書類ID = $m_tameru->書類区分;
                                    $file->備考 = $m_tameru->検索ワード;

                                    $file->ファイルパス = config('prefix.server') == "cloud" ? $prefix . '/' . $t_optional->ファイルパス : $t_optional->ファイルパス;
                                    $file->ファイル変更 = "";
                                    $file->削除フラグ = "";
                                    $file->ファイル形式 = $t_optional->ファイル形式;
                                    $file->過去データID = $t_flow->過去データID;
                                    $file->バージョン = 1;
                                    $file->保存者ID = $application_user_id;
                                    $file->更新者ID = $application_user_id;
                                    $file->save();
                                }
                            }
                        }
                    }
                }

                // 次のフローポイントの処理
                $next_flow_points = M_next_flow_point::where("現フロー地点ID", $t_flow_point->フロー地点ID)
                    ->get();
                foreach ($next_flow_points as $next_flow_point) {

                    $t_flow_point = DB::table('t_flow_points')
                        ->select('t_flow_points.id as t_flow_point_id', 't_flow_points.承認移行ステータス')
                        ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
                        ->where('m_flow_points.フロントエンド表示ポイント', $next_flow_point->次フロントエンド表示ポイント)
                        ->where('フローテーブルID', $t_flow_point->フローテーブルID)
                        ->first();
                    // 保存
                    // DB::table('t_flow_points')
                    //     ->where('id', $t_flow_point->t_flow_point_id)
                    //     ->increment('承認移行ステータス', 1);

                    $t_flow_point->承認移行ステータス += 1;


                    // もし承認移行ステータスが0、つまり承認に必要なポイントがたまったら
                    // 次の承認テーブルのステータスを承認可能状態に変更する
                    if ($t_flow_point->承認移行ステータス == 0) {
                        $t_approvals = T_approval::where('フロー地点テーブルID', $t_flow_point->t_flow_point_id)
                            ->get();
                        foreach ($t_approvals as $t_approval) {
                            $t_approval->ステータス = 2;
                            $t_approval->save();
                            $this->workflowmailpost($t_approval->ユーザーID, 'approval', $t_approval->id);
                        }
                    }
                }
            }
            // 差し戻しの場合
            else if ($result == "remand") {
                $t_approval->ステータス = 6;
                $t_approval->コメント = $approvecomment;
                $t_approval->save();

                $next_reapply_number = intval($t_flow->再承認番号) + 1;
                $t_flow->ステータス = 5;
                $t_flow->save();


                $change_t_approvals = T_approval::where('フローテーブルID', $t_flow->id)
                    ->where('再承認番号', $t_flow->再承認番号)
                    ->where(function ($query) {
                        $query->where('ステータス', 0)
                            ->orWhere('ステータス', 1)
                            ->orWhere('ステータス', 2)
                            ->orWhere('ステータス', 4)
                            ->orWhere('ステータス', 6)
                            ->orWhere('ステータス', 8);
                    })
                    ->get();
                foreach ($change_t_approvals as $change_t_approval) {
                    // 再承認番号を1増やした新たな承認テーブルを追加
                    $new_t_approval = new T_approval();
                    $new_t_approval->フローテーブルID = $t_flow->id;
                    $new_t_approval->フロー地点テーブルID = $change_t_approval->フロー地点テーブルID;
                    $new_t_approval->ユーザーID = $change_t_approval->ユーザーID;
                    if ($change_t_approval->ステータス == 0 || $change_t_approval->ステータス == 8) {
                        $new_t_approval->ステータス = 7;
                    }
                    // 承認資格未達、承認可能状態、承認済みの人はすべて承認資格未達ステータスに
                    else {
                        $new_t_approval->ステータス = 1;
                    }
                    $new_t_approval->再承認番号 = $next_reapply_number;
                    $new_t_approval->save();

                    // 現時点で承認可能状態になっている場合は承認資格喪失
                    if ($change_t_approval->ステータス == 2) {
                        $change_t_approval->ステータス = 3;
                        $change_t_approval->save();
                    }
                }

                $change_t_flow_points = T_flow_point::where('フローテーブルID', $t_flow->id)
                    ->get();
                foreach ($change_t_flow_points as $change_t_flow_point) {
                    $m_flow_point = M_flow_point::find($change_t_flow_point->フロー地点ID);
                    $flow_point_num = $m_flow_point->承認移行ポイント;
                    $flow_next_point_num = $m_flow_point->承認移行ポイント;
                    $change_t_flow_point->承認移行ステータス = (int)$flow_point_num * (-1);
                    $change_t_flow_point->承認ステータス = (int)$flow_next_point_num * (-1);
                    $change_t_flow_point->save();
                }
            }
            // 却下の場合
            else if ($result == "reject") {
                // 承認テーブルの承認可能状態から承認済ステータスに変更
                $t_approval->ステータス = 5;
                $t_approval->コメント = $approvecomment;
                $t_flow_point->承認ステータス = 999;

                $t_approval->save();
                $t_flow_point->save();

                $t_flow->ステータス = 2;
                $t_flow->save();

                // 承認資格喪失
                $loss_t_approvals = T_approval::where('フローテーブルID', $t_flow->id)
                    ->where('ステータス', 2)
                    ->get();
                foreach ($loss_t_approvals as $loss_t_approval) {
                    $loss_t_approval->ステータス = 3;
                    $loss_t_approval->save();
                }
                $this->workflowmailpost($t_flow->申請者ID, 'reject', $t_flow->id);
            } else if ($result == "stamp_approve") {
            }
        }
        return redirect()->route('workflowapprovalview');
    }

    // 承認印
    public function workflowapprovalstampget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $t_approval = T_approval::find($id);
        $t_flow = T_flow::find($t_approval->フローテーブルID);
        $category_id = M_flow::find($t_flow->フローマスタID)->カテゴリマスタID;
        $comment = $request->input('comment');
        $m_stamp = M_stamp::where('ユーザーID', Auth::id())->first();
        return view('flow.workflowapprovalstamp', compact("prefix", "server", "t_approval", "category_id", "comment", "m_stamp"));
    }
    // 承認印ポスト
    public function workflowapprovalstamppost(Request $request)
    {
        $prefix = config('prefix.prefix');

        $server = config('prefix.server');
        $t_approval = T_approval::find($request->input("t_approval"));
        $t_approval = T_approval::find($request->input("t_approval"));
        $t_flow_id = $request->input("t_flow_id");
        $t_flow = T_flow::find($t_flow_id);
        // dd($request->all());
        // TCPDFでPDFを作成し、画像を追加する
        $pdf = new Fpdi();
        $m_category = M_category::find($request->input("category_id"));
        $pdf->setPrintHeader(false);
        $pdf->AddPage($t_flow->縦横, [$t_flow->横, $t_flow->縦]);

        $top = $request->input("top");
        $left = $request->input("left");

        $m_stamp = M_stamp::where('ユーザーID', Auth::id())->first();

        if (config('prefix.server') == "cloud") {
            // // S3からPDFを一時的にダウンロード
            $s3imgpath = $prefix . '/' . $m_stamp->ファイルパス;
            $s3pdfpath = $prefix . '/' . $t_flow->変更前承認ファイルパス;
            $now = Carbon::now();
            $currentTime = $now->format('YmdHis');
            $tempDir = storage_path('app/application/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $tempimgPath = storage_path("app/application/temp/{$currentTime}_img.png");
            $temppdfPath = storage_path("app/application/temp/{$currentTime}_pdf.pdf");

            $s3Client = Storage::disk('s3')->getClient(); // S3 クライアントを取得

            $s3Client->getObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $s3imgpath,
                'SaveAs' => $tempimgPath
            ]);
            $s3Client->getObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $s3pdfpath,
                'SaveAs' => $temppdfPath
            ]);
            $imgpath = $tempimgPath;
            $pdfpath = $temppdfPath;
        } else if (config('prefix.server') == "onpre") {
            $imgpath = Config::get('custom.file_upload_path') . '\\' . $m_stamp->ファイルパス;
            $pdfpath = Config::get('custom.file_upload_path') . '\\' . $t_flow->変更前承認ファイルパス;
        }

        // 入力されたPDFデータを新しいPDFに結合
        $pdf->setSourceFile($pdfpath);
        $tplIdx = $pdf->importPage(1);
        // $size = $pdf->getTemplateSize($tplIdx);
        $pdf->useTemplate($tplIdx); // サイズ調整が必要かもしれません

        $pdf->Image($imgpath, $left, $top, 9.5, 0, '', '', '', false);

        $now = Carbon::now();
        $currentTime = $now->format('YmdHis');
        $randomID = $this->generateRandomCode();

        $new_pdf_name = $currentTime . '_' . $randomID . '.pdf';
        $root = Config::get('custom.file_upload_path');
        if (config('prefix.server') == 'cloud') {
            $new_pdf_name =  'flow/application/' . $new_pdf_name;
            $new_pdf_path = $prefix . '/' . $new_pdf_name;
            $pdfcontent = $pdf->Output('', 'S');
            Storage::disk('s3')->put($new_pdf_path, $pdfcontent);
        } else if (config('prefix.server') == 'onpre') {
            $new_pdf_path = $root . '\\' . $new_pdf_name;
            $pdfcontent = $pdf->Output('', 'S');
            file_put_contents($new_pdf_path, $pdfcontent);
        }


        // $t_flow->ステータス = 0;
        $t_flow->save();
        $t_approval->承認ファイルパス = $new_pdf_name;
        $t_approval->save();

        $comment = $request->input("comment");
        if (config('prefix.server') == "cloud") {
            Storage::delete("application/temp/{$currentTime}_img.png");
            Storage::delete("application/temp/{$currentTime}_pdf.pdf");
        }

        return redirect()->route('workflowapprovalget', ["id" => $t_approval->id, "comment" => $comment, "approval" => "approve"]);
    }

    // 閲覧一覧
    public function workflowcheckviewget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $m_categories = M_category::all();
        $users = DB::table('t_flows')
            ->select('users.id as user_id', 'users.name')
            ->leftJoin('users', 't_flows.申請者ID', '=', 'users.id')
            ->distinct()
            ->get();



        $title = $request->input('title');
        $category = $request->input('category');
        $user = $request->input('user');
        $start_day = $request->input('start_day');
        $end_day = $request->input('end_day');
        $status = $request->input('status') ? $request->input('status') : "approvable_tab";


        $viewable_m_flows = DB::table('m_flow_view_groups')
            ->leftJoin('group_user', 'm_flow_view_groups.グループID', '=', 'group_user.グループID')
            ->where('group_user.ユーザーID', Auth::id())
            ->pluck('m_flow_view_groups.フローマスタID')
            ->unique();


        $t_flows_ongoing = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where("ステータス", 1)
            ->get();

        foreach ($t_flows_ongoing as $t_flow_ongoing) {
            $t_flow_points_ongoing = T_flow_point::where("フローテーブルID", $t_flow_ongoing->flow_id)->get();
            // フロー地点数をカウント、申請者も含まれるのでマイナス1
            $t_flow_ongoing->母数 = $t_flow_points_ongoing->count() - 1;
            // 承認ステータスから承認済みのものをカウント、申請者も含まれるのでマイナス1
            $t_flow_ongoing->承認数 = $t_flow_points_ongoing->where("承認ステータス", 0)->count() - 1;
        }


        $t_flows_reject = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where("ステータス", 2)
            ->get();

        // 決裁済かつTAMERUに保存、未保存どちらのレコードも取得
        $t_flows_approved = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where(function ($query) {
                $query->where("ステータス", 3)
                    ->orwhere("ステータス", 4);
            })
            ->get();

        $t_flows_reapplication = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where("ステータス", 5)
            ->get();

        $status = $request->input('status');
        if ($status == null) {
            if ($t_flows_reapplication->count() == 0) {
                $status = "ongoing_tab";
            } else {
                $status = "reapplication_tab";
            }
        }

        return view('flow.workflowcheckview', compact("prefix", "server", "m_categories", "users", "title", "category", "user", "start_day", "end_day", "status", "t_flows_ongoing", "t_flows_reject", "t_flows_approved", "t_flows_reapplication"));
    }
    // 閲覧詳細
    public function workflowcheckdetailget(Request $request, $id)
    {

        $t_flow = T_flow::find($id);
        $m_flow = M_flow::find($t_flow->フローマスタID);
        $m_category = M_category::find($m_flow->カテゴリマスタID);
        $t_optionals =  $this->application_items($id, $t_flow->再承認番号);

        $user = User::find($t_flow->申請者ID);

        $past_approvals = DB::table('t_approvals')
            ->select('t_approvals.ステータス', 't_approvals.updated_at as 承認日', 'users.name', "m_flow_points.フロントエンド表示ポイント", "t_flow_points.承認ステータス", "t_approvals.コメント", "t_approvals.再承認番号")
            ->leftJoin('t_flow_points', 't_approvals.フロー地点テーブルID', '=', 't_flow_points.id')
            ->leftJoin('users', 't_approvals.ユーザーID', '=', 'users.id')
            ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
            ->where(function ($query) {
                $query->where('ステータス', 0)
                    ->orWhere('ステータス', 4)
                    ->orWhere('ステータス', 5)
                    ->orWhere('ステータス', 6)
                    ->orWhere('ステータス', 8);
            })
            ->where('t_flow_points.フローテーブルID', $id)
            ->orderBy('承認日', 'asc')
            ->get();



        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('flow.workflowcheckdetail', compact("prefix", "server", "t_flow", "user", "t_optionals", "past_approvals", "m_category"));
    }

    // ワークフロー申請一覧
    public function workflowviewget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $m_categories = M_category::all();
        $users = DB::table('t_flows')
            ->select('users.id as user_id', 'users.name')
            ->leftJoin('users', 't_flows.申請者ID', '=', 'users.id')
            ->distinct()
            ->get();


        $title = $request->input('title');
        $category = $request->input('category');
        $user = $request->input('user');
        $start_day = $request->input('start_day');
        $end_day = $request->input('end_day');



        $t_flows_ongoing = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where("ステータス", 1)
            ->get();

        foreach ($t_flows_ongoing as $t_flow_ongoing) {
            $t_flow_points_ongoing = T_flow_point::where("フローテーブルID", $t_flow_ongoing->flow_id)->get();
            // フロー地点数をカウント、申請者も含まれるのでマイナス1
            $t_flow_ongoing->母数 = $t_flow_points_ongoing->count() - 1;
            // 承認ステータスから承認済みのものをカウント、申請者も含まれるのでマイナス1
            $t_flow_ongoing->承認数 = $t_flow_points_ongoing->where("承認ステータス", 0)->count() - 1;
        }


        $t_flows_reject = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where("ステータス", 2)
            ->get();

        // 決裁済かつTAMERUに保存、未保存どちらのレコードも取得
        $t_flows_approved = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where(function ($query) {
                $query->where("ステータス", 3)
                    ->orwhere("ステータス", 4);
            })
            ->get();

        $t_flows_reapplication = DB::table("t_flows")
            ->select("t_flows.*", "t_flows.id as flow_id", "users.*", 'm_categories.カテゴリ名')
            ->leftJoin("users", "t_flows.申請者ID", "=", "users.id")
            ->leftJoin('m_flows', 't_flows.フローマスタID', '=', 'm_flows.id')
            ->leftJoin('m_categories', 'm_flows.カテゴリマスタID', '=', 'm_categories.id')
            ->where("申請者ID", Auth::id())
            ->where('標題', 'like', $title ? "%" . $title . "%" : "%%")
            ->where('m_flows.カテゴリマスタID', 'like', $category ? $category : "%%")
            ->where('users.id', 'like', $user ? $user : "%%")
            ->where('t_flows.created_at', '>=', $start_day ? $start_day : "1900/01/01")
            ->where('t_flows.created_at', '<=', $end_day ? $end_day : "2100/01/01")
            ->where("ステータス", 5)
            ->get();

        $status = $request->input('status');
        if ($status == null) {
            if ($t_flows_reapplication->count() == 0) {
                $status = "ongoing_tab";
            } else {
                $status = "reapplication_tab";
            }
        }


        return view('flow.workflowview', compact("prefix", "server", "users", "m_categories", "status", "title", "category", "user", "start_day", "end_day", "t_flows_ongoing", "t_flows_reject", "t_flows_approved", "t_flows_reapplication"));
    }
    // ワークフロー申請詳細
    public function workflowapplicationdetailget($id)
    {

        $t_flow = T_flow::find($id);
        $m_flow = M_flow::find($t_flow->フローマスタID);
        $m_category = M_category::find($m_flow->カテゴリマスタID);
        $t_optionals =  $this->application_items($id, $t_flow->再承認番号);

        $user = User::find($t_flow->申請者ID);

        $past_approvals = DB::table('t_approvals')
            ->select('t_approvals.ステータス', 't_approvals.updated_at as 承認日', 'users.name', "m_flow_points.フロントエンド表示ポイント", "t_flow_points.承認ステータス", "t_approvals.コメント", "t_approvals.再承認番号")
            ->leftJoin('t_flow_points', 't_approvals.フロー地点テーブルID', '=', 't_flow_points.id')
            ->leftJoin('users', 't_approvals.ユーザーID', '=', 'users.id')
            ->leftJoin('m_flow_points', 't_flow_points.フロー地点ID', '=', 'm_flow_points.id')
            ->where(function ($query) {
                $query->where('ステータス', 0)
                    ->orWhere('ステータス', 4)
                    ->orWhere('ステータス', 5)
                    ->orWhere('ステータス', 6)
                    ->orWhere('ステータス', 8);
            })
            ->where('t_flow_points.フローテーブルID', $id)
            ->orderBy('承認日', 'asc')
            ->get();



        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('flow.workflowapplicationdetail', compact("prefix", "server", "t_flow", "user", "t_optionals", "past_approvals", "m_category"));
    }
    // ワークフロー申請取消し
    public function workflowapplicationcancelget($id)
    {
        $t_flow = T_flow::where('申請者ID', Auth::id())
            ->where('id', $id)
            ->where('ステータス', 1)
            ->first();
        if ($t_flow) {
            $t_flow->delete();
        }
        return redirect()->route('workflowviewget');
    }

    public function workflowstampget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $m_stamp = M_stamp::where('ユーザーID', Auth::id())->first();
        $m_stamp_chars = "";
        if ($m_stamp) {
            $m_stamp->縦横比 = 1 - $m_stamp->縦横比;
            $m_stamp_chars = M_stamp_char::where('はんこマスタID', $m_stamp->id)->get();
            $str = "";
            $length = 0;
            foreach ($m_stamp_chars as $m_stamp_char) {
                $str = $str . $m_stamp_char->文字;
                $length = $length + 1;
            }
            $m_stamp->文字 = $str;
            $m_stamp->文字数 = $length;
        }
        $t_flow_id = $request->input('t_flow') ?? '';
        $t_approval_id = $request->input('t_approval') ?? '';

        return view('flow.workflowstamp', compact("prefix", "server", "m_stamp", "m_stamp_chars", "t_flow_id", "t_approval_id"));
    }
    public function workflowstamppost(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        // dd($request->all());
        $letter_length = $request->input("letter_length");
        $m_stamp = M_stamp::where('ユーザーID', Auth::id())->first();

        $imageData = $request->input('stamp_img');
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        // Base64デコード
        $imageBinaryData = base64_decode($imageData);
        $imagename = "stamp_" . Auth::id() . ".png";
        if (config('prefix.server') == "cloud") {
            $imagename = 'stamp/' . $imagename;
            // S3にファイルを保存
            Storage::disk('s3')->put($prefix . '/' . $imagename, $imageBinaryData);
        } else if (config('prefix.server') == "onpre") {
            $imagepath = Config::get('custom.file_upload_path') . '\\' . $imagename;
            file_put_contents($imagepath, $imageBinaryData);
        }

        if ($m_stamp) {
            $m_stamp->フォント = $request->input("font");
            $m_stamp->フォントサイズ = $request->input("font_size");
            $m_stamp->縦横比 = $request->input("aspect");
            $m_stamp->ファイルパス = $imagename;
            $m_stamp->save();
            M_stamp_char::where('はんこマスタID', $m_stamp->id)->delete();
            $m_stamp_id = $m_stamp->id;
        } else {
            $new_m_stamp = new M_stamp();
            $new_m_stamp->ユーザーID = Auth::id();
            $new_m_stamp->フォント = $request->input("font");
            $new_m_stamp->フォントサイズ = $request->input("font_size");
            $new_m_stamp->縦横比 = $request->input("aspect");
            $new_m_stamp->ファイルパス = $imagename;
            $new_m_stamp->save();
            $m_stamp_id = $new_m_stamp->id;
        }

        for ($i = 0; $i < $letter_length; $i++) {
            $new_m_stamp_char = new M_stamp_char();
            $new_m_stamp_char->はんこマスタID = $m_stamp_id;
            $new_m_stamp_char->文字 = $request->input('char' . $i);
            $new_m_stamp_char->top = $request->input('y' . $i);
            $new_m_stamp_char->left = $request->input('x' . $i);
            $new_m_stamp_char->文字番号 = $i;
            $new_m_stamp_char->save();
        }
        if ($request->input('t_flow')) {
            return redirect()->route('workflowapplicationstampget', ["id" => $request->input('t_flow')]);
        } else if ($request->input('t_approval')) {
            return redirect()->route('workflowapprovalstampget', ["id" => $request->input('t_approval')]);
        }
        return redirect()->route('workflow');
    }

    public function workflowstampimgget($id)
    {
        $M_stamp = M_stamp::where("ユーザーID", $id)->first();
        $prefix = config('prefix.prefix');
        if ($M_stamp) {

            $filepath = $M_stamp->ファイルパス;


            if (config('prefix.server') == "cloud") {
                // S3バケットの情報
                $bucket = 'astdocs.com';
                $key = $prefix . '/' . $filepath;
                $expiration = '+1 hour'; // 有効期限

                $s3Client = new S3Client([
                    'region' => 'ap-northeast-1',
                    'version' => 'latest',
                ]);

                $command = $s3Client->getCommand('GetObject', [
                    'Bucket' => $bucket,
                    'Key' => $key
                ]);
                // 署名付きURLを生成
                $path = $s3Client->createPresignedRequest($command, $expiration)->getUri();
            } else {
                $path = Config::get('custom.file_upload_path') . "\\" . $filepath;
            }


            // 画像形式の場合は画像を表示
            if (config('prefix.server') == "cloud") {
                return response()->json(['path' => $path, 'Type' => 'image/png']);
            } else {
                return response()->file($path, ['Content-Type' => 'image/png']);
            }
        } else {
            return response()->json("none");
        }
    }

    public function workflowfileget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $hierarchy = $request->input('hierarchy') ?? 'category';
        $lists = [];
        if ($hierarchy == 'category') {
            $lists = M_category::all();
            return view('flow.workflowfile', compact("prefix", "server", "lists", "hierarchy"));
        } else if ($hierarchy == 't_flow') {
            $category_id = $request->input('category_id') ?? '';
            $lists = T_flow::where('カテゴリマスタID', $category_id)
                ->where('ステータス', 3)
                ->get();
            $category = M_category::find($category_id);
            return view('flow.workflowfile', compact("prefix", "server", "lists", "hierarchy", "category"));
        } else if ($hierarchy == 'file') {
            $t_flow_id = $request->input('t_flow_id') ?? '';
            $t_flow = T_flow::find($t_flow_id);
            $category_id = $t_flow->カテゴリマスタID;
            $category = M_category::find($category_id);
            $lists = DB::table('t_optionals')
                ->select('t_optionals.*', 'm_optionals.項目名')
                ->leftJoin('m_optionals', 't_optionals.任意項目マスタID', '=', 'm_optionals.id')
                ->where('フローテーブルID', $t_flow_id)
                ->whereNotNull('ファイルパス')
                ->get();
            if ($t_flow->変更後承認ファイルパス) {
                // 
                $application_file = new \stdClass();
                $application_file->項目名 = "承認用紙";
                $application_file->id = $t_flow->id;
                $application_file->ファイルパス = $t_flow->変更後承認ファイルパス;
                $application_file->ファイル形式 = pathinfo($t_flow->変更後承認ファイルパス, PATHINFO_EXTENSION);
                $lists->push($application_file);
            }
            return view('flow.workflowfile', compact("prefix", "server", "lists", "hierarchy", "category", "t_flow"));
        }
    }

    // 一括ダウンロード
    public function workflowfilealldownload()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
    }

    public function flowimgget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');

        if ($request->input("type") == "t_flow_before") {
            $img = T_flow::find($id);
            $filepath = $img->変更前承認ファイルパス;
            $extension = "pdf";
        } else if ($request->input("type") == "t_flow_after") {
            $img = T_flow::find($id);
            $filepath = $img->変更後承認ファイルパス;
            $extension = "pdf";
        } else if ($request->input("type") == "t_optional") {
            $img = T_optional::find($id);
            $filepath = $img->ファイルパス . ($img->ファイル形式 == null ? "" : "." . $img->ファイル形式);
            $extension = $img->ファイル形式;
        }
        // // 承認用紙の場合はt_flowのIDを負の数でAPIをたたくため
        // if ($id < 0) {
        //     $t_flow_id = $id * (-1);
        //     $img = T_flow::find($t_flow_id);
        //     $filepath = str_replace(".pdf", "", $img->変更前承認ファイルパス);
        //     $extension = "pdf";
        // } else {
        //     $img = T_optional::find($id);
        //     $filepath = $img->ファイルパス;
        //     $extension = $img->ファイル形式;
        // }


        if (config('prefix.server') == "cloud") {
            // S3バケットの情報
            $bucket = 'astdocs.com';
            $key = $prefix . '/' . $filepath;
            $expiration = '+1 hour'; // 有効期限

            $s3Client = new S3Client([
                'region' => 'ap-northeast-1',
                'version' => 'latest',
            ]);

            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $key
            ]);
            // 署名付きURLを生成
            $path = $s3Client->createPresignedRequest($command, $expiration)->getUri();
        } else {
            $path = Config::get('custom.file_upload_path') . "\\" . $filepath;
        }


        // 画像形式の場合は画像を表示
        if (in_array($extension, ['jpeg', 'jpg', 'JPG', 'jpe', 'JPEG', 'png', 'PNG', 'gif', 'bmp', 'svg'])) {
            if (config('prefix.server') == "cloud") {
                return response()->json(['path' => $path, 'Type' => 'image/' . $extension]);
            } else {
                return response()->file($path, ['Content-Type' => 'image/' . $extension]);
            }
        } else if (in_array($extension, ['PDF', 'pdf'])) {
            if (config('prefix.server') == "cloud") {
                return response()->json(['path' => $path, 'Type' => 'application/pdf']);
            } else {
                return response()->file($path, ['Content-Type' => 'application/pdf']);
            }
        } else {
            if (config('prefix.server') == "cloud") {
                return response()->json(['path' => $path, 'Type' => '']);
            } else {
                return response()->file($path, ['Content-Type' => '']);
            }
        }
    }

    public function flowdownload(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        $type = $request->input("type");
        if ($type == "t_optional") {
            $file = T_optional::find($id);
            $filepath = $file->ファイルパス . ($file->ファイル形式 == null ? "" : "." . $file->ファイル形式);
        } else if ($type == "t_flow_before") {
            $file = T_flow::find($id);
            $filepath = $file->変更前承認ファイルパス;
        } else if ($type == "t_flow_after") {
            $file = T_flow::find($id);
            $filepath = $file->変更後承認ファイルパス;
        }

        if (config('prefix.server') == "cloud") {

            // if ($file->ファイル形式 == "") {
            //     $key = $file->ファイルパス;
            // } else {
            //     $key = $file->ファイルパス . "." . $file->ファイル形式;
            // }


            $key = $prefix . '/' . $filepath;
            $parts = explode('/', $key);
            $filename = end($parts); // 最後の要素を取得     

            $headers = [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ];

            return \Response::make(Storage::disk('s3')->get($key), 200, $headers);
        } else {
            $filepath = Config::get('custom.file_upload_path') . "\\" . $filepath;
        }

        // ファイルのダウンロード
        return response()->download($filepath);
    }

    // public function workflowapprovaldownload($id)
    // {
    //     $t_flow = T_flow::find($id);
    //     if (config('prefix.server') == "cloud") {

    //         if ($t_flow->変更後承認ファイルパス == "") {
    //             $key = $t_flow->変更前承認ファイルパス;
    //         } else {
    //             $key = $t_flow->変更後承認ファイルパス;
    //         }
    //         $parts = explode('/', $key);
    //         $filename = end($parts); // 最後の要素を取得       


    //         $headers = [
    //             'Content-Type' => 'application/octet-stream',
    //             'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    //         ];

    //         return \Response::make(Storage::disk('s3')->get($key), 200, $headers);
    //     } else {
    //         //拡張子がないファイルの場合分け
    //         if ($t_flow->変更後承認ファイルパス == "") {
    //             $filepath = Config::get('custom.file_upload_path') . "\\" . $t_flow->変更前承認ファイルパス;
    //         } else {
    //             $filepath = Config::get('custom.file_upload_path') . "\\" . $t_flow->変更後承認ファイルパス;
    //         }
    //     }

    //     // ファイルのダウンロード
    //     return response()->download($filepath);
    // }


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
