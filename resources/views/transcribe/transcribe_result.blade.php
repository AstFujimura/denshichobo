@extends('layouts.transcribetemplate')

@section('title')
文書抽出
@endsection




@section('main')
<div class="MainElement" id="transcribe_title">
    @if (!empty($image_paths))
    @foreach ($image_paths as $img)
    <div class="mb-3">
        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($img)) }}" class="img-fluid" />
    </div>
    @endforeach
    @endif
</div>
@endsection

@section('footer')
@endsection