@extends('layouts.admintemplate')

@section('title')
ユーザー一覧 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')
<h2>ユーザー一覧</h2>
<div>
    <div class="admin_header_container">
        <a class="admin_header_container_button" href="{{route('adminregistGet')}}">新規ユーザー登録</a>
    </div>
    <div class="admin_top_table_div">
        <div class="name">ユーザー名</div>
        <div class="email">email</div>
        <div class="status">権限</div>
        <div class="hennkou">変更</div>
    </div>
    <div class="admin_top_table_element">
        @foreach ($users as $user)
        <div class="admin_top_table_body">

            <div class="name">{{$user->name}}</div>
            <div class="email">{{$user->email}}</div>
            <div class="status">{{$user->管理}}</div>
            <div class="hennkou">
                <img src="{{ asset($prefix.'/'.'img/transfer_3_fill.svg')}}" class="download" onclick="location.href='{{$prefix}}/admin/edit/{{$user->id}}';">
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
@section('footer')
@endsection