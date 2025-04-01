@extends('layouts.scheduletemplate')

@section('title')
Skett ~スケジュールアプリ
@endsection




@section('main')
<div class="MainElement">
    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/calendar.svg') }}" alt="" class="title_icon">スケジュール</h2>
    <input type="hidden" id="selected_group_id" value="{{ $selected_group_id }}">
    <div class="schedule_controll_container">
        <div class="schedule_group_container">
            <select class="schedule_group_select" name="" id="">
                @foreach ($groups as $group)
                <option value="{{ $group->id }}" {{ $selected_group_id == $group->id ? 'selected' : '' }}>{{ $group->グループ名 }}</option>
                @endforeach
            </select>
            @if($selected_group_id < 100000)
                <a href="{{ route('schedulegroupregistget', ['selected_group_id' => $selected_group_id])     }}" class="schedule_group_edit_button">
                個人グループ編集
                </a>
                @endif
                <a href="{{ route('schedulegroupregistget') }}" class="schedule_group_regist_button">
                    個人グループ作成
                </a>
        </div>
        <div class="date_change_container">
            <a href="{{ route('scheduleget', ['selected_group_id' => $selected_group_id, 'base_date' => Carbon\Carbon::parse($base_date)->subWeek()->format('Y-m-d')]) }}" class="date_change_button" data-when="last_week">
                先週
            </a>
            <a href="{{ route('scheduleget', ['selected_group_id' => $selected_group_id, 'base_date' => Carbon\Carbon::parse($base_date)->subDay()->format('Y-m-d')]) }}" class="date_change_button" data-when="yesterday">
                前日
            </a>
            <a href="{{ route('scheduleget', ['selected_group_id' => $selected_group_id, 'base_date' => Carbon\Carbon::now()->format('Y-m-d')]) }}" class="date_change_button" data-when="today">
                今日
            </a>
            <a href="{{ route('scheduleget', ['selected_group_id' => $selected_group_id, 'base_date' => Carbon\Carbon::parse($base_date)->addDay()->format('Y-m-d')]) }}" class="date_change_button" data-when="tomorrow">
                翌日
            </a>
            <a href="{{ route('scheduleget', ['selected_group_id' => $selected_group_id, 'base_date' => Carbon\Carbon::parse($base_date)->addWeek()->format('Y-m-d')]) }}" class="date_change_button" data-when="next_week">
                翌週
            </a>
            <input type="text" class="search_date" value="">
        </div>
    </div>
    <div class="schedule_base_container">
        <div class="schedule_base_element">
            <div class="schedule_base_element_content">
                {{ Carbon\Carbon::parse($base_date)->format('Y年m月d日') }}
            </div>
        </div>
    </div>
    <div class="schedule_container">
        <div class="schedule_header">
            <div class="schedule_header_cell">

            </div>
            @foreach ($cells as $key => $value)
            <div class="schedule_header_cell" data-day-num="{{ $value->day_num }}">
                <div class="schedule_date">
                    {{ $value->date }}({{ $value->day }})
                </div>
            </div>
            @endforeach
        </div>
        @foreach ($users as $user)
        <div class="schedule_row">
            <div class="schedule_cell schedule_cell_header">
                <div class="schedule_user">
                    {{ $user->name }}
                </div>
                <a class="schedule_cell_header_month" href="{{ route('schedulemonthget', ['user_id' => $user->id, 'month' => Carbon\Carbon::parse($base_date)->format('Y-m')]) }}">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/schedule/calendar_month.svg') }}" alt="" class="schedule_cell_header_month_icon">
                    月予定
                </a>
            </div>
            @for ($i = 0; $i < 7; $i++)
                <div class="schedule_cell">

                <div class="event_container">
                    @foreach ($user->{'index' . $i} as $event)
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
                <a href="{{ route('scheduleregistget', ['user_id' => $user->id, 'date' => $cells->{$i}->ymd]) }}" class="regist_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/schedule/regist.svg') }}" alt="" class="regist_icon">
                </a>
        </div>
        @endfor
    </div>
    <div class="schedule_term_container">
        @foreach ($user->long_events as $long_event)
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
    @endforeach

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