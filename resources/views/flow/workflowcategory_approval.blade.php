@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')

<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle" id="approval_setting"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/setting.svg') }}" alt="" class="title_icon">承認設定</h2>
    <form action="{{route('categoryapprovalsettingpost')}}" method="post" id="category_approval_setting_form" enctype="multipart/form-data">
        @csrf

        <div class="category_setting_container">
            <div class="flow_application_button_content">
                <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                    トップへもどる
                </a>
                <button>送信</button>
            </div>
            <div class="approval_setting_container">
                <div class="approval_setting_content">
                    <input type="checkbox" id="approval_setting_issue" {{$m_category->発行 ? 'checked' : ''}}>
                    <label class="approval_setting_element" for="approval_setting_issue">承認用紙を発行する</label>
                    <!-- <div class="approval_setting_registed">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/button/check.svg') }}" alt="" class="button_icon">
                        インポート済み
                    </div> -->

                </div>
                <label class="approval_setting_change_file {{$m_category->発行 ? '' : 'display_none'}}" for="approval_setting_file">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/change.svg') }}" alt="" class="button_icon">
                    ファイルの変更
                </label>

                <label class="approval_setting_droparea display_none">
                    <p>ここにドラッグ＆ドロップ</p>
                    <input type="file" name="pdf" id="approval_setting_file" class="file_input" accept=".pdf">
                </label>
                <div class="approval_setting_detail_button {{$m_category->発行 ? '' : 'display_none'}}">
                    詳細設定
                </div>

            </div>
        </div>
        <div class="approval_setting_detail_container display_none">
            <div class="preview_property_container">
                <div class="font_size_content">
                    <div class="property_title">
                        フォントサイズ
                    </div>
                    <input type="number" id="font_size_input" class="font_size_input">
                    px
                </div>

            </div>
            <div class="preview_pages_container"></div>
            <div class="preview_main_container">

            </div>
            <div class="preview_control_container">
                <div class="preview_control_items">
                    @foreach ($m_optionals as $m_optional)
                    <div class="preview_control_item" data-optional_id="{{$m_optional['id']}}">
                        <span class="preview_control_plus">＋</span>
                        <span class="preview_control_item_title">{{$m_optional["項目名"]}}</span>
                    </div>
                    @foreach ($m_optional["pointers"] as $pointer)
                    <div class="preview_test_str" data-pointer_id="{{$pointer->id}}">
                        <input type="text" class="preview_test_str_input" value='{{$m_optional["項目名"]}}'>
                        <div class="preview_item_batsu">×</div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
                <div class="stamp_check_container">
                    <div class="stamp_check_content">

                        <input type="checkbox" name="approval_stamp" id="approval_stamp" {{$m_category->承認印 ? 'checked' : ''}}>
                        <label class="approval_setting_element" for="approval_stamp">承認印を必須にする。</label>
                    </div>
                    <div class="stamp_check_content">

                        <input type="checkbox" name="application_stamp" id="application_stamp" {{$m_category->申請印 ? 'checked' : ''}}>
                        <label class="approval_setting_element" for="application_stamp">申請印を必須にする。</label>
                    </div>
                </div>
                <div class="preview_control_button_container">
                    <div class="preview_control_close_button">
                        閉じる
                    </div>
                </div>
            </div>

        </div>
        <div id="inputs">
            <input type="hidden" id="pointer_num" value="10000">
            <input type="hidden" id="category_id" name="category_id" value="{{$id}}">
            <input type="hidden" id="width" name="width" value="">
            <input type="hidden" id="height" name="height" value="">
            <input type="hidden" id="p_l" name="p_l" value="">
            <input type="hidden" id="status" name="status" value="{{$m_category->status}}">

            @foreach ($m_optionals as $m_optional)

            @foreach ($m_optional["pointers"] as $pointer)
            <input type="hidden" name="pointer_array[]" value="{{$pointer->id}}" data-pointer_id="{{$pointer->id}}">
            <input type="hidden" name="optional_id{{$pointer->id}}" value="{{$m_optional['id']}}" data-prop="optional_id" data-pointer_id="{{$pointer->id}}">
            <input type="hidden" name="top{{$pointer->id}}" value="{{$pointer->top}}" data-prop="top" data-pointer_id="{{$pointer->id}}">
            <input type="hidden" name="left{{$pointer->id}}" value="{{$pointer->left}}" data-prop="left" data-pointer_id="{{$pointer->id}}">
            <input type="hidden" name="font_size{{$pointer->id}}" value="{{$pointer->フォントサイズ}}" data-prop="font_size" data-pointer_id="{{$pointer->id}}">
            <input type="hidden" name="font{{$pointer->id}}" value="{{$pointer->フォント}}" data-prop="font" data-pointer_id="{{$pointer->id}}">
            <input type="hidden" name="page{{$pointer->id}}" value="{{$pointer->ページ}}" data-prop="page" data-pointer_id="{{$pointer->id}}">
            @endforeach

            @endforeach
        </div>

    </form>
    @endsection

    @section('footer')
    @endsection