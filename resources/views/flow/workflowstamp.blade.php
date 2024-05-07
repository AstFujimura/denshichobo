@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection
<div class="flow_application_gray">

</div>
<div class="flow_application_preview_container">

</div>



@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/stamp.svg') }}" alt="" class="title_icon">印鑑設定</h2>
    <form action="{{route('workflowstamppost')}}" id="stamp_regist" class="stamp_regist" method="post" enctype="multipart/form-data">
        @csrf
        <div class="flow_stamp_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
            <button class="stamp_regist_button" id="stamp_regist_button">
                登録
            </button>
        </div>
        <div class="flow_stamp_main_container">
            <!-- <div class="flow_stamp_left_container">

        </div> -->
            <div class="flow_stamp_middle_container">
                <div class="flow_stamp_letter_content">
                    <input type="text" class="flow_stamp_letter" id="flow_stamp_letter" placeholder="(例) 佐藤">
                    <div class="flow_stamp_lettter_change_button">
                        変更
                    </div>
                </div>
                <div class="flow_stamp_preview">
                </div>
            </div>
            <div class="flow_stamp_right_container">
                <div class="flow_stamp_font_container">
                    <div class="flow_stamp_right_subtitle">
                        フォント
                    </div>

                    <div class="flow_stamp_font_property">
                        <select class="flow_stamp_font_select" name="font" id="flow_stamp_font_select">
                            <option value="毛筆体">毛筆体</option>
                            <option value="HG正楷書体">楷書体</option>
                            <option value="HGゴシック体">ゴシック</option>
                            <option value="HGR明朝体">明朝</option>
                        </select>
                    </div>
                    <div class="flow_font_size_title">
                        フォントサイズ
                    </div>
                    <input type="range" name="font_size" min="0" max="400" value="245" step="1" class="stamp_slider font_size_slider">
                    <div class="flow_font_size_title">
                        縦横比
                    </div>
                    <input type="range" min="-1" max="0.8" value="0.2" step="0.01" class="stamp_slider scale_slider">
                    <input type="hidden" name="aspect" id="aspect" value="">
                </div>
                <div class="letter_container">
                    <input type="hidden" id="letter_length" value="0" name="letter_length">
                    <!-- <div class="flow_stamp_str_container">
                    <div class="flow_stamp_right_subtitle" data-str_num="0">
                        藤
                    </div>
                    <div class="flow_font_size_title">
                        X方向
                    </div>
                    <input type="range" min="0" max="100" value="75" step="1" class="font_size_slider">
                    <div class="flow_font_size_title">
                        Y方向
                    </div>
                    <input type="range" min="0" max="100" step="1" class="font_size_slider">
                </div> -->
                </div>
            </div>
        </div>
        <input type="hidden" id="stamp_img" name="stamp_img" value="aa">
        <input type="hidden" id="毛筆体" value="{{ asset(config('prefix.prefix').'/'.'font/毛筆体.ttf') }}">
        <input type="hidden" id="HG正楷書体" value="{{ asset(config('prefix.prefix').'/'.'font/HG正楷書体.TTF') }}">
        <input type="hidden" id="HGゴシック体" value="{{ asset(config('prefix.prefix').'/'.'font/HGゴシック体.TTC') }}">
        <input type="hidden" id="HGR明朝体" value="{{ asset(config('prefix.prefix').'/'.'font/HGR明朝体.TTC') }}">

    </form>
</div>
@endsection

@section('footer')
@endsection