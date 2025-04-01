@extends('layouts.scheduletemplate')

@section('title')
Skett ~スケジュールアプリ
@endsection




@section('main')
<div class="MainElement">
    <h2 id="schedule_group_regist" class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/regist_title.svg') }}" alt="" class="title_icon">個人グループ作成</h2>
    <div class="page_reload_container">
        <a class="page_back_button" href="{{ route('scheduleget')}}">
            トップへもどる
        </a>
        <a class="page_cancel_button" href="{{ route('schedulegroupregistget') }}">
            キャンセル
        </a>
    </div>
    <form class="schedule_group_regist_form" id="schedule_group_regist_form" action="{{ route('schedulegroupregistpost') }}" method="post">
        @csrf
        <input type="hidden" name="schedule_group_id" value="{{ $schedule_group->id ?? '' }}">
        <input type="hidden" name="delete_flag" id="delete_flag" value="false">
        <div class="schedule_regist_content">
            <div class="schedule_regist_content_title">
                個人グループ名
            </div>
            <div class="schedule_regist_element">
                <input class="schedule_input_text" type="text" name="group_name" id="group_name" data-required="true" value="{{ $schedule_group->グループ名 ?? '' }}">
            </div>
        </div>
        <div class="schedule_regist_content">
            <div class="schedule_regist_content_title">
                所属ユーザー
            </div>
            <div class="schedule_regist_element">
                <div class="join_member_container">
                    @foreach ($schedule_group_users as $schedule_group_user)
                    <div class="join_member_element" data-user_id="{{ $schedule_group_user->id }}">
                        <span class="join_member_name">{{ $schedule_group_user->name }}</span>
                        <span class="join_member_delete_button" data-user_id="{{ $schedule_group_user->id }}">×</span>
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
                デフォルトに設定する
            </div>
            <div class="schedule_regist_element">
                <input class="checkbox_input" type="checkbox" name="default_checkbox" id="default_checkbox" data-required="true" {{ $default_checkbox ? 'checked' : '' }}>
            </div>
        </div>
        <div class="inputs" id="inputs">
            @foreach ($schedule_group_users as $schedule_group_user)
            <input type="hidden" name="user_id[]" value="{{ $schedule_group_user->id }}">
            @endforeach 
        </div>
        <div class="schedule_regist_content">
            <div class="regist_button" id="regist_button">
                登録
            </div>
            <div class="delete_button" id="delete_button">
                削除
            </div>
        </div>
    </form>
</div>
@endsection

@section('footer')
@endsection