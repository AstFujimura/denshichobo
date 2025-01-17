@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle" id="category_detail"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/category.svg') }}" alt="" class="title_icon">カテゴリ登録</h2>
    <form action="{{route('categoryregistpost')}}" method="post">
        @csrf
        <input type="hidden" name="order" id="order" value="50000">
        <input type="hidden" name="delete" id="delete" value="">
        <input type="hidden" name="optional_max" id="optional_max" value="50001">
        <div class="category_detail_container">
            <div class="category_detail_button_content">
                <a href="{{route('categoryget')}}" class="back_button " id="flow_next_button">
                    カテゴリ一覧へもどる
                </a>
                <button class="change_button" id="flow_next_button">
                    登録
                </button>
                <a href="javascript:void(0);" onclick="window.location.reload();" class="cancel_button " id="flow_next_button">
                    キャンセル
                </a>
            </div>
            <div class="category_detail_annotation_container">
                <div class="category_detail_annotation_title">新規カテゴリ名</div>
                <div class="category_regist_title_container">
                    <input class="category_regist_title" type="text" name="category_name">
                </div>
            </div>
            <div class="category_detail_annotation_container">
                <div class="category_detail_annotation_title">注釈</div>
                <textarea name="annotation" class="category_detail_annotation"></textarea>
            </div>
            <div class="category_detail_optional_container">
                <div class="category_detail_optional_title">
                    申請項目
                </div>
                <div class="category_detail_optional_add_button" id="category_detail_optional_add_button">
                    + 追加
                </div>
                <div class="category_detail_optional_content_thead">
                    <div class="category_detail_optional_column">
                        項目名
                    </div>
                    <div class="category_detail_optional_type">
                        型
                    </div>
                    <div class="category_detail_optional_max">
                        最大
                    </div>
                    <div class="category_detail_optional_required">
                        必須項目
                    </div>
                    <div class="category_detail_optional_price">
                        金額条件
                    </div>
                    <div class="category_detail_optional_delete">
                        削除
                    </div>

                </div>
                <div class="category_detail_sortable">

        
                    <div class="category_detail_optional_content" data-id="50000" data-default="1">
                        <div class="category_detail_optional_column">
                            <input name="name_50000" type="text" class="category_detail_optional_text input_element" value="標題">
                        </div>
                        <div class="category_detail_optional_type">
                            <select name="type_50000" class="category_detail_optional_select input_element type" data-type="1">
                                <option value="1">文字列</option>
                                <option value="2">数値</option>
                                <option value="3">日付</option>
                                <option value="4">ファイル</option>
                                <option value="5">bool</option>
                            </select>
                        </div>
                        <div class="category_detail_optional_max">
                            <input name="max_50000" type="number" class="category_detail_optional_number input_element" value="30">
                        </div>
                        <div class="category_detail_optional_required">
                            <select name="required_50000" class="category_detail_optional_select input_element" data-type="1">
                                <option value="1">必須</option>
                                <option value="0">任意</option>
                            </select>
                        </div>
                        <div class="category_detail_optional_price">
                            <label for="radio50000" class="category_detail_optional_price_label">
                                <input name="price" type="checkbox" id="radio50000" value="50000">
                            </label>
                        </div>
                        <div class="category_detail_optional_delete">
                            <div class="category_detail_optional_delete_button">
                                ×
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('footer')
@endsection