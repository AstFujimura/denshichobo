@extends('layouts.template')

@section('title')
電子帳簿保存システム
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2>帳簿一覧</h2>
<form class="searchform" action="{{route('searchPost')}}" method="get" enctype="multipart/form-data">

    <div class="searchbox">
        <div class="searchelement">
            <div class="searchlabel">日付:</div>
            <input type="text" id="startyear" name="starthiduke" class="searchinputtext ">
            ~
            <input type="text" id="endyear" name="endhiduke" class="searchinputtext ">
        </div>
        <div class="searchelement">
            <div class="searchlabel">金額:</div>
            <input type="text" id="startkinngaku" class="searchinputtext kinngakuinput">円
            ~<input type="text" id="endkinngaku" class="searchinputtext kinngakuinput">円
        </div>
        <div class="searchelement">
            <div class="searchlabel">取引先:</div>
            <input type="text" id="torihikisaki" class="searchinputtext torihikisakiinput">
        </div>
        <div class="searchelement">
            <div class="searchlabel">書類区分:</div>
            <input type="text" id="syoruikubunn" class="searchinputtext syoruikubunninput">
        </div>
        <input type="submit" value="検索" class="searchbutton">
    </div>
</form>
        <table class="top_table">
            <thead>
                <tr>
                    <td>日付</td>
                    <td>金額</td>
                    <td>取引先</td>
                    <td>書類区分</td>
                    <td>備考</td>
                    <td>訂正歴</td>
                    <td></td>
                    <td></td>
                </tr>
            </thead>
        @foreach ($files as $file)
            <tbody>
                <tr>
                    <td class="hidukeTd">{{$file->日付}}</td>
                    <td class="kinngakuTd">{{$file->金額}}</td>
                    <td>{{$file->取引先}}</td>
                    <td>{{$file->書類}}</td>
                    <td>{{$file->備考}}</td>
                    <td>@if ($file->バージョン != 1)
                        あり
                        @else
                        なし
                        @endif
                    </td>
                    <td>
                        <img src="{{asset('img/download_2_line.svg')}}"  onclick="location.href='/download/{{$file->id}}';" class="download">
                    </td>
                    <td onclick="location.href='/detail/{{$file->過去データID}}';" class="detail">
                        詳細
                    </td>
                </tr>
            </tbody>
        @endforeach
        </table>    
        
@endsection 
    @section('footer')

    @endsection 


