<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function loginGet()
    {
        return view('login.login');
    }

    public function loginPost(Request $request)
    {
        $name = $request->name;
        $password = $request->password;
        if (Auth::attempt(['name' => $name,'password' => $password])) {

                return redirect() ->route('topGet');
            
        }
        else {
            // エラーメッセージをフラッシュデータに設定
            session()->flash('error', 'ユーザー名もしくはパスワードが間違っています。');
            
            // リダイレクト先に戻す
            return redirect()->back();
        }
    }

}
