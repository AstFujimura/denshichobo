@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle" id="card_view_title"><img src="{{ asset(config('prefix.prefix').'/'.'img/card/title/folder_title.svg') }}" alt="" class="title_icon">名刺一括取込</h2>
    <div class="multiple_upload_container">

        <form id="multiple_upload_form" class="multiple_upload_form" action="{{ route('cardmultipleuploadpost') }}" method="POST" enctype="multipart/form-data">
            @csrf


            <input type="file" id="folder_upload" class="folder_upload" webkitdirectory multiple>
            <label for="folder_upload" class="folder_upload_label">
                <div class="folder_upload_label_text">
                    タップしてフォルダを選択
                </div>
            </label>
            <div class="analyzing_text">
                分析中
            </div>

        </form>
        <div class="upload_list_container">
            <div class="upload_list_item_container">

            </div>
            <div class="checkbox_controller">
                <div class="checkbox_description_container" data-status="new">
                    <div class="checkbox_description_item">
                        <span class="new_card_icon"></span>
                        <span class="new_card_text">
                            新規名刺
                        </span>
                    </div>
                    <div class="checkbox_description_item">
                        <span class="other_card_icon"></span>
                        <span class="other_card_text">
                            他ユーザーが登録済
                        </span>
                    </div>
                    <div class="checkbox_description_item">
                        <span class="my_card_icon"></span>
                        <span class="my_card_text">
                            マイ名刺登録済
                        </span>
                    </div>
                </div>
                <div class="checkbox_description_container close" data-status="again">
                    <div class="checkbox_description_item">
                        <span class="failed_card_icon"></span>
                        <span class="failed_card_text">
                            取込失敗
                        </span>
                        <div class="failed_card_count">
                            0
                        </div>
                    </div>
                    <div class="checkbox_description_item">
                        <span class="success_card_icon"></span>
                        <span class="success_card_text">
                            取込済
                        </span>
                        <div class="success_card_count">
                            0
                        </div>
                    </div>
                </div>
                <div class="checkbox_controller_item_container">
                    <div class="checkbox_controller_item">
                        <input type="checkbox" id="checkbox_controller_item_all" class="checkbox_controller_item_all">
                        <label for="checkbox_controller_item_all" class="checkbox_controller_item_all_label">
                            すべてにチェック
                        </label>
                    </div>
                    <div class="checkbox_controller_item">
                        <input type="checkbox" id="checkbox_controller_item_new" class="checkbox_controller_item_new">
                        <label for="checkbox_controller_item_new" class="checkbox_controller_item_new_label">
                            新規名刺のみチェック
                        </label>
                    </div> 
                </div>
                <div class="upload_button_container">
                    <div class="upload_button">
                        アップロード開始
                    </div>
                    <div class="upload_button_cancel">
                        一括取込をやめる
                    </div>
                </div>

            </div>

        </div>

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
            <input type="hidden" id="uploadedfiles_count" value="0">
            <input type="hidden" id="total_files_count" value="0">
            <input type="hidden" id="frontfiles_count" value="0">
        </div>
        <div class="error_wrapper">
            <div class="error_title">
                名刺解析エラー一覧
            </div>
            <div class="error_content error_content_clone">
                <form action="{{ route('cardmultipleuploadpost') }}" class="resend_form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- <input type="file" name="cards" class="error_file"> -->
                    <input type="hidden" name="upload_id" value="">
                    <input type="hidden" name="status" value="">
                    <input type="hidden" name="uploaded_card_id" value="">
                    <input type="hidden" name="filename" value="">
                </form>
                <div class="error_image">
                    <img src="" alt="" class="">
                </div>
                <div class="error_card_name">
                    〇〇さん
                </div>
                <div class="error_button">
                    <div class="error_button_resend">
                        再送信
                    </div>
                    <div class="error_button_delete">
                        削除
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection