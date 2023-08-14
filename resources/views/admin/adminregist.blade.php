@extends('layouts.admintemplate')

@section('title')
管理画面
@endsection 

@section('menuebar')

@endsection 

@section('menue')

@endsection

@section('main')
<h2>ユーザー登録</h2>

<form action="{{route('adminregistPost')}}" method="post" enctype="multipart/form-data" id="myForm">
    @csrf
    <div class="input-container">
        <label class="label">
            ユーザー名
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="text" name="name" class="input-field" id="name">
            <span class="errorelement" id="required4">必須項目です</span>
        </div>
    </div>
    <div class="input-container">
        <label class="label">
            メールアドレス
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="text" name="email" class="input-field" id="email">
            <span class="errorelement" id="required4">必須項目です</span>
        </div>
    </div>
    <div class="input-container">
        <label class="label">
            パスワード
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="password" name="password" class="input-field" id="password">
            <span class="errorelement" id="required4">必須項目です</span>
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

    <button class="adminregistbutton">登録</button>

</form>

@endsection 
    @section('footer')
    @endsection 