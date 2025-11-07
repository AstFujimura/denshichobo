@extends('layouts.transcribetemplate')

@section('title')
文書抽出
@endsection




@section('main')
<div class="MainElement" id="transcribe_title">
    {{-- <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/transcribe_title/transcribe.svg') }}"
            alt="" class="title_icon">文書抽出</h2> --}}
    <div class="transcribe_container">
        <form action="{{route('transcribepost')}}" method="post" enctype="multipart/form-data" id="transcribe_form" >
            @csrf
            <input type="file" name="file" id="file_input" accept=".pdf,image/*">
            <button type="submit">抽出</button>
        </form>
        <div id="result_preview" class="pdf-preview">
            <p class="placeholder">ここをクリックまたはファイルをドラッグ＆ドロップしてください</p>
          </div>
        <div id="gemini_output" class="mt-3"></div>
    </div>
</div>
@endsection

@section('footer')
@endsection