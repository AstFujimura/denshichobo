@extends('layouts.scheduletemplate')

@section('title')
Skett ~スケジュールアプリ
@endsection




@section('main')
<div class="MainElement">
    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/calendar.svg') }}" alt="" class="title_icon">スケジュール</h2>
    <div class="schedule_controll_container">
        <div class="schedule_group_container">
            <select class="month_user_select" name="" id="">
                @foreach ($selected_users as $selected_user)
                <option value="{{ $selected_user->id }}" {{ $user->id == $selected_user->id ? 'selected' : '' }}>{{ $selected_user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="date_change_container">
            <a href="{{ route('schedulemonthget', ['user_id' => $user->id, 'month' => Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="date_change_button" data-when="last_month">
                先月
            </a>
            <a href="{{ route('schedulemonthget', ['user_id' => $user->id, 'month' => Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}" class="date_change_button" data-when="next_month">
                翌月
            </a>
        </div>
    </div>
    <div class="schedule_base_container">
        <div class="schedule_base_element">
            <div class="schedule_base_element_content">
                {{ Carbon\Carbon::parse($month)->format('Y年m月') }}
            </div>
        </div>
    </div>
    <div class="schedule_container">
        @for ($w = 0; $w < 5; $w++)
            <div class="schedule_header">
            @foreach ($cells->{$w} as $key => $value)
            <div class="schedule_header_cell month_schedule_header_cell" data-day-num="{{ $value->day_num }}">
                <div class="schedule_date">
                    {{ $value->date }}({{ $value->day }})
                </div>
            </div>
            @endforeach
    </div>
    <div class="schedule_row">
        @for ($i = 0; $i < 7; $i++)
            <div class="schedule_cell month_schedule_cell">

            <div class="event_container">
                @foreach ($user->{$w}->{'index' . $i} as $event)
                <div class="event_content">
                    <div class="event_time">
                        @if ($event->開始時間指定)
                        <div class="event_time_start">
                            {{ Carbon\Carbon::parse($event->開始)->format('H:i') }}

                        </div>
                        @endif
                        @if ($event->開始時間指定 && $event->終了時間指定)
                        <div class="event_time_dash">
                            -
                        </div>
                        @endif
                        @if ($event->終了時間指定)
                        <div class="event_time_end">
                            {{ Carbon\Carbon::parse($event->終了)->format('H:i') }}
                        </div>
                        @endif
                    </div>

                    <div class="event_name">
                        @if ($event->予定ID)
                        <span class="plan" style="background-color: {{ $event->装飾 }};">
                            {{ $event->予定 }}
                        </span>
                        @endif
                        <a class="event_name_text" href="{{ route('scheduleregistget', ['event_id' => $event->event_id]) }}">{{ $event->予定詳細 }}</a>
                    </div>
                </div>
                @endforeach

            </div>
            <a href="{{ route('scheduleregistget', ['user_id' => $user->id, 'date' => $cells->{$w}->{$i}->ymd]) }}" class="regist_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/schedule/regist.svg') }}" alt="" class="regist_icon">
            </a>
    </div>

    @endfor

</div>
<div class="schedule_term_container month_schedule_term_container">
        @foreach ($user->{$w}->{'long_events'} as $long_event)
        <div class="schedule_term_element" style="grid-row: {{ $long_event->start_row }} / {{ $long_event->end_row }}; grid-column: {{ $long_event->start_col }} / {{ $long_event->end_col }};">
            @if ($long_event->予定ID)
            <span class="plan" style="background-color: {{ $long_event->装飾 }};">
                {{ $long_event->予定 }}
            </span>
            @endif
            <a href="{{ route('scheduleregistget', ['event_id' => $long_event->event_id]) }}" class="schedule_term_element_title">
                {{ $long_event->予定詳細 }}
            </a>
        </div>
        @endforeach
    </div>
@endfor
<div class="menu_container">
    <div class="menu_element">
        @if(Auth::user()->管理 == '管理')
        <a href="{{ route('schedulemasterregistget') }}">
            予定マスタ登録
        </a>
        @endif
    </div>
</div>
</div>

</div>
@endsection

@section('footer')
@endsection