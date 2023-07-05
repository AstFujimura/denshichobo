@extends('layouts.admintemplate')

@section('title')
名刺管理システム　管理者ページ
@endsection 

@section('menuebar')
<a href="{{route('adminpageGet')}}" class="nav">ユーザー一覧</a> > <a href='/admin/{{$user->id}}' class="nav">{{$user->name}}さん詳細 </a> > {{$user->name}}さん編集
@endsection 

@section('menue') 

@endsection

@section('main')

<form action="{{route('adminPut')}}" method="post" enctype="multipart/form-data" id="admin-myForm">
        @csrf
        @method('PUT')
    <div class="admin-form-container">
        <div class="input-container">
            <div class="registform">
            <div class="label"> ユーザー名<div class="ast">*</div></div>
            <div><input type="text" name="name" value="{{$user->name}}" id="name" class="admin-input-field"></div>
            </div>
            <div class="registform">
            <div class="label">メールアドレス <div class="ast" >*</div></div>
            <div><input type="text" name="email" value="{{$user->email}}" id="email" class="admin-input-field"></div>
            </div>
            <div class="registform">
            <div class="label">新しいパスワード <div class="ast" >*</div></div>
            <div><input type="password" name="password" id="password" class="admin-input-field"></div>
            </div>
            <input type="hidden" name="id" value="{{$user->id}}">
            <input type="checkbox" name="adminCheck" id="adminCheck" class="adminCheck" {{$check}}>
            <label type="checkbox" name="adminCheck" for="adminCheck" class="adminCheck">管理者権限を付与する</label>



            <div class="redmessage" id="error-message">
                必須項目が入力されていません
            </div>
        </div>
        <!-- input-container終わり -->
        <input type="submit" value="変更" class="admin-registbutton">

    </div>

       </form>

@endsection 
    @section('footer')
    @endsection 