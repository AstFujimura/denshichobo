@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle">承認</h2>
    <div class="flow_view_container">
        <div class="tab_container">
            <div class="flow_tab tab_focus">
                承認画面
            </div>
            <div class="flow_tab">
                承認状況
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
                    <div class="flow_view_td flow_view_status unapproved">
                        未承認
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
                    <div class="flow_view_td flow_view_status unapproved">
                        未承認
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
                    <div class="flow_view_td flow_view_status unapproved">
                        未承認
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
                    <div class="flow_view_td flow_view_status unapproved">
                        未承認
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