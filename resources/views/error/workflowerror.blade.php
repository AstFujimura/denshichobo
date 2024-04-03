@extends('layouts.flowtemplate')

@section('title')
電子帳簿保存システム
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')
<div class="flow_error_container">

    <h2 class="pagetitle">エラー</h2>
    <div>
        {{$message}}
    </div>
</div>

@endsection
@section('footer')

@endsection