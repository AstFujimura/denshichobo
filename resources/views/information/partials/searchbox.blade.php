@php
    $datacountZenkenValue = $datacountZenkenValue ?? '10000';
    $searchDetailOpen = $searchDetailOpen ?? false;
    $detailOpenClass = $searchDetailOpen ? 'is-open' : '';
    $detailExpanded = $searchDetailOpen ? 'true' : 'false';
@endphp
<div class="searchbox">
    <div class="searchbox-layout">
        <div class="searchbox-primary">
            <div class="searchelement searchelement--range searchelement--date">
                <label class="searchlabel" for="startyear">取引日</label>
                <div class="searchfield-group searchfield-group--range">
                    <input type="text" id="startyear" name="starthiduke" value="{{ $starthiduke ?? '' }}" class="searchinputtext dateinputtext search-date-flatpickr" autocomplete="off">
                    <span class="searchfield-separator">～</span>
                    <input type="text" id="endyear" name="endhiduke" value="{{ $endhiduke ?? '' }}" class="searchinputtext dateinputtext search-date-flatpickr" autocomplete="off">
                </div>
            </div>

            <div class="searchelement searchelement--range searchelement--date">
                <label class="searchlabel" for="starttourokubi">登録日時</label>
                <div class="searchfield-group searchfield-group--range">
                    <input type="text" id="starttourokubi" name="starttourokubi" value="{{ $starttourokubi ?? '' }}" class="searchinputtext dateinputtext search-date-flatpickr" autocomplete="off">
                    <span class="searchfield-separator">～</span>
                    <input type="text" id="endtourokubi" name="endtourokubi" value="{{ $endtourokubi ?? '' }}" class="searchinputtext dateinputtext search-date-flatpickr" autocomplete="off">
                </div>
            </div>

            <div class="searchelement searchelement--range searchelement--amount">
                <label class="searchlabel" for="startkinngaku">金額</label>
                <div class="searchfield-group searchfield-group--range">
                    <input type="text" id="startkinngaku" name="startkinngaku" value="{{ $startkinngaku ?? '' }}" class="searchinputtext kinngakuinput">
                    <span class="searchfield-unit">円</span>
                    <span class="searchfield-separator">～</span>
                    <input type="text" id="endkinngaku" name="endkinngaku" value="{{ $endkinngaku ?? '' }}" class="searchinputtext kinngakuinput">
                    <span class="searchfield-unit">円</span>
                </div>
            </div>

            <div class="searchelement searchelement--torihikisaki">
                <label class="searchlabel" for="torihikisaki">取引先</label>
                <div class="searchfield-control">
                    <input type="text" id="torihikisaki" name="torihikisaki" value="{{ $torihikisaki ?? '' }}" class="searchinputtext torihikisakiinput" placeholder="部分一致" autocomplete="off">
                    <div class="torihikisakiselect" id="torihikisakiselect"></div>
                </div>
            </div>

            <div class="searchelement searchelement--keyword">
                <label class="searchlabel" for="kennsakuword">検索ワード</label>
                <input type="text" id="kennsakuword" name="kennsakuword" value="{{ $kennsakuword ?? '' }}" class="searchinputtext kensakuwordinput" placeholder="部分一致">
            </div>
        </div>

        <button type="button" class="searchbox-detail-toggle {{ $detailOpenClass }}" aria-expanded="{{ $detailExpanded }}" aria-controls="searchbox-detail-fields">
            詳細検索
        </button>

        <div id="searchbox-detail-fields" class="searchbox-detail {{ $detailOpenClass }}">
            <div class="searchelement">
                <label class="searchlabel" for="syoruikubunn">書類区分</label>
                <select id="syoruikubunn" name="syoruikubunn" class="searchinputtext searchselect">
                    <option></option>
                    @foreach($documents as $document)
                    <option {{ $document->selected ?? '' }} value="{{ $document->id }}">{{ $document->書類 }}</option>
                    @endforeach
                </select>
            </div>
            <div class="searchelement">
                <label class="searchlabel" for="teisyutu">提出・受領</label>
                <select id="teisyutu" name="teisyutu" class="searchinputtext searchselect">
                    <option></option>
                    <option {{ $teisyutu ?? '' }}>提出</option>
                    <option {{ $jyuryo ?? '' }}>受領</option>
                </select>
            </div>
            <div class="searchelement">
                <label class="searchlabel" for="hozonn">保存方法</label>
                <select id="hozonn" name="hozonn" class="searchinputtext searchselect">
                    <option {{ $dennshinone ?? '' }}></option>
                    <option {{ $dennshi ?? '' }}>電子保存</option>
                    <option {{ $scan ?? '' }}>スキャナ保存</option>
                </select>
            </div>
            <div class="searchelement">
                <label class="searchlabel" for="selectdata">データ</label>
                <select id="selectdata" name="selectdata" class="searchinputtext dataselect">
                    <option {{ $yukou ?? '' }}>有効データ</option>
                    <option {{ $delete ?? '' }}>削除データ</option>
                    <option {{ $zenken ?? '' }}>全件データ</option>
                </select>
            </div>
            <div class="searchelement">
                <label class="searchlabel" for="datacount">表示件数</label>
                <select id="datacount" name="datacount" class="searchinputtext input">
                    <option {{ $k25 ?? '' }}>25</option>
                    <option {{ $k50 ?? '' }}>50</option>
                    <option {{ $k100 ?? '' }}>100</option>
                    <option {{ $k500 ?? '' }}>500</option>
                    <option value="{{ $datacountZenkenValue }}" {{ $k100000 ?? $k10000 ?? '' }}>全件</option>
                </select>
            </div>
            <div class="searchelement">
                <label class="searchlabel" for="search-group">グループ</label>
                <select id="search-group" name="group" class="searchinputtext userselectbox">
                    <option></option>
                    @foreach($groups as $group)
                    <option {{ $group->groupselected ?? '' }} value="{{ $group->id }}">{{ $group->グループ名 }}</option>
                    @endforeach
                </select>
            </div>
            <div class="searchelement">
                <label class="searchlabel" for="search-updater">更新者</label>
                <select id="search-updater" name="updater" class="searchinputtext userselectbox">
                    <option></option>
                    @foreach($users as $user)
                    <option {{ $user->updaterselected ?? '' }} value="{{ $user->id }}">{{ $user->表示名 ?? $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="searchelement">
                <label class="searchlabel" for="search-creater">作成者</label>
                <select id="search-creater" name="creater" class="searchinputtext userselectbox">
                    <option></option>
                    @foreach($users as $user)
                    <option {{ $user->createrselected ?? '' }} value="{{ $user->id }}">{{ $user->表示名 ?? $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="searchbox-submit">
            <input type="submit" value="検索" class="searchbutton">
        </div>
    </div>

    <input type="hidden" id="deleteOrzenken" name="deleteOrzenken" value="{{ $deleteOrzenken ?? 'yukou' }}">
</div>
