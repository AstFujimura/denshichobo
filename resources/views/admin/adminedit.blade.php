@extends('layouts.admintemplate')

@section('title')
管理者ページ
@endsection 

@section('menuebar')

@endsection 

@section('menue') 

@endsection

@section('main')
<h2 id="admineditpage">ユーザー変更画面</h2>

<form action="{{route('admineditPut',['id' => $user->id])}}" method="post" enctype="multipart/form-data" id="admin-myForm">
        @csrf
        @method('PUT')
        <div class="input-container">
        <label class="label">
            ユーザー名
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="text" name="name" class="input-field" id="name" value="{{$user->name}}">
            <span class="errorelement" id="required1">必須項目です</span>
            <span class="errorelement" id="userformat">形式が不正です</span>
            <span class="errorelement" id="usercheck">ユーザー名が重複しています</span>
        </div>
    </div>
    <div class="input-container">
        <label class="label">
            メールアドレス
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="text" name="email" class="input-field" id="email" value="{{$user->email}}">
            <span class="errorelement" id="required2">必須項目です</span>
            <span class="errorelement" id="emailformat">形式が不正です</span>
        </div>
    </div>
    <div class="input-container">
        <label class="label">
            アクセス権限
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <select name="admin" class="input-field" id="admin">
                <option {{$admin}}>管理</option>
                <option {{$normal}}>一般</option>
            </select>
            <div class="errorelement" id="changeerror">管理ユーザーが他にいないためこの変更はできません。</div>
        </div>
    </div>
    <input type="hidden" value="{{$user->id}}" id = "userid">
    <button class="admineditbutton">変更</button>
</form>
<div class="important">
        <div class="title">重要設定
        </div>
        <div class="importantelement">

                
                <form class="adminreset" action="{{route('adminresetPost',['id' => $user->id])}}" method="post" enctype="multipart/form-data" id="adminreset">
                        @csrf
                        <input type="submit" class="adminresetbutton" value="パスワードリセット">
                            
                </form>


                <form class="adminDelete" action="{{route('adminDelete',['id' => $user->id])}}" method="post" enctype="multipart/form-data" id="admindelete">
                        @csrf
                        @method('DELETE')
                        <input type="submit" class="admindeletebutton" value="削除">
                </form>


        </div>
        <div class="errorelement" id="deleteerror">管理ユーザーが他にいないため削除できません。</div>
</div>
<input type="hidden" id="userID" value="{{$user->id}}">





@endsection 
    @section('footer')
    @endsection 