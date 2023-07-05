@extends('layouts.admintemplate')

@section('title')
名刺管理システム
@endsection 

@section('menuebar')
選択ページ
@endsection 

@section('menue')

@endsection


@section('main')

<form action="{{route('adminpageGet')}}" method="Get" class="button-choice">
@csrf
<button type="submit" name="adminpage" value="adminpage" class="adminbutton">管理者画面</button>
</form>

<form action="{{route('topGet')}}" method="Get" class="button-choice">
@csrf
<button type="submit" name="superuser" value="superuser" class="adminbutton">管理者として使用</button>
</form>

@endsection 
    @section('footer')
    @endsection 
