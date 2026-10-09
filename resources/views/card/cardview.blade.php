@extends('layouts.cardtemplate')

@section('title')
名刺管理
@endsection

@section('main')
<div class="MainElement card_view_page" id="card_view_title">

    <input type="hidden" id="user_id" value="{{ Auth::user()->id }}">
    <div class="card_view_header">
        <div class="card_view_filters_layout">
            <div class="card_view_filters_left">
                <div class="search_container card_view_search_row">
                    <div class="card_view_search_primary">
                        <div class="card_view_search_field">
                            <svg class="card_view_search_icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" class="search_input" id="card_view_search_input" placeholder="会社名・氏名・キーワードで検索" autocomplete="off">
                        </div>
                        <button type="button" class="card_view_apply_search" title="検索を実行">検索</button>
                        <button type="button" class="card_view_toggle_extra" id="card_view_toggle_extra"
                                aria-expanded="false" aria-controls="card_view_extra_filters"
                                aria-label="条件の表示切替" title="条件の表示切替">
                            <svg class="card_view_toggle_extra_icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="4" y1="21" x2="4" y2="14"></line>
                                <line x1="4" y1="10" x2="4" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12" y2="3"></line>
                                <line x1="20" y1="21" x2="20" y2="16"></line>
                                <line x1="20" y1="12" x2="20" y2="3"></line>
                                <line x1="1" y1="14" x2="7" y2="14"></line>
                                <line x1="9" y1="8" x2="15" y2="8"></line>
                                <line x1="17" y1="16" x2="23" y2="16"></line>
                            </svg>
                            <span class="card_view_toggle_extra_label">条件を表示</span>
                        </button>
                    </div>
                </div>

                <div class="card_view_quick_filters">
                    <label class="card_view_quick_chip card_view_sort_chip" for="sort_select">
                        <svg class="card_view_quick_chip_icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle>
                            <polyline points="12 7 12 12 15 14"></polyline>
                        </svg>
                        <select name="sort_select" id="sort_select" aria-label="並び替え">
                            <option value="3">新しい順</option>
                            <option value="4">更新順</option>
                            <option value="1">名前順</option>
                            <option value="2">会社名順</option>
                        </select>
                    </label>
                    <button type="button" class="card_view_quick_chip card_view_tag_modal_open" aria-haspopup="dialog" aria-controls="card_view_tag_modal">
                        <svg class="card_view_quick_chip_icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                        <span class="card_view_tag_modal_open_label">タグ選択</span>
                    </button>
                    <div id="card_view_meta_slot_mobile" class="card_view_meta_slot card_view_meta_slot_mobile"></div>
                </div>

                <div class="sub_search_container is_collapsed" id="card_view_extra_filters">
                    <div class="sub_search_container_item">
                        <label class="sub_search_container_item_label" for="start_date">登録年月日(開始)</label>
                        <input type="text" placeholder="登録年月日(開始)" id="start_date" autocomplete="off">
                    </div>
                    <div class="sub_search_container_item">
                        <label class="sub_search_container_item_label" for="end_date">登録年月日(終了)</label>
                        <input type="text" placeholder="登録年月日(終了)" id="end_date" autocomplete="off">
                    </div>
                </div>
            </div>

            <aside class="card_view_filters_right" id="card_view_tag_slot_desktop" aria-label="タグで絞り込み">
                <div id="card_view_tag_filter_movable" class="card_view_tag_filter_block">
                    <div class="card_view_tag_filter_heading">タグで絞り込み<span class="card_view_tag_filter_heading_note">（複数可・いずれか一致）</span></div>
                    <div class="card_view_tag_filter_list">
                        @forelse (($filterTags ?? []) as $ftag)
                        @php
                            $ftHex = trim((string) $ftag->カラーコード);
                            $ftHexOk = (bool) preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $ftHex);
                            $ftBg = $ftHexOk ? $ftHex : '#cccccc';
                            $ftPrivate = !empty($ftag->非公開);
                            $ftHexFull = ltrim($ftBg, '#');
                            if (strlen($ftHexFull) === 3) {
                                $ftHexFull = $ftHexFull[0].$ftHexFull[0].$ftHexFull[1].$ftHexFull[1].$ftHexFull[2].$ftHexFull[2];
                            }
                            $ftR = hexdec(substr($ftHexFull, 0, 2));
                            $ftG = hexdec(substr($ftHexFull, 2, 2));
                            $ftB = hexdec(substr($ftHexFull, 4, 2));
                            $ftLuma = (0.299 * $ftR + 0.587 * $ftG + 0.114 * $ftB) / 255;
                            $ftFg = $ftLuma > 0.62 ? '#1f1f1f' : '#ffffff';
                        @endphp
                        <label class="card_view_tag_filter_item @if($ftPrivate) is_private @endif"
                               style="background-color: {{ $ftBg }}; color: {{ $ftFg }};"
                               @if($ftPrivate) title="非公開タグ（他のユーザーには表示されません）" @endif>
                            <input type="checkbox" class="card_view_tag_filter_cb" value="{{ $ftag->id }}" name="card_view_tag_filter[]">
                            <span class="card_view_tag_filter_name">{{ $ftag->タグ名 }}</span>
                            @if($ftPrivate)
                            <span class="card_view_tag_filter_private" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4.5" y="10.5" width="15" height="10.5" rx="2"></rect>
                                    <path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"></path>
                                </svg>
                            </span>
                            @endif
                        </label>
                        @empty
                        <span class="card_view_tag_filter_empty">登録済みタグがありません。<a href="{{ route('cardtagsettingsget') }}">タグ設定</a>から追加できます。</span>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>

        <div class="card_view_tag_modal" id="card_view_tag_modal" role="dialog" aria-modal="true" aria-labelledby="card_view_tag_modal_title" hidden>
            <div class="card_view_tag_modal_overlay" tabindex="-1"></div>
            <div class="card_view_tag_modal_panel">
                <div class="card_view_tag_modal_header">
                    <h2 class="card_view_tag_modal_title" id="card_view_tag_modal_title">タグ選択</h2>
                    <button type="button" class="card_view_tag_modal_close" aria-label="閉じる">&times;</button>
                </div>
                <p class="card_view_tag_modal_hint">一覧をタグで絞り込みます。タグの追加・変更は<a href="{{ route('cardtagsettingsget') }}">タグ設定画面</a>へ。</p>
                <div id="card_view_tag_slot_modal" class="card_view_tag_modal_body"></div>
                <div class="card_view_tag_modal_footer">
                    <button type="button" class="card_view_tag_modal_done">閉じる</button>
                </div>
            </div>
        </div>

        <div class="card_view_header_nav">
            <div class="card_view_result_bar">
                <div class="card_view_result_count">
                    <span class="card_view_header_count_text">{{ $totalCount ?? 0 }}</span>
                    <span class="card_view_result_count_unit">件</span>
                </div>
                <div class="item_view_container card_view_result_view_toggle" aria-label="表示切替">
                    <input type="radio" name="view_type" id="large_view" value="large_view" @if(Auth::user()->名刺表示サイズ) checked @endif>
                    <label for="large_view" class="large_view" title="表示大">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/large_view.svg') }}" alt="">
                    </label>
                    <input type="radio" name="view_type" id="small_view" value="small_view" @if(!Auth::user()->名刺表示サイズ) checked @endif>
                    <label for="small_view" class="small_view" title="表示小">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/small_view.svg') }}" alt="">
                    </label>
                </div>
            </div>

            <div class="tab_container">
                <div class="tab_item" data-tab="all_user">
                    すべて
                    <span class="tab_item_count" data-count-for="all_user">({{ $allCount ?? ($totalCount ?? 0) }})</span>
                </div>
                <div class="tab_item tab_item_active" data-tab="my_card_user">
                    マイ名刺
                    <span class="tab_item_count" data-count-for="my_card_user">({{ $myCount ?? ($totalCount ?? 0) }})</span>
                </div>
            </div>

            <div class="card_view_header_meta" id="card_view_header_meta">
                <div class="card_view_header_count">
                    <div class="card_view_excel_button">
                        Excel出力
                    </div>
                    <form id="card_view_excel_form" action="{{ route('cardviewexcelpost') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="card_view_card_array" id="card_view_card_array">
                        <input type="hidden" name="search">
                        <input type="hidden" name="start_date">
                        <input type="hidden" name="end_date">
                        <input type="hidden" name="sort">
                        <input type="hidden" name="tag_ids" id="card_view_excel_tag_ids" value="">
                        <input type="hidden" name="only_my" id="card_view_excel_only_my" value="1">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card_view_container">
        <div class="card_view_card_header @if(Auth::user()->名刺表示サイズ) large_view @else small_view @endif" aria-hidden="true">
            <div class="card_view_card_header_item" data-sort="name">名前</div>
            <div class="card_view_card_header_item" data-sort="company_name">会社名</div>
            <div class="card_view_card_header_item" data-sort="department_name">部署名</div>
            <div class="card_view_card_header_item" data-sort="created_at">登録年月日</div>
            <div class="card_view_card_header_item" data-sort="updated_at">更新年月日</div>
        </div>
        <div id="card-list">
            @include('card.partials.cardlist', ['cardusers' => $cardusers])
        </div>
    </div>
</div>
@endsection

@section('footer')
@endsection
