@extends('layouts.admintemplate')

@section('title')
ユーザー一覧 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')
<h2>管理者画面</h2>
<div>
    <div class="admin_console">
        <div class="admin_console_item">
            <a href="{{route('adminregistGet')}}">
                ユーザー管理
            </a>
        </div>
        <div class="admin_console_item">
            <a href="{{route('admingroupregistGet')}}">
                グループ管理
            </a>
        </div>
        <div class="admin_console_item">
            <a href="{{route('admindocumentGet')}}">
                書類管理
            </a>
        </div>
    </div>
</div>

@endsection
@section('footer')
@endsection