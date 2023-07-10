@extends('layouts.template')

@section('title')
電子帳簿保存システム
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
        <table class="top_table">
            <thead>
                <tr>
                    <td>日付</td>
                    <td>金額</td>
                    <td>取引先</td>
                    <td>備考</td>
                    <td>訂正歴</td>
                    <td></td>
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
                    <td><input type="checkbox"></td>
                </tr>
            </tbody>
        @endforeach
        </table>    
        
@endsection 
    @section('footer')

    @endsection 


