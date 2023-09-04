@extends('layouts.template')

@section('title')
電子帳簿保存システム
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')
<h2 class="pagetitle">変更履歴</h2>
<div class="wholecontainer">

</div>
<div class="previewcontainer">

</div>
<div class="history_table_div">
    <div class="hiduke">日付</div>
    <div class="kinngaku">金額</div>
    <div class="torihikisaki">取引先</div>
    <div class="syoruikubunn pale">書類区分</div>
    <div class="hozonn pale">保存方法</div>
    <div class="bikou pale">検索ワード</div>
    <div class="filehennkou pale">ファイル変更</div>
    <div class="downloadTd pale"></div>
    <div class="extension pale">形式</div>
    <div class="preview pale"></div>
    <div class="koushinn pale">更新日時</div>
    <div class="updater pale">更新者</div>
</div>

<div class="top_table_element">
    @foreach ($files as $file)
    @if ($file->バージョン == 9999)
    <div class="delete_history_table_body">

        <div class="hidukeTd hiduke">※削除</div>
        <div class="kinngakuTd kinngaku"></div>
        <div class="torihikisaki"></div>
        <div class="syoruikubunn"></div>
        <div class="hozonn"></div>
        <div class="bikou"></div>
        <div class="filehennkou"></div>
        <div class="downloadTd">
        </div>
        <div class="extension"></div>
        <div class="preview">
        </div>
        <div class="koushinn">
            {{$file->created_at}}
        </div>
        <div class="updater">
            {{$file->更新者}}
        </div>
    </div>

    @else
    <div class="history_table_body">

        <div class="hidukeTd hiduke">{{$file->日付}}</div>
        <div class="kinngakuTd kinngaku">{{$file->金額}}</div>
        <div class="torihikisaki">{{$file->取引先}}</div>
        <div class="syoruikubunn">{{$file->書類}}</div>
        <div class="hozonn">{{$file->保存}}</div>
        <div class="bikou">{{$file->備考}}</div>
        <div class="filehennkou">{{$file->ファイル変更}}</div>
        <div class="downloadTd">
            <img src="{{asset('img/download_2_line.svg')}}" onclick="location.href='/download/{{$file->id}}';" class="download">
        </div>
        <div class="extension">{{$file->ファイル形式}}</div>
        <div class="preview">
            @if ($file->ファイル形式 == "png"||$file->ファイル形式 == "jpg"||$file->ファイル形式 == "jpeg"||$file->ファイル形式 == "bmp"||$file->ファイル形式 == "gif"||$file->ファイル形式 == "pdf")
            <img src="{{asset('img/file_search_line.svg')}}" class="download previewbutton" id="{{$file->id}}">
            @endif
        </div>
        <div class="koushinn">
            {{$file->created_at}}
        </div>
        <div class="updater">
            {{$file->更新者}}
        </div>
    </div>
    @endif


    @endforeach
</div>


@endsection
@section('footer')

@endsection