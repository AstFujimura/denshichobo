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

    <h2 class="pagetitle">ワークフロー申請</h2>
    <form action="{{route('workflowapplicationpost')}}" method="post" id="flow_application_form" class="flow_application_form" enctype="multipart/form-data">
        @csrf
        <div class="flow_application_container">
            <div class="application_form_content ">
                <div class="application_form_label">
                    標題
                </div>
                <input type="text" name="title" class="application_form_text text_long_content">
            </div>
            <div class="application_form_content ">
                <div class="application_form_label">
                    取引先
                </div>
                <input type="text" name="company" class="application_form_text text_long_content">
                <!-- <div class="registtorihikisakiselect" id="torihikisakiselect"></div> -->
            </div>

            <div class="application_form_content ">
                <div class="application_form_label">
                    取引日
                </div>
                <input type="text" name="date" class="application_form_text text_short_content">
            </div>
            <div class="application_form_content ">
                <div class="application_form_label">
                    金額
                </div>
                <input type="number" name="price" class="application_form_text text_short_content">
            </div>
            <div class="application_form_content ">
                <div class="application_form_label">
                    コメント
                </div>
                <textarea name="comment" class="application_form_text text_area_content"></textarea>
            </div>
            <div class="application_form_content">
                <div class="application_form_label">
                    請求書
                </div>
                <div class="flow_application_droparea">
                    <p>ここにドラッグ＆ドロップ</p>
                    <input type="file" name="file" id="file" class="file_input">
                </div>
                <div class="flow_application_preview_button">プレビュー</div>
            </div>

        </div>
        <button class="next_button flow_next_button" id="flow_next_button">
            次へ
        </button>



    </form>

</div>
@endsection

@section('footer')
@endsection