@extends('layouts.admintemplate')

@section('title')
グループ編集 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>{{$group->グループ名}}　ユーザー設定</h2>
<div class="bread_crumb_container">

    <div class="bread_crumb_content">
        <a>ユーザー設定</a>
    </div>
    <div class="bread_crumb_content">
        <a href="{{ route('admingroupregistGet') }}">グループ一覧</a>
    </div>
    <div class="bread_crumb_content">
        <a href="{{ route('adminGet') }}">管理画面一覧</a>
    </div>

</div>
<form method="post" action="{{route('admingroupuserPost',['id'=>$group->id])}}" enctype="multipart/form-data"
    id="admin_group_user_form">
    @csrf
    <span class="savemessage">※更新ボタンを押して変更を反映させてください</span>
    <div class="link_container">

        <button class="groupuser_change_button">
            更新
        </button> 
    </div>
    <input type="hidden" id="save" value="save">
    <div class="groupuser_addbutton" id="groupuser_addbutton">
        + 追加
    </div>

    <div class="groupusertable">
        <div class="groupusertable_header">
            <div class="groupuser_name">
                ユーザー名
            </div>
            <div class="groupuser_position">
                役職名
            </div>
            <div class="groupuser_delete">
                削除
            </div>
        </div>

        <div class="groupusertable_body">
            @foreach ($users as $index => $user)
            <div class="groupusertable_tr correct_table">
                <div class="groupuser_name">
                    <select class="groupuser_select groupuser_select_user" data-default_userid="{{$user->id}}"
                        name="user{{ $loop->index + 1}}">
                        @foreach ($allusers as $alluser)
                        <option value="{{$alluser->id}}">{{$alluser->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="groupuser_position">
                    <select class="groupuser_select groupuser_select_position" data-default_positionid="{{$user->役職ID}}"
                        name="position{{ $loop->index + 1}}">
                        <option></option>
                        @foreach ($positions as $position)
                        <option value="{{$position->id}}">{{$position->役職}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="groupuser_delete">
                    <div class="groupuser_delete_button">
                        <img src="{{ asset($prefix.'/'.'img/delete.svg')}}" class="delete_icon">
                        <span>削除</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>




    <div class="groupusertable_tr dummy_table">
        <div class="groupuser_name">
            <select class="groupuser_select groupuser_select_user">
                <option></option>
                @foreach ($allusers as $alluser)
                <option value="{{$alluser->id}}">{{$alluser->name}}</option>
                @endforeach
            </select>
        </div>

        <div class="groupuser_position">
            <select class="groupuser_select groupuser_select_position">
                <option></option>
                @foreach ($positions as $position)
                <option value="{{$position->id}}">{{$position->役職}}</option>
                @endforeach
            </select>
        </div>
        <div class="groupuser_delete">
            <div class="groupuser_delete_button">
                <img src="{{ asset($prefix.'/'.'img/delete.svg')}}" class="delete_icon">
                <span>削除</span>
            </div>
        </div>
    </div>
    <input type="hidden" value="noneerror" id="error">
    <input type="hidden" name="groupusercount" value="{{$count}}" id="groupusercount">
</form>




@endsection
@section('footer')
@endsection