@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection

@section('menuebar')

@endsection

@section('menue')
<div class="left_side_menu">
    <div class="left_side_section">
        <div class="left_side_content_title">
            承認フロー名
        </div>
        <div>
            <input type="text" class="flow_name_text" placeholder="(例)フロー1">
        </div>
    </div>
    <div class="left_side_section">
        <div class="left_side_content_title">
            条件
        </div>
        <div class="accordion_menu">
            <div class="accordion_menu_title accordion_group">
                グループ
            </div>
            <div class="accordion_content">
                <div>
                    <input type="checkbox" name="flow_group" id="flow_group1">
                    <label for="flow_group1">アステック</label>
                </div>
                <div>
                    <input type="checkbox" name="flow_group" id="flow_group2">
                    <label for="flow_group2">アステック</label>
                </div>
                <div>
                    <input type="checkbox" name="flow_group" id="flow_group3">
                    <label for="flow_group3">アステック</label>
                </div>
                <div>
                    <input type="checkbox" name="flow_group" id="flow_group4">
                    <label for="flow_group4">アステック</label>
                </div>
                <div>
                    <input type="checkbox" name="flow_group" id="flow_group5">
                    <label for="flow_group5">アステック</label>
                </div>
            </div>

        </div>
        <div class="accordion_menu">
            <div class="accordion_menu_title accordion_price">
                金額
            </div>
            <div class="accordion_content">
                <div class="flow_plice_box">
                    <input type="number" class="flow_plice_text" id="start_flow_price">円以上
                </div>
                <div class="flow_plice_box">
                    <input type="number" class="flow_plice_text" id="end_flow_price">円未満
                </div>
            </div>

        </div>

    </div>
    <div class="left_side_section">
        <div class="left_side_content_title">
            通知メール
        </div>
        <div>
            <input type="radio" name="mailpoint" id="mailpoint1">
            <label for="mailpoint1">最終承認ポイントでの送信</label>
        </div>
        <div>
            <input type="radio" name="mailpoint" id="mailpoint2">
            <label for="mailpoint2">任意のポイントでの送信</label>
        </div>
        <div>
            <input type="radio" name="mailpoint" id="mailpoint3">
            <label for="mailpoint3">送信しない</label>
        </div>
    </div>
</div>
<div class="right_side_menu">
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
                    <input type="text" class="person_elment">
                    <div class="batsu_button">
                        ×
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
            <div class="autorizer_number_container">
                <span class="parameter">3</span>人中 <input type="number" class="authorizer_number"> 人
            </div>

        </div>

        <div>
            <input type="radio" class="authorizer" name="authorizer" id="authorizer2">
            <label for="authorizer2">グループ</label>
        </div>

        <div class="group_container">
            <div>
                <select class="group_select">
                    <option>本営</option>
                    <option>東京</option>
                    <option>大阪</option>
                    <option>川越</option>
                    <option>管理</option>
                </select>
            </div>
            <div>
                <input type="radio" class="choice_method" name="choice_method" id="choice_method1" checked>
                <label for="choice_method1">限定無し</label>
            </div>
            <div>
                <input type="radio" class="choice_method" name="choice_method" id="choice_method2">
                <label for="choice_method2">申請者が選択</label>
            </div>
            <div class="choice_container">
                <div>
                    選択可能人数
                </div>
                <div class="choice_content">
                    <div>
                        <input type="radio" name="choice_limit" id="choice_limit1" checked>
                        <label for="choice_limit1">無制限</label>
                    </div>
                    <div>
                        <input type="radio" name="choice_limit" id="choice_limit2">
                        <label for="choice_limit2">選択人数指定</label>
                    </div>
                    <div class="autorizer_number_container">
                        <input type="number" class="authorizer_number"> 人
                    </div>
                </div>

            </div>

            <div>
                <input type="radio" class="choice_method" name="choice_method" id="choice_method3">
                <label for="choice_method3">役職から選択</label>
            </div>
            <div class="post_choice_container">
                <div>
                    <input type="checkbox" name="post_choice" id="post_choice1">
                    <label for="post_choice1">社長</label>
                </div>
                <div>
                    <input type="checkbox" name="post_choice" id="post_choice2">
                    <label for="post_choice2">部長</label>
                </div>
                <div>
                    <input type="checkbox" name="post_choice" id="post_choice3">
                    <label for="post_choice3">取締役</label>
                </div>
            </div>

        </div>
        <div class="right_side_section">
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
        </div>
    </div>
</div>
@endsection

@section('main')

<h2 class="pagetitle">ワークフロー登録</h2>
<div class="element_input">
    <input type="hidden" class="route" id="route" data-routecount="1">
    <input type="hidden" class="maxgrid" id="maxgrid" data-maxcolumn="1" data-maxrow="1">
    <input type="hidden" class="element" data-column="1" data-row="1" data-last="none">
    <input type="hidden" class="element" data-column="1" data-row="2" data-last="last">

    <input type="hidden" class="line" data-startcolumn="1" data-startrow="1" data-endcolumn="1" data-endrow="2">
</div>





<div class="grid">
</div>


@endsection
@section('footer')
@endsection