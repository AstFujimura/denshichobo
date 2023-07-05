<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mater;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Common\Type;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class TestController extends Controller
{
    public function test()
    {
        return view('test.test');
    }
    public function test2()
    {
        return view('test.test2');
    }
    public function testpost(Request $request)
    {
        $keyword = $request->input('word');
        if ($keyword == "aaa"){
            Mater::truncate();
        }

        return redirect()->route('topGet');

                
    }
}