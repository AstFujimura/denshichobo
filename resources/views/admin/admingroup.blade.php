@extends('layouts.admintemplate')

@section('title')
グループ編集 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>グループ一覧</h2>
<div class="bread_crumb_container">

    <div class="bread_crumb_content">
        <a>グループ一覧</a>
    </div>
    <div class="bread_crumb_content">
        <a href="{{ route('adminGet') }}">管理画面一覧</a>
    </div>

</div>
<form action="{{route('admingroupregistPost')}}" method="post" enctype="multipart/form-data" id="admin_group_form">
    @csrf
    <div class="link_container">
        <button class="group_change_button">
            更新
        </button>
    </div>

    <div class="grouptable">
        <div class="gr_addbutton" id="gr_addbutton">
            + 追加
        </div>
        <div class="grouptable_header">
            <div class="admin_group">グループ</div>
            <div class="admin_group_change">名称変更</div>
            @if ($version != 0)
            <div class="admin_group_detail_change">ユーザー設定</div>
            <div class="admin_group_detail_change">役職設定</div>
            @endif
            <div class="admin_group_delete">削除</div>
        </div>
        <div class="nonsortable">
            @foreach($groups as $group)

            <div class="grouptable_body gr_past" id="container{{$group->id}}">
                <div class="admin_group">
                    <div class="admin_group_text group_open">{{$group->グループ名}}</div>
                    <input type="text" value="{{$group->グループ名}}" class="admin_group_value">
                </div>
                <div class="admin_group_change">
                    <div class="gr_change_button">
                        <img src="{{ asset($prefix.'/'.'img/edit.svg')}}" class="edit_icon">
                        <span>変更</span>
                    </div>
                </div>
                @if ($version != 0)
                <div class="admin_group_detail_change">
                    <a class="setting_link" href="{{$prefix}}/admin/groupuser/{{$group->id}}">
                        <img src="{{ asset($prefix.'/'.'img/user_setting.svg')}}" class="setting_icon">
                        <span>ユーザー設定</span>
                    </a>
                </div>
                <div class="admin_group_detail_change">
                    <a class="setting_link" href="{{$prefix}}/admin/groupposition/{{$group->id}}">
                        <img src="{{ asset($prefix.'/'.'img/position.svg')}}" class="setting_icon">
                        <span>役職設定</span>
                    </a>
                </div>
                @endif
                <div class="admin_group_delete">
                    <div class="gr_delete_button" id="{{$group->id}}">
                        <img src="{{ asset($prefix.'/'.'img/delete.svg')}}" class="delete_icon">
                        <span>削除</span>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="add">

            </div>

        </div>

    </div>

    <input type="hidden" id="save" value="save"><span class="savemessage">※更新ボタンを押して変更を反映させてください</span>

</form>




@endsection
@section('footer')
@endsection