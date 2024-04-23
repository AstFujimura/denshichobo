@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection


@section('main')
<div class="MainElement">
    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/master_view.svg') }}" alt="" class="title_icon">ワークフローマスタ一覧</h2>
    <div class="flow_master_container">
        <div class="flow_application_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
        </div>
        <div class="flow_master_content">
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
                        <div class="flow_master_start_price">
                            @if ($flow->金額下限条件 == 0)
                            下限なし
                            @else
                            {{$flow->金額下限条件}}
                            @endif
                        </div>
                        <div class="flow_master_end_price">
                            @if ($flow->金額上限条件 == 2000000000)
                            上限なし
                            @else
                            {{$flow->金額上限条件}}
                            @endif
                        </div>
                        <div class="flow_master_edit">
                            <div onclick="location.href='{{$prefix}}/workflowedit/{{$flow->id}}';" class="flow_master_edit_button">編集</div>
                        </div>
                        <div class="flow_master_delete">
                            <div data-location='{{$prefix}}/workflowdelete/{{$flow->id}}' class="flow_master_delete_button">削除</div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection