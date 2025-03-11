@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection



<div class="approve_gray"></div>
<div class="approve_preview_container"></div>
@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="approve_phase"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application_view.svg') }}" alt="" class="title_icon">申請内容</h2>
    <div class="flow_view_container">
        <div class="flow_application_button_content">
            <a href="{{route('workflowviewget')}}" class="back_button " id="flow_next_button">
                申請一覧へもどる
            </a>
        </div>
        <div class="flow_view_container_inner">
            <div class="tab_container">
                <div class="flow_tab tab_focus" data-tabname="approve_tab">
                    申請内容
                </div>
                <div class="flow_tab" data-tabname="approve_condition_tab">
                    承認状況
                </div>
            </div>
            <div class="flow_view_content">
                <div class="approve_tab open_tab">
                    <div class="approve_container">
                        <div class="approve_authorizer_container">
                            <div class="approval_sub_title">
                                申請ステータス
                            </div>
                            @if ($t_flow->ステータス == 1)
                            <div class="application_status_container">

                                <div class="application_status_ongoing">
                                    進行中
                                </div>
                            </div>
                            <div class="approval_sub_title">
                                申請取り消し操作
                            </div>
                            <div class="application_cancel_container">

                                <div class="application_cancel_button" id="application_cancel_button" data-id="{{$t_flow->id}}">
                                    申請取り消し
                                </div>
                            </div>

                            @elseif ($t_flow->ステータス == 2)
                            <div class="application_status_container">

                                <div class="application_status_rejected">
                                    却下
                                </div>
                            </div>
                            @elseif ($t_flow->ステータス == 3)
                            <div class="application_status_container">

                                <div class="application_status_approved">
                                    決裁済
                                </div>
                            </div>
                            <!-- <div class="approval_sub_title">
                            TAMERU
                        </div>
                        <div class="tameru_container">
                            <div class="tameru_content">
                                <span class="tameru_status_title">現在のステータス:</span> <span class="tameru_status unsaved">未保存</span>
                            </div>
                            <div class="tameru_button">
                                TAMERUに保存
                            </div>
                        </div> -->
                            @elseif ($t_flow->ステータス == 4)
                            <div class="application_status_container">

                                <div class="application_status_approved">
                                    決裁済
                                </div>
                            </div>
                            <!-- <div class="approval_sub_title">
                            TAMERU
                        </div>
                        <div class="tameru_container">
                            <div class="tameru_content">
                                <span class="tameru_status_title">現在のステータス:</span> <span class="tameru_status saved">保存済</span>
                            </div>
                        </div> -->
                            @endif
                            <!-- 再申請待ちの場合 -->
                            @if ($t_flow->ステータス == 5)

                            <div class="application_status_container">

                                <div class="application_status_reapply">
                                    再申請待ち
                                </div>
                            </div>
                            <div class="approval_sub_title">
                                申請操作
                            </div>
                            <div class="application_status_container">
                                <a href="{{route('workflowreapplyget', ['id' => $t_flow->id])}}" class="application_reapply_button">
                                    再申請を行う
                                </a>
                            </div>
                            @endif
                        </div>
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
                                    <div class="approve_preview_button" data-id="{{$t_optional->id}}" data-type="t_optional">プレビュー</div>
                                    <img src="{{ asset(config('prefix.prefix').'/'.'img/download_2_line.svg') }}" class="approve_download" data-url="{{$prefix}}/workflow/download/{{$t_optional->id}}?type=t_optional&timestamp={{time()}}">
                                    @elseif ($t_optional->値 == "file_none_246851")
                                    @else
                                    {{$t_optional->値}}
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @if ($m_category->発行)
                            <div class="approval_sub_title">
                                承認用紙
                            </div>
                            <div class="approve_content">
                                <div class="approve_content_title">
                                    承認用紙
                                </div>
                                <div class="approve_content_element">
                                    <div class="approve_preview_button" data-id="{{$t_flow->id}}" data-type="t_flow_before">プレビュー</div>
                                    <img src="{{ asset(config('prefix.prefix').'/'.'img/download_2_line.svg') }}" class="approve_download" data-url="{{$prefix}}/workflow/download/{{$t_flow->id}}?type=t_flow_before&timestamp={{time()}}">
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
                                <div class="approve_condition_th approve_condition_comment">
                                    コメント
                                </div>
                            </div>
                            @foreach ($past_approvals as $past_approval)
                            <div class="approve_condition_tbody_tr @if ($past_approval->ステータス == 6) bold_underline @endif" data-front_point="{{$past_approval->フロントエンド表示ポイント}}" data-point_status="{{$past_approval->承認ステータス}}">
                                <div class="approve_condition_td approve_condition_name">
                                    {{$past_approval->name}}
                                </div>
                                @if ($past_approval->ステータス == 0)
                                <div class="approve_condition_td approve_condition_status applicant_status" data-each_status="{{$past_approval->ステータス}}" data-reapproval_number="{{$past_approval->再承認番号}}">
                                    申請
                                </div>
                                @elseif ($past_approval->ステータス == 4)
                                <div class="approve_condition_td approve_condition_status approved_status" data-each_status="{{$past_approval->ステータス}}" data-reapproval_number="{{$past_approval->再承認番号}}">
                                    承認
                                </div>
                                @elseif ($past_approval->ステータス == 5)
                                <div class="approve_condition_td approve_condition_status reject_status" data-each_status="{{$past_approval->ステータス}}" data-reapproval_number="{{$past_approval->再承認番号}}">
                                    却下
                                </div>
                                @elseif ($past_approval->ステータス == 6)
                                <div class="approve_condition_td approve_condition_status remand_status" data-each_status="{{$past_approval->ステータス}}" data-reapproval_number="{{$past_approval->再承認番号}}">
                                    差し戻し
                                </div>
                                @elseif ($past_approval->ステータス == 8)
                                <div class="approve_condition_td approve_condition_status reapply_status" data-each_status="{{$past_approval->ステータス}}" data-reapproval_number="{{$past_approval->再承認番号}}">
                                    再申請
                                </div>
                                @endif
                                <div class="approve_condition_td approve_condition_date">
                                    {{\Carbon\Carbon::parse($past_approval->承認日)->toDateString()}}
                                </div>
                                <div class="approve_condition_td approve_condition_comment">
                                    {{$past_approval->コメント}}
                                </div>
                            </div>
                            @endforeach

                        </div>
                        <div class="approve_flow_status">
                            <div class="view_grid">

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- <input type="hidden" value="{{$t_flow->id}}" id="t_flowid"> -->
    <input type="hidden" value="{{$t_flow->フローマスタID}}" id="m_flow_id">
    <div class="element_input">
        <input type="hidden" id="maxgrid_column">
        <input type="hidden" id="maxgrid_row">
    </div>

</div>
@endsection

@section('footer')
@endsection