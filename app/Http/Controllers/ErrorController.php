<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function errorGet(Request $request,$code)
    {
        if ($code == 'P127262'){
            $message = "パスワードリセットを連続で受け付けました。時間を置いてからパスワードの再発行をしてください。";
        }
        return view('error.adminerror',["message"=>$message]);
    }
}