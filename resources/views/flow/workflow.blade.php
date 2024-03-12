@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle">メニュー</h2>
    <div class="main_menu_container">

        <div class="main_general_container">
            <div class="main_general_content">
                <a href="{{route('workflowapplicationget')}}" class="main_general_button">
                    ワークフロー申請
                </a>
                <a href="{{route('workflowapprovalview')}}" class="main_general_button">
                    承認
                </a>
                <a href="{{route('workflowviewget')}}" class="main_general_button">
                    申請一覧
                </a>

            </div>
            <div class="main_admin_content">
                <a href="{{route('workflowmaster')}}" class="main_admin_button">
                    ワークフローマスタ一覧
                </a>
                <a href="{{route('workflowregistget')}}" class="main_admin_button">
                    ワークフローマスタ登録
                </a>

            </div>
        </div>
        <div class="main_admin_container">

        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection