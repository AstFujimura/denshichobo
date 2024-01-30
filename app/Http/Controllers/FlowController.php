<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Models\Group;
use App\Models\Group_User;
use App\Models\M_flow;
use App\Models\M_flow_group;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FlowController extends Controller
{

    public function workflow()
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
        return view('flow.workflow', compact("prefix", "server", "groups", "positions"));
    }
    public function workflowpost(Request $request)
    {
        // -------------フローマスタ-----------------------------
        $flow_name = $request->input("flow_name");
        $flow_groups = $request->input("flow_group");
        $start_flow_price = $request->input("start_flow_price");
        $end_flow_price = $request->input("end_flow_price");

        // 分岐の数
        $arraycount = $request->input("arraycount");
        // 決裁地点数
        $lastelementcount = $request->input("lastelementcount");
dd($arraycount);
        $flow_master = new M_flow();
        
        $flow_master->フロー名 = $flow_name;
        if (!$flow_master){
            $flow_groups->グループ条件 = false;
        }
        $flow_master->金額下限条件 = $start_flow_price;
        $flow_master->金額上限条件 = $end_flow_price;
        $flow_master->決裁地点数 = $lastelementcount;

        // フローマスタを登録
        $flow_master->save();
         // -----------------------------------------------------



         // -------------フローグループ条件マスタ-------------------


        // フローグループ条件マスタをそれぞれ登録
        foreach ($flow_groups as $groupid){
            $flow_group_master = new M_flow_group();
            $flow_group_master->フローマスタID = $flow_master->id;
            $flow_group_master->グループiD = $groupid;
            $flow_group_master->save();
        }


         // -----------------------------------------------------



         // -------------フロー地点マスタ-------------------


        return redirect()->route('workflowpost');
    }

    public function flowuserlist(Request $request)
    {
        $searchtext = $request->input("search");

        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $users = User::where('name', 'like', '%' . $searchtext . '%')
            ->where('削除', "")
            ->get();
        return response()->json($users);
    }
}
