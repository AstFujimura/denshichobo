@extends('layouts.scheduletemplate')

@section('title')
Skett ~スケジュールアプリ
@endsection




@section('main')
<div class="MainElement">
    <h2 id="schedule_regist" class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/regist.svg') }}" alt="" class="title_icon">スケジュール登録</h2>
    <div class="page_reload_container">
        <a class="page_back_button" href="{{ route('scheduleget')}}">
            トップへもどる
        </a>
        <a class="page_cancel_button" href="{{ route('scheduleregistget', ['user_id' => $user_id, 'event_id' => $event_id]) }}">
            キャンセル
        </a>
    </div>
    <form id="schedule_regist_form" action="{{ route('scheduleregistpost') }}" method="post">
        @csrf
        <input type="hidden" name="event_id" value="{{ $event_id }}">
        <input type="hidden" name="delete_flag" value="false" id="delete_flag">
        <div class="schedule_regist_container">
            <div class="schedule_switch_container">
                <a href="{{ route('scheduletermregistget', ['user_id' => $user_id, 'event_id' => $event_id]) }}" class="switch_button term_button">
                    期間入力
                </a>
                @if (!$event_id)
                <a href="{{ route('scheduleregularregistget', ['user_id' => $user_id, 'event_id' => $event_id]) }}" class="switch_button multiple_button">
                    繰り返し登録
                </a>
                @endif
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
                <div class="schedule_regist_element_container">
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
            </div>
            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    予定
                </div>
                <div class="schedule_regist_element">
                    <select class="schedule_plan_select" name="schedule_type" id="schedule_type">
                        <option value="-">--</option>
                        @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}" {{ $plan->id == $plan_id ? 'selected' : '' }}>{{ $plan->予定 }}</option>
                        @endforeach
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
                        @foreach ($event_users as $event_user)
                        <div class="join_member_element" data-user_id="{{ $event_user->id }}">
                            <span class="join_member_name">{{ $event_user->name }}</span>
                            <span class="join_member_delete_button" data-user_id="{{ $event_user->id }}">×</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="candidate_member_container">
                        <div class="group_candidate_container">
                            <select class="group_candidate_select" name="group_candidate" id="group_candidate">
                                <option value="-">--</option>
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->グループ名 }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="candidate_list_container">

                            <!-- <label for="user_1" class="candidate_list_element">
                                <input type="checkbox" class="candidate_checkbox" name="candidate_checkbox" data-user_id="1" id="user_1">
                                <span class="candidate_name">ユーザー名</span>
                            </label> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    メモ
                </div>
                <div class="schedule_regist_element">
                    <textarea class="schedule_memo_textarea" name="memo" id="memo">{{ $memo }}</textarea>
                </div>
            </div>
            <div class="inputs" id="inputs">
                @foreach ($event_users as $event_user)
                <input type="hidden" name="user_id[]" value="{{ $event_user->id }}">
                @endforeach
            </div>
            <div class="schedule_regist_content">
                <div class="regist_button" id="regist_button">
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