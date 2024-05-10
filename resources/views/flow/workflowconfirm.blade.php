@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection
<div class="flow_application_gray">

</div>
<div class="flow_application_preview_container">

</div>


<div class="approve_gray"></div>
<div class="approve_preview_container"></div>
@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="flow_confirm"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application.svg') }}" alt="" class="title_icon">確認画面</h2>
    <form action="{{route('workflowconfirmpost')}}" method="post" id="flow_application_choice_form" class="flow_confirm_form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" value="{{$t_flow->フローマスタID}}" name="m_flow_id" id="m_flow_id">
        <input type="hidden" value="{{$t_flow->id}}" name="t_flow_id" id="t_flow_id">

        <a href="{{route('workflowchoiceget',['id' => $id])}}" class="back_button flow_application_back_button">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
            もどる
        </a>
        <button class="dicision_button flow_application_button" id="flow_next_button">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/button/application.svg') }}" alt="" class="button_icon">
            申請
        </button>


    </form>
    <div class="flow_confirm_container">
        <div class="flow_meta_information_container">
            <div class="view_application_title">
                申請情報
            </div>
            @foreach ($t_optionals as $t_optional)
            <div class="flow_confirm_content">
                <div class="flow_confirm_label">
                    {{$t_optional->項目名}}
                </div>
                <div class="flow_confirm_element">
                    @if ($t_optional->値 == "file_regist_2545198")
                    <div class="approve_preview_button" data-id="{{$t_optional->id}}">プレビュー</div>
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/download_2_line.svg') }}" class="approve_download" id="{{$prefix}}/workflow/download/{{$t_optional->id}}">
                    @else
                    {{$t_optional->値}}
                    @endif
                </div>
            </div>
            @endforeach
            <div class="view_application_title">
                承認用紙
            </div>
            <div class="flow_confirm_content">
                <div class="flow_confirm_label">
                    承認用紙
                </div>
                <div class="flow_confirm_element">
                    <div class="approve_preview_button" data-id="-{{$t_flow->id}}">プレビュー</div>
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/download_2_line.svg') }}" class="approve_download" id="{{$prefix}}/workflow/download/-{{$t_flow->id}}">
                </div>
            </div>
        </div>
        <div class="flow_confirm_view_container">

            <div class="flow_confirm_meta_container">
                <div class="view_condition_title">
                    経路情報
                </div>
                <div class="condition_accordion_trigger">
                    条件詳細
                </div>
                <div class="condition_accordion condition_accordion_close">
                    <div class="view_condition_group_content">
                        <div class="view_condition_group_title">
                            申請者グループ
                        </div>
                        <div class="view_condition_group_element">

                        </div>
                    </div>
                    <div class="view_condition_price_content">
                        <div class="view_condition_price_title">
                            金額
                        </div>
                        <div class="view_condition_start_price">
                            下限金額: <span class="view_condition_start_price_value"></span>
                        </div>
                        <div class="view_condition_end_price">
                            上限金額: <span class="view_condition_end_price_value"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="view_grid">

            </div>
        </div>

    </div>


    <div class="element_input">
        <input type="hidden" id="maxgrid_column">
        <input type="hidden" id="maxgrid_row">
    </div>
</div>
@endsection

@section('footer')
@endsection