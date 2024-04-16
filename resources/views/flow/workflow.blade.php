@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">トップ</h2>
    <div class="main_menu_container">

        <div class="main_general_container">
            <div class="main_general_title">
                メニュー
            </div>
            <div class="main_general_content">

                <a href="{{route('workflowapplicationget')}}" class="main_general_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application.svg') }}" alt="" class="flow_title_icon">
                    <div class="main_button_title">
                        ワークフロー申請
                    </div>
                </a>
                <a href="{{route('workflowapprovalview')}}" class="main_general_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/approve.svg') }}" alt="" class="flow_title_icon">
                    <div class="main_button_title">
                        承認
                    </div>
                </a>
                <a href="{{route('workflowviewget')}}" class="main_general_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application_view.svg') }}" alt="" class="flow_title_icon">
                    <div class="main_button_title">
                        申請一覧
                    </div>
                </a>

            </div>

        </div>
        @if (Auth::user()->管理 == "管理")
        <div class="main_admin_container">
            <div class="main_admin_title">
                管理メニュー
            </div>
            <div class="main_admin_content">

                <a href="{{route('workflowmaster')}}" class="main_admin_button">

                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/master_view.svg') }}" alt="" class="flow_title_icon">

                    <div class="main_button_title">
                        ワークフローマスタ一覧
                    </div>
                </a>
                <a href="{{route('workflowregistget')}}" class="main_admin_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/master_regist.svg') }}" alt="" class="flow_title_icon">

                    <div class="main_button_title">
                        ワークフローマスタ登録
                    </div>
                </a>
                <a href="{{route('mailsettingget')}}" class="main_admin_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/mail.svg') }}" alt="" class="flow_title_icon">

                    <div class="main_button_title">
                        メール設定
                    </div>
                </a>
                <a href="{{route('categoryget')}}" class="main_admin_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/setting.svg') }}" alt="" class="flow_title_icon">

                    <div class="main_button_title">
                        カテゴリ設定
                    </div>
                </a>

            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('footer')
@endsection