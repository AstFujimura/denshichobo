<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function errorGet(Request $request,$code)
    {
        if ($code == 'P127262'){
            $message = "パスワードリセットを連続で受け付けました。時間を置いてからパスワードの再発行をしてください。";
            return view('error.adminerror',["message"=>$message]);
        }
        else if ($code == 'R134534'){
            $message = "重複した情報が登録されました。確認してください。";
            return view('error.error',["message"=>$message]);
        }
        else if ($code == 'K183623'){
            $message = "「戻る」を使用しないでください。";
            return view('error.error',["message"=>$message]);
        }
        else if ($code == 'E145323'){
            $message = "予期せぬエラーが発生しました";
            return view('error.error',["message"=>$message]);
        }
        
    }
}