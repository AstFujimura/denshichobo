@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application_view.svg') }}" alt="" class="title_icon">申請一覧</h2>
    <div class="flow_view_container">
        <div class="tab_container">
            <div class="flow_tab tab_focus" data-tabname="ongoing_tab">
                進行中
            </div>
            <div class="flow_tab" data-tabname="approved_tab">
                決裁済
            </div>
            <div class="flow_tab" data-tabname="reject_tab">
                却下済
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
                <div class="ongoing_tab open_tab">
                    @if ($t_flows_ongoing->count() == 0)
                    <div class="none_data_message">進行中の申請データはありません</div>
                    @endif
                    @foreach ($t_flows_ongoing as $t_flow_ongoing)
                    <a href="/workflow/application/detail/{{$t_flow_ongoing->flow_id}}" class="flow_view_tbody_tr">

                        <div class="flow_view_td flow_view_title">
                            {{$t_flow_ongoing->標題}}
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
                <div class="approved_tab">
                    @if ($t_flows_approved->count() == 0)
                    <div class="none_data_message">決裁済の申請データはありません</div>
                    @endif
                    @foreach ($t_flows_approved as $t_flow_approved)
                    <a href="/workflow/application/detail/{{$t_flow_approved->flow_id}}" class="flow_view_tbody_tr">

                        <div class="flow_view_td flow_view_title">
                            {{$t_flow_approved->標題}}
                        </div>
                        <div class="flow_view_td flow_view_status ongoing">
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
                <div class="reject_tab">
                    @if ($t_flows_reject->count() == 0)
                    <div class="none_data_message"> 却下済の申請データはありません</div>
                    @endif
                    @foreach ($t_flows_reject as $t_flow_reject)
                    <a href="/workflow/application/detail/{{$t_flow_reject->flow_id}}" class="flow_view_tbody_tr">

                        <div class="flow_view_td flow_view_title">
                            {{$t_flow_reject->標題}}
                        </div>
                        <div class="flow_view_td flow_view_status ongoing">
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

                <!-- <div class="flow_view_tbody_tr">
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
                </div> -->

            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection