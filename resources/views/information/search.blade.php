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
        <div class="searcharea">
            <div class="searchelement">
                <div class="searchlabel">日付:</div>
                <input type="text" id="startyear" name="starthiduke" value="{{$starthiduke}}" class="searchinputtext dateinputtext">
                ~
                <input type="text" id="endyear" name="endhiduke" value="{{$endhiduke}}" class="searchinputtext dateinputtext">
            </div>
            <div class="searchelement">
                <div class="searchlabel">金額:</div>
                <input type="text" id="startkinngaku" name="startkinngaku" value="{{$startkinngaku}}" class="searchinputtext kinngakuinput">円
                ~<input type="text" id="endkinngaku" name="endkinngaku" value="{{$endkinngaku}}" class="searchinputtext kinngakuinput">円
            </div>
            <div class="searchelement">
            <div class="searchlabel">取引先:</div>
                <input type="text" id="torihikisaki" name="torihikisaki" value="{{$torihikisaki}}" class="searchinputtext torihikisakiinput">
                <div>(部分一致)</div>
            </div>
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
        <table class="top_table">
            <thead>
                <tr class="top_table_column">
                    <td class="hiduke">日付</td>
                    <td class="kinngaku">金額</td>
                    <td class="torihikisaki">取引先</td>
                    <td class="syoruikubunn">書類区分</td>
                    <td class="bikou">検索ワード</td>
                    <td class="teisei">訂正歴</td>
                    <td class="downloadTd"></td>
                    <td class="hennkou"></td>
                </tr>
            </thead>
        @foreach ($files as $file)
            <tbody>
                <tr class="top_table_body">
                    <td class="hidukeTd hiduke">{{$file->日付}}</td>
                    <td class="kinngakuTd kinngaku">{{$file->金額}}</td>
                    <td class="torihikisaki">{{$file->取引先}}</td>
                    <td class="syoruikubunn">{{$file->書類}}</td>
                    <td class="bikou">{{$file->備考}}</td>
                    <td class="teisei">@if ($file->バージョン != 1)
                        <div class="maru" onclick="location.href='/history/{{$file->過去データID}}';">〇</div>
                        @else
                        
                        @endif
                    </td>
                    <td class="downloadTd">
                        <img src="{{asset('img/download_2_line.svg')}}"  onclick="location.href='/download/{{$file->id}}';" class="download">
                    </td class="syousai">
                    <td class="hennkou">
                        <div class="detail"  onclick="location.href='/edit/{{$file->過去データID}}';">
                            変更
                        </div>
                    </td>
                </tr>
            </tbody>
        @endforeach
        </table>    
        
@endsection 
    @section('footer')

    @endsection 


