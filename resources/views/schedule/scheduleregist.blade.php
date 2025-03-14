@extends('layouts.scheduletemplate')

@section('title')
スケジュール
@endsection




@section('main')
<div class="MainElement">
    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">スケジュール登録</h2>
    <form id="schedule_regist_form" action="{{ route('scheduleregistpost') }}" method="post">
        @csrf
        <input type="hidden" name="event_id" value="{{ $event_id }}">
        <input type="hidden" name="delete_flag" value="false">
        <div class="schedule_regist_container">
            <div class="schedule_switch_container">
                <div class="switch_button term_button">
                    期間入力
                </div>
                <div class="switch_button multiple_button">
                    複数日入力
                </div>
            </div>
            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    日付
                </div>
                <div class="schedule_regist_element">
                    <input class="schedule_input_date" type="text" name="date" id="date" data-required="true" value="{{ Carbon\Carbon::parse($date)->format('Y/m/d') }}">
                </div>

            </div>
            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    時刻
                </div>
                <div class="schedule_regist_element">
                    <select class="schedule_time_select" name="start_time_hour" id="start_time_hour">
                        <option value="-">--</option>
                        @for ($i = 0; $i < 24; $i++)
                            <option value="{{ $i }}" {{ $start_time_hour == $i ? 'selected' : '' }}>{{ $i }}時</option>
                            @endfor
                    </select>
                </div>
                <div class="schedule_regist_element">
                    <select class="schedule_time_select" name="start_time_minute" id="start_time_minute">
                        <option value="-">--</option>
                        @for ($i = 0; $i < 60; $i +=5)
                            <option value="{{ $i }}" {{ $start_time_minute == $i ? 'selected' : '' }}>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}分</option>
                            @endfor
                    </select>
                </div>
                <div class="schedule_regist_element">
                    ～
                </div>
                <div class="schedule_regist_element">
                    <select class="schedule_time_select" name="end_time_hour" id="end_time_hour">
                        <option value="-">--</option>
                        @for ($i = 0; $i < 24; $i++)
                            <option value="{{ $i }}" {{ $end_time_hour == $i ? 'selected' : '' }}>{{ $i }}時</option>
                            @endfor
                    </select>
                </div>
                <div class="schedule_regist_element">
                    <select class="schedule_time_select" name="end_time_minute" id="end_time_minute">
                        <option value="-">--</option>
                        @for ($i = 0; $i < 60; $i +=5)
                            <option value="{{ $i }}" {{ $end_time_minute == $i ? 'selected' : '' }}>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}分</option>
                            @endfor
                    </select>
                </div>
            </div>
            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    予定
                </div>
                <div class="schedule_regist_element">
                    <select class="schedule_plan_select" name="schedule_type" id="schedule_type">
                        <option value="-">--</option>
                        <option value="1">予定</option>
                        <option value="2">メモ</option>
                    </select>
                </div>
                <div class="schedule_regist_element">
                    <input class="schedule_event_input" type="text" name="event_name" id="event_name" value="{{ $event_name }}">
                </div>
            </div>
            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    参加者
                </div>
                <div class="schedule_regist_element">
                    <div class="join_member_container">

                    </div>
                    <div class="candidate_member_container">
                        <div class="group_candidate_container">
                            <select class="group_candidate_select" name="group_candidate" id="group_candidate">
                                <option value="-">--</option>
                            </select>
                        </div>
                        <div class="candidate_list_container">

                        </div>
                    </div>
                </div>
            </div>
            <div class="schedule_regist_content">
                <div class="regist_button">
                    登録
                </div>
                @if ($event_id)
                <div class="delete_button">
                    削除
                </div>
                @endif

            </div>
    </form>
</div>
@endsection

@section('footer')
@endsection