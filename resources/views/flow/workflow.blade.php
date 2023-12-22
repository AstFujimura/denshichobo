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
            <input type="radio" name="authorizer" id="authorizer1">
            <label for="authorizer1">個人</label>
        </div>
        <div>
            <input type="radio" name="authorizer" id="authorizer2">
            <label for="authorizer2">グループ</label>
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