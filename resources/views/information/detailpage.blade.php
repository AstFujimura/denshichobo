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
    <a href="/edit/{{$file->ファイルパス}}" class="addbutton">
        <div class="addbuttonelement01">
            <div class="button1logo01">
                <img src="{{ asset('img/user_edit_line.svg') }}">
            </div>
            <div class="accordion1name01">
            変更
            </div>

        </div>
    </a>
        <table class="detail_table">
            <thead>
                <tr>
                    <td>日付</td>
                    <td>金額</td>
                    <td>取引先</td>
                    <td>備考</td>
                    <td></td>
                </tr>
            </thead>
        @foreach ($files as $file)
            <tbody>
                <tr>
                    <td>{{$file->日付}}</td>
                    <td>{{$file->金額}}</td>
                    <td>{{$file->取引先}}</td>
                    <td>{{$file->備考}}</td>
                    <td>
                        @if ($file->ファイル変更 == 'あり')
                            <img src="{{asset('img/download_2_line.svg')}}"  onclick="location.href='/download/{{$file->id}}';" class="download">
                        @else
                        @endif
                    </td>
                </tr>
            </tbody>
        @endforeach
        </table>    
        
@endsection 
    @section('footer')

    @endsection 


