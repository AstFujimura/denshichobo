@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">メニュー</h2>
    <div class="main_menu_container">

        <div class="main_general_container">
            <div class="main_general_title">
                一般メニュー
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

            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection