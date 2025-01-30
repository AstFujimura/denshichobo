@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="card_view_title"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">名刺管理トップ</h2>
    <div class="card_view_container">
        @foreach ($cardusers as $carduser)
        <a href="{{ route('carddetailget', ['id' => $carduser->carduser_id]) }}" class="card_view_card">
            <div class="text_container">
                <div class="card_view_card_name">
                    {{ $carduser->表示名 }}
                </div>
                <div class="company_info">
                    <div class="card_view_card_company">
                        {{ $carduser->現会社名 }}
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