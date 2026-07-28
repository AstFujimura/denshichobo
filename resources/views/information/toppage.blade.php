@extends('layouts.template')

@section('title')
TAMERU ~電子帳簿保存
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')
<div class="{{$newsshow}}">
    <div class="news">
        <div class="news_delete_button">

        </div>
        <div class="news_title">
            システム名変更のお知らせ
        </div>
        <div class="news_content">
            あけましておめでとうございます<br>
            旧年中はひとかたならぬご愛顧にあずかり誠にありがとうございました<br>
            さて本サービスについてですが1月より「電子帳簿保存システム」から「<strong>TAMERU</strong>」に<br>
            システム名を変更いたしました。<br> 
            本年もより一層のサービス向上を目指し誠心誠意努める所存でございます<br>
            今後ともよろしくお願い申し上げます<br>             
        </div>
        <div class="news_astec">株式会社アステック</div>
    </div>
</div>
<div class="loader">
    <img src="{{ asset($prefix.'/'.'img/loading.gif')}}">
    <div class="searchcomment">検索中です</div>
</div>
<h2 class="pagetitle">帳簿一覧</h2>

<div class="ledger-top-actions">
    <a href="{{ route('registGet') }}" class="ledger-top-actions__btn ledger-top-actions__btn--single">
        <img src="{{ asset($prefix.'/img/pencil_2_line.svg') }}" alt="" class="ledger-top-actions__icon" width="16" height="16">
        <span class="ledger-top-actions__label">新規帳簿保存</span>
    </a>
    @if(!empty($banbanEnabled))
    <a href="{{ route('registGet', ['mode' => 'bulk']) }}" class="ledger-top-actions__btn ledger-top-actions__btn--bulk">
        <img src="{{ asset($prefix.'/img/pencil_2_line.svg') }}" alt="" class="ledger-top-actions__icon" width="16" height="16">
        <span class="ledger-top-actions__label">一括帳簿保存</span>
    </a>
    @endif
</div>

<form class="searchform" action="{{route('searchPost')}}" method="get" enctype="multipart/form-data">
    @include('information.partials.searchbox', ['datacountZenkenValue' => '10000'])
</form>
<div class="wholecontainer">

</div>
<div class="previewcontainer">

</div>

@include('information.partials.ledger-toolbar')
@include('information.partials.ledger-table')



@endsection
@section('footer')

@endsection