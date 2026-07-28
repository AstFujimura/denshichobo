@extends('layouts.admintemplate')

@section('title')
書類管理 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>書類管理</h2>

<div class="bread_crumb_container">
    <div class="bread_crumb_content">
        <a>書類管理</a>
    </div>
    <div class="bread_crumb_content">
        <a href="{{ route('adminGet') }}">管理画面一覧</a>
    </div>
</div>

<form action="{{ route('admindocumentPost') }}" method="post" id="admin_document_form">
    @csrf
    <input type="hidden" id="save" value="save">
    <p class="savemessage admin_save_hint">※「変更を保存」を押して変更を反映してください</p>

    <div class="admin_header_container">
        <div class="add_user_button" id="docu_addbutton" role="button" tabindex="0">＋ 書類を追加</div>
    </div>

    <div class="admin_document_page">
        <div class="admin_top_table_header admin_document_table_header">
            <div data-cell="enabled">有効</div>
            <div data-cell="name">書類名</div>
            <div data-cell="ocr">OCR設定</div>
            <div data-cell="change">編集</div>
            <div data-cell="delete">削除</div>
        </div>
        <div class="admin_top_table_body admin_document_table_body sortable" id="admin_document_sortable">
            @foreach ($documents as $document)
            @php
                $sumAmounts = $document->ocrSumAmountsEnabled();
                $taxIncluded = $document->ocrTaxIncluded();
                $enabled = $document->check === 'check';
                $ocrBadgeText = ($taxIncluded ? '税込' : '税抜') . ($sumAmounts ? '・複数合算' : '');
            @endphp
            <div class="admin_top_table_element admin_document_row docu_past" id="container{{ $document->id }}" data-document-id="{{ $document->id }}">
                <div class="cell_content document_enabled_cell" data-cell="enabled">
                    <input type="checkbox" class="docu_check document_enabled_input" id="check{{ $document->id }}" @if ($enabled) checked @endif>
                    <button type="button" class="document_enabled_toggle_btn @if ($enabled) is-on @endif" aria-pressed="{{ $enabled ? 'true' : 'false' }}" aria-label="帳簿保存で選択可能">
                        <span class="document_enabled_toggle_track" aria-hidden="true"><span class="document_enabled_toggle_knob"></span></span>
                    </button>
                </div>
                <div class="cell_content" data-cell="name">
                    <span class="admin_document_name_display" id="text{{ $document->id }}">{{ $document->書類 }}</span>
                </div>
                <div class="cell_content" data-cell="ocr">
                    <span class="admin_document_ocr_badge {{ ($sumAmounts || $taxIncluded) ? 'is-on' : '' }}" data-ocr-badge="{{ $document->id }}">
                        {{ $ocrBadgeText }}
                    </span>
                </div>
                <div class="cell_content icon_cell" data-cell="change">
                    <div class="document_edit_button user_edit_button">
                        <img src="{{ asset($prefix.'/img/edit.svg') }}" class="edit_icon" alt="">
                        <span>編集</span>
                    </div>
                </div>
                <div class="cell_content icon_cell" data-cell="delete">
                    <div class="user_delete_button document_row_delete" id="{{ $document->id }}">
                        <img src="{{ asset($prefix.'/img/delete.svg') }}" class="delete_icon" alt="">
                        <span>削除</span>
                    </div>
                </div>

                <div class="document_edit_container">
                    <div class="document_setting_edit_content">
                        <div class="user_form_content document_name_form_content">
                            <label for="value{{ $document->id }}">書類名</label>
                            <input type="text" class="document_name_input" id="value{{ $document->id }}" value="{{ $document->書類 }}" autocomplete="off">
                        </div>
                        <div class="user_form_content document_ocr_settings_block" data-form_content="ocr">
                            <label>OCR設定</label>
                            <div class="document_ocr_settings">
                                <div class="document_ocr_option_row document_ocr_tax_row">
                                    <span class="document_ocr_tax_heading">金額の税区分</span>
                                    <label class="document_ocr_radio_label">
                                        <input type="radio" class="ocr_tax_mode_radio" name="ocr_tax_mode_{{ $document->id }}" value="excluded" @if (!$taxIncluded) checked @endif>
                                        <span>税抜</span>
                                    </label>
                                    <label class="document_ocr_radio_label">
                                        <input type="radio" class="ocr_tax_mode_radio" name="ocr_tax_mode_{{ $document->id }}" value="included" @if ($taxIncluded) checked @endif>
                                        <span>税込</span>
                                    </label>
                                    <span class="document_ocr_tax_note @if (!$taxIncluded) is-hidden @endif">税抜表示の場合は、税抜金額から税込金額を計算します。</span>
                                </div>
                                <div class="document_ocr_option_row">
                                    <label class="document_ocr_checkbox_label">
                                        <input type="checkbox" class="ocr_sum_amounts_check" @if ($sumAmounts) checked @endif>
                                        <span>1ファイル内の複数ページ・複数セットの金額を合算する</span>
                                    </label>
                                    <button type="button" class="document_ocr_info_btn" aria-label="複数ページ・複数セットの金額合算について">i</button>
                                </div>
                            </div>
                        </div>
                        <div class="user_setting_edit_button_container document_edit_actions">
                            <button type="button" class="document_setting_cancel_button">閉じる</button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="document_add_rows add"></div>
        </div>
    </div>

    <div class="admin_document_footer link_container">
        <button type="submit" class="document_change_button admin_primary_button">変更を保存</button>
        <a href="{{ route('adminGet') }}" class="link_back">管理画面一覧に戻る</a>
    </div>
</form>

<div class="admin_document_info_modal" id="documentOcrSumInfoModal" aria-hidden="true">
    <div class="admin_document_info_modal__backdrop" tabindex="-1"></div>
    <div class="admin_document_info_modal__dialog" role="dialog" aria-modal="true" aria-labelledby="documentOcrSumInfoTitle">
        <h3 class="admin_document_info_modal__title" id="documentOcrSumInfoTitle">複数ページ・複数セットの金額合算</h3>
        <p class="admin_document_info_modal__body">
            主に納品書などを想定した設定です。1つの PDF や画像ファイルの中に、複数ページへ同じ種類の帳票（納品書）が複数セット並んでいる場合、各セットに記載された税込合計・合計金額などをすべて読み取り、それらを<strong>加算した合計</strong>を AI OCR の金額（kinngaku）として扱います。<br><br>
            1セットだけのファイルの場合は、その1セットの合計金額をそのまま読み取ります。明細行の単価を足すのではなく、各セットの「合計」「請求金額」など帳票単位の金額を合算するイメージです。
        </p>
        <div class="admin_document_info_modal__actions">
            <button type="button" class="admin_document_info_modal__close">閉じる</button>
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection
