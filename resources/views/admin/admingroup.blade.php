@extends('layouts.admintemplate')

@section('title')
グループ編集 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>グループ編集</h2>

<form action="{{route('admingroupregistPost')}}" method="post" enctype="multipart/form-data" id="admin_group_form">
    @csrf
    <div class="grouptable">
        <div class="grouptable_header">
            <div class="admin_group">グループ</div>
            <div class="admin_group_change">名称変更</div>
            <div class="admin_group_delete">削除</div>
        </div>
        <div class="nonsortable">
            @foreach($groups as $group)

            <div class="grouptable_body gr_past" id="container{{$group->id}}">
                <div class="admin_group">
                    <div class="admin_group_text group_open" id="text{{$group->id}}">{{$group->グループ名}}</div>
                    <input type="text" value="{{$group->グループ名}}" class="admin_group_value" id="value{{$group->id}}">
                </div>
                <div class="admin_group_change">
                    <div class="gr_change_button" id="change{{$group->id}}">変更</div>
                </div>
                <div class="admin_group_delete">
                    <div class="gr_delete_button" id="{{$group->id}}">削除</div>
                </div>
            </div>
            @endforeach

        </div>

    </div>
    <div class="gr_addbutton" id="gr_addbutton">
        + 追加
    </div>
    <div class="add">

    </div>
    <button class="group_change_button">
        更新
    </button>
    <input type="hidden" id="save" value="save"><span class="savemessage">※更新ボタンを押して変更を反映させてください</span>

</form>




@endsection
@section('footer')
@endsection