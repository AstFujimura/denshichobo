@extends('layouts.cardtemplate')

@section('title')
タグ設定
@endsection

@section('main')
<div class="card_tag_settings_container MainElement">
    <h2 class="pagetitle" id="card_tag_settings_title">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/tag_gray.svg') }}" alt="" class="title_icon">
        タグ設定
    </h2>

    @if ($errors->any())
        <div class="tag_settings_errors">
            <ul>
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="card_tag_settings_form" class="tag_settings_form" method="post" action="{{ route('cardtagsettingspost') }}">
        @csrf
        <div id="deleted_ids_container" class="deleted_ids_container" aria-hidden="true">
            @foreach (old('deleted_ids', []) as $deletedId)
                <input type="hidden" name="deleted_ids[]" value="{{ $deletedId }}">
            @endforeach
        </div>

        <div class="tag_bulk_table_wrap">
            <table class="tag_bulk_table">
                <thead>
                    <tr>
                        <th class="tag_bulk_col_name">タグ名<span class="required">*</span></th>
                        <th class="tag_bulk_col_color">カラー</th>
                        <th class="tag_bulk_col_hidden">非公開</th>
                        <th class="tag_bulk_col_assign">名刺追加</th>
                        <th class="tag_bulk_col_action"></th>
                    </tr>
                </thead>
                <tbody id="tag_bulk_tbody">
                    @foreach ($rows as $index => $row)
                        @include('card.partials.tag_bulk_row', [
                            'index' => $index,
                            'row' => $row,
                            'presetLabels' => $presetLabels,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="tag_bulk_toolbar">
            <button type="button" class="tag_add_row_btn" id="tag_add_row_btn">追加</button>
            <button type="submit" class="tag_settings_submit" id="tag_bulk_save_btn">一括で保存</button>
        </div>
    </form>

    <template id="tag-bulk-row-template">
        @include('card.partials.tag_bulk_row', [
            'index' => '__INDEX__',
            'row' => [
                'id' => null,
                'タグ名' => '',
                'color_preset' => 'red',
                'custom_color' => '#e53935',
                '非公開' => false,
            ],
            'presetLabels' => $presetLabels,
        ])
    </template>

    {{-- 名刺紐付けモーダル --}}
    <div class="tag_assign_modal" id="tag_assign_modal" role="dialog" aria-modal="true" aria-labelledby="tag_assign_modal_title" hidden>
        <div class="tag_assign_modal_overlay" tabindex="-1"></div>
        <div class="tag_assign_modal_panel">
            <div class="tag_assign_modal_header">
                <h2 class="tag_assign_modal_title" id="tag_assign_modal_title">
                    名刺をタグに追加
                    <span class="tag_assign_modal_tag_label" id="tag_assign_modal_tag_label"></span>
                </h2>
                <button type="button" class="tag_assign_modal_close" aria-label="閉じる">&times;</button>
            </div>
            <p class="tag_assign_modal_hint">右側の候補をクリックするとタグに追加されます。左側の<span class="tag_assign_inline_x">×</span>で外せます。「一括登録」を押すまで保存されません。</p>
            <div class="tag_assign_modal_body">
                <section class="tag_assign_column tag_assign_column_linked">
                    <header class="tag_assign_column_head">
                        <h3 class="tag_assign_column_title">追加済み</h3>
                        <span class="tag_assign_column_count" id="tag_assign_count_linked">0</span>
                    </header>
                    <ul class="tag_assign_list" id="tag_assign_list_linked"></ul>
                </section>
                <section class="tag_assign_column tag_assign_column_unlinked">
                    <header class="tag_assign_column_head">
                        <h3 class="tag_assign_column_title">候補（マイ名刺）</h3>
                        <span class="tag_assign_column_count" id="tag_assign_count_unlinked">0</span>
                    </header>
                    <div class="tag_assign_search_wrap">
                        <input type="text" class="tag_assign_search_input" id="tag_assign_search_input" placeholder="会社名や名前で検索（部分一致）" autocomplete="off">
                    </div>
                    <ul class="tag_assign_list" id="tag_assign_list_unlinked"></ul>
                </section>
            </div>
            <div class="tag_assign_modal_footer">
                <button type="button" class="tag_assign_modal_cancel">キャンセル</button>
                <button type="button" class="tag_assign_modal_save" id="tag_assign_modal_save">一括登録</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
<script>
(function () {
    var tbody = document.getElementById('tag_bulk_tbody');
    var addBtn = document.getElementById('tag_add_row_btn');
    var tpl = document.getElementById('tag-bulk-row-template');
    var deletedContainer = document.getElementById('deleted_ids_container');
    if (!tbody || !addBtn || !tpl) return;

    function nextRowIndex() {
        var max = -1;
        tbody.querySelectorAll('input[name^="tags["]').forEach(function (inp) {
            var m = inp.name.match(/^tags\[(\d+)]/);
            if (m) max = Math.max(max, parseInt(m[1], 10));
        });
        return max + 1;
    }

    function appendNewRow() {
        var idx = nextRowIndex();
        var html = tpl.innerHTML.replace(/__INDEX__/g, String(idx));
        tbody.insertAdjacentHTML('beforeend', html);
        var lastRow = tbody.lastElementChild;
        if (lastRow) {
            var sel = lastRow.querySelector('.tag_row_color_preset');
            if (sel) syncCustomColorVisibility(sel);
        }
    }

    function syncCustomColorVisibility(selectEl) {
        var row = selectEl.closest('tr');
        if (!row) return;
        var colorInput = row.querySelector('.tag_row_custom_color');
        if (!colorInput) return;
        if (selectEl.value === 'custom') {
            colorInput.classList.remove('display_none');
        } else {
            colorInput.classList.add('display_none');
        }
    }

    tbody.addEventListener('change', function (e) {
        if (e.target.classList && e.target.classList.contains('tag_row_color_preset')) {
            syncCustomColorVisibility(e.target);
        }
    });

    tbody.querySelectorAll('.tag_row_color_preset').forEach(syncCustomColorVisibility);

    addBtn.addEventListener('click', appendNewRow);

    tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.tag_row_remove_btn');
        if (!btn) return;
        var tr = btn.closest('tr');
        if (!tr) return;
        var idInput = tr.querySelector('input[name*="[id]"]');
        if (idInput && idInput.value) {
            var h = document.createElement('input');
            h.type = 'hidden';
            h.name = 'deleted_ids[]';
            h.value = idInput.value;
            deletedContainer.appendChild(h);
        }
        tr.remove();
        if (!tbody.querySelector('tr')) {
            appendNewRow();
        }
    });
})();

(function () {
    // タグごとの名刺紐付けモーダル
    var modal = document.getElementById('tag_assign_modal');
    if (!modal) return;
    var tbody = document.getElementById('tag_bulk_tbody');
    var listLinked = document.getElementById('tag_assign_list_linked');
    var listUnlinked = document.getElementById('tag_assign_list_unlinked');
    var countLinked = document.getElementById('tag_assign_count_linked');
    var countUnlinked = document.getElementById('tag_assign_count_unlinked');
    var searchInput = document.getElementById('tag_assign_search_input');
    var saveBtn = document.getElementById('tag_assign_modal_save');
    var tagLabel = document.getElementById('tag_assign_modal_tag_label');
    var overlay = modal.querySelector('.tag_assign_modal_overlay');
    var closeBtns = modal.querySelectorAll('.tag_assign_modal_close, .tag_assign_modal_cancel');

    var apiGetTpl = "{{ route('cardtagcardsget', ['tag' => '__TAG_ID__']) }}";
    var apiPostTpl = "{{ route('cardtagcardspost', ['tag' => '__TAG_ID__']) }}";
    var csrfToken = "{{ csrf_token() }}";

    var state = {
        tagId: null,
        tagName: '',
        // card_id をキーにしたカード情報
        cards: {},
        linkedIds: [],
        unlinkedIds: [],
        // 検索キーワード（クライアント側で再フィルタする）
        query: '',
        loading: false,
        saving: false,
    };

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function buildItemHtml(item, side) {
        var name = escapeHtml(item.name || '(名前未設定)');
        var company = escapeHtml(item.company_name || '');
        var inner = '' +
            '<div class="tag_assign_item_main">' +
                (company ? '<div class="tag_assign_item_company">' + company + '</div>' : '<div class="tag_assign_item_company tag_assign_item_company_empty">（会社未設定）</div>') +
                '<div class="tag_assign_item_name">' + name + '</div>' +
            '</div>';
        if (side === 'linked') {
            inner += '<button type="button" class="tag_assign_item_remove" aria-label="外す">&times;</button>';
        } else {
            inner += '<span class="tag_assign_item_add" aria-hidden="true">+</span>';
        }
        return inner;
    }

    function render() {
        listLinked.innerHTML = '';
        listUnlinked.innerHTML = '';

        var sorter = function (a, b) {
            var aRow = state.cards[a];
            var bRow = state.cards[b];
            return String(aRow && aRow.name_kana || '').localeCompare(String(bRow && bRow.name_kana || ''), 'ja');
        };

        var linkedSorted = state.linkedIds.slice().sort(sorter);
        linkedSorted.forEach(function (id) {
            var item = state.cards[id];
            if (!item) return;
            var li = document.createElement('li');
            li.className = 'tag_assign_item tag_assign_item_linked';
            li.setAttribute('data-card-id', String(id));
            li.innerHTML = buildItemHtml(item, 'linked');
            listLinked.appendChild(li);
        });

        var q = state.query.trim().toLowerCase();
        var unlinkedFiltered = state.unlinkedIds.filter(function (id) {
            var item = state.cards[id];
            if (!item) return false;
            if (!q) return true;
            var name = String(item.name || '').toLowerCase();
            var company = String(item.company_name || '').toLowerCase();
            return name.indexOf(q) !== -1 || company.indexOf(q) !== -1;
        });
        unlinkedFiltered.sort(sorter);

        if (unlinkedFiltered.length === 0) {
            var empty = document.createElement('li');
            empty.className = 'tag_assign_list_empty';
            empty.textContent = q ? '該当する名刺がありません。' : '候補のマイ名刺がありません。';
            listUnlinked.appendChild(empty);
        } else {
            unlinkedFiltered.forEach(function (id) {
                var item = state.cards[id];
                var li = document.createElement('li');
                li.className = 'tag_assign_item tag_assign_item_unlinked';
                li.setAttribute('data-card-id', String(id));
                li.innerHTML = buildItemHtml(item, 'unlinked');
                listUnlinked.appendChild(li);
            });
        }

        countLinked.textContent = state.linkedIds.length;
        countUnlinked.textContent = unlinkedFiltered.length;
    }

    function moveToLinked(cardId) {
        var idx = state.unlinkedIds.indexOf(cardId);
        if (idx >= 0) state.unlinkedIds.splice(idx, 1);
        if (state.linkedIds.indexOf(cardId) < 0) state.linkedIds.push(cardId);
        render();
    }

    function moveToUnlinked(cardId) {
        var idx = state.linkedIds.indexOf(cardId);
        if (idx >= 0) state.linkedIds.splice(idx, 1);
        if (state.unlinkedIds.indexOf(cardId) < 0) state.unlinkedIds.push(cardId);
        render();
    }

    function openModal() {
        modal.removeAttribute('hidden');
        modal.classList.add('is_open');
        document.body.classList.add('tag_assign_modal_open');
    }
    function closeModal() {
        modal.setAttribute('hidden', '');
        modal.classList.remove('is_open');
        document.body.classList.remove('tag_assign_modal_open');
        state.tagId = null;
    }

    function fetchData(tagId) {
        state.loading = true;
        listLinked.innerHTML = '<li class="tag_assign_list_empty">読み込み中…</li>';
        listUnlinked.innerHTML = '<li class="tag_assign_list_empty">読み込み中…</li>';
        var url = apiGetTpl.replace('__TAG_ID__', String(tagId));
        return fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }).then(function (data) {
            state.cards = {};
            state.linkedIds = [];
            state.unlinkedIds = [];
            (data.linked || []).forEach(function (item) {
                state.cards[item.card_id] = item;
                state.linkedIds.push(item.card_id);
            });
            (data.unlinked || []).forEach(function (item) {
                state.cards[item.card_id] = item;
                state.unlinkedIds.push(item.card_id);
            });
            if (data.tag) {
                state.tagName = data.tag.name || '';
                tagLabel.textContent = state.tagName ? '「' + state.tagName + '」' : '';
            }
        }).catch(function () {
            listLinked.innerHTML = '<li class="tag_assign_list_empty">取得に失敗しました。</li>';
            listUnlinked.innerHTML = '<li class="tag_assign_list_empty">取得に失敗しました。</li>';
        }).finally(function () {
            state.loading = false;
            render();
        });
    }

    function save() {
        if (state.saving || state.tagId == null) return;
        state.saving = true;
        saveBtn.disabled = true;
        var url = apiPostTpl.replace('__TAG_ID__', String(state.tagId));
        var fd = new FormData();
        fd.append('_token', csrfToken);
        state.linkedIds.forEach(function (id) {
            fd.append('card_ids[]', String(id));
        });
        fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin',
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }).then(function () {
            closeModal();
            alert('タグへの紐付けを保存しました。');
        }).catch(function () {
            alert('保存に失敗しました。時間をおいて再度お試しください。');
        }).finally(function () {
            state.saving = false;
            saveBtn.disabled = false;
        });
    }

    // 「名刺追加」ボタン
    if (tbody) {
        tbody.addEventListener('click', function (e) {
            var btn = e.target.closest('.tag_row_assign_btn');
            if (!btn) return;
            var tagId = parseInt(btn.getAttribute('data-tag-id'), 10);
            if (!tagId) return;
            var tagName = btn.getAttribute('data-tag-name') || '';
            state.tagId = tagId;
            state.tagName = tagName;
            state.query = '';
            if (searchInput) searchInput.value = '';
            tagLabel.textContent = tagName ? '「' + tagName + '」' : '';
            openModal();
            fetchData(tagId);
        });
    }

    // 候補クリック → 追加
    listUnlinked.addEventListener('click', function (e) {
        var li = e.target.closest('.tag_assign_item_unlinked');
        if (!li) return;
        var id = parseInt(li.getAttribute('data-card-id'), 10);
        if (!id) return;
        moveToLinked(id);
    });

    // 追加済みの×ボタン → 解除
    listLinked.addEventListener('click', function (e) {
        var btn = e.target.closest('.tag_assign_item_remove');
        if (!btn) return;
        var li = btn.closest('.tag_assign_item_linked');
        if (!li) return;
        var id = parseInt(li.getAttribute('data-card-id'), 10);
        if (!id) return;
        moveToUnlinked(id);
    });

    // 検索
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            state.query = searchInput.value || '';
            render();
        });
    }

    // 保存
    if (saveBtn) saveBtn.addEventListener('click', save);

    // 閉じる
    if (overlay) overlay.addEventListener('click', closeModal);
    closeBtns.forEach(function (b) { b.addEventListener('click', closeModal); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is_open')) closeModal();
    });
})();
</script>
@endsection
