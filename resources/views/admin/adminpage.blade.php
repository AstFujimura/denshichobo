@extends('layouts.admintemplate')

@section('title')
名刺管理システム
@endsection 

@section('menuebar')
<a href="{{route('adminpageGet')}}">ユーザー一覧</a>
@endsection 




@section('main')              

                <a href="{{route('adminregistGet')}}" class="addbutton">
                    <div class="addbuttonelement01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/user_add_2_fill.svg') }}">
                        </div>
                        <div class="accordion1name01">
                          ユーザー登録
                        </div>

                    </div>
                </a>
                    <table class="admintable">
                        <thead class="adminthead">
                            <tr class="admintr">
                                <td>ユーザー名</td>
                                <td>メールアドレス</td>
                                <td>管理者権限</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                            <tr onclick="location.href='/admin/{{$user->id}}';" class="admintr">
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->管理者権限}}</td>
                            </tr>
                            @endforeach
                        </tbody>


                    </table>
                    
                    
                    
@endsection 
    @section('footer')
    @endsection 
