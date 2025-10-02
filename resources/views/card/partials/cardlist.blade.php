@foreach ($cardusers as $carduser)
<a href="{{ route('carddetailget', ['id' => $carduser->carduser_id]) }}" class="card_view_card @if(Auth::user()->名刺表示サイズ) large_view @else small_view @endif"
    data-show="{{ $carduser->マイ名刺ユーザー ?? false }}" data-my_card_user="{{ $carduser->マイ名刺ユーザー ?? false }}"
    {{-- data-favorite_user="{{ $carduser->お気に入りユーザー ?? false }}" --}}
    data-name_kana="{{ $carduser->名前カナ }}"
    data-company_name="{{ $carduser->会社名カナ }}"
    data-card_created_at="{{ $carduser->登録年月日 }}"
    data-card_updated_at="{{ $carduser->更新年月日 }}"
    >
    <div class="text_container">
        <div class="card_view_card_name">
            {{ $carduser->表示名 }}
        </div>
        <div class="company_info">
            <div class="card_view_card_company" data-company_id="{{ $carduser->会社ID }}">
                <span class="card_view_card_company_text">{{ $carduser->会社名 }}</span>
            </div>
            <div class="card_view_card_department">
                @foreach ($carduser->departments as $department)
                <div class="card_view_card_department_item">{{ $department->部署名 }}</div>
                @endforeach
            </div>
        </div>
        <div class="created_at_info">
            <div class="created_at_info_value">{{ date('Y.m.d', strtotime($carduser->登録年月日)) }}</div>
        </div>
        <div class="updated_at_info">
            <div class="updated_at_info_value">{{ date('Y.m.d', strtotime($carduser->更新年月日)) }}</div>
        </div>
    </div>
    <img class="lazyload" data-card_id="{{ $carduser->card_id }}" data-front="front" alt="">
    <div class="other_user_card_check display_none" data-carduser_id="{{ $carduser->carduser_id }}">
        <img class="other_user_card_check_icon"
            src="{{ asset(config('prefix.prefix').'/'.'img/card/other_users.svg') }}" alt="">
        <div class="other_user_list">
            <div class="other_user_list_title">
                登録しているユーザー
            </div>
        </div>
    </div>
</a>
@endforeach