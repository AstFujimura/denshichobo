@extends('layouts.template')

@section('title')
電子帳簿保存システム
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
<form class="searchform" action="{{route('searchPost')}}" method="get" enctype="multipart/form-data">

    <div class="searchbox">
        <div class="requirearea">
            <div class="searchelement">
                <div class="searchlabel requirelabel">取引日:</div>
                <input type="text" id="startyear" name="starthiduke" class="searchinputtext dateinputtext" autocomplete="off">
                　～　
                <input type="text" id="endyear" name="endhiduke" class="searchinputtext dateinputtext" autocomplete="off">
            </div>

            <div class="searchelement">
                <div class="searchlabel requirelabel">金額:</div>
                <input type="text" id="startkinngaku" name="startkinngaku" class="searchinputtext kinngakuinput">円
                ～　<input type="text" id="endkinngaku" name="endkinngaku" class="searchinputtext kinngakuinput">円
            </div>

            <div class="searchelement">
                <div class="searchlabel requirelabel">取引先:</div>
                <input type="text" id="torihikisaki" name="torihikisaki" class="searchinputtext torihikisakiinput" autocomplete="off">
                <div class="torihikisakiselect" id="torihikisakiselect"></div>
                <div>(部分一致)</div>
            </div>

        </div>
        <div class="nonerequirearea">
            <div class="searchelement">
                <div class="searchlabel">書類区分:</div>
                <select id="syoruikubunn" name="syoruikubunn" class="searchinputtext input-field searchselect">
                    <option></option>
                    @foreach($documents as $document)
                    <option value="{{ $document->id }}">{{ $document->書類 }}</option>
                    @endforeach
                </select>
            </div>
            <div class="searchelement">
                <div class="searchlabel">提出・受領:</div>
                <select id="teisyutu" name="teisyutu" class="searchinputtext input-field searchselect">
                    <option></option>
                    <option>提出</option>
                    <option>受領</option>
                </select>
            </div>
            <div class="searchelement">
                <div class="searchlabel">保存方法:</div>
                <select id="hozonn" name="hozonn" class="searchinputtext input-field searchselect">
                    <option></option>
                    <option>電子保存</option>
                    <option>スキャナ保存</option>
                </select>
            </div>



        </div>
        <div class="nonerequirearea">

            <div class="searchelement">
                <div class="searchlabel">検索ワード:</div>
                <input type="text" id="kennsakuword" name="kennsakuword" class="searchinputtext kensakuwordinput">
                <div>(部分一致)</div>
            </div>

            <div class="searchelement">
                <div class="searchlabel">データ:</div>

                <select id="selectdata" name="selectdata" class="searchinputtext dataselect">
                    <option>有効データ</option>
                    <option>削除データ</option>
                    <option>全件データ</option>
                </select>
            </div>
            <div class="searchelement">
                <div class="searchlabel">表示件数:</div>
                <select id="datacount" name="datacount" class="searchinputtext input">
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                    <option>500</option>
                    <option value="10000">全件</option>
                </select>
            </div>
        </div>
        <div class="nonerequirearea">
            <div class="searchelement">
                <div class="searchlabel">グループ:</div>
                <select name="group" class="searchinputtext userselectbox">
                    <option></option>
                    @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->グループ名 }}</option>
                    @endforeach
                </select>
            </div>
            @if (Auth::user()->管理 == "管理")
            <div class="searchelement">
                <div class="searchlabel">更新者:</div>
                <select name="updater" class="searchinputtext userselectbox">
                    <option></option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="searchelement">
                <div class="searchlabel">作成者:</div>
                <select name="creater" class="searchinputtext userselectbox">
                    <option></option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <input type="hidden" id="deleteOrzenken" name="deleteOrzenken" value="yukou">


        <div class="buttonarea">
            <input type="submit" value="検索" class="searchbutton">
        </div>

    </div>
</form>
<div class="wholecontainer">

</div>
<div class="previewcontainer">

</div>

<div class="info">
    <div class="showcontainer">
        <div class="showelement" id="showcount1">
            {{$startdata}}
        </div>
        <div class="showelement">
            -
        </div>
        <div class="showelement" id="showcount2">
            {{$enddata}}件
        </div>
        <div class="allshowelement">
            {{$alldata}}件中
        </div>

    </div>

    <div class="pagecontainer">
        @foreach ($paginate as $pagebutton)
        <a class={{$pagebutton["class"]}} href={{$prefix}}{{$pagebutton["a"]}}>
            {{$pagebutton["value"]}}
        </a>
        @endforeach
    </div>

    
    <div class="excelbutton">
        エクセル出力
    </div>
    
    <div class="excelerror">
        表示件数を500件以下にしてください。
    </div>

</div>
<div class="top_table_div">
    <div class="hiduke">取引日</div>
    <div class="kinngaku">金額</div>
    <div class="torihikisaki">取引先</div>
    <div class="syoruikubunn pale">書類区分</div>
    <div class="teisyutu pale">提出・受領</div>
    <div class="hozonn pale">保存方法</div>
    <div class="bikou pale">検索ワード</div>
    <div class="teisei pale">変更歴</div>
    <div class="downloadTd pale">DL.</div>
    <div class="extension pale">形式</div>
    <div class="preview pale">PV.</div>
    <div class="hennkou pale">変更</div>
    <div class="updater verypale">グループ</div>
    @if (Auth::user()->管理 == "管理")
    <div class="updater verypale">更新者</div>
    <div class="creater verypale">作成者</div>
    @endif
</div>

<div class="top_table_element">
    @foreach ($files as $file)
    @if ($file->削除フラグ == "済")
    <div class="delete_table">
        @else
        <div class="top_table_body table_selected">
            @endif
            <div class="hidukeTd hiduke">{{$file->日付}}</div>
            <div class="kinngakuTd kinngaku">{{$file->金額}}</div>
            <div class="torihikisaki">{{$file->取引先}}</div>
            <div class="syoruikubunn">{{$file->書類}}</div>
            <div class="teisyutu">{{$file->提出}}</div>
            <div class="hozonn">{{$file->保存}}</div>
            <div class="bikou">{{$file->備考}}</div>
            <div class="teisei">@if ($file->バージョン != 1)
                <div class="maru" onclick="location.href='{{$prefix}}/history/{{$file->過去データID}}';">〇</div>
                @else

                @endif
            </div>
            <div class="downloadTd">
                <img src="{{ asset($prefix.'/'.'img/download_2_line.svg')}}" id='{{$prefix}}/download/{{$file->id}}' class="download downloadbutton">
            </div>
            <div class="extension">{{$file->ファイル形式}}</div>
            <div class="preview">
                @if ($file->ファイル形式 == "png"||$file->ファイル形式 == "PNG"||$file->ファイル形式 == "jpg"||$file->ファイル形式 == "jpeg"||$file->ファイル形式 == "JPG"||$file->ファイル形式 == "jpe"||$file->ファイル形式 == "JPEG"||$file->ファイル形式 == "bmp"||$file->ファイル形式 == "gif"||$file->ファイル形式 == "pdf"||$file->ファイル形式 == "PDF")
                <img src="{{ asset($prefix.'/'.'img/file_search_line.svg')}}" class="download previewbutton" id="{{$file->id}}">
                @endif
            </div>
            <div class="hennkou">
                @if ($file->削除フラグ != "済")
                <img src="{{ asset($prefix.'/'.'img/transfer_3_fill.svg')}}" class="download" onclick="location.href='{{$prefix}}/edit/{{$file->過去データID}}';">

                @endif
            </div>
            @if ($file->グループID < 100000)
            <div class="updater"></div>
            @else
            <div class="updater">{{$file->グループ名}}</div>
            @endif

            @if (Auth::user()->管理 == "管理")
            @php
            $updater = str_replace('(削除ユーザー)', '', $file->更新者);
            $creater = str_replace('(削除ユーザー)', '', $file->作成者);
            @endphp
            <div class="updater">{{$updater}}</div>
            <div class="creater">{{$creater}}</div>
            @endif
        </div>
        @endforeach
    </div>



    @endsection
    @section('footer')

    @endsection