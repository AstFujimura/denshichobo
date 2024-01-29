@extends('layouts.admintemplate')

@section('title')
グループ編集 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>{{$group->グループ名}}役職設定</h2>

<form action="{{route('admingroupregistPost')}}" method="post" enctype="multipart/form-data" id="admin_position_form">
    @csrf
    <div class="positiontable">
        <div class="positiontable_header">
            <div class="position_name">
                役職名
            </div>
            <div class="edit_container">
                変更
            </div>
            <div class="delete_container">
                削除
            </div>
        </div>
        @foreach ($positions as $position)
        <div class="positiontable_body position_past">
            <div>
                <div class="position_name" >
                    <div class="position_text position_open">{{$position->役職}}</div>
                    <input type="text" value="{{$position->役職}}" class="position_name_value" id="position{{$position->id}}">
                </div>
                <div class="edit_container">
                    <div class="position_edit_button">変更</div>
                </div>
                <div class="delete_container">
                    <div class="position_delete_button" id="delete{{$position->id}}">削除</div>
                </div>
            </div>


        </div>
        @endforeach


    </div>
    <div class="position_addbutton" id="position_addbutton">
        + 追加
    </div>
    <div class="add">

    </div>
    <button class="position_change_button">
        更新
    </button>
    <input type="hidden" id="save" value="save"><span class="savemessage">※更新ボタンを押して変更を反映させてください</span>
    <input type="hidden" id="groupid" value="{{$group->id}}">
</form>




@endsection
@section('footer')
@endsection