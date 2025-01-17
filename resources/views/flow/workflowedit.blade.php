@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<form action="{{route('workflowregistpost')}}" method="post" id="workflowform" enctype="application/x-www-form-urlencoded">
    @csrf
    <div class="maincontent01 flowbackground">
        <!-- <div class="MenueBar">
        </div> -->
        <div class="MainElement flow_master_element">
            <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/flow.svg') }}" alt="" class="title_icon">経路マスタ編集</h2>
            <div class="element_input">
                <input type="hidden" name="edit" class="edit" id="edit" value="edit">
                <input type="hidden" name="flow_master_id" class="flow_master_id" id="flow_master_id" value="{{$flow_master->id}}">
                @foreach ($positions as $position)
                <input type="hidden" class="position" data-groupid="{{$position->グループID}}" data-positionid="{{$position->id}}" data-position_count="{{$position->count}}" value="{{$position->役職}}">
                @endforeach
                <input type="hidden" class="route" id="route" name="route" data-routecount="1">
                <input type="hidden" class="maxgrid" id="maxgrid" data-maxcolumn="1" data-maxrow="1">
                @foreach ($flow_points as $flow_point)
                <input type="hidden" id="{{$flow_point->id}}" class="element" data-column="{{$flow_point->column}}" data-row="{{$flow_point->row}}" data-last="none" data-authorizer="{{$flow_point->person_group}}" data-person_required_number="{{$flow_point->person_required}}" data-person_parameter="{{$flow_point->person_required}}" data-group_parameter="{{$flow_point->group_parameter}}" data-group_required_number="{{$flow_point->group_required}}" data-select_method="{{$flow_point->select_method}}">
                @if ($flow_point->申請者選択数)
                <input type="hidden" data-id="{{$flow_point->id}}" class="byapplicant" data-group_choice_number="{{$flow_point->申請者選択数}}">
                @endif
                @endforeach
                @foreach ($flow_approvals as $flow_approval)
                @if ($flow_approval->newgroup == "person")
                <input type="hidden" data-id="{{$flow_approval->フロー地点ID}}" class="person" data-person_name="{{$flow_approval->name}}">
                @endif
                @if ($flow_approval->newgroup == "none")
                <input type="hidden" data-id="{{$flow_approval->フロー地点ID}}" class="post" data-positionid="{{$flow_approval->役職ID}}">
                @endif
                @if ($flow_approval->newgroup == "newgroup_post")
                <input type="hidden" data-id="{{$flow_approval->フロー地点ID}}" class="post" data-positionid="{{$flow_approval->役職ID}}">
                <input type="hidden" data-id="{{$flow_approval->フロー地点ID}}" class="group" data-group_name="{{$flow_approval->グループ名}}" data-group_id="{{$flow_approval->グループID}}" data-group_count="">
                @endif
                @if ($flow_approval->newgroup == "newgroup_none_post")
                <input type="hidden" data-id="{{$flow_approval->フロー地点ID}}" class="group" data-group_name="{{$flow_approval->グループ名}}" data-group_id="{{$flow_approval->グループID}}" data-group_count="">

                @endif

                @endforeach
                @foreach ($next_flow_points as $next_flow_point)
                <input type="hidden" class="line" data-startcolumn="{{$next_flow_point->startcolumn}}" data-startrow="{{$next_flow_point->startrow}}" data-endcolumn="{{$next_flow_point->endcolumn}}" data-endrow="{{$next_flow_point->endrow}}">
                @endforeach


                <input type="hidden" id="focus" class="focus" data-id="">

            </div>
            <div class="flow_master_button_container">
                <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                    トップへもどる
                </a>
                <button class="workflow_submit_button">
                    変更
                </button>
                <a href="javascript:void(0);" onclick="window.location.reload();" class="cancel_button " id="flow_next_button">
                    キャンセル
                </a>
            </div>
            <div class="flow_main_master_container">

                <div class="left_side_menu">
                    <div class="left_side_section">
                        <div class="left_side_content_title">
                            カテゴリー<span class="red">　※</span>
                        </div>
                        <div>
                            <select name="flow_category" id="flow_category" class="flow_name_text">
                                @foreach ($categories as $category)
                                <option value="{{$category->id}}" {{$category->selected}}>{{$category->カテゴリ名}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="left_side_section">
                        <div class="left_side_content_title">
                            承認フロー名<span class="red">　※</span>
                        </div>
                        <div>
                            <input type="text" id="flow_name_text" class="flow_name_text" name="flow_name" placeholder="(例)フロー1" value="{{$flow_master->フロー名}}">
                        </div>
                    </div>
                    <div class="left_side_section">
                        <div class="left_side_content_title">
                            申請条件
                        </div>
                        <div class="accordion_menu accordion_menu_group">
                            <div class="accordion_menu_title accordion_group accordion_menu_title_open">
                                申請者グループ<span class="red">　※</span>
                            </div>
                            <div class="accordion_content accordion_content_open">

                                @foreach ($groups as $index=>$group)
                                <div>
                                    <input type="checkbox" class="group_checkbox" name="flow_group[]" id="flow_group{{ $loop->index + 1}}" value="{{$group->id}}" {{$group->checked}}>
                                    <label for="flow_group{{ $loop->index + 1}}">{{$group->グループ名}}</label>
                                </div>
                                @endforeach
                            </div>

                        </div>
                        <div class="accordion_menu">
                            <div class="accordion_menu_title accordion_price accordion_menu_title_open">
                                金額
                            </div>
                            <div class="accordion_content accordion_content_open">
                                <div class="flow_plice_box">
                                    @if ($flow_master->金額下限条件 == 0)
                                    <input type="number" class="flow_plice_text" id="start_flow_price" name="start_flow_price" value="">円以上
                                    @else
                                    <input type="number" class="flow_plice_text" id="start_flow_price" name="start_flow_price" value="{{$flow_master->金額下限条件}}">円以上
                                    @endif
                                </div>
                                <div class="flow_plice_box">
                                    @if ($flow_master->金額上限条件 == 2000000000)
                                    <input type="number" class="flow_plice_text" id="end_flow_price" name="end_flow_price" value="">円以下
                                    @else
                                    <input type="number" class="flow_plice_text" id="end_flow_price" name="end_flow_price" value="{{$flow_master->金額上限条件}}">円以下
                                    @endif

                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="left_side_section">
                        <div class="left_side_content_title">
                            閲覧者
                        </div>
                        <div class="accordion_menu accordion_menu_group">
                            <div class="accordion_menu_title accordion_group accordion_menu_title_open">
                                閲覧者グループ<span class="red">　※</span>
                            </div>
                            <div class="accordion_content accordion_content_open">

                                @foreach ($groups as $index=>$group)
                                <div>
                                    <input type="checkbox" class="group_checkbox" name="flow_view_group[]" id="flow_view_group{{ $loop->index + 1}}" value="{{$group->id}}" {{$group->view_checked}}>
                                    <label for="flow_view_group{{ $loop->index + 1}}">{{$group->グループ名}}</label>
                                </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
                <div class="grid_container">
                    <div class="zoom_in_out">

                        <img src="{{ asset($prefix.'/'.'img/zoom_in.svg')}}" class="zoom" id="zoom_in">
                        <img src="{{ asset($prefix.'/'.'img/zoom_out.svg')}}" class="zoom" id="zoom_out">

                    </div>
                    <div class="grid">
                    </div>

                </div>
                <div class="right_side_menu">
                    <div class="gray"></div>
                    <div class="right_side_section">
                        <div class="right_side_content_title">
                            承認者
                        </div>
                        <div>
                            <input type="radio" class="authorizer" name="authorizer" id="authorizer1" checked>
                            <label for="authorizer1">個人</label>
                        </div>
                        <div class="person_container person_container_open">
                            <div class="plus_button">
                                +
                            </div>
                            <div class="person_content">
                                <div class="person_box">
                                    <input type="text" class="person_text">
                                    <div class="batsu_button">
                                        ×
                                    </div>
                                    <div class="flow_user_list">

                                    </div>
                                </div>
                            </div>
                            <div>
                                <input type="radio" class="authorizer_condition" name="authorizer_condition" id="authorizer_condition1" checked>
                                <label for="authorizer_condition1">全員の承認</label>
                            </div>
                            <div>
                                <input type="radio" class="authorizer_condition" name="authorizer_condition" id="authorizer_condition2">
                                <label for="authorizer_condition2">条件指定</label>
                            </div>
                            <div class="autorizer_number_container" id="person_authorizer_number_container">
                                <span class="parameter">0</span>人中 <input type="number" class="authorizer_number" id="person_required_number"> 人承認
                            </div>

                        </div>

                        <div>
                            <input type="radio" class="authorizer" name="authorizer" id="authorizer2">
                            <label for="authorizer2">グループ</label>
                        </div>

                        <div class="group_container">
                            <div>
                                <select class="group_select">
                                    <option></option>
                                    @foreach ($groups as $group)
                                    <option data-group_id="{{$group->id}}" data-group_count="{{$group->count}}">{{$group->グループ名}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="radio" class="choice_method" name="choice_method" id="nolimit" checked>
                                <label for="nolimit">限定無し</label>
                            </div>


                            <div>
                                <input type="radio" class="choice_method" name="choice_method" id="postchoice">
                                <label for="postchoice">役職から選択</label>
                            </div>
                            <div class="post_choice_container">
                            </div>
                            <div class="group_authorizer_number_container" id="group_authorizer_number_container"><span class="group_parameter">0</span>人中<input type="number" class="authorizer_number" id="group_authorizer_number">人承認</div>

                        </div>
                        <!-- <div class="right_side_section">
                            <div class="right_side_content_title">
                                通知メール
                            </div>
                            <div>
                                <input type="radio" name="section_mailpoint" id="section_mailpoint1">
                                <label for="section_mailpoint1">承認通知メールを送信</label>
                            </div>
                        </div>
                        <div class="right_side_section">
                            <div class="right_side_content_title">
                                その他設定
                            </div>
                            <div>
                                <input type="radio" name="omission" id="omission1">
                                <label for="omission1">承認時に以降のフローを省略する選択</label>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
</form>
@endsection

@section('footer')
@endsection