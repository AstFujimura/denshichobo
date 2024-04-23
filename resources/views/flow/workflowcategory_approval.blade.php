@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')

<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/setting.svg') }}" alt="" class="title_icon">承認設定</h2>
    <div class="category_setting_container">
        <div class="flow_application_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
        </div>
        <div class="approval_setting_container">
            <div class="approval_setting_content">
                <input type="checkbox" id="approval_setting_issue">
                <label class="approval_setting_element" for="approval_setting_issue">承認用紙を発行する</label>
            </div>
            <div class="approval_setting_droparea display_none">
                <p>ここにドラッグ＆ドロップ</p>
                <input type="file" name="" id="approval_setting_file" class="file_input" accept=".pdf">
            </div>
            <div class="approcal_setting_detail_button display_none">
                詳細設定
            </div>

        </div>
    </div>
    <div class="approval_setting_detail_container display_none">
        <div class="preview_pages_container"></div>
        <div class="preview_main_container"></div>
        <div class="preview_control_container">
            <div class="preview_control_items">
                @foreach ($categories as $category)
                <div class="preview_control_item">
                    <span class="preview_control_plus">＋</span>
                    <span>{{$category["項目名"]}}</span>
                </div>
                @endforeach
            </div>
            <div class="preview_control_button_container">
                <div class="preview_control_close_button">
                    閉じる
                </div>
            </div>
        </div>

    </div>
    @endsection

    @section('footer')
    @endsection