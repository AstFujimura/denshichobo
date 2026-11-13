@extends('platform.layout')

@section('title', $tenant->name)

@section('content')
@php
    $statusLabels = [
        'provisioning' => '準備中',
        'active' => '利用中',
        'suspended' => '停止中',
        'archived' => '保管済み',
    ];
@endphp

<div class="platform-card">
    <div class="tenant-detail-heading">
        <div>
            <h1>{{ $tenant->name }} <span class="badge {{ $tenant->status }}">{{ $statusLabels[$tenant->status] ?? $tenant->status }}</span></h1>
            <div class="tenant-meta">
                <span>テナントコード <code>{{ $tenant->code }}</code></span>
                <span>URLプレフィックス <code>/{{ $tenant->prefix }}</code></span>
                <span>DB名 <code>{{ $tenant->db_database }}</code></span>
            </div>
        </div>
        <div class="form-actions">
            @if ($tenant->status === 'provisioning' && ! $tenant->provisioned_at && Auth::guard('platform')->user()->can_provision)
                <form method="POST" action="{{ route('platform.tenants.discard', $tenant) }}" onsubmit="return confirm(@json('準備中の「'.$tenant->name.'」を取り消します。作成途中のデータベースがある場合は削除します。この操作は元に戻せません。'))">
                    @csrf
                    <button type="submit" class="platform-btn danger">作成を取り消す</button>
                </form>
            @endif
            <a href="{{ url('/'.$tenant->prefix.'/login') }}" class="platform-btn secondary" target="_blank" rel="noopener">通常ログインを開く</a>
            <a href="{{ route('platform.tenants.index') }}" class="platform-btn secondary">一覧へ戻る</a>
        </div>
    </div>
</div>

<nav class="settings-nav" aria-label="設定メニュー">
    <a href="#basic-settings">基本情報</a>
    <a href="#feature-settings">利用機能</a>
    <a href="#ai-settings">AI設定</a>
    <a href="#support-access">サポート入場</a>
    <a href="#database-history">DB更新履歴</a>
</nav>

<form method="POST" action="{{ route('platform.tenants.update', $tenant) }}" class="tenant-settings-form tenant-manage-form">
    @csrf
    <div class="platform-card" id="basic-settings">
        <h2>基本情報</h2>
        <div class="tenant-create-grid">
            <div>
                <label for="name">顧客名</label>
                <input id="name" type="text" name="name" value="{{ old('name', $tenant->name) }}" placeholder="例：株式会社アステック" required>
            </div>
            <div>
                <label for="status">利用状態</label>
                <select id="status" name="status">
                    @foreach ($statusLabels as $status => $label)
                        <option value="{{ $status }}" @selected(old('status', $tenant->status) === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="storage_mode">ファイル保存方式</label>
                <select id="storage_mode" name="storage_mode">
                    <option value="cloud" @selected(old('storage_mode', $tenant->storage_mode) === 'cloud')>クラウド（S3）</option>
                    <option value="onpre" @selected(old('storage_mode', $tenant->storage_mode) === 'onpre')>オンプレ</option>
                </select>
            </div>
            <div>
                <label for="storage_prefix">保存先プレフィックス</label>
                <input id="storage_prefix" type="text" name="storage_prefix" value="{{ old('storage_prefix', $tenant->storage_prefix) }}" placeholder="例：astec" required>
            </div>
            <div class="tenant-create-span-2 onpre-path-field">
                <label for="file_upload_path">オンプレ保存先</label>
                <div class="path-input-row">
                    <input id="file_upload_path" type="text" name="file_upload_path" value="{{ old('file_upload_path', $tenant->file_upload_path) }}" placeholder="例：/var/www/denshichobo-files/test">
                    <button type="button" class="platform-btn secondary" id="test-storage-access">接続テスト</button>
                </div>
                <div id="storage-test-result" class="storage-test-result" hidden></div>
            </div>
            <div class="tenant-create-span-2">
                <label for="notes">運用メモ</label>
                <textarea id="notes" name="notes" placeholder="契約内容や運用上の注意事項">{{ old('notes', $tenant->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="tenant-create-lower tenant-manage-lower">
        <div class="platform-card" id="feature-settings">
            <h2>利用機能</h2>
            <div class="feature-grid tenant-create-features">
                @foreach ($features as $key => $feature)
                    <label class="feature-item">
                        <input type="checkbox" name="features[{{ $key }}]" value="1" @checked($feature['enabled'])>
                        <span>{{ $feature['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="platform-card" id="ai-settings">
            <h2>AI設定</h2>
            <div class="meta-grid">
                <div>
                    <label for="ledger_ocr_provider">TAMERU AIの利用先</label>
                    <select id="ledger_ocr_provider" name="ledger_ocr_provider">
                        <option value="gemini" @selected(old('ledger_ocr_provider', $ai->ledger_ocr_provider ?? 'gemini') === 'gemini')>Gemini</option>
                        <option value="internal" @selected(old('ledger_ocr_provider', $ai->ledger_ocr_provider ?? 'gemini') === 'internal')>社内AI</option>
                    </select>
                </div>
                <div>
                    <label for="gemini_model">Geminiモデル</label>
                    <input id="gemini_model" type="text" name="gemini_model" value="{{ old('gemini_model', $ai->gemini_model ?? '') }}" placeholder="未入力時は共通設定を使用">
                </div>
            </div>
        </div>
    </div>

    <div class="tenant-settings-actions">
        <button type="submit" class="platform-btn">設定を一括保存</button>
    </div>
</form>

<div class="platform-card" id="support-access">
    <h2>サポート入場</h2>
    <p class="platform-muted">パスワードを使わず顧客画面へ入り、表示や動作を確認します。操作は監査ログに記録されます。</p>
    <form method="POST" action="{{ route('platform.tenants.support.enter', $tenant) }}" class="form-actions" style="margin-bottom:20px;">
        @csrf
        <button type="submit" class="platform-btn" @disabled($tenant->status !== 'active')>サポート担当者として開く</button>
    </form>

    <h3>顧客ユーザーとして確認</h3>
    @if (! $canImpersonate)
        <p class="platform-muted">顧客ユーザーとして開く権限がありません。</p>
    @elseif ($usersError)
        <div class="platform-alert error">ユーザー一覧を取得できませんでした: {{ $usersError }}</div>
    @else
        <div class="platform-table-wrap">
            <table class="platform-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>ログイン名</th>
                    <th>表示名</th>
                    <th>権限</th>
                    <th><span class="visually-hidden">操作</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($tenantUsers as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->表示名 }}</td>
                        <td>{{ $user->管理 }}</td>
                        <td class="table-action">
                            <form method="POST" action="{{ route('platform.tenants.support.enter', $tenant) }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <button type="submit" class="platform-btn secondary" @disabled($tenant->status !== 'active')>このユーザーで開く</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="platform-empty">ユーザーが登録されていません。</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="platform-card" id="database-history">
    <h2>DB更新履歴</h2>
    @if ($lastRun)
        <p>
            状態: <span class="badge">{{ $lastRun->status }}</span>
            / 処理番号: <code>{{ $lastRun->batch_id }}</code><br>
            {{ $lastRun->from_migration ?: '-' }} → {{ $lastRun->to_migration ?: '-' }}<br>
            <span class="platform-muted">{{ $lastRun->finished_at?->toDateTimeString() ?: $lastRun->started_at?->toDateTimeString() }}</span>
        </p>
        @if ($lastRun->error_message)
            <div class="platform-alert error">{{ $lastRun->error_message }}</div>
        @endif
    @else
        <p class="platform-muted">DB更新履歴はまだありません。</p>
    @endif
</div>

@if (Auth::guard('platform')->user()->can_provision)
<div class="platform-card" id="delete-tenant">
    <h2>テナントの削除</h2>
    <p class="platform-muted">テナント「{{ $tenant->name }}」とデータベース <code>{{ $tenant->db_database }}</code> を削除します。中のデータも消え、元に戻せません。</p>
    <form method="POST" action="{{ route('platform.tenants.destroy', $tenant) }}" class="tenant-delete-form">
        @csrf
        <label for="confirm_code">確認のため、テナントコード <code>{{ $tenant->code }}</code> を入力</label>
        <input id="confirm_code" type="text" name="confirm_code" value="{{ old('confirm_code') }}" autocomplete="off" required>
        <button type="submit" class="platform-btn danger">テナントとデータベースを削除</button>
    </form>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageMode = document.getElementById('storage_mode');
    const pathField = document.querySelector('.onpre-path-field');
    const pathInput = document.getElementById('file_upload_path');
    const testButton = document.getElementById('test-storage-access');
    const result = document.getElementById('storage-test-result');
    const endpoint = @json(route('platform.storage.test-access'));
    const csrfToken = document.querySelector('.tenant-settings-form input[name="_token"]').value;

    function updateStorageFields() {
        pathField.hidden = storageMode.value !== 'onpre';
        if (pathField.hidden) {
            result.hidden = true;
        }
    }

    testButton.addEventListener('click', async function () {
        const path = pathInput.value.trim();
        result.hidden = false;
        result.className = 'storage-test-result pending';
        result.textContent = '接続を確認しています...';
        testButton.disabled = true;

        if (path === '') {
            result.className = 'storage-test-result error';
            result.textContent = 'オンプレ保存先を入力してください。';
            testButton.disabled = false;
            return;
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ path: path })
            });
            const data = await response.json();
            if (! response.ok) {
                throw new Error(data.message || '保存先へ接続できません。');
            }
            result.className = 'storage-test-result success';
            result.textContent = data.message;
        } catch (error) {
            result.className = 'storage-test-result error';
            result.textContent = error.message;
        } finally {
            testButton.disabled = false;
        }
    });

    storageMode.addEventListener('change', updateStorageFields);
    updateStorageFields();
});
</script>
@endsection
