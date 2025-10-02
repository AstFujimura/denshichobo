@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement" id="card_view_title">

    <!-- <h2 class="pagetitle" ><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">名刺管理トップ</h2> -->
    <input type="hidden" id="user_id" value="{{ Auth::user()->id }}">
    <div class="card_view_header">
        <div class="search_container">
            <div class="search_container_item">
                <input type="text" class="search_input" placeholder="ユーザーや会社名を検索">
                <button class="search_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/card/search.svg') }}" alt="">
                </button>
            </div>
            <div class="search_container_item"></div>
        </div>
        <div class="sub_search_container">
            <div class="sub_search_container_item">
                <label class="sub_search_container_item_label" for="start_date">登録年月日(開始)</label>
                <input type="text" placeholder="登録年月日(開始)" id="start_date" autocomplete="off">
            </div>
            <div class="sub_search_container_item">
                <label class="sub_search_container_item_label" for="end_date">登録年月日(終了)</label>
                <input type="text" placeholder="登録年月日(終了)" id="end_date" autocomplete="off">
            </div>
            <div class="sub_search_container_item">
                <label class="sub_search_container_item_label" for="sort_select">並び替え</label>
                <select name="sort_select" id="sort_select">
                    <option value="1">ユーザー名順</option>
                    <option value="2">会社名順</option>
                    <option value="3">登録日時</option>
                    <option value="4">更新日時</option>
                </select>
            </div>
            <div class="sub_search_container_item">
                <div class="item_view_container">
                    {{-- 表示大 --}}
                    <input type="radio" name="view_type" id="large_view" value="large_view" @if(Auth::user()->名刺表示サイズ) checked @endif>
                    <label for="large_view" class="large_view">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/large_view.svg') }}" alt="">
                        <span>表示大</span>
                    </label>
                    {{-- 表示小 --}}
                    <input type="radio" name="view_type" id="small_view" value="small_view" @if(!Auth::user()->名刺表示サイズ) checked @endif>
                    <label for="small_view" class="small_view">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/small_view.svg') }}" alt="">
                        <span>表示小</span>
                    </div>
                </div>

            </div>

        </div>
        <div class="card_view_header_count">
            <div class="card_view_header_count_text_container">
                <span class="card_view_header_count_text">
                    <span class="card_view_header_count_text_my">
                        {{ $myCount ?? 0 }}
                    </span>
                    <span class="card_view_header_count_text_total" style="display: none;">
                        {{ $totalCount ?? 0 }}
                    </span>
                </span>件
            </div>

            <div class="card_view_excel_button">
                Excel出力
            </div>
            <form id="card_view_excel_form" action="{{ route('cardviewexcelpost') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="card_view_card_array" id="card_view_card_array">
                <input type="hidden" name="search">
                <input type="hidden" name="start_date">
                <input type="hidden" name="end_date">
                <input type="hidden" name="sort">
            </form>
        </div>
        <div class="tab_container">
            <div class="tab_item tab_item_active" data-tab="my_card_user">
                マイ名刺
            </div>
            <div class="tab_item" data-tab="all_user">
                すべて
            </div>
            {{-- <div class="tab_item" data-tab="favorite_user">
                お気に入り
            </div> --}}
        </div>
    </div>

    <div class="card_view_container">
        <div class="card_view_card_header @if(Auth::user()->名刺表示サイズ) large_view @else small_view @endif">
            <div class="card_view_card_header_item" data-sort="name">
                名前
            </div>
            <div class="card_view_card_header_item" data-sort="company_name">
                会社名
            </div>
            <div class="card_view_card_header_item" data-sort="department_name">
                部署名
            </div>
            <div class="card_view_card_header_item" data-sort="created_at">
                登録年月日
            </div>
            <div class="card_view_card_header_item" data-sort="updated_at">
                更新年月日
            </div>
        </div>
        <div id="card-list">
            @include('card.partials.cardlist', ['cardusers' => $cardusers])
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection