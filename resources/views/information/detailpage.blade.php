@extends('layouts.template')

@section('title')
変更結果 |TAMERU
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2>変更結果</h2>
    <div class="menubar">
            <a href="/edit/{{$file->過去データID}}" class="addbutton">
                <div class="addbuttonelement01">
                    <div class="button1logo01">
                        <img src="{{ asset('img/user_edit_line.svg') }}">
                    </div>
                    <div class="accordion1name01">
                    変更
                    </div>

                </div>
            </a>
            <a href="/history/{{$file->過去データID}}" class="addbutton">
                <div class="addbuttonelement01">
                    <div class="button1logo01">
                        <img src="{{ asset('img/user_edit_line.svg') }}">
                    </div>
                    <div class="accordion1name01">
                    履歴
                    </div>

                </div>
            </a>
            <div class="detail_download">
                <img src="{{ asset($prefix.'/'.'img/download_2_line.svg')}}"  onclick="location.href='/download/{{$file->id}}';" class="download">
            </div>

    </div>


    <table class="detail_table">
            <tbody>
                <tr>
                    <td>日付</td><td class="hidukeTd">{{$file->日付}}</td>
                </tr>
                <tr>
                    <td>金額</td><td class="kinngakuTd">{{$file->金額}}</td>
                </tr>
                <tr>
                    <td>取引先</td><td>{{$file->取引先}}</td>
                </tr>
                <tr>
                    <td>書類区分</td><td>{{$file->書類}}</td>
                </tr>
                <tr>
                    <td>最終更新者</td><td>{{$file->users->name}}</td>
                </tr>
                <tr>
                    <td>変更履歴</td><td>{{$file->ファイル変更}}</td>
                </tr>
                <tr>
                    <td>検索ワード</td><td>{{$file->備考}}</td>
                </tr>
                <tr>
                    <td>保存方法</td><td>{{$file->保存}}</td>
                </tr>
                <tr>
                    <td>最終更新日時</td><td>{{$file->created_at}}</td>
                </tr>
            </tbody>

    </table>


        
@endsection 
    @section('footer')

    @endsection 


