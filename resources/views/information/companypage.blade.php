@extends('layouts.template')

@section('title')
名刺管理システム
@endsection 

@section('menuebar')
会社一覧
@endsection 

@section('menue')
@endsection


@section('main')

    <span class="maincount">
            {{$count}}件表示
    </span>

    <form action="{{route('companysearch')}}" method="GET" class="top-form" id = "companypage">
        @csrf
            <input type="text" name="keyword" placeholder="検索ワードを入力" class="searchText">
            <button type="submit" name="Button" value="search" class="bluebutton">検索</button>

    </form>





    <div class="wrap-tab">
    <input id="tab-radio1" class="tab-radio" name="tab" type="radio" checked>
    <input id="tab-radio2" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio3" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio4" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio5" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio6" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio7" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio8" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio9" class="tab-radio" name="tab" type="radio">
    <input id="tab-radio10" class="tab-radio" name="tab" type="radio">
    
    <ul class="list-tab-label">
        <li>
        <label id="tab-label1" class="tab-label" for="tab-radio1">あ行</label>
        </li>
        <li>
        <label id="tab-label2" class="tab-label" for="tab-radio2">か行</label>
        </li>
        <li>
        <label id="tab-label3" class="tab-label" for="tab-radio3">さ行</label>
        </li>
        <li>
        <label id="tab-label4" class="tab-label" for="tab-radio4">た行</label>
        </li>
        <li>
        <label id="tab-label5" class="tab-label" for="tab-radio5">な行</label>
        </li>
        <li>
        <label id="tab-label6" class="tab-label" for="tab-radio6">は行</label>
        </li>
        <li>
        <label id="tab-label7" class="tab-label" for="tab-radio7">ま行</label>
        </li>
        <li>
        <label id="tab-label8" class="tab-label" for="tab-radio8">や行</label>
        </li>
        <li>
        <label id="tab-label9" class="tab-label" for="tab-radio9">ら行</label>
        </li>
        <li>
        <label id="tab-label10" class="tab-label" for="tab-radio10">わ行</label>
        </li>
    </ul>
    <div class="wrap-tab-content">
        <table class="companytable">
            <thead class="top-thead">
                <tr class="companytr companythead ">
                    <td class="comp">会社名</td>
                    <td class="compKana">会社名カナ</td>
                    <td class="post_address">郵便番号</td>
                    <td class="address">住所</td>
                    <td class="phone_number">電話番号</td>   
                </tr>
            </thead>
            <tbody>
            @foreach ($company as $data)
                <tr class="companytr companytbody" onclick="location.href='/company/{{$data->会社コード}}';">
                    <td class="comp">{{$data->会社名}}</td>
                    <td class="compKana">{{$data->会社名カナ}}</td>
                    <td class="post_address">{{$data->郵便番号}}</td>
                    <td class="address">{{$data->住所}}</td>
                    <td class="phone_number">{{$data->電話番号}}</td>   
                    
                </tr>
            
            @endforeach
            </tbody>
        </table>
    </div>

        
@endsection 
    @section('footer')
    @endsection 
