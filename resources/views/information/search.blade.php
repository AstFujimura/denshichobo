@extends('layouts.template')

@section('title')
電子帳簿保存システム
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2 class="pagetitle">検索結果</h2>
<form class="searchform" action="{{route('searchPost')}}" method="get" enctype="multipart/form-data">

    <div class="searchbox">
        <div class="requirearea">
            <div class="searchelement">
                <div class="searchlabel requirelabel">日付:</div>
                <input type="text" id="startyear" name="starthiduke" value="{{$starthiduke}}" class="searchinputtext dateinputtext">
                ~
                <input type="text" id="endyear" name="endhiduke" value="{{$endhiduke}}" class="searchinputtext dateinputtext">
            </div>
            <div class="searchelement">
                <div class="searchlabel requirelabel">金額:</div>
                <input type="text" id="startkinngaku" name="startkinngaku" value="{{$startkinngaku}}" class="searchinputtext kinngakuinput">円
                ~<input type="text" id="endkinngaku" name="endkinngaku" value="{{$endkinngaku}}" class="searchinputtext kinngakuinput">円
            </div>
            <div class="searchelement">
            <div class="searchlabel requirelabel">取引先:</div>
                <input type="text" id="torihikisaki" name="torihikisaki" value="{{$torihikisaki}}" class="searchinputtext torihikisakiinput">
                <div>(部分一致)</div>
            </div>
        </div>
        <div class="nonerequirearea">
            <div class="searchelement">
                <div class="searchlabel">書類区分:</div>
                <select id="syoruikubunn" name="syoruikubunn"class="searchinputtext input-field">
                    <option {{$none}}></option>
                    <option {{$seikyusyo}}>請求書</option>
                    <option {{$nohinnsyo}}>納品書</option>
                    <option {{$keiyakusyo}}>契約書</option>
                    <option {{$mitumorisyo}}>見積書</option>
                </select>
            </div>
            <div class="searchelement">
                <div class="searchlabel">保存方法:</div>
                <select id="hozonn" name="hozonn" class="searchinputtext input-field">
                    <option {{$dennshinone}}></option>
                    <option {{$dennshi}}>電子保存</option>
                    <option {{$scan}}>スキャナ保存</option>
                </select>
            </div>
            <div class="searchelement">
                <div class="searchlabel">検索ワード:</div>
                <input type="text" id="kennsakuword" name="kennsakuword" value="{{$kennsakuword}}" class="searchinputtext kensakuwordinput">
                <div>(部分一致)</div>
            </div>
        </div>

        <div class="buttonarea">
            <input type="submit" value="検索" class="searchbutton">
        </div>
    </div>
</form>
<div class="info">
            <div class="count">
                {{$count}}件 表示
            </div>
            <div class="deletecount">
                {{$deletecount}}件 表示
            </div>
            <div class="notdeletecount selected">
                {{$notdeletecount}}件 表示
            </div>
            <div class="select">
                <select id="select" class="dataselect">
                    <option>有効データ</option>
                    <option>削除データ</option>
                    <option>全件データ</option>
                </select>
            </div>
            
        </div>
        <div class="top_table_div">
            <div class="hiduke">日付</div>
            <div class="kinngaku">金額</div>
            <div class="torihikisaki">取引先</div>
            <div class="syoruikubunn">書類区分</div>
            <div class="bikou">検索ワード</div>
            <div class="teisei">訂正歴</div>
            <div class="hozonn">保存方法</div>
            <div class="downloadTd"></div>
            <div class="hennkou"></div>
            <div class="delete">削除</div>
        </div>

        <div class="top_table_element">
            @foreach ($files as $file)
                @if ($file->削除フラグ != "済")
                <div class="top_table_body table_selected">    
                @else
                <div class="delete_table">
                @endif
                    <div class="hidukeTd hiduke">{{$file->日付}}</div>
                    <div class="kinngakuTd kinngaku">{{$file->金額}}</div>
                    <div class="torihikisaki">{{$file->取引先}}</div>
                    <div class="syoruikubunn">{{$file->書類}}</div>
                    <div class="bikou">{{$file->備考}}</div>
                    <div class="teisei">@if ($file->バージョン != 1)
                        <div class="maru" onclick="location.href='/history/{{$file->過去データID}}';">〇</div>
                        @else
                        
                        @endif
                    </div>
                    <div class="hozonn">{{$file->保存}}</div>
                    <div class="downloadTd">
                        <img src="{{asset('img/download_2_line.svg')}}"  onclick="location.href='/download/{{$file->id}}';" class="download">
                    </div class="syousai">
                    <div class="hennkou">
                        @if ($file->削除フラグ != "済")
                            <div class="detail"  onclick="location.href='/edit/{{$file->過去データID}}';">
                                変更
                            </div>
                        @endif
                    </div>
                    <div class="delete">{{$file->削除フラグ}}</div>
                </div>
            @endforeach
        </div>
        
@endsection 
    @section('footer')

    @endsection 


