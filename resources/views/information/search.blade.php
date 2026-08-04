@extends('layouts.template')

@section('title')
TAMERU ~電子帳簿保存
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')

<div class="loader">
    <img src="{{ asset($prefix.'/'.'img/loading.gif')}}">
    <div class="searchcomment">検索中です</div>
</div>

<h2 class="pagetitle">帳簿一覧</h2>
@php
    $searchDetailOpen = request()->filled('syoruikubunn')
        || request()->filled('teisyutu')
        || request()->filled('hozonn')
        || (request('selectdata') && request('selectdata') !== '有効データ')
        || (request('datacount') && !in_array(request('datacount'), ['25', '50', '100', '500'], true))
        || request()->filled('group')
        || request()->filled('updater')
        || request()->filled('creater');
@endphp
<form class="searchform" action="{{route('searchPost')}}" method="get" enctype="multipart/form-data">
    @include('information.partials.searchbox', [
        'datacountZenkenValue' => '100000',
        'searchDetailOpen' => $searchDetailOpen,
        'starthiduke' => $starthiduke,
        'endhiduke' => $endhiduke,
        'starttourokubi' => $starttourokubi ?? '',
        'endtourokubi' => $endtourokubi ?? '',
        'startkinngaku' => $startkinngaku,
        'endkinngaku' => $endkinngaku,
        'torihikisaki' => $torihikisaki,
        'kennsakuword' => $kennsakuword,
        'teisyutu' => $teisyutu,
        'jyuryo' => $jyuryo,
        'dennshinone' => $dennshinone,
        'dennshi' => $dennshi,
        'scan' => $scan,
        'yukou' => $yukou,
        'delete' => $delete,
        'zenken' => $zenken,
        'k25' => $k25,
        'k50' => $k50,
        'k100' => $k100,
        'k500' => $k500,
        'k100000' => $k100000,
        'deleteOrzenken' => $deleteOrzenken,
    ])
</form>
<div class="wholecontainer">

</div>
<div class="previewcontainer">

</div>
@include('information.partials.ledger-toolbar')
@include('information.partials.ledger-table')

@endsection
    @section('footer')

    @endsection