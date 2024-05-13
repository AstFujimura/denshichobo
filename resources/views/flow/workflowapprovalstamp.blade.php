@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')

<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle" id="approvestamp">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/approve.svg') }}" alt="" class="title_icon">
        承認印
    </h2>
    <form action="{{route('workflowapplicationstamppost')}}" method="post" id="category_approval_setting_form" enctype="multipart/form-data">
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
                            <a href="{{route('workflow')}}" class="back_button application_stamp_back_button" id="flow_next_button">
                                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                                もどる
                            </a>
                            <button class="next_button application_stamp_submit_button" id="flow_next_button">
                                次へ
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>
        <div id="inputs">
            <input type="hidden" id="category_id" name="category_id" value="{{$category_id}}">
            <input type="hidden" id="user_id" value="{{$t_approval->ユーザーID}}">
            <input type="hidden" id="t_flow_id" name="t_flow_id" value="{{$t_approval->フローテーブルID}}">
            <input type="hidden" id="top" name="top" value="">
            <input type="hidden" id="left" name="left" value="">

            <!-- <img src="" alt=""> -->
        </div>
    </form>
    @endsection

    @section('footer')
    @endsection