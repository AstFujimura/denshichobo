@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection


@section('main')
<div class="MainElement">
    <h2 class="pagetitle">ワークフローマスタ一覧</h2>
    <div class="flow_master_container">
        <div class="flow_master_table">
            <div class="flow_master_th">
                <div class="flow_master_flowname">フロー名</div>
                <div class="flow_master_group">グループ</div>
                <div class="flow_master_start_price">下限金額</div>
                <div class="flow_master_end_price">上限金額</div>
                <div class="flow_master_edit">編集</div>
                <div class="flow_master_delete">削除</div>
            </div>

            <div class="flow_master_tbody">
                @foreach ($flow_master as $flow)
                <div class="flow_master_tr">
                    <div class="flow_master_flowname">{{$flow->フロー名}}</div>
                    <div class="flow_master_group">
                        @foreach ($flow_groups as $group)
                        @if ($group->フローマスタID == $flow->id)
                        <div>{{$group->グループ名}}</div>
                        @endif
                        @endforeach
                    </div>
                    <div class="flow_master_start_price">{{$flow->金額下限条件}}</div>
                    <div class="flow_master_end_price">{{$flow->金額上限条件}}</div>
                    <div class="flow_master_edit">
                        <div onclick="location.href='{{$prefix}}/workflowedit/{{$flow->id}}';" class="flow_master_edit_button">編集</div>
                    </div>
                    <div class="flow_master_delete">
                        <div class="flow_master_delete_button">削除</div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection