@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/setting.svg') }}" alt="" class="title_icon">カテゴリ設定</h2>
    <div class="category_setting_container">
        @foreach($m_categories as $m_category)
        <div class="category_setting_content" data-category_id="{{$m_category->id}}">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/category/pen.svg') }}" alt="" class="category_pen_icon" title="名称変更">
            <div class="category_setting_name">
                {{$m_category->カテゴリ名}}
            </div>
            <input type="hidden" name="{{$m_category->id}}" value="{{$m_category->カテゴリ名}}" class="category_setting_input">

        </div>
        @endforeach
    </div>
</div>
@endsection

@section('footer')
@endsection