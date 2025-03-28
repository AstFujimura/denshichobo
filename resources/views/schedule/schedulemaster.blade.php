@extends('layouts.scheduletemplate')

@section('title')
スケジュール
@endsection




@section('main')
<div class="MainElement">
    <h2 id="schedulemaster_regist" class="pagetitle">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/master.svg') }}" alt="" class="title_icon">
        予定マスタ登録
    </h2>
    <div class="page_reload_container">
        <a class="page_back_button" href="{{ route('scheduleget')}}">
            トップへもどる
        </a>
        <a class="page_cancel_button" href="{{ route('schedulemasterregistget') }}">
            キャンセル
        </a>
    </div>
    <form class="schedulemaster_regist_form" id="schedulemaster_regist_form" action="{{ route('schedulemasterregistpost') }}" method="post">
        @csrf
        <div class="inputs">
        <input type="hidden" name="now_count" value="100000" id="now_count">
        @foreach ($plans as $plan)
        <input type="hidden" name="plan_id[]" value="{{ $plan->id }}">
        @endforeach


        </div>
        <div class="schedule_add_button">
            ＋追加
        </div>
        <table class="schedule_master_table">
            <thead>
                <tr>
                    <th>
                        予定
                    </th>
                    <th>
                        背景色

                    </th>
                    <th>
                        プレビュー
                    </th>
                    <th>
                        削除
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plans as $plan)
                <tr>
                    <td>
                        <input class="schedule_master_text_input" type="text" name="schedule_name{{ $plan->id }}" id="schedule_name{{ $plan->id }}" data-id="{{ $plan->id }}" value="{{ $plan->予定 }}" data-required="true">
                    </td>
                    <td>
                        <input class="schedule_master_color_input" type="color" name="background_color{{ $plan->id }}" id="background_color{{ $plan->id }}" data-id="{{ $plan->id }}" value="{{ $plan->装飾 }}">
                    </td>
                    <td>
                        <div class="schedule_master_preview" data-id="{{ $plan->id }}"></div>
                    </td>
                    <td>
                        <div class="delete_button" data-id="{{ $plan->id }}">
                            ×
                        </div>
                    </td>
                </tr>
                @endforeach
                <!-- <tr>
                    <td>
                        <input class="schedule_master_text_input" type="text" name="schedule_name100001" id="schedule_name100001" data-id="100001" data-required="true">
                    </td>
                    <td>
                        <input class="schedule_master_input" type="color" name="background_color100001" id="background_color100001" data-id="100001" data-required="true">
                    </td>
                    <td>
                        <div class="schedule_master_preview" data-id="100001"></div>
                    </td>
                    <td>
                        <div class="delete_button">
                            ×
                        </div>
                    </td>
                </tr> -->

            </tbody>
        </table>
        <div class="schedule_regist_content">
            <div class="regist_button" id="regist_button">
                登録
            </div>
        </div>
    </form>

</div>
@endsection

@section('footer')
@endsection