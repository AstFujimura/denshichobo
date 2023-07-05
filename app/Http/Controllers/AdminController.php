<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    //管理者画面に進む時
    public function adminpage()
    {
        //管理者ユーザーとしてログイン状態かどうかを確認して管理者ユーザー出なければトップページにリダイレクト
        if (Auth::user()->管理者権限 == "あり"){
            $users = User::where('id', '>=', 2)->get();
            return view('admin.adminpage',['users' => $users]);
        } else {
            return redirect()->route('topGet');
        }
    }

    // public function admintop()
    // {
    //     return redirect()->route('topGet');
    // }

    public function adminregist()
    {
        if (Auth::user()->管理者権限 == "あり"){
            return view('admin.adminregist');
        } else {
            return redirect()->route('topGet');
        }
    }
    
    public function admindetail($id)
    {
        if (Auth::user()->管理者権限 == "あり"){
            $user = User::where('id', '=', $id) ->first();
            if (!$user) {
                return response()->json(['message' => 'ユーザーが見つかりません'], 404);
            }

    
            // 取得したユーザー情報を利用する処理
    
            return view('admin.admindetail',['user' => $user]);
        } else {
            return redirect()->route('topGet');
        }
    }

    public function usercreate(Request $request)
    {
        if (Auth::user()->管理者権限 == "あり"){

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|unique:users,email|string|email|max:255',
                'password' => 'required|string|min:8',
            ]);
            if ($request->has('adminCheck')) {
                // チェックボックスが選択されている場合の処理
                $admin = "あり";
            } else {
                // チェックボックスが選択されていない場合の処理
                $admin = "なし";
            }
    
            // ユーザーの作成
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                '管理者権限' => $admin
            ]);


            return redirect()->route('adminpageGet');
        } else {
            return redirect()->route('topGet');
        }
    }

    public function userdelete(Request $request)
    {
        $id = $request->input('id');
        $record = User::find($id);
        if (!$record) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }
        User::where('id' ,'=', $id) ->delete();
        return redirect()->route('adminpageGet');
    }

        public function usereditGet($id)
    {
        if (Auth::user()->管理者権限 == "あり"){
            $user = User::where('id', '=', $id) ->first();
            if (!$user) {
                return response()->json(['message' => 'ユーザーが見つかりません'], 404);
            }
            $check = "";
            if ($user->管理者権限 == "あり"){
                $check = "checked";
            }
    
            return view('admin.adminedit',['user' => $user, 'check' => $check]);
        } else {
            return redirect()->route('topGet');
        }
    }

    public function useredit(Request $request)
    {
        if (Auth::user()->管理者権限 == "あり"){
            $id = $request->input('id');
            $record = User::find($id);
            if (!$record) {
                return response()->json(['message' => 'ユーザーが見つかりません'], 404);
            }

            if ($request->has('adminCheck')) {
                // チェックボックスが選択されている場合の処理
                $admin = "あり";
            } else {
                // チェックボックスが選択されていない場合の処理
                $admin = "なし";
            }
    
            // 取得したユーザー情報を利用する処理
            $record->name = $request->input('name');
            $record->email = $request->input('email');
            $record->password = Hash::make($request->input('password'));
            $record->管理者権限 = $admin;

            $record->save();
    
            return redirect()->route('admindetailGet', ['id' => $id]);
        } else {
            return redirect()->route('topGet');
        }
    }
}
