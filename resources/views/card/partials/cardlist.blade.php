@foreach ($cardusers as $carduser)
@php
    $deptNames = collect($carduser->departments ?? [])->pluck('部署名')->filter()->values();
    $role = trim((string) ($carduser->役職 ?? ''));
    $deptLineParts = $deptNames->all();
    if ($role !== '') {
        $deptLineParts[] = $role;
    }
    $deptLine = implode(' ', $deptLineParts);
    $initial = mb_substr((string) ($carduser->表示名 ?? ''), 0, 1) ?: '名';
@endphp
<a href="{{ route('carddetailget', ['id' => $carduser->carduser_id]) }}"
    class="card_view_card @if(Auth::user()->名刺表示サイズ) large_view @else small_view @endif"
    data-name_kana="{{ $carduser->名前カナ }}"
    data-company_name="{{ $carduser->会社名カナ }}"
    data-card_created_at="{{ $carduser->登録年月日 }}"
    data-card_updated_at="{{ $carduser->更新年月日 }}"
    >
    <div class="card_view_card_avatar" aria-hidden="true">
        <span class="card_view_card_avatar_initial">{{ $initial }}</span>
    </div>

    <div class="text_container">
        <div class="card_view_card_name">
            {{ $carduser->表示名 }}
        </div>
        <div class="company_info">
            <div class="card_view_card_company" data-company_id="{{ $carduser->会社ID }}">
                <span class="card_view_card_company_text">{{ $carduser->会社名 }}</span>
            </div>
            @if($deptLine !== '')
            <div class="card_view_card_department">
                <div class="card_view_card_department_item">{{ $deptLine }}</div>
            </div>
            @endif
        </div>
        @if(!empty($carduser->tag_colors))
        <div class="card_view_card_tag_swatches">
            @foreach ($carduser->tag_colors as $tag)
            @php
                $tagHex = $tag['hex'] ?? '#cccccc';
                $hexFull = ltrim($tagHex, '#');
                if (strlen($hexFull) === 3) {
                    $hexFull = $hexFull[0].$hexFull[0].$hexFull[1].$hexFull[1].$hexFull[2].$hexFull[2];
                }
                $r = hexdec(substr($hexFull, 0, 2));
                $g = hexdec(substr($hexFull, 2, 2));
                $b = hexdec(substr($hexFull, 4, 2));
                $luma = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                $tagFg = $luma > 0.62 ? '#1f1f1f' : '#ffffff';
            @endphp
            <span class="card_view_card_tag_swatch" style="background-color: {{ $tagHex }}; color: {{ $tagFg }};" title="{{ $tag['name'] }}">{{ $tag['name'] }}</span>
            @endforeach
        </div>
        @endif
        <div class="created_at_info">
            <div class="created_at_info_value">{{ date('Y.m.d', strtotime($carduser->登録年月日)) }}</div>
        </div>
        <div class="updated_at_info">
            <div class="updated_at_info_value">{{ date('Y.m.d', strtotime($carduser->更新年月日)) }}</div>
        </div>
    </div>

    <div class="card_view_card_thumb_cluster">
        <img class="lazyload" data-card_id="{{ $carduser->card_id }}" data-front="front" alt="">
        @if(!empty($carduser->has_hidden_tag))
        <span class="card_view_card_lock card_view_card_lock_corner" title="非公開タグが付いた名刺">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="4.5" y="10.5" width="15" height="10.5" rx="2"></rect>
                <path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"></path>
            </svg>
        </span>
        @endif
    </div>

    @if(!empty($carduser->has_hidden_tag))
    <span class="card_view_card_lock card_view_card_lock_edge" title="非公開タグが付いた名刺">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="4.5" y="10.5" width="15" height="10.5" rx="2"></rect>
            <path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"></path>
        </svg>
    </span>
    @endif

    <div class="card_view_card_actions">
        <span
            class="other_user_card_check card_view_card_menu_dots display_none"
            data-carduser_id="{{ $carduser->carduser_id }}"
            role="button"
            tabindex="0"
            aria-label="登録しているユーザーを表示"
            title="登録しているユーザー">
            <span class="card_view_card_menu_dots_icon" aria-hidden="true">
                <span></span><span></span><span></span>
            </span>
            <div class="other_user_list">
                <div class="other_user_list_title">
                    登録しているユーザー
                </div>
            </div>
        </span>
        <span class="card_view_card_chevron" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 6 15 12 9 18"></polyline>
            </svg>
        </span>
    </div>
</a>
@endforeach
