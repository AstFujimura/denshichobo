@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection




@section('main')

<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">
    <div class="stamp_gray">
        <div class="stamp_none_message">
            印鑑登録が済んでいません。下記から印鑑登録をしてください
            <a href="{{route('workflowstampget',['t_approval' => $t_approval->id])}}" class="regist_stamp_button">
                印鑑登録
            </a>
        </div>
    </div>
    <h2 class="pagetitle" id="approvestamp">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/approve.svg') }}" alt="" class="title_icon">
        承認印
    </h2>
    <form action="{{route('workflowapprovalstamppost')}}" method="post" id="category_approval_setting_form" enctype="multipart/form-data">
        @csrf


        <div class="application_stamp_container">
            <div class="preview_pages_container">

            </div>
            <div class="preview_main_container">

            </div>
            <div class="preview_control_container">
                <div class="preview_control_items">
                    <div class="application_stamp_button_container">
                        <div class="application_stamp_button_content">
                            <a href="{{route('workflowapprovalget', ['id' => $t_approval->id])}}" class="back_button application_stamp_back_button" id="flow_next_button">
                                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                                もどる
                            </a>
                            <button class="next_button application_stamp_submit_button" id="flow_next_button">
                                次へ
                            </button>
                            <a href="{{route('workflowstampget',['t_approval' => $t_approval->id])}}" class="change_stamp_button">
                                印鑑を変更する
                            </a>
                        </div>

                    </div>
                </div>

            </div>

        </div>
        <div id="inputs">
            <input type="hidden" id="stamp_img" name="stamp_img" value="{{$m_stamp ?? 'none'}}">
            <input type="hidden" id="comment" name="comment" value="{{$comment}}">
            <input type="hidden" id="category_id" name="category_id" value="{{$category_id}}">
            <input type="hidden" id="t_approval" name="t_approval" value="{{$t_approval->id}}">
            <input type="hidden" id="user_id" value="{{$t_approval->ユーザーID}}">
            <input type="hidden" id="t_flow_id" name="t_flow_id" value="{{$t_approval->フローテーブルID}}">
            <input type="hidden" id="top" name="top" value="">
            <input type="hidden" id="left" name="left" value="">
            <input type="hidden" id="width" name="width" value="">
            <input type="hidden" id="height" name="height" value="">
            <input type="hidden" id="scale" name="scale" value="">

            <!-- <img src="" alt=""> -->
        </div>
    </form>
    @endsection

    @section('footer')
    @endsection