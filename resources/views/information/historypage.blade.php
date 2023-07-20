@extends('layouts.template')

@section('title')
電子帳簿保存システム
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2>変更履歴</h2>
        <table class="history_table">
            <thead>
                <tr class="history_table_column">
                    <td>日付</td>
                    <td>金額</td>
                    <td>取引先</td>
                    <td>書類区分</td>
                    <td>ファイル変更</td>
                    <td>更新者</td>
                    <td>検索ワード</td>
                    <td>保存方法</td>
                    <td></td>
                    <td>更新日時
                    </td>
                </tr>
            </thead>
        @foreach ($files as $file)
            <tbody>
                <tr>
                    <td class="hidukeTd">{{$file->日付}}</td>
                    <td class="kinngakuTd">{{$file->金額}}</td>
                    <td>{{$file->取引先}}</td>
                    <td>{{$file->書類区分}}</td>
                    <td>{{$file->ファイル変更}}</td>
                    <td>{{$file->users->name}}</td>
                    <td>{{$file->備考}}</td>
                    <td>{{$file->保存}}</td>
                    <td>
                        <img src="{{asset('img/download_2_line.svg')}}"  onclick="location.href='/download/{{$file->id}}';" class="download">
            
                    </td>
                    <td>{{$file->created_at}}</td>

                </tr>
            </tbody>
        @endforeach
        </table>    
        
@endsection 
    @section('footer')

    @endsection 


