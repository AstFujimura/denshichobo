@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">
    <div class="crop_controller_container">
        <div class="crop_controller_content">
            <img src="" class="croppable_image">
            <div class="crop_complete_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/complete.svg') }}" alt="">
            </div>
            <div class="crop_cancel_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/cancel.svg') }}" alt="">
            </div>
        </div>
    </div>
    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">名刺登録</h2>
   
    <div class="card_regist_container">
        <form action="{{ route('cardregistpost') }}" method="post" enctype="multipart/form-data">
            @csrf            
            <div class="crop_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/card/crop.svg') }}" alt="">
                切り取り
            </div>
            <input type="file" name="card_file_front" id="card_file_front" class="card_file_front">
            <label for="card_file_front" class="card_file_front_label">
                <div class="cropped_image_container">
                    <div class="cropped_image_container_text">
                        タップして画像を読みこんでください
                    </div>
                </div>
            </label>
            <!-- <canvas id="canvas"></canvas> -->
            <div class="send_button" id="send_button">
                AI読み取り
            </div>

        </form>
    </div>
</div>
@endsection

@section('footer')
@endsection