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

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/application.svg') }}" alt="" class="title_icon" id="workflow_reapply_title">ワークフロー再申請</h2>
    <form action="{{route('workflowreapplypost')}}" method="post" id="flow_application_form" class="flow_application_form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="t_flow_id" value="{{$t_flow->id}}">
        <div class="flow_application_container">
            <div class="flow_application_button_content">
                <a href="{{route('categorydetailget',['id'=>$t_flow->id])}}" class="back_button " id="flow_next_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                    もどる
                </a>
                <button class="next_button flow_next_button" id="flow_next_button">
                    次へ
                </button>
            </div>
            <div class="flow_reapplication_main_content">

                <div class="flow_application_content">

                    <div class="flow_application_title">
                        再申請内容
                    </div>
                    <div class="flow_application_area">
                        <div class="flow_application_area">

                        </div>
                        @foreach ($t_optionals as $t_optional)
                        <div class="application_form_content">
                            <div class="application_form_label">
                                {{$t_optional->項目名}}
                                @if ($t_optional->必須 == 1)
                                <span class="application_red">*</span>
                                @endif
                            </div>
                            @if ($t_optional->型 == 1)
                            <input type="text" class="application_form_text text_long_content" name="application_form_input{{$t_optional->id}}" value="{{$t_optional->値}}" data-required="{{$t_optional->必須}}">
                            @elseif ($t_optional->型 == 2)
                            <input type="number" class="application_form_text text_long_content" name="application_form_input{{$t_optional->id}}" value="{{$t_optional->値}}" data-required="{{$t_optional->必須}}">
                            @elseif ($t_optional->型 == 3)
                            <div class="date_form_container">
                                <input type="text" class="application_form_text application_form_date text_short_content" name="application_form_input{{$t_optional->id}}" value="{{$t_optional->値}}" data-required="{{$t_optional->必須}}">
                            </div>
                            @elseif ($t_optional->型 == 4)
                            <div class="flow_application_droparea">
                                <p>ここにドラッグ&ドロップ</p>
                                <input type="file" class="file_input" name="application_form_input{{$t_optional->id}}" data-required="{{$t_optional->必須}}">
                            </div>
                            <div class="flow_application_preview_button" data-id="{{$t_optional->id}}">プレビュー</div>
                            @endif
                        </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
        <div class="pointer_input">
            @foreach ($m_pointers as $m_pointer)
            <input type="hidden" class="m_pointer" data-m_pointer_id="{{$m_pointer->id}}" data-m_optional_id="{{$m_pointer->任意項目マスタID}}" data-category_id="{{$m_pointer->カテゴリマスタID}}" data-font_size="{{$m_pointer->フォントサイズ}}">
            <input type="hidden" class="m_pointer_img" data-m_pointer_id="{{$m_pointer->id}}" id="m_pointer_img{{$m_pointer->id}}" name="m_pointer_img{{$m_pointer->id}}" value="">
            @endforeach
        </div>

    </form>

</div>
@endsection

@section('footer')
@endsection