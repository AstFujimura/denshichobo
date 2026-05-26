<div class="ledger-table">

    <div class="top_table_div">

        <div class="hiduke">取引日</div>

        <div class="kinngaku">金額</div>

        <div class="torihikisaki">取引先</div>

        <div class="syoruikubunn pale">書類区分</div>

        <div class="teisyutu pale">提出・受領</div>

        <div class="hozonn pale">保存方法</div>

        <div class="bikou pale">検索ワード</div>

        <div class="teisei pale">変更歴</div>

        <div class="downloadTd pale">DL.</div>

        <div class="extension pale">形式</div>

        <div class="preview pale">PV.</div>

        <div class="updater verypale">グループ</div>

        <div class="updater verypale">更新者</div>

        <div class="creater verypale">作成者</div>

    </div>



    <div class="top_table_element">

        @foreach ($files as $file)

        @php

            $detailUrl = $prefix . '/detail/' . $file->過去データID;

        @endphp

        @if ($file->削除フラグ == "済")

        <div class="delete_table ledger-table-row" data-detail-href="{{ $detailUrl }}" role="link" tabindex="0">

        @else

        <div class="top_table_body table_selected ledger-table-row" data-detail-href="{{ $detailUrl }}" role="link" tabindex="0">

        @endif

            <div class="hidukeTd hiduke">{{ $file->日付 }}</div>

            <div class="kinngakuTd kinngaku">{{ $file->金額 }}</div>

            <div class="torihikisaki">{{ $file->取引先 }}</div>

            <div class="syoruikubunn">{{ $file->書類 }}</div>

            <div class="teisyutu">{{ $file->提出 }}</div>

            <div class="hozonn">{{ $file->保存 }}</div>

            <div class="bikou">{{ $file->備考 }}</div>

            <div class="teisei">
                @if ($file->バージョン != 1)
                <span class="maru maru--indicator" aria-label="変更履歴あり">〇</span>
                @endif
            </div>

            <div class="downloadTd" data-row-action="stop">

                <img src="{{ asset($prefix.'/img/download_2_line.svg') }}" id="{{ $prefix }}/download/{{ $file->id }}" class="download downloadbutton" alt="ダウンロード">

            </div>

            <div class="extension">{{ $file->ファイル形式 }}</div>

            <div class="preview" data-row-action="stop">

                @if ($file->ファイル形式 == "png"||$file->ファイル形式 == "PNG"||$file->ファイル形式 == "jpg"||$file->ファイル形式 == "jpeg"||$file->ファイル形式 == "JPG"||$file->ファイル形式 == "jpe"||$file->ファイル形式 == "JPEG"||$file->ファイル形式 == "bmp"||$file->ファイル形式 == "gif"||$file->ファイル形式 == "pdf"||$file->ファイル形式 == "PDF")

                <img src="{{ asset($prefix.'/img/file_search_line.svg') }}" class="download previewbutton" id="{{ $file->id }}" alt="プレビュー">

                @endif

            </div>

            @if ($file->グループID < 100000)

            <div class="updater"></div>

            @else

            <div class="updater">{{ $file->グループ名 }}</div>

            @endif

            @php
            $updater = str_replace('(削除ユーザー)', '', $file->更新者);
            $creater = str_replace('(削除ユーザー)', '', $file->作成者);
            @endphp

            <div class="updater">{{ $updater }}</div>

            <div class="creater">{{ $creater }}</div>

        </div>

        @endforeach

    </div>

</div>

