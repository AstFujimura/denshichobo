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
        return view('flow.workflow',compact("prefix","server"));
    }
}
