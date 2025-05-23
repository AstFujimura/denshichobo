<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Version;
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
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        return view('login.login', compact('prefix', 'server'));
    }

    public function loginPost(Request $request)
    {
        $name = $request->name;
        $password = $request->password;



        // Mysqlは標準では大文字と小文字の区別がされないため、BINARYをつけることによって区別される
        $user = User::whereRaw('BINARY name = ?', $name)->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            // ログイン前に指定したurlがある場合("/"の場合も含む)
            if (config('prefix.server') == 'onpre') {
                // もしデモ環境でTAMERUのトップを指定しようとした場合は名刺管理の方に進む
                if (session('url.intended')) {
                    return redirect(session('url.intended'));

                    return redirect(route('cardviewget'));
                }
                return redirect(route('startchoiceGet'));
            }
            // ログアウトボタンを押してログイン画面に遷移してきた場合
            else if (config('prefix.server') == 'cloud') {
                // デモ環境でTAMERUのトップを指定しようとした場合は名刺管理の方に進む
                if (Version::where('tameru', false)->first()) {
                    return redirect(route('cardviewget'));
                }
                else {
                    return redirect(route('topGet'));
                }
            }
        } else {
            // エラーメッセージをフラッシュデータに設定
            session()->flash('error', 'ユーザー名もしくはパスワードが間違っています。');

            // リダイレクト先に戻す
            return redirect()->back();
        }
    }

    public function startchoiceGet()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('login.startchoice', compact('prefix', 'server'));
    }
}
