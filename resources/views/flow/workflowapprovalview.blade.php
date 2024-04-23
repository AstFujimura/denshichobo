@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/approve.svg') }}" alt="" class="title_icon">承認一覧</h2>
    <div class="flow_view_container">
        <div class="flow_application_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
        </div>
        <form action="{{route('workflowapprovalview')}}" method="get" id="search_form">
            <div class="flow_search_box">
                <div class="flow_search_application_box">
                    <div class="flow_search_application_title">
                        標題 :
                    </div>
                    <input type="text" class="flow_search_input" name="title" value="{{$title}}">
                    (部分一致)
                </div>
                <div class="flow_search_application_box">
                    <div class="flow_search_application_title">
                        カテゴリ :
                    </div>
                    <select name="category" id="" class="flow_search_input" data-id="{{$category}}">
                        <option value="">すべて</option>
                        @foreach ($m_categories as $m_category)
                        <option value="{{$m_category->id}}">{{$m_category->カテゴリ名}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flow_search_application_box">
                    <div class="flow_search_application_title">
                        申請者 :
                    </div>
                    <select name="user" id="" class="flow_search_input" data-id="{{$user}}">
                        <option value="">すべて</option>
                        @foreach ($users as $user)
                        <option value="{{$user->user_id}}">{{$user->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flow_search_application_box">
                    <div class="flow_search_application_title">
                        申請日
                    </div> 
                    <input type="text" class="flow_search_input search_form_date"  name="start_day" value="{{$start_day}}">
                    　～　
                    <input type="text" class="flow_search_input search_form_date"  name="end_day" value="{{$end_day}}">
                </div>
                <input type="hidden" id="status" name="status" value="{{$status}}">
                <button class="flow_search_button">
                    検索
                </button>
            </div>
        </form>
        <div class="tab_container">
            <div class="flow_tab {{($status == 'approvable_tab' ? 'tab_focus' : '')}}" data-tabname="approvable_tab">
                未承認
            </div>
            <div class="flow_tab {{($status == 'approved_tab' ? 'tab_focus' : '')}}" data-tabname="approved_tab">
                承認済
            </div>
            <div class="flow_tab {{($status == 'rejected_tab' ? 'tab_focus' : '')}}" data-tabname="rejected_tab">
                却下済
            </div>
        </div>
        <div class="flow_view_content">
            <div class="flow_view_table">
                <div class="flow_view_thead_tr">
                    <div class="flow_view_th flow_view_title">
                        標題
                    </div>
                    <div class="flow_view_th flow_view_category">
                        カテゴリ
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
                <div class="approvable_tab {{($status == 'approvable_tab' ? 'open_tab' : '')}}">
                    @if ($approvables->count() == 0)
                    <div class="none_data_message">未承認の申請データはありません</div>
                    @endif

                    @foreach ($approvables as $approvable)
                    <a href="/workflow/approval/{{$approvable->approval_id}}" class="flow_view_tbody_tr">
                        <div class="flow_view_td flow_view_title">
                            {{$approvable->標題}}
                        </div>
                        <div class="flow_view_td flow_view_category">
                            {{$approvable->カテゴリ名}}
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
                <div class="approved_tab {{($status == 'approved_tab' ? 'open_tab' : '')}}">
                    @if ($approveds->count() == 0)
                    <div class="none_data_message">承認済の申請データはありません</div>
                    @endif
                    @foreach ($approveds as $approved)
                    <a href="/workflow/approval/{{$approved->approval_id}}" class="flow_view_tbody_tr">
                        <div class="flow_view_td flow_view_title">
                            {{$approved->標題}}
                        </div>
                        <div class="flow_view_td flow_view_category">
                            {{$approved->カテゴリ名}}
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

                <div class="rejected_tab  {{($status == 'rejected_tab' ? 'open_tab' : '')}}">
                    @if ($rejecteds->count() == 0)
                    <div class="none_data_message">却下済の申請データはありません</div>
                    @endif
                    @foreach ($rejecteds as $rejected)
                    <a href="/workflow/approval/{{$rejected->approval_id}}" class="flow_view_tbody_tr">
                        <div class="flow_view_td flow_view_title">
                            {{$rejected->標題}}
                        </div>
                        <div class="flow_view_td flow_view_category">
                            {{$rejected->カテゴリ名}}
                        </div>
                        <div class="flow_view_td flow_view_status rejected">
                            却下済
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