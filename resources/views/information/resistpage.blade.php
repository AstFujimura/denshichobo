@extends('layouts.template')

@section('title')
帳簿保存 | TAMERU
@endsection

@section('menuebar')
@endsection

@section('menue')
@endsection

@section('main')

<h2 id="regist" class="pagetitle">帳簿保存</h2>

<div class="ledger-regist droppreview">
    <input type="hidden" id="banbanEnabled" value="{{ !empty($banbanEnabled) ? 1 : 0 }}">
    <input type="hidden" id="ichifujiEnabled" value="{{ !empty($ichifujiEnabled) ? 1 : 0 }}">
    <input type="hidden" id="aiOcrTraceEnabled" value="{{ config('ai_ocr.trace') ? 1 : 0 }}">
    <input type="hidden" id="ledgerRegistInitialMode" value="{{ ($ledgerRegistMode ?? 'normal') === 'bulk' ? 'bulk' : 'normal' }}">

    @if(!empty($banbanEnabled))
    <div class="ledger-regist-tabs" role="tablist" aria-label="取込方式">
        <button type="button" class="ledger-regist-tabs__tab {{ ($ledgerRegistMode ?? 'normal') !== 'bulk' ? 'is-active' : '' }}" role="tab" aria-selected="{{ ($ledgerRegistMode ?? 'normal') !== 'bulk' ? 'true' : 'false' }}" aria-controls="ledgerRegistTabNormal" id="ledgerRegistTabNormalButton" data-ledger-tab="normal">通常取込</button>
        <button type="button" class="ledger-regist-tabs__tab {{ ($ledgerRegistMode ?? 'normal') === 'bulk' ? 'is-active' : '' }}" role="tab" aria-selected="{{ ($ledgerRegistMode ?? 'normal') === 'bulk' ? 'true' : 'false' }}" aria-controls="ledgerRegistTabBulk" id="ledgerRegistTabBulkButton" data-ledger-tab="bulk">一括取込</button>
    </div>
    @endif

    <div class="ledger-regist-tabpanel {{ ($ledgerRegistMode ?? 'normal') !== 'bulk' ? 'is-active' : '' }}" id="ledgerRegistTabNormal" role="tabpanel" aria-labelledby="ledgerRegistTabNormalButton">
        <form class="ledger-regist__form form" action="{{ route('registPost') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="ledger-regist__layout">
                <div class="ledger-regist__fields">
                    <div class="ledger-regist__file-row">
                        <div class="ledger-regist__dropzone droparea" role="button" tabindex="0">ここにドラッグ＆ドロップ</div>
                        <label class="ledger-regist__file-button" for="file">ファイルを選択</label>
                        <input type="file" name="file" id="file" class="ledger-regist__file-input">
                        <span class="fileerrorelement ledger-regist__error">ファイルを選択してください</span>
                    </div>

                <div class="ledger-regist__ocr-grid">
                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="hiduke">取引日<span class="requirered">*</span></label>
                        <div class="ledger-regist__control dateform">
                            <input type="text" name="hiduke" class="input-field dateinputtext ledger-regist-date ledger-regist__input" id="hiduke" autocomplete="off">
                            <span class="errorelement ledger-regist__error" id="required1">必須項目です</span>
                            <span class="errorelement ledger-regist__error" id="dateformat">形式が不正です</span>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="kinngaku">金額<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <input type="text" name="kinngaku" class="input-field kinngakuinput-field ledger-regist__input ledger-regist__input--amount" id="kinngaku">
                            <span class="errorelement ledger-regist__error" id="required2">必須項目です</span>
                            <span class="errorelement ledger-regist__error" id="kinngakuformat">形式が不正です</span>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="torihikisaki">取引先<span class="requirered">*</span></label>
                        <div class="ledger-regist__control torihikisakiinput">
                            <input type="text" name="torihikisaki" class="input-field ledger-regist__input" id="torihikisaki" autocomplete="off">
                            <div class="registtorihikisakiselect" id="torihikisakiselect"></div>
                            <span class="errorelement ledger-regist__error" id="required3">必須項目です</span>
                            <span class="errorelement ledger-regist__error" id="torihikiformat">形式が不正です</span>
                        </div>
                    </div>
                </div>

                <div class="ledger-regist__assist" id="aiOcrButtonSlot"></div>

                <div class="ledger-regist__detail-grid">
                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="syorui">書類区分<span class="requirered">*</span></label>
                        <div class="ledger-regist__control ledger-regist__control--with-ocr-detail">
                            <select name="syorui" class="input-field ledger-regist__input ledger-regist__select" id="syorui">
                                @foreach($documents as $document)
                                <option value="{{ $document->id }}" data-ocr-sum-amounts="{{ $document->ocrSumAmountsEnabled() ? '1' : '0' }}" data-ocr-tax-included="{{ $document->ocrTaxIncluded() ? '1' : '0' }}">{{ $document->書類 }}</option>
                                @endforeach
                            </select>
                            @if(!empty($ichifujiEnabled))
                            <button type="button" class="ledger-ocr-settings-detail-btn" data-ledger-ocr-settings-open data-ocr-syorui-source="normal" aria-label="AI OCR の読み取り設定">
                                <img src="{{ asset($prefix.'/img/settings_4_line.svg') }}" class="ledger-ocr-settings-detail-btn__icon" alt="">
                                <span class="ledger-ocr-settings-detail-btn__text">詳細</span>
                            </button>
                            <span class="ledger-ocr-settings-chips" id="ledgerOcrSettingsChipNormal" aria-live="polite" aria-label="現在の AI OCR 設定"></span>
                            @endif
                            <span class="errorelement ledger-regist__error" id="required4">必須項目です</span>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="teisyutu">受領・提出<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select name="teisyutu" class="input-field ledger-regist__input ledger-regist__select" id="teisyutu">
                                <option>受領</option>
                                <option>提出</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="hozonn">保存方法<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select name="hozonn" class="input-field ledger-regist__input ledger-regist__select" id="hozonn">
                                <option>電子保存</option>
                                <option>スキャナ保存</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="group">グループ</label>
                        <div class="ledger-regist__control">
                            <select name="group" class="input-field ledger-regist__input ledger-regist__select" id="group">
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->グループ名 }}</option>
                                @endforeach
                                <option value="{{ Auth::id() }}">指定なし</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field ledger-regist__field--span2">
                        <label class="ledger-regist__label" for="kennsakuword">検索ワード</label>
                        <div class="ledger-regist__control">
                            <input type="text" name="kennsakuword" class="input-field ledger-regist__input" id="kennsakuword">
                            <span class="errorelement ledger-regist__error" id="kennsakuwordformat">形式が不正です</span>
                        </div>
                    </div>
                </div>

                <div class="ledger-regist__submit">
                    <input type="submit" value="登録" id="registbutton" class="ledger-regist__submit-btn">
                </div>
            </div>

            <div class="previewarea registpreviewarea ledger-regist__preview">
                プレビュー
            </div>
            </div>
        </form>
    </div>

    @if(!empty($banbanEnabled))
    <div class="ledger-regist-tabpanel {{ ($ledgerRegistMode ?? 'normal') === 'bulk' ? 'is-active' : '' }}" id="ledgerRegistTabBulk" role="tabpanel" aria-labelledby="ledgerRegistTabBulkButton">
        <div class="ledger-bulk-layout" id="ledgerBulkLayout">
        <form class="ledger-bulk" action="{{ route('registBulkPost') }}" method="post" enctype="multipart/form-data" id="bulkRegistForm">
            @csrf
            <input type="file" id="bulkFiles" name="files[]" class="ledger-regist__file-input" multiple accept="image/*,application/pdf">

            <div class="ledger-bulk__files-bar ledger-bulk-dropzone" id="bulkFilesBar">
                <div class="ledger-regist__file-row ledger-bulk__file-pick" id="bulkFilePickRow">
                    <div class="ledger-regist__dropzone" id="bulkPickZone" role="button" tabindex="0" aria-label="複数ファイルをドラッグ＆ドロップ。ファイル選択で複数まとめて選べます">
                        <span class="ledger-bulk__dropzone-main">ここにドラッグ＆ドロップ</span>
                        <span class="ledger-bulk__dropzone-hint">複数ファイルをまとめて選択できます</span>
                    </div>
                    <label class="ledger-regist__file-button" for="bulkFiles">ファイルを選択<br><span class="ledger-bulk__file-button-note">（複数可）</span></label>
                </div>
                <div class="ledger-bulk__files-loaded is-hidden" id="bulkFilesLoaded">
                    <div class="ledger-regist__file-row ledger-bulk__list-head">
                        <span class="ledger-bulk__list-count" id="bulkListCount"></span>
                        <button type="button" class="ledger-regist__file-button" id="bulkAddMoreFiles">ファイルを追加<br><span class="ledger-bulk__file-button-note">（複数可）</span></button>
                    </div>
                    <div class="ledger-bulk__list" id="bulkFileList" aria-live="polite"></div>
                </div>
            </div>

            <div class="ledger-bulk__fields" id="bulkDetailPanel"></div>

            <div class="ledger-bulk__assist">
                <button type="button" class="ledger-bulk__action ledger-bulk__action--ocr" id="bulkAiOcrAll" disabled>一括でAI OCRで読み込む</button>
            </div>

            <div class="ledger-bulk__shared">
                <div class="ledger-bulk__common-grid">
                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="bulkCommonSyorui">書類区分<span class="requirered">*</span></label>
                        <div class="ledger-regist__control ledger-regist__control--with-ocr-detail">
                            <select class="input-field ledger-regist__input ledger-regist__select" id="bulkCommonSyorui">
                                @foreach($documents as $document)
                                <option value="{{ $document->id }}" data-ocr-sum-amounts="{{ $document->ocrSumAmountsEnabled() ? '1' : '0' }}" data-ocr-tax-included="{{ $document->ocrTaxIncluded() ? '1' : '0' }}">{{ $document->書類 }}</option>
                                @endforeach
                            </select>
                            @if(!empty($ichifujiEnabled))
                            <button type="button" class="ledger-ocr-settings-detail-btn" data-ledger-ocr-settings-open data-ocr-syorui-source="bulk" aria-label="AI OCR の読み取り設定">
                                <img src="{{ asset($prefix.'/img/settings_4_line.svg') }}" class="ledger-ocr-settings-detail-btn__icon" alt="">
                                <span class="ledger-ocr-settings-detail-btn__text">詳細</span>
                            </button>
                            <span class="ledger-ocr-settings-chips" id="ledgerOcrSettingsChipBulk" aria-live="polite" aria-label="現在の AI OCR 設定"></span>
                            @endif
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="bulkCommonTeisyutu">受領・提出<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select class="input-field ledger-regist__input ledger-regist__select" id="bulkCommonTeisyutu">
                                <option>受領</option>
                                <option>提出</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="bulkCommonHozonn">保存方法<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select class="input-field ledger-regist__input ledger-regist__select" id="bulkCommonHozonn">
                                <option>電子保存</option>
                                <option>スキャナ保存</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="bulkCommonGroup">グループ</label>
                        <div class="ledger-regist__control">
                            <select class="input-field ledger-regist__input ledger-regist__select" id="bulkCommonGroup">
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->グループ名 }}</option>
                                @endforeach
                                <option value="{{ Auth::id() }}">指定なし</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="bulkCommonKensaku">検索ワード</label>
                        <div class="ledger-regist__control">
                            <input type="text" class="input-field ledger-regist__input" id="bulkCommonKensaku">
                        </div>
                    </div>
                </div>
            </div>

            <div class="ledger-bulk__submit">
                <button type="submit" class="ledger-bulk__submit-btn" id="bulkRegistButton" disabled>一括登録</button>
            </div>
        </form>
        <div class="ledger-bulk__preview-col" id="bulkPreviewCol" aria-live="polite">
            <div class="ledger-bulk__preview previewarea" id="bulkSharedPreview">プレビュー</div>
        </div>
        </div>
    </div>
    @endif

    @if(!empty($ichifujiEnabled))
<div class="ledger-regist-modal ledger-ocr-settings-modal" id="ledgerOcrSettingsModal" role="dialog" aria-labelledby="ledgerOcrSettingsModalTitle" aria-hidden="true">
    <div class="ledger-regist-modal__backdrop" data-ledger-ocr-settings-close tabindex="-1"></div>
    <div class="ledger-regist-modal__dialog ledger-ocr-settings-modal__dialog">
        <div class="ledger-regist-modal__header">
            <h2 class="ledger-regist-modal__title" id="ledgerOcrSettingsModalTitle">AI OCR の読み取り設定</h2>
            <button type="button" class="ledger-regist-modal__close" data-ledger-ocr-settings-close aria-label="閉じる">&times;</button>
        </div>
        <div class="ledger-regist-modal__body ledger-ocr-settings-modal__body">
            <p class="ledger-ocr-options__lead">この操作での AI OCR のみ適用されます（帳簿登録内容そのものは変わりません）。</p>
            <p class="ledger-ocr-options__master" id="ledgerOcrOptionsMasterHint"></p>
            <label class="ledger-ocr-options__check is-hidden" id="ledgerOcrSumAmountsWrap">
                <input type="checkbox" id="ledgerOcrUseSumAmounts" checked>
                <span>複数ページ・複数セットの金額を合算する</span>
            </label>
            <div class="ledger-ocr-options__tax">
                <span class="ledger-ocr-options__tax-label">金額の税区分</span>
                <label class="ledger-ocr-options__radio">
                    <input type="radio" name="ledger_ocr_tax_mode" value="excluded" checked>
                    <span>税抜</span>
                </label>
                <label class="ledger-ocr-options__radio">
                    <input type="radio" name="ledger_ocr_tax_mode" value="included">
                    <span>税込</span>
                </label>
            </div>
            <p class="ledger-ocr-options__tax-note">税込を選んだ場合、税抜表示のときは税抜金額から税込金額を計算します。</p>
        </div>
        <div class="ledger-ocr-settings-modal__footer">
            <button type="button" class="ledger-ocr-settings-modal__close-btn" data-ledger-ocr-settings-close>閉じる</button>
        </div>
    </div>
</div>
    @endif
</div>

<div class="ledger-regist-modal ledger-ocr-kinngaku-breakdown-modal" id="ledgerOcrKinngakuBreakdownModal" role="dialog" aria-labelledby="ledgerOcrKinngakuBreakdownModalTitle" aria-hidden="true">
    <div class="ledger-regist-modal__backdrop" data-kinngaku-breakdown-close tabindex="-1"></div>
    <div class="ledger-regist-modal__dialog ledger-ocr-kinngaku-breakdown-modal__dialog">
        <div class="ledger-regist-modal__header">
            <h2 class="ledger-regist-modal__title" id="ledgerOcrKinngakuBreakdownModalTitle">OCR合算の内訳</h2>
            <button type="button" class="ledger-regist-modal__close" data-kinngaku-breakdown-close aria-label="閉じる">&times;</button>
        </div>
        <div class="ledger-regist-modal__body ledger-ocr-kinngaku-breakdown-modal__body">
            <p class="ledger-ocr-kinngaku-breakdown-modal__hint">×を押すと合算から除外できます。再度押すと含め直せます。</p>
            <div class="ledger-ocr-kinngaku-breakdown-modal__list" id="ledgerOcrKinngakuBreakdownList"></div>
        </div>
        <div class="ledger-ocr-kinngaku-breakdown-modal__footer">
            <p class="ledger-ocr-kinngaku-breakdown-modal__sum">合計: <strong id="ledgerOcrKinngakuBreakdownTotal">0円</strong></p>
            <div class="ledger-ocr-kinngaku-breakdown-modal__actions">
                <button type="button" class="ledger-ocr-kinngaku-breakdown-modal__cancel" data-kinngaku-breakdown-close>閉じる</button>
                <button type="button" class="ledger-ocr-kinngaku-breakdown-modal__apply" id="ledgerOcrKinngakuBreakdownApply">金額に反映</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer')
@endsection
