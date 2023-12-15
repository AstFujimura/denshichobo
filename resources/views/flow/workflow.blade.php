@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>ワークフロー登録</h2>
<div class="element_input">
    <input type="hidden" class="route" id="route" data-routecount="1">
    <input type="hidden" class="element" data-column="1" data-row="1" data-last="none">
    <input type="hidden" class="element" data-column="1" data-row="2" data-last="last">

    <input type="hidden" class="line" data-startcolumn="1" data-startrow="1" data-endcolumn="1" data-endrow="2">
</div>





<div class="grid">
</div>


@endsection
@section('footer')
@endsection