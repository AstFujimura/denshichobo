@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection




@section('main')
<h2 class="pagetitle" id="card_view_title"><img
        src="{{ asset(config('prefix.prefix').'/'.'img/card/title/regist_title.svg') }}" alt="" class="title_icon">会社編集
</h2>
<input type="hidden" id="card_company_edit_title">
<div class="MainElement">
    <form id="card_company_edit_form" action="{{ route('cardcompanyeditpost', ['company_id' => $company->id]) }}"
        method="post">
        @csrf
        <div class="card_company_edit_container">
            <div class="card_company_edit_button_container">
                <div class="card_company_edit_button">
                    更新
                </div>
                <div class="card_company_edit_button_cancel">
                    取消
                </div>
            </div>

            <div class="card_company_edit_content">
                <div class="card_company_edit_content_title">
                    会社名
                </div>
                <input type="text" name="company_name" id="card_company_edit_name" value="{{ $company->会社名 }}">
            </div>

            <div class="card_company_edit_content">
                <div class="card_company_edit_content_title">
                    会社名カナ
                </div>
                <input type="text" name="company_name_kana" id="card_company_edit_name_kana"
                    value="{{ $company->会社名カナ }}">
            </div>

            <div class="card_company_edit_content">
                <div class="card_company_edit_content_title">
                    部署
                </div>
                <div class="card_company_edit_department_container">
                    @foreach ($departments as $department)
                    <div class="card_company_edit_department_item">
                        <input type="text" name="department_name[{{ $department->id }}]" value="{{ $department->部署名 }}">
                        <div class="card_company_edit_department_item_delete">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/card/delete.svg') }}" alt="">
                        </div>
                    </div>
                    @endforeach
                    <div class="department_add_button">
                        追加
                    </div>
                </div>
            </div>

            <div class="card_company_edit_content">
                <div class="card_company_edit_content_title">
                    拠点
                </div>
                <div class="card_company_edit_branch_container">
                    @foreach ($branches as $branch)
                    @if ($branch->拠点指定 == 1)
                    <div class="card_company_edit_branch_item">
                        <input type="text" name="branch[{{ $branch->id }}][branch_name]" value="{{ $branch->拠点名 }}">
                        <div class="card_company_edit_branch_item_delete">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/card/delete.svg') }}" alt="">
                        </div>
                        <div class="card_company_edit_branch_item_detail">
                            <div class="card_company_edit_branch_item_detail_content">
                                <div class="card_company_edit_branch_item_detail_title">
                                    住所
                                </div>
                                <div class="card_company_edit_branch_item_detail_content">
                                    <input type="text" name="branch[{{ $branch->id }}][branch_address]"
                                        value="{{ $branch->拠点所在地 }}">
                                </div>
                            </div>
                            <div class="card_company_edit_branch_item_detail_content">
                                <div class="card_company_edit_branch_item_detail_title">
                                    電話番号
                                </div>
                                <div class="card_company_edit_branch_item_detail_content">
                                    <input type="text" name="branch[{{ $branch->id }}][branch_tel]"
                                        value="{{ $branch->電話番号 }}">
                                </div>
                            </div>
                            <div class="card_company_edit_branch_item_detail_content">
                                <div class="card_company_edit_branch_item_detail_title">
                                    FAX番号
                                </div>
                                <div class="card_company_edit_branch_item_detail_content">
                                    <input type="text" name="branch[{{ $branch->id }}][branch_fax]"
                                        value="{{ $branch->FAX番号 }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    <div class="branch_add_button">
                        追加
                    </div>
                </div>
            </div>


        </div>
    </form>
</div>
@endsection

@section('footer')
@endsection