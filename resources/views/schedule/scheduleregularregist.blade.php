@extends('layouts.scheduletemplate')

@section('title')
スケジュール
@endsection




@section('main')
<div class="MainElement">
    <h2 id="schedule_regular_regist" class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/regist.svg') }}" alt="" class="title_icon">スケジュール登録</h2>
    <div class="page_reload_container">
        <a class="page_back_button" href="{{ route('scheduleget')}}">
            トップへもどる
        </a>
        <a class="page_cancel_button" href="{{ route('scheduleregularregistget', ['user_id' => $user_id , 'regular_event_id' => $regular_event_id]) }}">
            キャンセル
        </a>
    </div>
    <form id="schedule_regular_regist_form" action="{{ route('scheduleregularregistpost') }}" method="post">
        @csrf
        <input type="hidden" name="regular_event_id" value="{{ $regular_event_id }}">
        <input type="hidden" name="delete_flag" value="false" id="delete_flag">
        <div class="schedule_regist_container">
            @if (!$regular_event_id)
            <div class="schedule_switch_container">
                <a href="{{ route('scheduleregistget', ['user_id' => $user_id]) }}" class="switch_button multiple_button">
                    通常登録
                </a>
            </div>
            @endif

            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    繰り返し頻度
                </div>
                <div class="schedule_regist_element">
                    <select class="schedule_regular_select" name="regular_frequency" id="regular_frequency">
                        <option value="0" {{ $regular_frequency == 0 ? 'selected' : '' }}>毎日</option>
                        <option value="1" {{ $regular_frequency == 1 ? 'selected' : '' }}>毎週</option>
                        <option value="2" {{ $regular_frequency == 2 ? 'selected' : '' }}>毎月</option>
                    </select>
                    <select class="schedule_regular_select_detail display_none" name="regular_frequency_day_detail" id="regular_frequency_day_detail">
                        <option value="1" {{ $regular_frequency_day_detail == 1 ? 'selected' : '' }}>月曜日</option>
                        <option value="2" {{ $regular_frequency_day_detail == 2 ? 'selected' : '' }}>火曜日</option>
                        <option value="3" {{ $regular_frequency_day_detail == 3 ? 'selected' : '' }}>水曜日</option>
                        <option value="4" {{ $regular_frequency_day_detail == 4 ? 'selected' : '' }}>木曜日</option>
                        <option value="5" {{ $regular_frequency_day_detail == 5 ? 'selected' : '' }}>金曜日</option>
                        <option value="6" {{ $regular_frequency_day_detail == 6 ? 'selected' : '' }}>土曜日</option>
                        <option value="7" {{ $regular_frequency_day_detail == 7 ? 'selected' : '' }}>日曜日</option>
                    </select>
                    <select class="schedule_regular_select_detail display_none" name="regular_frequency_date_detail" id="regular_frequency_date_detail">
                        @for ($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">{{ $i }}日</option>
                            @endfor
                            <option value="100">月末</option>
                    </select>
                </div>
            </div>

            <div class="schedule_regist_content">
                <div class="schedule_regist_content_title">
                    期間
                </div>
                <div class="schedule_regist_element_container">
                    <div class="schedule_regist_element">
                        <input class="schedule_input_date" type="text" name="start_date" id="start_date" data-required="true" value="{{ $start_date }}">
                    </div>
                    <div class="schedule_regist_element">
                        ～
                    </div>
                    <div class="schedule_regist_element">
                        <input class="schedule_input_date" type="text" name="end_date" id="end_date" value="{{ $end_date }}">
                    </div>
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
                @if ($regular_event_id)
                <div class="delete_button" id="delete_button">
                    削除
                </div>
                @endif

            </div>
    </form>
</div>
@endsection

@section('footer')
@endsection