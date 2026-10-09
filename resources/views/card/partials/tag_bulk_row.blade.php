@php
    $id = $row['id'] ?? null;
    $name = $row['タグ名'] ?? '';
    $preset = $row['color_preset'] ?? 'red';
    $customColor = $row['custom_color'] ?? '#e53935';
    $hidden = ! empty($row['非公開']);
    $showCustom = $preset === 'custom';
@endphp
<tr class="tag_bulk_row" data-tag-bulk-row>
    <td data-label="タグ名">
        @if ($id)
            <input type="hidden" name="tags[{{ $index }}][id]" value="{{ $id }}">
        @endif
        <input type="text" name="tags[{{ $index }}][タグ名]" value="{{ $name }}" maxlength="255" class="tag_bulk_name_input"
            autocomplete="off" placeholder="タグ名を入力">
    </td>
    <td class="tag_bulk_color_cell" data-label="カラー">
        <div class="tag_bulk_color_controls">
            <select name="tags[{{ $index }}][color_preset]" class="tag_color_preset_select tag_row_color_preset">
                @foreach ($presetLabels as $key => $label)
                    @if ($key !== 'custom')
                        <option value="{{ $key }}" @selected($preset === $key)>{{ $label }}</option>
                    @endif
                @endforeach
                <option value="custom" @selected($preset === 'custom')>{{ $presetLabels['custom'] }}</option>
            </select>
            <input type="color" name="tags[{{ $index }}][custom_color]" value="{{ $customColor }}"
                class="tag_row_custom_color @if (! $showCustom) display_none @endif">
        </div>
    </td>
    <td class="tag_bulk_hidden_cell" data-label="非公開">
        <label class="tag_hidden_toggle tag_hidden_toggle_compact">
            <input type="checkbox" name="tags[{{ $index }}][非公開]" value="1" class="tag_hidden_toggle_input"
                @checked($hidden)>
            <span class="tag_hidden_toggle_track" aria-hidden="true"></span>
            <span class="tag_hidden_toggle_label tag_hidden_toggle_label_mobile">非公開</span>
        </label>
    </td>
    <td class="tag_bulk_assign_cell" data-label="名刺追加">
        @if ($id)
            <button type="button" class="tag_row_assign_btn" data-tag-id="{{ $id }}" data-tag-name="{{ $name }}"
                    aria-label="このタグに名刺を追加" title="このタグに名刺を追加">
                <svg class="tag_row_assign_btn_icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" y1="8" x2="19" y2="14"></line>
                    <line x1="22" y1="11" x2="16" y2="11"></line>
                </svg>
                <span class="tag_row_assign_btn_text">名刺を追加</span>
            </button>
        @else
            <span class="tag_bulk_assign_placeholder" title="行を保存すると追加できます">保存後に追加できます</span>
        @endif
    </td>
    <td class="tag_bulk_action_cell" data-label="">
        <button type="button" class="tag_row_remove_btn">行を削除</button>
    </td>
</tr>
