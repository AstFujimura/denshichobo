<!-- @extends('layouts.template')

@section('title')
名刺管理システム
@endsection 

@section('menuebar')
検索結果
@endsection 

@section('menue')
@endsection

@section('main')
       <form action="{{route('search')}}" method="GET" class="top-form">
        @csrf
            <input type="text" name="keyword" placeholder="検索ワードを入力" value="{{$keyword}}" class="searchText">

            <button type="submit" name="Button" value="search" class="bluebutton">検索</button>

            <button type="submit" name="Button" value="company" class="sortbutton">会社</button>
            <button type="submit" name="Button" value="name" class="sortbutton">名前</button>           
            

        </form>
        

        <button onclick="window.location.href='{{ route('registGet') }}'" class="registbutton"> 新規登録</button>
        <a href="{{route('topGet')}}">もどる</a>
      
      

        <div>
            {{$count}}件表示 {{$msg}}
        </div>

        
        <table>
            <tr class="table-column">
                <td>企業名</td>
                <td>企業名カナ</td>
                <td>担当者名</td>
                <td>担当者名カナ</td>
            </tr>
        @foreach ($DBdata as $item)
            <tr class="top-list" onclick = "location.href='/top/{{$item->id}}';">
                <td>{{$item->会社名}}</td>
                <td>{{$item->会社名カナ}}</td>
                <td>{{$item->名前}}</td>
                <td>{{$item->カナ名}}</td>
            </tr>
        @endforeach
        </table>
        @endsection 
        @section('footer')
    @endsection  -->