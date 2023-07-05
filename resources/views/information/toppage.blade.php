@extends('layouts.template')

@section('title')
名刺管理システム
@endsection 

@section('menuebar')
<div>
    {{$year}}年
</div>
<div>
{{$month}}月
</div>

@endsection 

@section('menue')


@endsection


@section('main')



        <table>
            <thead>
                <tr>
                    <td>日</td>
                    <td>曜</td>
                    <td>使用者</td>
                    <td>訪問先</td>
                    <td>高速</td>
                    <td>出発時</td>
                    <td></td>
                    <td>帰着時</td>
                    <td></td>
                    <td>着メーター</td>
                    <td>給油</td>
                    <td>SS名</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr class="top-list">
                    <td>{{$d['日']}}</td>
                    <td>{{$d['曜']}}</td>
                    <td>{{$d['使用者']}}</td>
                    <td>{{$d['訪問先']}}</td>
                    <td>{{$d['高速']}}</td>
                    <td>{{$d['出発時']}}</td>
                    <td>{{$d['出発分']}}</td>
                    <td>{{$d['到着時']}}</td>
                    <td>{{$d['到着分']}}</td>
                    <td>{{$d['着メーター']}}</td>
                    <td>{{$d['給油']}}</td>
                    <td>{{$d['SS']}}</td>

                </tr>
                @endforeach

            </tbody>
        </table>

        
@endsection 
    @section('footer')

    @endsection 


