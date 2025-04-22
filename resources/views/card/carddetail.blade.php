@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">
    <div class="card_detail_container">
        <div class="gray_area">
            <div class="popup_content">
                <a href="{{ route('cardeditget', ['id' => $now_card->card_id]) }}" class="card_edit_button" data-card_id="{{ $now_card->card_id }}">
                    この名刺を編集する
                </a>
                <a class="card_delete_button" data-card_id="{{ $now_card->card_id }}">
                    @csrf
                    この名刺を削除する
                </a>
            </div>
        </div>
        <div class="card_history_container">
            @foreach ($cards as $card)
            <div class="card_history_content">
                <input type="radio" name="card_history" id="card_history_{{ $card->card_id }}" value="{{ $card->card_id }}" @if ($card->最新フラグ == 1) checked @endif>
                <label for="card_history_{{ $card->card_id }}" class="card_history_label">
                    <div class="history_card">
                        @if ($card->最新フラグ == 1)
                        <img class="new_card_check" src="{{asset(config('prefix.prefix').'/'.'img/card/new_card_check.svg')}}" alt="">
                        @endif
                        <img class="lazyload" data-card_id="{{ $card->card_id }}" data-front="true" alt="">
                    </div>
                    <div class="history_card_text">
                        <div class="card_detail_text_name">
                            {{ $card->名前 }}
                        </div>
                        <div class="history_card_text_company">
                            {{ $card->会社名 }}
                        </div>
                        <div class="history_card_text_department">
                            @foreach ($card->departments as $department)
                            {{ $department->部署名 }}
                            @endforeach
                        </div>
                    </div>
                </label>
            </div>
            @endforeach
            <div class="card_history_close_button">
                閉じる
            </div>
            <a href="{{ route('cardaddget', ['id' => $carduser->id]) }}" class="card_history_add_button">
                名刺を追加する
            </a>
        </div>
        <div class="card_info_container">
            <h2 class="pagetitle" id="card_detail_title"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">{{ $carduser->表示名 }} さん</h2>
            <div class="card_detail_content">
                <div class="card_info_content">
                    <div class="setting_container">
                        <img class="card_history_button" src="{{ asset(config('prefix.prefix').'/'.'img/card/history.svg')}}" alt="">
                        <img class="card_setting_button" src="{{ asset(config('prefix.prefix').'/'.'img/card/setting.svg')}}" alt="">
                    </div>
                    <div class="card_detail_card">
                        <img class="lazyload" data-card_id="{{ $now_card->card_id }}" data-front="true" alt="">
                    </div>
                </div>
                <div class="personal_info">
                    <div class="personal_info_content">
                        <div class="personal_info_content_title">
                            名前
                        </div>
                        <div class="personal_info_content_text" id="name">
                            {{ $now_card->名前 }}
                        </div>
                    </div>

                    <div class="personal_info_content">
                        <div class="personal_info_content_title">
                            名前カナ
                        </div>
                        <div class="personal_info_content_text" id="name_kana">
                            {{ $now_card->名前カナ }}
                        </div>
                    </div>
                    <div class="personal_info_content">
                        <div class="personal_info_content_title">
                            携帯電話
                        </div>
                        <div class="personal_info_content_text" id="phone_number">
                            {{ $now_card->携帯電話番号 }}
                        </div>
                    </div>
                    <div class="personal_info_content">
                        <div class="personal_info_content_title">
                            メールアドレス
                        </div>
                        <div class="personal_info_content_text" id="email">
                            {{ $now_card->メールアドレス }}
                        </div>
                    </div>
                </div>
                <div class="company_info">
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            会社名
                        </div>
                        <div class="company_info_content_text" id="company_name">
                            {{ $now_card->会社名 }}
                        </div>
                    </div>
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            会社名カナ
                        </div>
                        <div class="company_info_content_text" id="company_name_kana">
                            {{ $now_card->会社名カナ }}
                        </div>
                    </div>
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            拠点名
                        </div>
                        <div class="company_info_content_text" id="branch_name">
                            {{ $now_card->拠点名 }}
                        </div>
                    </div>
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            所在地
                        </div>
                        <div class="company_info_content_text" id="company_address">
                            {{ $now_card->拠点所在地 }}
                        </div>
                    </div>
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            電話番号
                        </div>
                        <div class="company_info_content_text" id="company_phone_number">
                            {{ $now_card->電話番号 }}
                        </div>
                    </div>
                    <div class="company_info_content">
                        <div class="company_info_content_title">
                            FAX番号
                        </div>
                        <div class="company_info_content_text" id="company_fax_number">
                            {{ $now_card->FAX番号 }}
                        </div>
                    </div>
                </div>
                <div class="position_info">
                    <div class="position_info_content">
                        <div class="position_info_content_title">
                            役職
                        </div>
                        <div class="position_info_content_text" id="position">
                            {{ $now_card->役職 }}
                        </div>
                    </div>
                    @foreach ($now_card->departments as $index => $department)
                    <div class="position_info_content department_content">
                        <div class="position_info_content_title">
                            部署{{ $index + 1 }}
                        </div>
                        <div class="position_info_content_text" id="department{{$index+1}}">
                            {{ $department->部署名 }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>


            <div class="card_edit_delete_container">
                <a href="{{ route('cardeditget', ['id' => $now_card->card_id]) }}" id="card_edit_button" class="card_edit_button" data-card_id="{{ $now_card->id }}">
                    この名刺を編集する
                </a>
                <a class="card_delete_button" data-card_id="{{ $now_card->card_id }}">
                    @csrf
                    この名刺を削除する
                </a>
            </div>

        </div>

    </div>
</div>
@endsection

@section('footer')
@endsection