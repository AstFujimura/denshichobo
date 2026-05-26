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

                    <div class="ledger-regist__field ledger-regist__field--span2">
                        <label class="ledger-regist__label" for="torihikisaki">取引先<span class="requirered">*</span></label>
                        <div class="ledger-regist__control torihikisakiinput">
                            <input type="text" name="torihikisaki" class="input-field ledger-regist__input" id="torihikisaki" autocomplete="off">
                            <div class="registtorihikisakiselect" id="torihikisakiselect"></div>
                            <span class="errorelement ledger-regist__error" id="required3">必須項目です</span>
                            <span class="errorelement ledger-regist__error" id="torihikiformat">形式が不正です</span>
                        </div>
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

@endsection

@section('footer')
@endsection
