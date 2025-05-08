@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement" id="card_view_title">

    <!-- <h2 class="pagetitle" ><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">名刺管理トップ</h2> -->
    <div class="card_view_header">
        <div class="search_container">
            <input type="text" class="search_input" placeholder="ユーザーや会社名を検索">
            <button class="search_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/search.svg') }}" alt="">
            </button>
        </div>
        <div class="tab_container">
            <div class="tab_item tab_item_active" data-tab="my_card_user">
                マイ名刺ユーザー
            </div>
            <div class="tab_item" data-tab="all_user">
                すべてのユーザー
            </div>
            <div class="tab_item" data-tab="favorite_user">
                お気に入りユーザー
            </div>
        </div>
    </div>

    <div class="card_view_container">
        @foreach ($cardusers as $carduser)
        <a href="{{ route('carddetailget', ['id' => $carduser->carduser_id]) }}" class="card_view_card">
            <div class="text_container">
                <div class="card_view_card_name">
                    {{ $carduser->表示名 }}
                </div>
                <div class="company_info">
                    <div class="card_view_card_company">
                        {{ $carduser->会社名 }}
                    </div>
                    <div class="card_view_card_department">
                        @foreach ($carduser->departments as $department)
                        <div class="card_view_card_department_item">{{ $department->部署名 }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
            <img class="lazyload" data-card_id="{{ $carduser->card_id }}" data-front="true" alt="">
        </a>
        @endforeach
    </div>
</div>
@endsection

@section('footer')
@endsection