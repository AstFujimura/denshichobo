<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
        $prefix = config('prefix.prefix');
        if ($prefix !==""){
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        return view('login.login',compact('prefix','server'));
    }

    public function loginPost(Request $request)
    {
        $name = $request->name;
        $password = $request->password;



        // Mysqlは標準では大文字と小文字の区別がされないため、BINARYをつけることによって区別される
        $user = User::whereRaw('BINARY name = ?', $name)->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            return redirect(session('url.intended'));
        }
        else {
            // エラーメッセージをフラッシュデータに設定
            session()->flash('error', 'ユーザー名もしくはパスワードが間違っています。');
            
            // リダイレクト先に戻す
            return redirect()->back();
        }
    }

}
