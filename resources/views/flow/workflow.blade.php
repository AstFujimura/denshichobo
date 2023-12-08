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
<input type="hidden" class="element" data-column="1" data-row="1">
<input type="hidden" class="element" data-column="1" data-row="2">
<input type="hidden" class="element" data-column="2" data-row="2">
<input type="hidden" class="element" data-column="3" data-row="2">
<input type="hidden" class="element" data-column="1" data-row="3">
<input type="hidden" class="element" data-column="1" data-row="4">
<input type="hidden" class="element" data-column="1" data-row="5">
<input type="hidden" class="element" data-column="1" data-row="5">
<input type="hidden" class="element" data-column="3" data-row="3">


<input type="hidden" class="line" data-startcolumn="1" data-startrow="1" data-endcolumn="2" data-endrow="2">
<input type="hidden" class="line" data-startcolumn="1" data-startrow="1" data-endcolumn="3" data-endrow="2">
<input type="hidden" class="line" data-startcolumn="2" data-startrow="2" data-endcolumn="1" data-endrow="3">
<input type="hidden" class="line" data-startcolumn="1" data-startrow="2" data-endcolumn="3" data-endrow="3">
<input type="hidden" class="line" data-startcolumn="3" data-startrow="2" data-endcolumn="1" data-endrow="3">
<input type="hidden" class="line" data-startcolumn="1" data-startrow="3" data-endcolumn="1" data-endrow="4">
<input type="hidden" class="line" data-startcolumn="1" data-startrow="2" data-endcolumn="1" data-endrow="3">
<input type="hidden" class="line" data-startcolumn="1" data-startrow="1" data-endcolumn="1" data-endrow="2">
<div class="grid">

</div>


@endsection
@section('footer')
@endsection