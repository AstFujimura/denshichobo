@extends('layouts.admintemplate')

@section('title')
名刺管理システム　管理者ページ
@endsection 

@section('menuebar')

@endsection 

@section('menue') 

@endsection

@section('main')
<h2>ユーザー変更画面</h2>

<form action="{{route('admineditPut',['id' => 2])}}" method="post" enctype="multipart/form-data" id="admin-myForm">
        @csrf
</form>

@endsection 
    @section('footer')
    @endsection 