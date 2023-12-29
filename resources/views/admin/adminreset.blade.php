@extends('layouts.admintemplate')

@section('title')
パスワードリセット | TAMERU
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
