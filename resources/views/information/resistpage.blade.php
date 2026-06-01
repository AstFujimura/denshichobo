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

    @if(!empty($banbanEnabled))
    <div class="ledger-regist-tabs" role="tablist" aria-label="取込方式">
        <button type="button" class="ledger-regist-tabs__tab is-active" role="tab" aria-selected="true" aria-controls="ledgerRegistTabNormal" id="ledgerRegistTabNormalButton" data-ledger-tab="normal">通常取込</button>
        <button type="button" class="ledger-regist-tabs__tab" role="tab" aria-selected="false" aria-controls="ledgerRegistTabBulk" id="ledgerRegistTabBulkButton" data-ledger-tab="bulk">一括取込</button>
    </div>
    @endif

    <div class="ledger-regist-tabpanel is-active" id="ledgerRegistTabNormal" role="tabpanel" aria-labelledby="ledgerRegistTabNormalButton">
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

                    <div class="ledger-regist__field ledger-regist__field--aiocr">
                        <label class="ledger-regist__label" aria-hidden="true">&nbsp;</label>
                        <div class="ledger-regist__control ledger-regist__aiocr-slot" id="aiOcrButtonSlot"></div>
                    </div>
                </div>

                <div class="ledger-regist__detail-grid">
                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="syorui">書類区分<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select name="syorui" class="input-field ledger-regist__input ledger-regist__select" id="syorui">
                                @foreach($documents as $document)
                                <option value="{{ $document->id }}">{{ $document->書類 }}</option>
                                @endforeach
                            </select>
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

                <div class="ledger-regist__actions">
                    <input type="submit" value="登録" id="registbutton" class="registbutton ledger-regist__submit">
                </div>
            </div>

            <div class="previewarea registpreviewarea ledger-regist__preview">
                プレビュー
            </div>
            </div>
        </form>
    </div>

    @if(!empty($banbanEnabled))
    <div class="ledger-regist-tabpanel" id="ledgerRegistTabBulk" role="tabpanel" aria-labelledby="ledgerRegistTabBulkButton">
        <form class="ledger-bulk" action="{{ route('registBulkPost') }}" method="post" enctype="multipart/form-data" id="bulkRegistForm">
            @csrf
            <div class="ledger-bulk__file-row">
                <label class="ledger-regist__file-button" for="bulkFiles">ファイルを複数選択</label>
                <input type="file" id="bulkFiles" name="files[]" class="ledger-regist__file-input" multiple>
                <span class="ledger-bulk__hint">※画像/PDFを複数選択できます</span>
            </div>

            <div class="ledger-bulk__common">
                <div class="ledger-bulk__common-grid">
                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="bulkCommonSyorui">書類区分<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select class="input-field ledger-regist__input ledger-regist__select" id="bulkCommonSyorui">
                                @foreach($documents as $document)
                                <option value="{{ $document->id }}">{{ $document->書類 }}</option>
                                @endforeach
                            </select>
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

                    <div class="ledger-regist__field ledger-regist__field--span2">
                        <label class="ledger-regist__label" for="bulkCommonKensaku">検索ワード</label>
                        <div class="ledger-regist__control">
                            <input type="text" class="input-field ledger-regist__input" id="bulkCommonKensaku">
                        </div>
                    </div>
                </div>

                <div class="ledger-bulk__common-actions is-hidden" id="bulkCommonActions">
                    <button type="button" class="ledger-bulk__action" id="bulkApplyCommon">適用</button>
                    <button type="button" class="ledger-bulk__action ledger-bulk__action--primary" id="bulkAiOcrAll">一括でAI OCRで読み込む</button>
                    <button type="submit" class="ledger-bulk__action ledger-bulk__action--primary" id="bulkRegistButton" disabled>一括登録</button>
                </div>
            </div>

            <div class="ledger-bulk__list" id="bulkFileList" aria-live="polite"></div>
        </form>
    </div>
    @endif
</div>

@endsection

@section('footer')
@endsection
