{{-- タグ設定エリア（常に表示）。タグが未登録の場合は誘導リンクを表示する。 --}}
<div class="form_container card_tag_picker_container">
    <table>
        <tr>
            <td>タグ</td>
            <td>
                @if (isset($userTags) && $userTags->isNotEmpty())
                    <div class="card_tag_checkbox_list">
                        @php
                            $selected = old('tag_ids', $checkedTagIds ?? []);
                            if (! is_array($selected)) {
                                $selected = [];
                            }
                            $selected = array_map('intval', $selected);
                        @endphp
                        @foreach ($userTags as $tag)
                            <label class="card_tag_checkbox_item" style="--tag-color: {{ $tag->カラーコード }}">
                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                    @checked(in_array((int) $tag->id, $selected, true))>
                                <span class="card_tag_checkbox_label">{{ $tag->タグ名 }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="card_tag_checkbox_empty">
                        まだタグが登録されていません。
                        <a href="{{ route('cardtagsettingsget') }}" target="_blank" rel="noopener">タグ設定画面</a>
                        からタグを追加できます。
                    </div>
                @endif
            </td>
            <td></td>
        </tr>
    </table>
</div>
