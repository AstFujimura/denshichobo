@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection




@section('main')
<div class="MainElement">
    <h2 class="pagetitle" id="workflow_title"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">ワークフロートップ</h2>
   
    <div class="main_menu_container">
        <div class="main_general_container">
            <div class="section-header">
                <h3 class="main_general_title">
                    <span class="section-title-icon">📋</span>
                    メニュー
                </h3>
            </div>
            <div class="main_general_content">
                <a href="{{route('workflowapplicationget')}}" class="main_general_button workflow-card">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        ワークフロー申請
                    </div>
                    <div class="workflow-card-arrow">→</div>
                </a>

                <a href="{{route('workflowapprovalview')}}" class="main_general_button workflow-card">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/approve.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        承認
                    </div>
                    @if ($t_approval_count > 0)
                    <div class="workflow-badge">
                        <span class="workflow-badge-text">{{$t_approval_count}}</span>
                    </div>
                    @endif
                    <div class="workflow-card-arrow">→</div>
                </a>

                <a href="{{route('workflowviewget')}}" class="main_general_button workflow-card">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application_view.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        申請一覧
                    </div>
                    @if ($t_reapplication_count > 0)
                    <div class="workflow-badge">
                        <span class="workflow-badge-text">{{$t_reapplication_count}}</span>
                    </div>
                    @endif
                    <div class="workflow-card-arrow">→</div>
                </a>

                <a href="{{route('workflowcheckviewget')}}" class="main_general_button workflow-card">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/checkview.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        閲覧一覧
                    </div>
                    <div class="workflow-card-arrow">→</div>
                </a>

                <a href="{{route('workflowstampget')}}" class="main_general_button workflow-card">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/stamp.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        印鑑設定
                    </div>
                    <div class="workflow-card-arrow">→</div>
                </a>

                <a href="{{route('workflowfileget')}}" class="main_general_button workflow-card">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/file.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        ファイル管理
                    </div>
                    <div class="workflow-card-arrow">→</div>
                </a>
            </div>
        </div>

        @if (Auth::user()->管理 == "管理")
        <div class="main_admin_container">
            <div class="section-header">
                <h3 class="main_admin_title">
                    <span class="section-title-icon">⚙️</span>
                    管理メニュー
                </h3>
            </div>
            <div class="main_admin_content">
                <a href="{{route('workflowmaster')}}" class="main_admin_button workflow-card workflow-card-admin">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/flow.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        経路マスタ
                    </div>
                    <div class="workflow-card-arrow">→</div>
                </a>

                <a href="{{route('mailsettingget')}}" class="main_admin_button workflow-card workflow-card-admin">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/mail.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        メール設定
                    </div>
                    <div class="workflow-card-arrow">→</div>
                </a>

                <a href="{{route('categoryget')}}" class="main_admin_button workflow-card workflow-card-admin">
                    <div class="workflow-card-icon-wrapper">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/category.svg') }}" alt="" class="flow_title_icon">
                    </div>
                    <div class="main_button_title">
                        カテゴリ設定
                    </div>
                    <div class="workflow-card-arrow">→</div>
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('footer')
@endsection