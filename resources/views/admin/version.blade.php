@extends('layouts.admintemplate')

@section('title')
機能設定 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>機能設定</h2>
<div class="bread_crumb_container">
    <div class="bread_crumb_content">
        <a>機能設定</a>
    </div>
    <div class="bread_crumb_content">
        <a href="{{ route('adminGet') }}">管理画面一覧</a>
    </div>
</div>

@if (session('success'))
<p class="savemessage">{{ session('success') }}</p>
@endif

<form action="{{ route('versionPost') }}" method="post" id="admin_version_form">
    @csrf
    <div class="link_container">
        <button type="submit" class="document_change_button">
            更新
        </button>
        <a href="{{ route('adminGet') }}" class="link_back">
            管理画面一覧に戻る
        </a>
    </div>

    <div class="documenttable">
        <div class="documenttable_header">
            <div class="admin_use">有効</div>
            <div class="admin_document">機能</div>
        </div>
        @foreach ($featureColumns as $column => $label)
        <div class="documenttable_body">
            <div class="admin_use">
                <input
                    type="checkbox"
                    name="{{ $column }}"
                    id="version_{{ $column }}"
                    value="1"
                    @checked($version->{$column})
                >
            </div>
            <div class="admin_document">
                <label for="version_{{ $column }}">{{ $label }}</label>
            </div>
        </div>
        @endforeach
    </div>
</form>
@endsection

@section('footer')
@endsection
