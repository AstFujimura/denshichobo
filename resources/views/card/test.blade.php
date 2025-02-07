@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection

@section('main')
<div class="MainElement">
    <!-- カメラ映像を表示する video -->
    <video id="video" autoplay style="width: 320px; height: auto; background: #000;"></video>
    <button id="capture">キャプチャ</button>
    <!-- キャプチャ用、一時的に表示はしない canvas -->
    <canvas id="canvas" style="display:none;"></canvas>
    <!-- 結果を表示する canvas -->
    <canvas id="output"></canvas>

    <!-- OpenCV.js 読み込み。ロード完了時に onOpenCvReady() が呼ばれる。 -->
    <script 
        async 
        src="https://docs.opencv.org/4.x/opencv.js"
        onload="cv.onRuntimeInitialized = onOpenCvReady;"
        type="text/javascript">
    </script>

    <!-- メインのJS(例: test.js) -->
    <script src="{{ asset(config('prefix.prefix').'/js/test.js') }}"></script>
</div>
@endsection

@section('footer')
@endsection
