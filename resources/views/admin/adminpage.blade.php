@extends('layouts.admintemplate')

@section('title')
管理者画面 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')
<div class="admin_home">
    <header class="admin_home__header">
        <h2 class="admin_home__title">管理者画面</h2>
        <p class="admin_home__lead">ユーザー・グループ・書類区分など、TAMERU の運用設定を行います。</p>
    </header>

    <nav class="admin_console" aria-label="管理メニュー">
        <a href="{{ route('adminregistGet') }}" class="admin_console_card admin_console_card--users">
            <span class="admin_console_card__icon" aria-hidden="true">
                <img src="{{ asset($prefix.'/img/user_add_2_fill.svg') }}" alt="">
            </span>
            <span class="admin_console_card__body">
                <span class="admin_console_card__title">ユーザー管理</span>
                <span class="admin_console_card__desc">利用者の登録・権限・パスワード</span>
            </span>
            <span class="admin_console_card__arrow" aria-hidden="true">›</span>
        </a>

        <a href="{{ route('admingroupregistGet') }}" class="admin_console_card admin_console_card--groups">
            <span class="admin_console_card__icon" aria-hidden="true">
                <img src="{{ asset($prefix.'/img/group.svg') }}" alt="">
            </span>
            <span class="admin_console_card__body">
                <span class="admin_console_card__title">グループ管理</span>
                <span class="admin_console_card__desc">グループと所属ユーザーの整理</span>
            </span>
            <span class="admin_console_card__arrow" aria-hidden="true">›</span>
        </a>

        @if (App\Models\Version::where('tameru', true)->first())
        <a href="{{ route('admindocumentGet') }}" class="admin_console_card admin_console_card--documents">
            <span class="admin_console_card__icon" aria-hidden="true">
                <img src="{{ asset($prefix.'/img/document_2_line.svg') }}" alt="">
            </span>
            <span class="admin_console_card__body">
                <span class="admin_console_card__title">書類管理</span>
                <span class="admin_console_card__desc">書類区分と AI OCR の設定</span>
            </span>
            <span class="admin_console_card__arrow" aria-hidden="true">›</span>
        </a>
        @endif

        @if (Auth::id() === 1)
        <a href="{{ route('versionGet') }}" class="admin_console_card admin_console_card--features">
            <span class="admin_console_card__icon" aria-hidden="true">
                <img src="{{ asset($prefix.'/img/settings_4_line.svg') }}" alt="">
            </span>
            <span class="admin_console_card__body">
                <span class="admin_console_card__title">機能設定</span>
                <span class="admin_console_card__desc">TAMERU など機能の有効化</span>
            </span>
            <span class="admin_console_card__arrow" aria-hidden="true">›</span>
        </a>
        @endif
    </nav>
</div>

@endsection
@section('footer')
@endsection
