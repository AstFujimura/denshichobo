@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application_view.svg') }}" alt="" class="title_icon">申請一覧</h2>
    <div class="flow_view_container">
        <div class="flow_application_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
        </div>
        <form action="{{route('workflowviewget')}}" method="get" id="search_form">
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
            <div class="flow_tab {{($status == 'ongoing_tab' ? 'tab_focus' : '')}}" data-tabname="ongoing_tab">
                進行中
            </div>
            <div class="flow_tab {{($status == 'approved_tab' ? 'tab_focus' : '')}}" data-tabname="approved_tab">
                決裁済
            </div>
            <div class="flow_tab {{($status == 'reject_tab' ? 'tab_focus' : '')}}" data-tabname="reject_tab">
                却下済
            </div>
            <div class="flow_tab {{($status == 'reapplication_tab' ? 'tab_focus' : '')}}" data-tabname="reapplication_tab">
                再申請待ち
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
                <div class="ongoing_tab {{($status == 'ongoing_tab' ? 'open_tab' : '')}}">
                    @if ($t_flows_ongoing->count() == 0)
                    <div class="none_data_message">進行中の申請データはありません</div>
                    @endif
                    @foreach ($t_flows_ongoing as $t_flow_ongoing)
                    <a href="{{$prefix}}/workflow/application/detail/{{$t_flow_ongoing->flow_id}}" class="flow_view_tbody_tr">

                        <div class="flow_view_td flow_view_title">
                            {{$t_flow_ongoing->標題}}
                        </div>
                        <div class="flow_view_td flow_view_category">
                            {{$t_flow_ongoing->カテゴリ名}}
                        </div>
                        <div class="flow_view_td flow_view_status ongoing">
                            {{$t_flow_ongoing->承認数}} / {{$t_flow_ongoing->母数}} 承認
                        </div>
                        <div class="flow_view_td flow_view_applicant">
                            {{$t_flow_ongoing->name}}
                        </div>
                        <div class="flow_view_td flow_view_date">
                            {{$t_flow_ongoing->created_at}}
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="approved_tab {{($status == 'approved_tab' ? 'open_tab' : '')}}">
                    @if ($t_flows_approved->count() == 0)
                    <div class="none_data_message">決裁済の申請データはありません</div>
                    @endif
                    @foreach ($t_flows_approved as $t_flow_approved)
                    <a href="{{$prefix}}/workflow/application/detail/{{$t_flow_approved->flow_id}}" class="flow_view_tbody_tr">

                        <div class="flow_view_td flow_view_title">
                            {{$t_flow_approved->標題}}
                        </div>
                        <div class="flow_view_td flow_view_category">
                            {{$t_flow_approved->カテゴリ名}}
                        </div>
                        <div class="flow_view_td flow_view_status completion">
                            決裁済
                        </div>
                        <div class="flow_view_td flow_view_applicant">
                            {{$t_flow_approved->name}}
                        </div>
                        <div class="flow_view_td flow_view_date">
                            {{$t_flow_approved->created_at}}
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="reject_tab {{($status == 'rejected_tab' ? 'open_tab' : '')}}">
                    @if ($t_flows_reject->count() == 0)
                    <div class="none_data_message"> 却下済の申請データはありません</div>
                    @endif
                    @foreach ($t_flows_reject as $t_flow_reject)
                    <a href="{{$prefix}}/workflow/application/detail/{{$t_flow_reject->flow_id}}" class="flow_view_tbody_tr">

                        <div class="flow_view_td flow_view_title">
                            {{$t_flow_reject->標題}}
                        </div>
                        <div class="flow_view_td flow_view_category">
                            {{$t_flow_reject->カテゴリ名}}
                        </div>
                        <div class="flow_view_td flow_view_status rejected">
                            却下済
                        </div>
                        <div class="flow_view_td flow_view_applicant">
                            {{$t_flow_reject->name}}
                        </div>
                        <div class="flow_view_td flow_view_date">
                            {{$t_flow_reject->created_at}}
                        </div>
                    </a>
                    @endforeach
                </div>

                <div class="reapplication_tab {{($status == 'reapplication_tab' ? 'open_tab' : '')}}">
                    @if ($t_flows_reapplication->count() == 0)
                    <div class="none_data_message">再申請待ちの申請データはありません</div>
                    @endif
                    @foreach ($t_flows_reapplication as $t_flow_reapplication)
                    <a href="{{$prefix}}/workflow/application/detail/{{$t_flow_reapplication->flow_id}}" class="flow_view_tbody_tr">
                    <div class="flow_view_td flow_view_title">
                            {{$t_flow_reapplication->標題}}
                        </div>
                        <div class="flow_view_td flow_view_category">
                            {{$t_flow_reapplication->カテゴリ名}}
                        </div>
                        <div class="flow_view_td flow_view_status reapplication">
                            再申請待ち
                        </div>
                        <div class="flow_view_td flow_view_applicant">
                            {{$t_flow_reapplication->name}}
                        </div>
                        <div class="flow_view_td flow_view_date">
                            {{$t_flow_reapplication->created_at}}
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