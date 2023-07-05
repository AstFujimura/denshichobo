
@extends('layouts.template')

@section('title')
名刺管理システム
@endsection 

@section('menuebar')
<a href="{{route('topGet')}}" class="nav">名刺一覧</a> > 検索結果
@endsection 

@section('menue')@endsection

@section('main')

    <span class="maincount">
            {{$count}}件表示
    </span>
    <form action="{{route('search')}}" method="GET" class="top-form" id = "toppage">
        @csrf
            <input type="text" name="keyword" value="{{$keyword}}" placeholder="検索ワードを入力" class="searchText"　>
            <button type="submit" name="Button" value="search" class="bluebutton">検索</button>

    </form>

    <!-- <input id="P-radio" class="P-radio" name="PorC-radio" type="radio" checked>
    <label id="P-label" class="P-label" for="P-radio">個人</label>
    <input id="C-radio" class="C-radio" name="PorC-radio" type="radio">
    <label id="C-label" class="C-label" for="C-radio">企業</label> -->

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
            <table class="top-table">
                <thead class="top-thead">
                    <tr id="table-colomn-company" class="table-column">
                        <td class="comp">
                            企業名
                        </td>
                        <td class="compKana">
                            企業名カナ
                        </td>
                        <td  class="nam">
                            氏名
                        </td>
                        <td class="namKana">
                            カナ
                        </td>
                        <td class="depart">
                            部署名
                        </td>
                        <td class="posi none">
                            役職名
                        </td>
                        <td class="Te">
                            電話番号
                        </td>
                        <td class="busicard">
                            名刺ファイル
                        </td>
                    </tr>
                    <tr id="table-colomn-person" class="table-column PorC-highlight">
                    <td  class="nam">
                            氏名
                        </td>
                        <td class="namKana">
                            カナ
                        </td>
                        <td class="comp">
                            企業名
                        </td>
                        <td class="compKana">
                            企業名カナ
                        </td>
                        <td class="depart">
                            部署名
                        </td>
                        <td class="posi none">
                            役職名
                        </td>
                        <td class="Te">
                            携帯電話番号
                        </td>
                        <td class="busicard">
                            名刺ファイル
                        </td>
                    </tr>
                </thead>
                <tbody class="content">
                @foreach ($name as $data)
                        <tr class="person-content top-list PorC-highlight" onclick="location.href='/top/{{$data->id}}';">
                            <td class="nam">{{$data->名前}}</td>
                            <td class="namKana">{{$data->カナ名}}</td>
                            <td class="comp">{{$data->companies->会社名}}</td>
                            <td class="compKana">{{$data->companies->会社名カナ}}</td>
                            <td class="depart">{{$data->部署名}}</td>
                            <td class="posi none">{{$data->役職名}}</td>
                            <td class="Te">{{$data->携帯電話番号}}</td>
                            <td class="busicard">@if ($data->名刺ファイル)
                                あり
                                @else
                                なし
                                @endif
                            </td>
                        </tr>
                    
                    @endforeach
                    @foreach ($company as $data)
                        
                        <tr class="company-content top-list" onclick="location.href='/top/{{$data->id}}';">
                            <td class="comp">{{$data->companies->会社名}}</td>
                            <td class="compKana">{{$data->companies->会社名カナ}}</td>
                            <td class="nam">{{$data->名前}}</td>
                            <td class="namKana">{{$data->カナ名}}</td>
                            <td class="depart">{{$data->部署名}}</td>
                            <td class="posi none">{{$data->役職名}}</td>
                            <td class="Te">{{$data->電話番号}}</td>
                            <td class="busicard">@if ($data->名刺ファイル)
                                あり
                                @else
                                なし
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        
    </div>
    
    </div>
        
@endsection 