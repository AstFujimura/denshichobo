@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection
<div class="flow_application_gray">

</div>
<div class="flow_application_preview_container">

</div>



@section('main')
<div class="MainElement">

    <h2 class="pagetitle">経路選択</h2>
    <form action="{{route('workflowchoicepost')}}" method="post" id="flow_application_choice_form" class="flow_application_choice_form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" value="{{$id}}" name="id">
        <div class="flow_choice_select_container">
            <div class="flow_choice_container">
                <select class="flow_choice_select" name="flowid">
                    <option></option>
                    @foreach($m_flows as $m_flow)
                    <option value="{{$m_flow->id}}">{{$m_flow->フロー名}}</option>
                    @endforeach

                </select>
                <div class="flow_choice_annotation">
                    経路を選択してください
                </div>

            </div>
            <div class="back_button flow_choice_back_button" id="flow_next_button">
                もどる
            </div>
            <button class="next_button flow_choice_next_button" id="flow_next_button">
                次へ
            </button>
        </div>

        <div class="view_grid">

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