@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection

@section('main')
<div class="MainElement">
    <!-- カメラ映像を表示する部分 -->
    <video id="video" autoplay style="width:320px; background:#000;"></video>
    <button id="capture">キャプチャ</button>

    <!-- キャプチャ用の canvas（非表示）-->
    <canvas id="canvas" style="display:none;"></canvas>

    <!-- 射影変換した結果などを表示する canvas -->
    <canvas id="output"></canvas>

    <!-- OpenCV.js 読み込み。完了時 onOpenCvReady() が呼ばれる -->
    <script
        async
        src="https://docs.opencv.org/4.x/opencv.js"
        onload="cv.onRuntimeInitialized = onOpenCvReady;"
        type="text/javascript">
    </script>

    <!-- メインJS -->
    <script src="{{ asset(config('prefix.prefix').'/js/test.js') }}"></script>
</div>
@endsection

@section('footer')
@endsection
