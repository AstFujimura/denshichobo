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
                    <td>ファイル名</td>
                    <td>保存日</td>
                </tr>
            </thead>
        @foreach ($files as $file)
            <tbody>
                <tr onclick="location.href='/download/{{$file->id}}';">
                    <td>{{$file->ファイルパス}}</td>
                    <td>{{$file->created_at}}</td>
                </tr>
            </tbody>
        @endforeach
        </table>    
        
@endsection 
    @section('footer')

    @endsection 


