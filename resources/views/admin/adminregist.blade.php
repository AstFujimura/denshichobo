@extends('layouts.admintemplate')

@section('title')
ユーザー登録 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2 id="adminregistpage">ユーザー登録</h2>

<form action="{{route('adminregistPost')}}" method="post" enctype="multipart/form-data" id="myForm">
    @csrf
    <div class="input-container">
        <label class="label">
            ユーザー名
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="text" name="name" class="input-field" id="name">
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
            <input type="text" name="email" class="input-field" id="email">
            <span class="errorelement" id="required2">必須項目です</span>
            <span class="errorelement" id="emailformat">形式が不正です</span>
        </div>
    </div>
    <div class="input-container">
        <label class="label">
            パスワード
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="password" name="password" class="input-field" id="password">
            <span class="errorelement" id="required3">必須項目です</span>
            <span class="errorelement" id="passwordformat">大文字,小文字,数字を含めた8文字以上にしてください</span>
        </div>

    </div>
    <div class="input-container">
        <label class="label">
            確認用パスワード
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="password" name="newpassword" class="input-field" id="newpassword">
            <span class="errorelement" id="newpasswordformat">同じパスワードを入力してください</span>
        </div>

    </div>

    <div class="input-container">
        <label class="label">
            アクセス権限
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <select name="admin" class="input-field">
                <option>管理</option>
                <option selected>一般</option>
            </select>
        </div>
    </div>

    <div class="input-container">
        <label class="label">
            グループ
        </label>
        <div class="checkform">
            @foreach ($groups as $group)
            <div>
                <input type="checkbox" name="grouparray[]" id="group{{$group->id}}" value="{{$group->id}}"><label for="group{{$group->id}}" class="group_check_label">{{$group->グループ名}}</label>
            </div>
            @endforeach
        </div>
    </div>

    <button class="adminregistbutton">登録</button>

</form>

@endsection
@section('footer')
@endsection