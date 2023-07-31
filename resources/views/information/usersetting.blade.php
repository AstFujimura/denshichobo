@extends('layouts.template')

@section('title')
電子帳簿保存システム
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2>変更結果</h2>
<form action="{{route('admineditPut',['id' => $user->id])}}" method="post" enctype="multipart/form-data" id="admin-myForm">
        @csrf
        @method('PUT')
        <div class="input-container">
        <label class="label">
            ユーザー名
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="text" name="name" class="input-field dateinputtext" id="name" value="{{$user->name}}">
            <span class="errorelement" id="required4">必須項目です</span>
        </div>
    </div>
    <div class="input-container">
        <label class="label">
            メールアドレス
            <span class="requirered">*</span>
        </label>
        <div class="dateform">
            <input type="text" name="email" class="input-field dateinputtext" id="email" value="{{$user->email}}">
            <span class="errorelement" id="required4">必須項目です</span>
        </div>
    </div>

    <button class="admineditbutton">変更</button>
</form>

@endsection 
    @section('footer')

    @endsection 


