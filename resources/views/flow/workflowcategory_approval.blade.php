@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection




@section('main')

<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle" id="approval_setting"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/category.svg') }}" alt="" class="title_icon">承認設定</h2>
    <form action="{{route('categoryapprovalsettingpost')}}" method="post" id="category_approval_setting_form" enctype="multipart/form-data">
        @csrf

        <div class="category_setting_container">
            <div class="flow_application_button_content">
                <a href="{{route('categoryget')}}" class="back_button " id="">
                    カテゴリ一覧へもどる
                </a>
                <button class="approval_setting_button">送信</button>
                <a href="javascript:void(0);" onclick="window.location.reload();" class="cancel_button " id="flow_next_button">
                    キャンセル
                </a>
            </div>
            <div class="approval_setting_container">
                <div class="approval_setting_content">
                    <input type="radio" id="approval_none_setting" name="approval_setting" value="0" {{$m_category->発行 == 0 ? 'checked' : ''}}>
                    <label class="approval_setting_element" for="approval_none_setting">承認用紙を発行しない</label>
                </div>
            </div>
            <div class="approval_setting_container">
                <div class="approval_setting_content">
                    <input type="radio" id="approval_event_setting" name="approval_setting" value="2" {{$m_category->発行 == 2 ? 'checked' : ''}}>
                    <label class="approval_setting_element" for="approval_event_setting">申請のたびに承認用紙を発行する</label>
                </div>
            </div>
            <div class="approval_setting_container">
                <div class="approval_setting_content">
                    <input type="radio" id="approval_setting_issue" name="approval_setting" value="1" {{$m_category->発行 == 1 ? 'checked' : ''}}>
                    <label class="approval_setting_element" for="approval_setting_issue">承認用紙を登録しておく</label>
                </div>
                <label class="approval_setting_change_file {{$m_category->発行 == 1 ? '' : 'display_none'}}" for="approval_setting_file">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/change.svg') }}" alt="" class="button_icon">
                    ファイルの変更
                </label>

                <label class="approval_setting_droparea display_none">
                    <p>ここにドラッグ＆ドロップ</p>
                    <input type="file" name="pdf" id="approval_setting_file" class="file_input" accept=".pdf">
                </label>
                <div class="approval_setting_detail_button {{$m_category->発行 == 1 ? '' : 'display_none'}}">
                    詳細設定
                </div>

            </div>
            <div class="stamp_check_container  {{$m_category->発行 == 1 || $m_category->発行 == 2 ? '' : 'display_none'}}">
                <div class="stamp_check_content">

                    <input type="checkbox" name="approval_stamp" id="approval_stamp" {{$m_category->承認印 ? 'checked' : ''}}>
                    <label class="approval_setting_element" for="approval_stamp">承認印を必須にする。</label>
                </div>
                <div class="stamp_check_content">

                    <input type="checkbox" name="application_stamp" id="application_stamp" {{$m_category->申請印 ? 'checked' : ''}}>
                    <label class="approval_setting_element" for="application_stamp">申請印を必須にする。</label>
                </div>
            </div>
        </div>
        <div class="approval_setting_detail_container display_none">
            <div class="preview_control_button_container">
                <div class="preview_control_close_button">
                    閉じる
                </div>
            </div>
            <div class="preview_property_container">
                <div class="font_size_content">
                    <div class="property_title">
                        フォントサイズ
                    </div>
                    <input type="number" id="font_size_input" class="font_size_input">
                    pt
                </div>

            </div>
            <div class="preview_pages_container"></div>
            <div class="preview_main_container">

            </div>
            <div class="preview_control_container">
                <div class="preview_control_items basic_items">
                    <div class="preview_control_item_content">
                        <div class="preview_control_item" data-basic_info="1">
                            <span class="preview_control_plus">＋</span>
                            <span class="preview_control_item_title">ユーザー名</span>
                        </div>
                        @foreach ($basic_users as $basic_user)
                        <div class="preview_test_str" data-pointer_id="{{$basic_user->id}}">
                            <input type="text" class="preview_test_str_input" value='{{Auth::user()->name}}'>
                            <div class="preview_item_batsu">×</div>
                        </div>
                        @endforeach
                    </div>
                    <div class="preview_control_item_content">
                        <div class="preview_control_item" data-basic_info="2">
                            <span class="preview_control_plus">＋</span>
                            <span class="preview_control_item_title">申請日</span>
                        </div>
                        @foreach ($basic_dates as $basic_date)
                        <div class="preview_test_str" data-pointer_id="{{$basic_date->id}}">
                            <input type="text" class="preview_test_str_input" value='{{Carbon\Carbon::now()->format('Y年m月d日')}}'>
                            <div class="preview_item_batsu">×</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="preview_control_items">
                    @foreach ($m_optionals as $m_optional)
                    <div class="preview_control_item_content">
                        <div class="preview_control_item" data-optional_id="{{$m_optional['id']}}" data-basic_info="0" data-type="{{$m_optional['型']}}">
                            <span class="preview_control_plus">＋</span>
                            <span class="preview_control_item_title">{{$m_optional["項目名"]}}</span>
                        </div>
                        @foreach ($m_optional["pointers"] as $pointer)
                        <div class="preview_test_str" data-pointer_id="{{$pointer->id}}">
                            <input type="text" class="preview_test_str_input" value='{{$m_optional["項目名"]}}'>
                            <div class="preview_item_batsu">×</div>
                            @if ($m_optional["型"] == 2)
                            <label for="preview_test_str_comma{{$pointer->id}}" class="preview_test_str_comma">
                                桁区切り
                                <input type="checkbox" name="comma{{$pointer->id}}" id="preview_test_str_comma{{$pointer->id}}" class="preview_test_str_comma_checkbox" {{$pointer->桁区切り ? 'checked' : ''}}>
                            </label>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>


            </div>

        </div>
        <div id="inputs">
            <input type="hidden" id="user_name" value="{{Auth::user()->name}}">
            <input type="hidden" id="date" value="{{Carbon\Carbon::now()->format('Y/m/d')}}">
            <input type="hidden" id="pointer_num" value="100000">
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
            @foreach ($basic_users as $basic_user)
            <input type="hidden" name="pointer_array[]" value="{{$basic_user->id}}" data-pointer_id="{{$basic_user->id}}">
            <input type="hidden" name="basic_info{{$basic_user->id}}" value="{{$basic_user->基本情報}}" data-prop="basic_info" data-pointer_id="{{$basic_user->id}}">
            <input type="hidden" name="top{{$basic_user->id}}" value="{{$basic_user->top}}" data-prop="top" data-pointer_id="{{$basic_user->id}}">
            <input type="hidden" name="left{{$basic_user->id}}" value="{{$basic_user->left}}" data-prop="left" data-pointer_id="{{$basic_user->id}}">
            <input type="hidden" name="font_size{{$basic_user->id}}" value="{{$basic_user->フォントサイズ}}" data-prop="font_size" data-pointer_id="{{$basic_user->id}}">
            <input type="hidden" name="font{{$basic_user->id}}" value="{{$basic_user->フォント}}" data-prop="font" data-pointer_id="{{$basic_user->id}}">
            <input type="hidden" name="page{{$basic_user->id}}" value="{{$basic_user->ページ}}" data-prop="page" data-pointer_id="{{$basic_user->id}}">
            @endforeach
            @foreach ($basic_dates as $basic_date)
            <input type="hidden" name="pointer_array[]" value="{{$basic_date->id}}" data-pointer_id="{{$basic_date->id}}">
            <input type="hidden" name="basic_info{{$basic_date->id}}" value="{{$basic_date->基本情報}}" data-prop="basic_info" data-pointer_id="{{$basic_date->id}}">
            <input type="hidden" name="top{{$basic_date->id}}" value="{{$basic_date->top}}" data-prop="top" data-pointer_id="{{$basic_date->id}}">
            <input type="hidden" name="left{{$basic_date->id}}" value="{{$basic_date->left}}" data-prop="left" data-pointer_id="{{$basic_date->id}}">
            <input type="hidden" name="font_size{{$basic_date->id}}" value="{{$basic_date->フォントサイズ}}" data-prop="font_size" data-pointer_id="{{$basic_date->id}}">
            <input type="hidden" name="font{{$basic_date->id}}" value="{{$basic_date->フォント}}" data-prop="font" data-pointer_id="{{$basic_date->id}}">
            <input type="hidden" name="page{{$basic_date->id}}" value="{{$basic_date->ページ}}" data-prop="page" data-pointer_id="{{$basic_date->id}}">
            @endforeach
        </div>

    </form>
    @endsection

    @section('footer')
    @endsection