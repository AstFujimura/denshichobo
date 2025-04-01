@extends('layouts.flowtemplate')

@section('title')
Rapid ~電子承認システム
@endsection




@section('main')
<!-- <div class="category_setting_gray"></div> -->
<div class="MainElement">

    <h2 class="pagetitle" id="category_detail"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/category.svg') }}" alt="" class="title_icon">カテゴリ - {{$m_category->カテゴリ名}}</h2>
    <form action="{{route('categorydetailpost')}}" method="post">
        @csrf
        <input type="hidden" name="id" id="category_id" value="{{$id}}">
        <input type="hidden" name="order" id="order" value="{{$order}}">
        <input type="hidden" name="delete" id="delete" value="">
        <input type="hidden" name="optional_max" id="optional_max" value="50000">
        <div class="category_detail_container">
            <div class="category_detail_button_content">
                <a href="{{route('categoryget')}}" class="back_button " id="flow_next_button">
                    カテゴリ一覧へもどる
                </a>
                <button class="change_button" id="flow_next_button">
                    変更
                </button>
                <a href="javascript:void(0);" onclick="window.location.reload();" class="cancel_button " id="flow_next_button">
                    キャンセル
                </a>
            </div>
            <div class="category_detail_annotation_container">
                <div class="category_detail_annotation_title">注釈</div>
                <textarea name="annotation" class="category_detail_annotation">{{$annotation}}</textarea>
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

                    @foreach ($items as $item)
                    <div class="category_detail_optional_content" data-id="{{$item['id']}}" data-default="{{$item['デフォルト']}}">
                        <div class="category_detail_optional_column">
                            <input name="name_{{$item['id']}}" type="text" class="category_detail_optional_text input_element" value="{{$item['項目名']}}">
                        </div>
                        <div class="category_detail_optional_type">
                            <select name="type_{{$item['id']}}" class="category_detail_optional_select input_element type" data-type="{{$item['型']}}">
                                <option value="1">文字列</option>
                                <option value="2">数値</option>
                                <option value="3">日付</option>
                                <option value="4">ファイル</option>
                            </select>
                        </div>
                        <div class="category_detail_optional_max">
                            <input name="max_{{$item['id']}}" type="number" class="category_detail_optional_number input_element" value="{{$item['最大']}}">
                        </div>
                        <div class="category_detail_optional_required">
                            <select name="required_{{$item['id']}}" class="category_detail_optional_select input_element" data-type="{{$item['必須項目']}}">
                                <option value="1">必須</option>
                                <option value="0">任意</option>
                            </select>
                        </div>
                        <div class="category_detail_optional_price">
                            <label for="radio{{$item['id']}}" class="category_detail_optional_price_label">
                                <input name="price" type="checkbox" id="radio{{$item['id']}}" value="{{$item['id']}}" {{$item['金額条件']}}>
                            </label>
                        </div>
                        <div class="category_detail_optional_tameru">

                        </div>
                        <div class="category_detail_optional_delete">
                            <div class="category_detail_optional_delete_button">
                                ×
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if ($tameru_condition)
                <a href="{{route('categorytamerusettingget', $id)}}" class="tameru_setting_button">
                    TAMERUの設定
                </a>
                @endif
                <div class="category_detail_delete_button">
                    このカテゴリを削除する
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@section('footer')
@endsection