@extends('layouts.admintemplate')

@section('title')
電子帳簿保存システム　管理画面
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2>パスワードリセット</h2>
    <div class="resetmessage">新しいパスワードはこれ以降確認できないため保存してください。</div>              
    <div class="resetcontainer">
        <div class="resetpassword">
            {{$password}}
        </div>   
    </div>
               
                    
@endsection 
    @section('footer')
    @endsection 
