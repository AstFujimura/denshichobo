@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection




@section('main')
<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/category.svg') }}" alt="" class="title_icon">カテゴリ設定</h2>
    <div class="category_setting_container">
        <div class="flow_application_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
        </div>
        <div class="category_setting_grid">

            @foreach($m_categories as $m_category)
            <div class="category_setting_content" data-category_id="{{$m_category->id}}">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/category/pen.svg') }}" alt="" class="category_pen_icon" title="名称変更">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/category/approval_setting.svg') }}" alt="" class="category_approval_setting_icon" title="承認設定">
                <div class="category_setting_name">
                    {{$m_category->カテゴリ名}}
                </div>
                <input type="hidden" name="{{$m_category->id}}" value="{{$m_category->カテゴリ名}}" class="category_setting_input">

            </div>
            @endforeach
            <div class="category_setting_content_add" data-category_id="">
                カテゴリを追加する
            </div>

        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection