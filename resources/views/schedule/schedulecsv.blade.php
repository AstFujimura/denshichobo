@extends('layouts.scheduletemplate')

@section('title')
スケジュール
@endsection




@section('main')
<div class="MainElement">
    <h2 id="schedulemaster_regist" class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">予定マスタ登録</h2>
    <div class="page_reload_container">
        <a class="page_back_button" href="{{ route('scheduleget')}}">
            トップへもどる
        </a>
        <a class="page_cancel_button" href="{{ route('schedulecsvget') }}">
            キャンセル
        </a>
    </div>
    <div class="schedule_csv_container">
        <input type="file" name="csv_file" id="csv_file" accept=".csv">
        <button type="submit">
            アップロード
        </button>
    </div>

</div>
@endsection

@section('footer')
@endsection