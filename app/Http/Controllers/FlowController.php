<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Models\Group;
use App\Models\Group_User;
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
