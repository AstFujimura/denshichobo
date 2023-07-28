@extends('layouts.admintemplate')

@section('title')
電子帳簿保存システム　管理画面
@endsection 

@section('menuebar')

@endsection 

@section('menue')


@endsection


@section('main')
<h2>ユーザー一覧</h2>              
    <div class="admin_top_table_div">
        <div class="name">ユーザー名</div>
        <div class="email">email</div>
        <div class="status">アクセス権限</div>
        <div class="hennkou"></div>
    </div>
    <div class="admin_top_table_element">
        @foreach ($users as $user)
            <div class="admin_top_table_body">    

                <div class="name">{{$user->name}}</div>
                <div class="email">{{$user->email}}</div>
                <div class="status">{{$user->管理}}</div>
                <div class="hennkou">
                    <div class="detail"  onclick="location.href='/admin/edit/{{$user->id}}';">
                        変更
                    </div>
                </div>
            </div>
        @endforeach
    </div>
                    
                    
@endsection 
    @section('footer')
    @endsection 
