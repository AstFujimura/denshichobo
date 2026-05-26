@extends('layouts.template')

@section('title')
ファイル詳細 | TAMERU
@endsection

@section('menuebar')
@endsection

@section('menue')
@endsection

@section('main')
@php
    $updaterName = str_replace('(削除ユーザー)', '', $file->更新者 ?? '');
    $createrName = str_replace('(削除ユーザー)', '', $file->作成者 ?? '');
    $groupName = ($file->グループID ?? 0) >= 100000 ? ($file->グループ名 ?? '') : '';
@endphp

<h2 class="pagetitle">ファイル詳細</h2>

<div class="file-detail">
    <div class="file-detail-summary">
        <dl class="file-detail-summary__grid">
            <div class="file-detail-summary__item">
                <dt>取引日</dt>
                <dd class="hidukeTd">{{ $file->日付 }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>金額</dt>
                <dd class="kinngakuTd">{{ $file->金額 }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>取引先</dt>
                <dd>{{ $file->取引先 }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>書類区分</dt>
                <dd>{{ $file->書類 }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>提出・受領</dt>
                <dd>{{ $file->提出 }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>保存方法</dt>
                <dd>{{ $file->保存 }}</dd>
            </div>
            <div class="file-detail-summary__item file-detail-summary__item--wide">
                <dt>検索ワード</dt>
                <dd>{{ $file->備考 }}</dd>
            </div>
            @if ($groupName !== '')
            <div class="file-detail-summary__item">
                <dt>グループ</dt>
                <dd>{{ $groupName }}</dd>
            </div>
            @endif
            <div class="file-detail-summary__item">
                <dt>更新者</dt>
                <dd>{{ $updaterName }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>作成者</dt>
                <dd>{{ $createrName }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>登録日時</dt>
                <dd>{{ $registeredAt }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>更新日時</dt>
                <dd>{{ $updatedAt }}</dd>
            </div>
            <div class="file-detail-summary__item">
                <dt>過去データID</dt>
                <dd>{{ $file->過去データID }}</dd>
            </div>
        </dl>
    </div>

    <div class="file-detail-actions">
        @if ($file->削除フラグ != "済")
        <a href="{{ $prefix }}/edit/{{ $file->過去データID }}" class="file-detail-edit-button">
            <img src="{{ asset($prefix.'/img/transfer_3_fill.svg') }}" alt="" class="file-detail-edit-button__icon" width="18" height="18">
            <span>変更</span>
        </a>
        @endif
        <a href="{{ $prefix }}/download/{{ $file->id }}" class="file-detail-download-button">
            <img src="{{ asset($prefix.'/img/download_2_line.svg') }}" alt="" class="file-detail-download-button__icon" width="18" height="18">
            <span>ダウンロード</span>
        </a>
    </div>

    <h3 class="file-detail-history-title">変更履歴 <span class="file-detail-history-count">（{{ $count }}件）</span></h3>

    <div class="ledger-table ledger-table--history">
        <div class="history_table_div top_table_div">
            <div class="hiduke">日付</div>
            <div class="kinngaku">金額</div>
            <div class="torihikisaki">取引先</div>
            <div class="syoruikubunn pale">書類区分</div>
            <div class="hozonn pale">保存方法</div>
            <div class="bikou pale">検索ワード</div>
            <div class="filehennkou pale">ファイル変更</div>
            <div class="downloadTd pale">DL.</div>
            <div class="extension pale">形式</div>
            <div class="preview pale">PV.</div>
            <div class="koushinn pale">更新日時</div>
            <div class="updater pale">グループ</div>
            <div class="updater pale">更新者</div>
        </div>

        <div class="top_table_element history_table_element">
            @foreach ($historyFiles as $historyFile)
            @if ($historyFile->バージョン == 9999)
            <div class="delete_history_table_body">
                <div class="hidukeTd hiduke">※削除</div>
                <div class="kinngakuTd kinngaku"></div>
                <div class="torihikisaki"></div>
                <div class="syoruikubunn"></div>
                <div class="hozonn"></div>
                <div class="bikou"></div>
                <div class="filehennkou"></div>
                <div class="downloadTd"></div>
                <div class="extension"></div>
                <div class="preview"></div>
                <div class="koushinn">{{ $historyFile->created_at }}</div>
                <div class="updater"></div>
                <div class="updater">{{ str_replace('(削除ユーザー)', '', $historyFile->更新者 ?? '') }}</div>
            </div>
            @else
            <div class="history_table_body">
                <div class="hidukeTd hiduke">{{ $historyFile->日付 }}</div>
                <div class="kinngakuTd kinngaku">{{ $historyFile->金額 }}</div>
                <div class="torihikisaki">{{ $historyFile->取引先 }}</div>
                <div class="syoruikubunn">{{ $historyFile->書類 }}</div>
                <div class="hozonn">{{ $historyFile->保存 }}</div>
                <div class="bikou">{{ $historyFile->備考 }}</div>
                <div class="filehennkou">{{ $historyFile->ファイル変更 }}</div>
                <div class="downloadTd">
                    <img src="{{ asset($prefix.'/img/download_2_line.svg') }}" id="{{ $prefix }}/download/{{ $historyFile->id }}" class="download downloadbutton" alt="ダウンロード">
                </div>
                <div class="extension">{{ $historyFile->ファイル形式 }}</div>
                <div class="preview">
                    @if ($historyFile->ファイル形式 == "png"||$historyFile->ファイル形式 == "PNG"||$historyFile->ファイル形式 == "jpg"||$historyFile->ファイル形式 == "jpeg"||$historyFile->ファイル形式 == "JPG"||$historyFile->ファイル形式 == "jpe"||$historyFile->ファイル形式 == "JPEG"||$historyFile->ファイル形式 == "bmp"||$historyFile->ファイル形式 == "gif"||$historyFile->ファイル形式 == "pdf"||$historyFile->ファイル形式 == "PDF")
                    <img src="{{ asset($prefix.'/img/file_search_line.svg') }}" class="download previewbutton" id="{{ $historyFile->id }}" alt="プレビュー">
                    @endif
                </div>
                <div class="koushinn">{{ $historyFile->created_at }}</div>
                <div class="updater">
                    @if (($historyFile->グループID ?? 0) >= 100000)
                    {{ $historyFile->グループ名 }}
                    @endif
                </div>
                <div class="updater">{{ str_replace('(削除ユーザー)', '', $historyFile->更新者 ?? '') }}</div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>

<div class="wholecontainer"></div>
<div class="previewcontainer"></div>
@endsection

@section('footer')
@endsection
