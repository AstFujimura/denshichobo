@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">
    <div class="card_detail_container">
        <div class="card_history_container">
            @foreach ($cards as $card)
            <div class="card_history_content">
                <div class="history_card">
                    <img class="lazyload" data-card_id="{{ $card->card_id }}" data-front="true" alt="">
                </div>
                <div class="history_card_text">
                    <div class="card_detail_text_name">
                        {{ $card->名前 }}
                    </div>
                    <div class="history_card_text_company">
                        {{ $card->現会社名 }}
                    </div>
                    <div class="history_card_text_department">
                        @foreach ($card->departments as $department)
                        {{ $department->部署名 }}
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
            <div class="card_history_add_button">
                名刺を追加する
            </div>
        </div>
        <div class="card_info_container">
            <h2 class="pagetitle" id="card_view_title"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">{{ $carduser->表示名 }} さん</h2>
            <div class="card_info_content">
                <div class="card_detail_card">
                    <img class="lazyload" data-card_id="{{ $now_card->card_id }}" data-front="true" alt="">
                </div>
                <div class="card_detail_card">
                    <img class="lazyload" data-card_id="{{ $now_card->card_id }}" data-front="false" alt="">
                </div>
            </div>
            <div class="card_info_detail_content">
                <div class="personal_info">
                    <div class="personal_info_content">
                        <div class="personal_info_content_title">
                            名前
                        </div>
                        <div class="personal_info_content_text">
                            {{ $now_card->名前 }}
                        </div>
                    </div>

                    <div class="personal_info_content">
                        <div class="personal_info_content_title">
                            名前カナ
                        </div>
                        <div class="personal_info_content_text">
                            {{ $now_card->名前カナ }}
                        </div>
                    </div>
                </div>
                <div class="company_info">
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            会社名
                        </div>
                        <div class="company_info_content_text">
                            {{ $now_card->現会社名 }}
                        </div>
                    </div>
                    @foreach ($now_card->departments as $index => $department)
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            部署名{{ $index + 1 }}
                        </div>
                        <div class="company_info_content_text">
                            {{ $department->部署名 }}
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="card_edit_button" data-card_id="{{ $now_card->id }}">
                この名刺を編集する
            </div>
        </div>

    </div>
</div>
@endsection

@section('footer')
@endsection