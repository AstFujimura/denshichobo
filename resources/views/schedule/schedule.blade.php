@extends('layouts.scheduletemplate')

@section('title')
スケジュール
@endsection




@section('main')
<div class="MainElement">
    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">スケジュール</h2>
    <div class="schedule_group_container">
        <select class="schedule_group_select" name="" id="">
            <option value="">アステック</option>
            <option value="">ダイワボウ</option>
            <option value="">ダイワボウ</option>
            <option value="">ダイワボウ</option>
            <option value="">ダイワボウ</option>
        </select>
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
            <div class="schedule_cell">
                <div class="schedule_user">
                    {{ $user->name }}
                </div>
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
                            <span class="plan">
                                {{ $event->予定ID }}
                            </span>
                            <a href="/schedule">{{ $event->予定詳細 }}</a>
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
    @endforeach
</div>

</div>
@endsection

@section('footer')
@endsection