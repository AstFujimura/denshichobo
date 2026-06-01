@extends('layouts.template')

@section('title')
帳簿変更 | TAMERU
@endsection

@section('menuebar')
@endsection

@section('menue')
@endsection

@section('main')

<h2 class="pagetitle" id="{{ $file->id }}">帳簿変更</h2>

<div class="ledger-regist ledger-regist--edit droppreview">
    <form class="ledger-regist__form form" action="{{ route('editPost', ['path' => $file->過去データID]) }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" value="{{ $file->過去データID }}" id="id">
        <input type="hidden" id="banbanEnabled" value="{{ !empty($banbanEnabled) ? 1 : 0 }}">
        <input type="hidden" id="aiOcrTraceEnabled" value="{{ config('ai_ocr.trace') ? 1 : 0 }}">

        <div class="ledger-regist__layout">
            <div class="ledger-regist__fields">
                <div class="ledger-regist__file-row">
                    <div class="ledger-regist__dropzone droparea" role="button" tabindex="0">ここにドラッグ＆ドロップ</div>
                    <label class="ledger-regist__file-button" for="file">ファイルを選択</label>
                    <input type="file" name="file" id="file" class="ledger-regist__file-input">
                    <span class="fileerrorelement ledger-regist__error">ファイルを選択してください</span>
                </div>
                <p class="ledger-regist__file-note">※ファイル自体に変更がない場合はファイルを選択しないでください</p>

                <div class="ledger-regist__ocr-grid">
                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="hiduke">取引日<span class="requirered">*</span></label>
                        <div class="ledger-regist__control dateform">
                            <input type="text" name="hiduke" class="input-field dateinputtext ledger-regist-date ledger-regist__input" value="{{ $hiduke }}" id="hiduke" autocomplete="off">
                            <span class="errorelement ledger-regist__error" id="required1">必須項目です</span>
                            <span class="errorelement ledger-regist__error" id="dateformat">形式が不正です</span>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="kinngaku">金額<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <input type="text" name="kinngaku" class="input-field kinngakuinput-field kinngakuedit ledger-regist__input ledger-regist__input--amount" id="kinngaku" value="{{ $file->金額 }}">
                            <span class="errorelement ledger-regist__error" id="required2">必須項目です</span>
                            <span class="errorelement ledger-regist__error" id="kinngakuformat">形式が不正です</span>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="torihikisaki">取引先<span class="requirered">*</span></label>
                        <div class="ledger-regist__control torihikisakiinput">
                            <input type="text" name="torihikisaki" class="input-field ledger-regist__input" id="torihikisaki" value="{{ $file->取引先 }}" autocomplete="off">
                            <div class="registtorihikisakiselect" id="torihikisakiselect"></div>
                            <span class="errorelement ledger-regist__error" id="required3">必須項目です</span>
                            <span class="errorelement ledger-regist__error" id="torihikiformat">形式が不正です</span>
                            @error('torihikisaki')
                            <span class="errorsentence ledger-regist__error">{{ $message }}</span>
                            @enderror
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
                                <option {{ $document->selected }} value="{{ $document->id }}">{{ $document->書類 }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="teisyutu">受領・提出<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select name="teisyutu" class="input-field ledger-regist__input ledger-regist__select" id="teisyutu">
                                <option {{ $jyuryo }}>受領</option>
                                <option {{ $teisyutu }}>提出</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="hozonn">保存方法<span class="requirered">*</span></label>
                        <div class="ledger-regist__control">
                            <select name="hozonn" class="input-field ledger-regist__input ledger-regist__select" id="hozonn">
                                <option {{ $dennshi }}>電子保存</option>
                                <option {{ $scan }}>スキャナ保存</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field">
                        <label class="ledger-regist__label" for="group">グループ</label>
                        <div class="ledger-regist__control">
                            <select name="group" class="input-field ledger-regist__input ledger-regist__select" id="group">
                                @foreach ($groups as $group)
                                <option {{ $group->selected ?? '' }} value="{{ $group->id }}">{{ $group->グループ名 }}</option>
                                @endforeach
                                <option {{ $selectstatus }} value="{{ Auth::id() }}">指定なし</option>
                            </select>
                        </div>
                    </div>

                    <div class="ledger-regist__field ledger-regist__field--span2">
                        <label class="ledger-regist__label" for="kennsakuword">検索ワード</label>
                        <div class="ledger-regist__control">
                            <input type="text" name="kennsakuword" class="input-field ledger-regist__input" value="{{ $file->備考 }}" id="kennsakuword">
                            <span class="errorelement ledger-regist__error" id="kennsakuwordformat">形式が不正です</span>
                            @error('kennsakuword')
                            <span class="errorsentence ledger-regist__error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="ledger-regist__actions ledger-regist__actions--edit">
                    <input type="submit" value="変更" id="registbutton" class="registbutton ledger-regist__submit">
                    <button type="button" class="ledger-regist__delete deletebutton">削除</button>
                </div>
            </div>

            <div class="ledger-regist__preview-panel">
                <div class="ledger-regist__preview-compare">
                    <div class="ledger-regist__preview-block">
                        <div class="ledger-regist__preview-heading-row">
                            <p class="ledger-regist__preview-heading">変更前</p>
                            <button type="button" class="ledger-regist__preview-expand" data-preview-target="past" data-preview-title="変更前" aria-label="変更前を拡大表示">
                                <img src="{{ asset($prefix.'/img/card/large_view.svg') }}" alt="" width="18" height="18">
                            </button>
                        </div>
                        <div class="pastpreview ledger-regist__preview-box">
                            <div class="defaultpreview">{{ $file->ファイル形式 }}形式ファイル</div>
                        </div>
                    </div>
                    <div class="ledger-regist__preview-arrow" aria-hidden="true">→</div>
                    <div class="ledger-regist__preview-block">
                        <div class="ledger-regist__preview-heading-row">
                            <p class="ledger-regist__preview-heading">変更後</p>
                            <button type="button" class="ledger-regist__preview-expand" data-preview-target="new" data-preview-title="変更後" aria-label="変更後を拡大表示">
                                <img src="{{ asset($prefix.'/img/card/large_view.svg') }}" alt="" width="18" height="18">
                            </button>
                        </div>
                        <div class="previewarea editpreviewarea ledger-regist__preview-box">
                            変更なし
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="ledger-regist-modal" id="ledgerRegistPreviewModal" aria-hidden="true">
    <div class="ledger-regist-modal__backdrop"></div>
    <div class="ledger-regist-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ledgerRegistPreviewModalTitle">
        <div class="ledger-regist-modal__header">
            <h3 class="ledger-regist-modal__title" id="ledgerRegistPreviewModalTitle">プレビュー</h3>
            <button type="button" class="ledger-regist-modal__close" aria-label="閉じる">&times;</button>
        </div>
        <div class="ledger-regist-modal__body" id="ledgerRegistPreviewModalBody"></div>
    </div>
</div>

@endsection

@section('footer')
@endsection
