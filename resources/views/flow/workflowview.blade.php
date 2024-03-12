@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle">申請一覧</h2>
    <div class="flow_view_container">
        <div class="tab_container">
            <div class="flow_tab tab_focus">
                最新一覧
            </div>
            <div class="flow_tab">
                送信一覧
            </div>
            <div class="flow_tab">
                受信一覧
            </div>
        </div>
        <div class="flow_view_content">
            <div class="flow_view_table">
                <div class="flow_view_thead_tr">
                    <div class="flow_view_th flow_view_title">
                        標題
                    </div>
                    <div class="flow_view_th flow_view_status">
                        状態
                    </div>
                    <div class="flow_view_th flow_view_applicant">
                        申請者
                    </div>
                    <div class="flow_view_th flow_view_date">
                        申請日
                    </div>
                </div>
                <div class="flow_view_tbody_tr">
                    <div class="flow_view_td flow_view_title">
                        ノートパソコン購入の件
                    </div>
                    <div class="flow_view_td flow_view_status ongoing">
                        進行中
                    </div>
                    <div class="flow_view_td flow_view_applicant">
                        藤村直輝
                    </div>
                    <div class="flow_view_td flow_view_date">
                        2024/02/01
                    </div>
                </div>
                <div class="flow_view_tbody_tr">
                    <div class="flow_view_td flow_view_title">
                        ノートパソコン購入の件
                    </div>
                    <div class="flow_view_td flow_view_status completion">
                        完了
                    </div>
                    <div class="flow_view_td flow_view_applicant">
                        藤村直輝
                    </div>
                    <div class="flow_view_td flow_view_date">
                        2024/02/01
                    </div>
                </div>
                <div class="flow_view_tbody_tr">
                    <div class="flow_view_td flow_view_title">
                        ノートパソコン購入の件
                    </div>
                    <div class="flow_view_td flow_view_status ongoing">
                        進行中
                    </div>
                    <div class="flow_view_td flow_view_applicant">
                        藤村直輝
                    </div>
                    <div class="flow_view_td flow_view_date">
                        2024/02/01
                    </div>
                </div>
                <div class="flow_view_tbody_tr">
                    <div class="flow_view_td flow_view_title">
                        ノートパソコン購入の件
                    </div>
                    <div class="flow_view_td flow_view_status ongoing">
                        進行中
                    </div>
                    <div class="flow_view_td flow_view_applicant">
                        藤村直輝
                    </div>
                    <div class="flow_view_td flow_view_date">
                        2024/02/01
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection