@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">
    <video id="video" autoplay></video>
    <button id="capture">キャプチャ</button>
    <canvas id="canvas"></canvas>
    <canvas id="output"></canvas>
    <script async src="https://docs.opencv.org/4.x/opencv.js" onload="cv.onRuntimeInitialized = onCvReady();" type="text/javascript"></script>
    <script src="{{ asset(config('prefix.prefix').'/js/test.js') }}"></script>
</div>
@endsection

@section('footer')
@endsection