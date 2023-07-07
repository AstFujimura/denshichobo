<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\meishiinformation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class EditController extends Controller
{


    public function editGet($path)
    {

        $file = File::where('ファイルパス','like',$path.'%' )
                ->orderby('ファイルパス','desc')
                ->first();

        return view('information.editpage',['file' => $file]);
    }

    public function editPost(Request $request,$path)
    {

    }


}
