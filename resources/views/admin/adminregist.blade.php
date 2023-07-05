@extends('layouts.admintemplate')

@section('title')
名刺管理システム
@endsection 

@section('menuebar')
<a href="{{route('adminpageGet')}}" class="nav">ユーザー一覧</a> > ユーザー登録
@endsection 

@section('menue')

@endsection

@section('main')

<form action="{{route('adminPost')}}" method="post" enctype="multipart/form-data" id="myForm">
        @csrf
    <div class="admin-form-container">
        <div class="input-container">
            <div class="registform">
            <div class="label"> ユーザー名<div class="ast">*</div></div>
            <div><input type="text" name="name" id="company" class="admin-input-field"></div>
            </div>
            <div class="registform">
            <div class="label">メールアドレス <div class="ast" >*</div></div>
            <div><input type="text" name="email" id="companyKana" class="admin-input-field"></div>
            </div>
            <div class="registform">
            <div class="label">パスワード <div class="ast" >*</div></div>
            <div><input type="password" name="password" id="Name" class="admin-input-field"></div>
            </div>
            <input type="checkbox" name="adminCheck" id="adminCheck" class="adminCheck">
            <label type="checkbox" name="adminCheck" for="adminCheck" class="adminCheck">管理者権限を付与する</label>


            <div class="redmessage" id="error-message">
                必須項目が入力されていません
            </div>
        </div>
        <!-- input-container終わり -->
        <input type="submit" value="登録" class="admin-registbutton" id="registbutton">

    </div>

       </form>

@endsection 
    @section('footer')
    @endsection 