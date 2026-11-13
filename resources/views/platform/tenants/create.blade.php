@extends('platform.layout')

@section('title', 'テナント追加')

@section('content')
<div class="platform-page-heading">
    <div>
        <h1>テナント追加</h1>
    </div>
    <a href="{{ route('platform.tenants.index') }}" class="platform-btn secondary">一覧へ戻る</a>
</div>

<form method="POST" action="{{ route('platform.tenants.store') }}" class="tenant-create-form">
    @csrf

    <div class="platform-card tenant-create-basic">
        <h2>基本情報</h2>
        <div class="tenant-create-grid">
            <div>
                <label for="name">顧客名</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="例：株式会社アステック" required>
            </div>
            <div>
                <label for="code">テナントコード</label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" placeholder="例：astec" required>
            </div>
            <div>
                <label for="prefix">URLプレフィックス</label>
                <input id="prefix" type="text" name="prefix" value="{{ old('prefix') }}" placeholder="例：astec" required>
            </div>
            <div>
                <label for="db_database">データベース名</label>
                <input id="db_database" type="text" name="db_database" value="{{ old('db_database') }}" placeholder="例：binder_astec" required>
            </div>
            <div>
                <label for="storage_mode">ファイル保存方式</label>
                <select id="storage_mode" name="storage_mode" required>
                    <option value="cloud" @selected(old('storage_mode', 'cloud') === 'cloud')>クラウド（S3）</option>
                    <option value="onpre" @selected(old('storage_mode') === 'onpre')>ローカル</option>
                </select>
            </div>
            <div>
                <label for="storage_prefix">保存先プレフィックス</label>
                <input id="storage_prefix" type="text" name="storage_prefix" value="{{ old('storage_prefix') }}" placeholder="未入力時はURLプレフィックス">
            </div>
            <div class="tenant-create-span-2">
                <label for="file_upload_path">ローカル保存先</label>
                <input id="file_upload_path" type="text" name="file_upload_path" value="{{ old('file_upload_path') }}" placeholder="例：\\server\share\astec">
            </div>
            <div class="tenant-create-span-2">
                <label for="notes">運用メモ</label>
                <textarea id="notes" name="notes" placeholder="契約内容や運用上の注意事項">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <div class="tenant-create-lower">
        <div class="platform-card">
            <h2>初期機能</h2>
            <div class="feature-grid tenant-create-features">
                @foreach ($features as $key => $feature)
                    <label class="feature-item">
                        <input
                            type="checkbox"
                            name="features[{{ $key }}]"
                            value="1"
                            @checked(old('features') !== null
                                ? old("features.{$key}", false)
                                : ($feature['default'] ?? false))
                        >
                        <span>{{ $feature['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="platform-card">
            <h2>AI設定</h2>
            <div class="meta-grid">
                <div>
                    <label for="ledger_ocr_provider">TAMERU AIの利用先</label>
                    <select id="ledger_ocr_provider" name="ledger_ocr_provider" required>
                        <option value="gemini" @selected(old('ledger_ocr_provider', 'gemini') === 'gemini')>Gemini</option>
                        <option value="internal" @selected(old('ledger_ocr_provider') === 'internal')>社内AI</option>
                    </select>
                </div>
                <div>
                    <label for="gemini_model">Geminiモデル</label>
                    <input id="gemini_model" type="text" name="gemini_model" value="{{ old('gemini_model') }}" placeholder="未入力時は共通設定を使用">
                </div>
            </div>
        </div>
    </div>

    <div class="create-actions">
        <a href="{{ route('platform.tenants.index') }}" class="platform-btn secondary">キャンセル</a>
        <button type="submit" class="platform-btn">DBを作成してテナントを追加</button>
    </div>
</form>
@endsection
