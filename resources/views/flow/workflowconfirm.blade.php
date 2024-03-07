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

    <h2 class="pagetitle" id="flow_confirm">確認画面</h2>
    <form action="{{route('workflowconfirmpost')}}" method="post" id="flow_application_choice_form" class="flow_confirm_form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" value="{{$id}}" name="id">
        <input type="hidden" value="{{$flowid}}" id="flowid">

        <div class="back_button flow_application_back_button">
            もどる
        </div>
        <button class="dicision_button flow_application_button" id="flow_next_button">
            申請
        </button>


    </form>
    <div class="flow_confirm_container">
        <div class="flow_meta_information_container">
            <div class="flow_confirm_content">
                <div class="flow_confirm_label">
                    標題
                </div>
                <div class="flow_confirm_element">
                    {{$draft->標題}}
                </div>
            </div>
            <div class="flow_confirm_content">
                <div class="flow_confirm_label">
                    取引先
                </div>
                <div class="flow_confirm_element">
                    {{$draft->取引先}}
                </div>
            </div>
            <div class="flow_confirm_content">
                <div class="flow_confirm_label">
                    日付
                </div>
                <div class="flow_confirm_element">
                    {{$draft->日付}}
                </div>
            </div>
            <div class="flow_confirm_content">
                <div class="flow_confirm_label">
                    金額
                </div>
                <div class="flow_confirm_element">
                    {{$draft->金額}}
                </div>
            </div>
            <div class="flow_confirm_content">
                <div class="flow_confirm_label">
                    コメント
                </div>
                <div class="flow_confirm_element">
                    {{$draft->コメント}}
                </div>
            </div>




        </div>
        <div class="view_grid">

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