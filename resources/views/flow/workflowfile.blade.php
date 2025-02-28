@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 id="fileget" class="pagetitle">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/file.svg') }}" alt="" class="title_icon">
        ファイル管理 @if ($hierarchy == 't_flow')
        - {{$category->カテゴリ名}}
        @elseif ($hierarchy == 'file')
        - {{$t_flow->標題}}
        @endif
    </h2>
    <div class="flow_application_button_content">
            <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                トップへもどる
            </a>
        </div>
    <div class="file_container">
        <div class="file_controller_container">
            <div class="file_controller_button">
                @if ($hierarchy == 'category')
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/up_disabled.svg') }}" alt="" class="controller_icon disabled" data-controller="up">
                @elseif ($hierarchy == 't_flow')
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/up.svg') }}" alt="" class="controller_icon" data-controller="up" data-id="{{$category->id}}">
                @elseif ($hierarchy == 'file')
                <input type="hidden" id="category_id" value="{{$category->id}}">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/up.svg') }}" alt="" class="controller_icon" data-controller="up" data-id="{{$t_flow->id}}">
                @endif
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/reload.svg') }}" alt="" class="controller_icon" data-controller="reload">
            </div>
            <div class="hierarchy_container">
                @if ($hierarchy == 'category')
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/file.svg') }}" alt="" class="hierarchy_icon">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/pankuzu.svg') }}" alt="" class="pankuzu_icon">
                @elseif ($hierarchy == 't_flow')
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/file.svg') }}" alt="" class="hierarchy_icon">
                <span>{{$category->カテゴリ名}}</span>
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/pankuzu.svg') }}" alt="" class="pankuzu_icon">
                @elseif ($hierarchy == 'file')
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/file.svg') }}" alt="" class="hierarchy_icon">

                <a href="{{$prefix}}/workflow/file/?category_id={{$category->id}}&hierarchy=t_flow">{{$category->カテゴリ名}}</a>
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/pankuzu.svg') }}" alt="" class="pankuzu_icon">
                <span>{{$t_flow->標題}}</span>
                <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/pankuzu.svg') }}" alt="" class="pankuzu_icon">

                @endif

            </div>
        </div>
        <table class="file_list_table">
            @if ($hierarchy == 'category')
            <tr class="title_tr">
                <td></td>
                <td>
                    カテゴリ名
                </td>
            </tr>
            @elseif ($hierarchy == 't_flow')
            <tr class="title_tr">
                <td></td>
                <td>
                    標題
                </td>
                <td>
                    決裁日
                </td>
            </tr>
            @endif
            @if ($hierarchy == 'file')
            <tr class="title_tr">
                <td></td>
                <td>
                    項目名
                </td>
                <td>
                    ファイル形式
                </td>
            </tr>
            @endif
            @foreach ($lists as $list)
            @if ($hierarchy == 'category')
            <tr data-id="{{$list->id}}" class="folder_tr">
                <td><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/file_orange.svg') }}" alt="" class="title_icon"></td>
                <td>
                    {{$list->カテゴリ名}}
                </td>
            </tr>
            @elseif ($hierarchy == 't_flow')
            <tr data-id="{{$list->id}}" class="folder_tr">
                <td><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/file_orange.svg') }}" alt="" class="title_icon"></td>
                <td>
                    {{$list->標題}}
                </td>
                <td>
                    {{date('Y/m/d', strtotime($list->updated_at))}}
                </td>
            </tr>
            @endif
            @if ($hierarchy == 'file')
            <tr class="file_tr">
                <td><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/document_red.svg') }}" alt="" class="title_icon"></td>
                <td>
                    {{$list->項目名}}
                </td>
                <td>
                    {{$list->ファイル形式}}
                </td>
                <td>
                    @if ($list->項目名 == "承認用紙")
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/download_2_line.svg') }}" alt="" class="title_icon download_icon" data-url="{{$prefix}}/workflow/approval/download/{{$list->id}}" data-type="t_optional">
                    @else
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_file/download.svg') }}" alt="" class="title_icon download_icon" data-url="{{$prefix}}/workflow/download/{{$list->id}}" data-type="t_optional">
                    @endif

                </td>

            </tr>
            @endif
            @endforeach
        </table>
        <input type="hidden" id="hierarchy" value="{{$hierarchy}}">
    </div>
</div>
@endsection

@section('footer')
@endsection