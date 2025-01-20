@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/home.svg') }}" alt="" class="title_icon">名刺登録</h2>
    <div class="card_regist_container">
        <form action="{{ route('cardregistpost') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="file" name="card_file_front" id="card_file_front">
            <button type="submit">登録</button>
        </form>
    </div>
</div>
@endsection

@section('footer')
@endsection