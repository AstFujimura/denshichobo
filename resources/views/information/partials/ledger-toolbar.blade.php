<div class="info">
    <div class="info__inner">
        <div class="info__summary showcontainer">
            <span class="showelement" id="showcount1">{{ $startdata }}</span>
            <span class="showelement info__range-sep">-</span>
            <span class="showelement" id="showcount2">{{ $enddata }}件</span>
            <span class="allshowelement">全 {{ $alldata }} 件</span>
        </div>

        <div class="info__pagination pagecontainer">
            @foreach ($paginate as $pagebutton)
            <a class="{{ $pagebutton['class'] }}" href="{{ $prefix }}{{ $pagebutton['a'] }}">{{ $pagebutton['value'] }}</a>
            @endforeach
        </div>

        <div class="info__actions">
            <button type="button" class="excelbutton">
                <img src="{{ asset($prefix.'/img/excel_export.svg') }}" alt="" class="excelbutton__icon" width="18" height="18">
                <span class="excelbutton__label">Excel出力</span>
            </button>
            <p class="excelerror" role="alert">表示件数を500件以下にしてください。</p>
        </div>
    </div>
</div>
