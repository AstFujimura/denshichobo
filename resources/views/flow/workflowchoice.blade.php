@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection
<div class="flow_application_gray">

</div>
<div class="flow_application_preview_container">

</div>



@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="flow_choice"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application.svg') }}" alt="" class="title_icon">経路選択</h2>
    <form action="{{route('workflowchoicepost')}}" method="post" id="flow_application_choice_form" class="flow_application_choice_form" enctype="multipart/form-data">
        @csrf
        <div class="flow_choice_button_container">
            <a href="{{route('workflowapplicationget',['t_flow_id' => $id])}}" class="back_button flow_choice_back_button" id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                もどる
            </a>
            <button class="next_button flow_choice_next_button" id="flow_next_button">
                次へ
            </button>
        </div>

        <input type="hidden" value="{{$id}}" name="id">
        <div class="flow_choice_select_container">
            <div class="flow_choice_container">
                <select class="flow_choice_select" name="m_flow_id">
                    <option></option>
                    @foreach($m_flows as $m_flow)
                    <option value="{{$m_flow->id}}">{{$m_flow->フロー名}}</option>
                    @endforeach

                </select>
                <div class="flow_choice_annotation unselected">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/exclamation.svg') }}" alt="" class="button_icon">
                    経路を選択してください
                </div>
                <div class="flow_choice_annotation selected">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/check.svg') }}" alt="" class="button_icon">
                    経路選択済み
                </div>

            </div>

        </div>
        <div class="flow_choice_view_container">
            <div class="view_meta_container">
                <div class="view_condition_title">
                    経路情報
                </div>
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
            <div class="view_grid_container">
                <div class="view_grid">

                </div>
            </div>

        </div>


        <div class="element_input">
            <input type="hidden" id="maxgrid_column">
            <input type="hidden" id="maxgrid_row">
        </div>
    </form>

</div>
@endsection

@section('footer')
@endsection