@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/approve.svg') }}" alt="" class="title_icon">承認一覧</h2>
    <div class="flow_view_container">
        <div class="tab_container">
            <div class="flow_tab tab_focus" data-tabname="approvable_tab">
                未承認
            </div>
            <div class="flow_tab" data-tabname="approved_tab">
                承認済み
            </div>
            <div class="flow_tab" data-tabname="rejected_tab">
                却下済み
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
                <div class="approvable_tab open_tab">
                    @if ($approvables->count() == 0)
                    <div class="none_data_message">未承認の申請データはありません</div>
                    @endif

                    @foreach ($approvables as $approvable)
                    <a href="/workflow/approval/{{$approvable->approval_id}}" class="flow_view_tbody_tr">
                        <div class="flow_view_td flow_view_title">
                            {{$approvable->標題}}
                        </div>
                        <div class="flow_view_td flow_view_status unapproved">
                            未承認
                        </div>
                        <div class="flow_view_td flow_view_applicant">
                            {{$approvable->name}}
                        </div>
                        <div class="flow_view_td flow_view_date">
                            {{\Carbon\Carbon::parse($approvable->flow_created_at)->toDateString()}}
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="approved_tab">
                    @if ($approveds->count() == 0)
                    <div class="none_data_message">承認済の申請データはありません</div>
                    @endif
                    @foreach ($approveds as $approved)
                    <a href="/workflow/approval/{{$approved->approval_id}}" class="flow_view_tbody_tr">
                        <div class="flow_view_td flow_view_title">
                            {{$approved->標題}}
                        </div>
                        <div class="flow_view_td flow_view_status approved">
                            承認済
                        </div>
                        <div class="flow_view_td flow_view_applicant">
                            {{$approved->name}}
                        </div>
                        <div class="flow_view_td flow_view_date">
                            {{\Carbon\Carbon::parse($approved->flow_created_at)->toDateString()}}
                        </div>
                    </a>
                    @endforeach
                </div>

                <div class="rejected_tab">
                    @if ($rejecteds->count() == 0)
                    <div class="none_data_message">却下済の申請データはありません</div>
                    @endif
                    @foreach ($rejecteds as $rejected)
                    <a href="/workflow/approval/{{$rejected->approval_id}}" class="flow_view_tbody_tr">
                        <div class="flow_view_td flow_view_title">
                            {{$rejected->標題}}
                        </div>
                        <div class="flow_view_td flow_view_status approved">
                            却下
                        </div>
                        <div class="flow_view_td flow_view_applicant">
                            {{$rejected->name}}
                        </div>
                        <div class="flow_view_td flow_view_date">
                            {{\Carbon\Carbon::parse($rejected->flow_created_at)->toDateString()}}
                        </div>
                    </a>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection