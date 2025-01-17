@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<form action="{{route('workflowregistpost')}}" method="post" id="workflowform" enctype="application/x-www-form-urlencoded">
    @csrf
    <div class="maincontent01 flowbackground">
        <!-- <div class="meta_flow_container">
            <div class="meta_flow_content">

            </div>
        </div> -->
        <!-- <div class="MenueBar">

        </div> -->
        <div class="MainElement flow_master_element">
            <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/flow.svg') }}" alt="" class="title_icon">経路マスタ登録</h2>
            <div class="element_input">
                <input type="hidden" name="edit" class="regist" id="regist" value="regist">
                @foreach ($positions as $position)
                <input type="hidden" class="position" data-groupid="{{$position->グループID}}" data-positionid="{{$position->id}}" data-position_count="{{$position->count}}" value="{{$position->役職}}">
                @endforeach
                <input type="hidden" class="route" id="route" name="route" data-routecount="1">
                <input type="hidden" class="maxgrid" id="maxgrid" data-maxcolumn="1" data-maxrow="1">
                <input type="hidden" id="10000" class="element" data-column="1" data-row="1" data-last="none">
                <input type="hidden" id="10001" class="element" data-column="1" data-row="2" data-last="last" data-authorizer="person" data-person_required_number="all" data-person_parameter="0" data-person_required_number="all" data-group_parameter="0" data-group_required_number="1" data-select_method="nolimit">

                <input type="hidden" class="line" data-startcolumn="1" data-startrow="1" data-endcolumn="1" data-endrow="2">
                <input type="hidden" id="focus" class="focus" data-id="">

            </div>

            <div class="flow_master_button_container">
                <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                    トップへもどる
                </a>
                <button class="workflow_submit_button">
                    登録
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
                                <option value="{{$category->id}}">{{$category->カテゴリ名}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="left_side_section">
                        <div class="left_side_content_title">
                            承認フロー名<span class="red">　※</span>
                        </div>
                        <div>
                            <input type="text" id="flow_name_text" class="flow_name_text" name="flow_name" placeholder="(例)フロー1">
                        </div>
                    </div>
                    <div class="left_side_section">
                        <div class="left_side_content_title">
                            条件
                        </div>
                        <div class="accordion_menu accordion_menu_group">
                            <div class="accordion_menu_title accordion_group accordion_menu_title_open">
                                グループ<span class="red">　※</span>
                            </div>
                            <div class="accordion_content accordion_content_open">

                                @foreach ($groups as $index=>$group)
                                <div>
                                    <input type="checkbox" class="group_checkbox" name="flow_group[]" id="flow_group{{ $loop->index + 1}}" value="{{$group->id}}">
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
                                    <input type="number" class="flow_plice_text" id="start_flow_price" name="start_flow_price">円以上
                                </div>
                                <div class="flow_plice_box">
                                    <input type="number" class="flow_plice_text" id="end_flow_price" name="end_flow_price">円未満
                                </div>
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
                        </div> -->
                        <!-- <div class="right_side_section">
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
    </div>
</form>
@endsection

@section('footer')
@endsection