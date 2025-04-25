@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="card_view_title"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">名刺管理トップ</h2>
    <div class="multiple_upload_container">

        <form id="multiple_upload_form" class="multiple_upload_form" action="{{ route('cardmultipleuploadpost') }}" method="POST" enctype="multipart/form-data">
            @csrf


            <input type="file" id="folder_upload" class="folder_upload" webkitdirectory multiple>
            <label for="folder_upload" class="folder_upload_label">
                <div class="folder_upload_label_text">
                    タップしてフォルダを選択
                </div>
            </label>
            <div class="upload_button">
                アップロード開始
            </div>

        </form>

        <div class="progress_container_wrapper">
            <div class="progress_container">
                <div class="progress_annotation">
                    画面遷移しないでください
                </div>
                <div class="progress_message">
                    アップロード中
                </div>
                <div class="progress_bar_wrapper">
                    <div class="progress_bar"></div>
                </div>

            </div>
            <input type="hidden" id="upload_complete_flag" value="false">
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection