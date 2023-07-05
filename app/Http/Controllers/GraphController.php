<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mater;

class GraphController extends Controller
{
    public function graph()
    {
        $drives = Mater::all(['年月', '走行距離']);
        return response()->json($drives);
    }
}
