@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 id="tameru_setting" class="pagetitle">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/category.svg') }}" alt="" class="title_icon">
        TAMERU設定-{{$m_category->カテゴリ名}}
    </h2>
    <div class="tameru_setting_container">
        <form id="tameru_setting_form" action="{{route('categorytamerusettingpost')}}" method="post">
            @csrf
            <input type="hidden" name="category_id" value="{{$m_category->id}}">
            <div class="flow_application_button_content">
                <a href="{{route('categorydetailget',['id'=>$m_category->id])}}" class="back_button " id="flow_next_button">
                    {{$m_category->カテゴリ名}} 設定へもどる
                </a>
                <div class="tameru_setting_change_button">
                    変更
                </div>
            </div>
            <div class="tameru_setting_title">
                決裁後にTAMERUに自動保存するファイルを選択してください
            </div>

            @foreach ($m_optionals as $m_optional)
            <div class="tameru_setting_item">
                <input {{ $m_optional->checked }} class="tameru_setting_checkbox" type="checkbox" name="optional{{$m_optional->id}}" id="optional{{$m_optional->id}}">
                <div class="tameru_setting_content">
                    <div class="tameru_setting_item">
                        {{$m_optional->項目名}}
                    </div>
                    <div class="tameru_setting_toggle_container">
                        <label class="toggle_button" for="optional{{$m_optional->id}}">
                            <div class="toggle_inner"></div>
                        </label>
                    </div>
                </div>
                <div class="tameru_input_list_container">
                    <table>
                        <tr>
                            <td>
                                TAMERU項目
                            </td>
                            <td>
                                申請項目
                            </td>
                        </tr>
                        <tr>
                            <td>
                                取引日
                            </td>
                            <td>
                                @if ($date_optionals->count() > 0)
                                <select name="date_optional{{$m_optional->id}}" id="date_optional{{$m_optional->id}}">
                                    @foreach ($date_optionals as $date_optional)
                                    <option {{ $m_optional->date_optional == $date_optional->id ? 'selected' : '' }} value="{{$date_optional->id}}">{{$date_optional->項目名}}</option>
                                    @endforeach
                                </select>
                                @else
                                条件を満たす項目がありません
                                @endif

                            </td>
                        </tr>
                        <tr>
                            <td>
                                金額
                            </td>
                            <td>
                                @if ($price_optionals->count() > 0)
                                <select name="price_optional{{$m_optional->id}}" id="price_optional{{$m_optional->id}}">
                                    @foreach ($price_optionals as $price_optional)
                                    <option {{ $m_optional->price_optional == $price_optional->id ? 'selected' : '' }} value="{{$price_optional->id}}">{{$price_optional->項目名}}</option>
                                    @endforeach
                                </select>
                                @else
                                条件を満たす項目がありません
                                @endif

                            </td>
                        </tr>
                        <tr>
                            <td>
                                取引先
                            </td>
                            <td>

                                @if ($company_optionals->count() > 0)
                                <select name="company_optional{{$m_optional->id}}" id="company_optional{{$m_optional->id}}">
                                    @foreach ($company_optionals as $company_optional)
                                    <option {{ $m_optional->company_optional == $company_optional->id ? 'selected' : '' }} value="{{$company_optional->id}}">{{$company_optional->項目名}}</option>
                                    @endforeach
                                </select>
                                @else
                                条件を満たす項目がありません
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>
                                書類区分
                            </td>
                            <td>
                                <select name="document_optional{{$m_optional->id}}" id="document_optional{{$m_optional->id}}">
                                    @foreach ($documents as $document)
                                    <option {{ $m_optional->document_optional == $document->id ? 'selected' : '' }} value="{{$document->id}}">{{$document->書類}}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                提出・受領
                            </td>
                            <td>
                                <select name="submit_optional{{$m_optional->id}}" id="submit_optional{{$m_optional->id}}">
                                    <option {{ $m_optional->submit_optional == 1 ? 'selected' : '' }} value="1">提出</option>
                                    <option {{ $m_optional->submit_optional == 2 ? 'selected' : '' }} value="2">受領</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                保存方法
                            </td>
                            <td>
                                <select name="save_optional{{$m_optional->id}}" id="save_optional{{$m_optional->id}}">
                                    <option {{ $m_optional->save_optional == 1 ? 'selected' : '' }} value="1">電子保存</option>
                                    <option {{ $m_optional->save_optional == 2 ? 'selected' : '' }} value="2">スキャナ保存</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                検索ワード
                            </td>
                            <td>
                                <textarea name="search_optional{{$m_optional->id}}" id="search_optional{{$m_optional->id}}" cols="30" rows="10">{{ $m_optional->search_optional }}</textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endforeach
        </form>
    </div>
</div>
@endsection

@section('footer')
@endsection