@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection



<div class="approve_gray"></div>
<div class="approve_preview_container"></div>
@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="approve_phase"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/approve.svg') }}" alt="" class="title_icon">承認</h2>
    <div class="flow_view_container">
        <div class="flow_application_button_content">
            <a href="{{route('workflowapprovalview')}}" class="back_button " id="flow_next_button">
               承認一覧へもどる
            </a>
        </div>

        <div class="tab_container">
            <div class="flow_tab tab_focus" data-tabname="approve_tab">
                承認内容
            </div>
            <div class="flow_tab" data-tabname="approve_condition_tab">
                承認状況
            </div>
        </div>
        <div class="flow_view_content">
            <div class="approve_tab open_tab">
                <div class="approve_container">
                    <div class="approve_application_info_container">
                        <div class="approval_sub_title">
                            申請情報
                        </div>
                        <div class="applicant_info">
                            <img class="approve_person_icon" src="{{ asset(config('prefix.prefix').'/'.'img/person.svg') }}">申請者 : {{$user->name}}
                        </div>
                        @foreach ($t_optionals as $t_optional)
                        <div class="approve_content">
                            <div class="approve_content_title">
                                {{$t_optional->項目名}}
                            </div>
                            <div class="approve_content_element">
                                @if ($t_optional->値 == "file_regist_2545198")
                                <div class="approve_preview_button" data-id="{{$t_optional->id}}">プレビュー</div>
                                <img src="{{ asset(config('prefix.prefix').'/'.'img/download_2_line.svg') }}" class="approve_download" id="{{$prefix}}/workflow/download/{{$t_optional->id}}">
                                @else
                                {{$t_optional->値}}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="approve_authorizer_container">
                        @if ($t_approval->ステータス == 2)
                        <div class="approval_sub_title">
                            承認操作
                        </div>
                        <form action="{{route('workflowapprovalpost')}}" method="post" id="approve_form" class="approve_form" enctype="multipart/form-data">
                            @csrf
                            <div class="approve_comment_container">
                                <div>承認者コメント</div>
                                <textarea class="approvecomment" name="approvecomment" id="approvecomment"></textarea>
                            </div>
                            <div class="approve_decision_container">
                                <div class="rejectbutton" id="rejectbutton">
                                    却下する
                                </div>
                                <div class="approvalbutton" id="approvalbutton">
                                    承認する
                                </div>
                            </div>
                            <input type="hidden" name="approval_id" value="{{$t_approval->id}}">
                            <input type="hidden" name="result" id="result" value="">

                        </form>
                        @else
                        <div class="approval_sub_title">
                            承認結果
                        </div>
                        <div class="approve_approved_container">
                            @if ($t_approval->ステータス == 4)
                            <div class="approve_approved_status">
                                承認
                            </div>
                            @elseif ($t_approval->ステータス == 5)
                            <div class="approve_reject_status">
                                却下
                            </div>
                            @endif
                            <div class="approve_approved_comment_container">
                                <div class="approve_approved_comment_title">
                                    承認者コメント:
                                </div>
                                <div class="approve_approved_comment">
                                    {{$t_approval->コメント}}
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            <div class="approve_condition_tab">
                <div class="approve_condition_container">
                    <div class="approve_condition_table">
                        <div class="approve_condition_thead_tr">
                            <div class="approve_condition_th approve_condition_name">
                                申請・承認者
                            </div>
                            <div class="approve_condition_th approve_condition_status">
                                結果
                            </div>
                            <div class="approve_condition_th approve_condition_date">
                                申請・承認日
                            </div>
                        </div>
                        @foreach ($past_approvals as $past_approval)
                        <div class="approve_condition_tbody_tr" data-front_point="{{$past_approval->フロントエンド表示ポイント}}" data-point_status="{{$past_approval->承認ステータス}}">
                            <div class="approve_condition_td approve_condition_name">
                                {{$past_approval->name}}
                            </div>
                            @if ($past_approval->ステータス == 0)
                            <div class="approve_condition_td approve_condition_status applicant_status" data-each_status="{{$past_approval->ステータス}}">
                                申請
                            </div>
                            @elseif ($past_approval->ステータス == 4)
                            <div class="approve_condition_td approve_condition_status approved_status" data-each_status="{{$past_approval->ステータス}}">
                                承認
                            </div>
                            @elseif ($past_approval->ステータス == 5)
                            <div class="approve_condition_td approve_condition_status reject_status" data-each_status="{{$past_approval->ステータス}}">
                                却下
                            </div>
                            @endif
                            <div class="approve_condition_td approve_condition_date">
                                {{\Carbon\Carbon::parse($past_approval->承認日)->toDateString()}}
                            </div>
                        </div>
                        @endforeach
                        <!-- <div class="approve_condition_tbody_tr" data-front_point="{{$t_approval->フロントエンド表示ポイント}}">
                            <div class="approve_condition_td approve_condition_name">
                                {{$t_approval->name}}
                            </div>
                            <div class="approve_condition_td approve_condition_status approve_wait_status">
                                承認待ち
                            </div>
                            <div class="approve_condition_td approve_condition_date">
                            </div>
                        </div> -->
                    </div>
                    <div class="approve_flow_status">
                        <div class="view_grid">

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <input type="hidden" value="{{$t_flow->id}}" id="t_flowid">
    <input type="hidden" value="{{$t_flow->フローマスタID}}" id="flowid">
    <div class="element_input">
        <input type="hidden" id="maxgrid_column">
        <input type="hidden" id="maxgrid_row">
    </div>

</div>
@endsection

@section('footer')
@endsection