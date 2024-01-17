<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Models\Group;
use App\Models\Group_User;
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
            $count = Group_User::where("グループID",$group->id)
            ->count();
            $group->count = $count;
        }
        return view('flow.workflow', compact("prefix", "server", "groups"));
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
