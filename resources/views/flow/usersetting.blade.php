@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2>{{$user->name}}さん情報</h2>
<form action="{{route('usersettingPost')}}" method="post" enctype="multipart/form-data" id="usersetting">
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
    <div class="important">
        <div class="important_title">パスワード変更
        </div>
        <div class="importantelement">
            <label class="label">
                現パスワード
                <span class="requirered">*</span>
            </label>
            <div class="dateform">
                <input type="password" name="oldpass" class="input-field" id="oldpass">
                <span class="errorelement" id="required3">必須項目です</span>
                <span class="errorelement differencepass">パスワードが違います</span>
            </div>
            <label class="label">
                新パスワード
                <span class="requirered">*</span>
            </label>
            <div class="dateform">
                <input type="password" name="newpass" class="input-field" id="newpass">
                <span class="errorelement" id="required4">必須項目です</span>
                <span class="errorelement passcheck">同じパスワードを入力してください</span>
                <span class="errorelement" id="passformat">大文字,小文字,数字を含めた8文字以上にしてください</span>
            </div>
            <label class="label">
                新パスワード確認
                <span class="requirered">*</span>
            </label>
            <div class="dateform">
                <input type="password" name="newpass" class="input-field" id="newpasscheck">
                <span class="errorelement" id="required5">必須項目です</span>
                <span class="errorelement passcheck">同じパスワードを入力してください</span>
                <span class="errorelement" id="passcheckformat">大文字,小文字,数字を含めた8文字以上にしてください</span>
            </div>

        </div>

    </div>

    <button class="usersettingbutton">変更</button>
</form>
<input type="hidden" id="userID" value="{{$user->id}}">
@endsection 
    @section('footer')

    @endsection 


