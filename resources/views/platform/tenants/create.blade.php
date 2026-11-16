@extends('platform.layout')

@section('title', 'テナント追加')

@section('content')
<div class="platform-page-heading">
    <div>
        <h1>テナント追加</h1>
    </div>
    <a href="{{ route('platform.tenants.index') }}" class="platform-btn secondary">一覧へ戻る</a>
</div>

<form method="POST" action="{{ route('platform.tenants.store') }}" id="tenant-create-form" class="tenant-create-form">
    @csrf
    <input type="hidden" name="otp_code" value="">

    <div class="platform-card tenant-create-basic">
        <h2>基本情報</h2>
        <div class="tenant-create-grid">
            <div>
                <label for="name">顧客名</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="例：株式会社アステック" required>
            </div>
            <div>
                <label for="code">テナントコード</label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" placeholder="例：astec" autocomplete="off" required pattern="[a-z0-9_]+" title="半角小文字・数字・アンダースコアのみ">
            </div>
            <div>
                <label for="prefix">URLプレフィックス</label>
                <input id="prefix" type="text" name="prefix" value="{{ old('prefix') }}" placeholder="例：astec" autocomplete="off" required pattern="[A-Za-z0-9_\-]+" title="半角英数字・ハイフン・アンダースコアのみ">
            </div>
            @php
                $dbSuffix = old('db_database_suffix');
                if ($dbSuffix === null) {
                    $dbFull = (string) old('db_database', '');
                    $dbSuffix = str_starts_with($dbFull, 'denshichobo_') ? substr($dbFull, strlen('denshichobo_')) : $dbFull;
                }
            @endphp
            <div class="tenant-create-span-2">
                <label for="db_database_suffix">データベース名</label>
                <div class="db-name-input">
                    <span>denshichobo_</span>
                    <input id="db_database_suffix" type="text" name="db_database_suffix" value="{{ $dbSuffix }}" placeholder="astec" autocomplete="off" required pattern="[A-Za-z0-9_]+" title="半角英数字・アンダースコアのみ">
                </div>
                <p class="field-help">先頭の denshichobo_ は固定です。後ろだけ入力します。</p>
            </div>
            <div>
                <label for="storage_mode">ファイル保存方式</label>
                <select id="storage_mode" name="storage_mode" required>
                    <option value="cloud" @selected(old('storage_mode', 'cloud') === 'cloud')>クラウド（S3）</option>
                    <option value="onpre" @selected(old('storage_mode') === 'onpre')>ローカル</option>
                </select>
            </div>
            <div>
                <div class="label-with-info">
                    <label for="storage_prefix">保存先プレフィックス</label>
                    <button type="button" class="info-mark" id="open-storage-prefix-info" aria-label="保存先プレフィックスの説明">i</button>
                </div>
                <input id="storage_prefix" type="text" name="storage_prefix" value="{{ old('storage_prefix') }}" placeholder="例：astec" autocomplete="off" pattern="[A-Za-z0-9_\-]*" title="半角英数字・ハイフン・アンダースコアのみ">
            </div>
            <div class="tenant-create-span-2 onpre-path-field" @if (old('storage_mode', 'cloud') !== 'onpre') hidden @endif>
                <label for="file_upload_path">ローカル保存先</label>
                <input id="file_upload_path" type="text" name="file_upload_path" value="{{ old('file_upload_path') }}" placeholder="例：/var/www/denshichobo-files/test">
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
            <p class="feature-catalog-lead">契約するサービスを選びます。</p>
            @include('platform.tenants._features', ['featureMode' => 'create', 'featureStates' => []])
        </div>

        @php
            $aiProvider = old('ledger_ocr_provider', 'gemini');
            $tameruAiOn = old('features') !== null
                ? (bool) old('features.ichifuji')
                : (bool) (config('tenancy.features.ichifuji.default') ?? false);
        @endphp
        <div class="platform-card" id="ai-settings" @if (! $tameruAiOn) hidden @endif>
            <h2>AI設定</h2>
            <div class="meta-grid">
                <div>
                    <label for="ledger_ocr_provider">TAMERU AIの利用先</label>
                    <select id="ledger_ocr_provider" name="ledger_ocr_provider">
                        <option value="gemini" @selected($aiProvider === 'gemini')>Gemini</option>
                        <option value="internal" @selected($aiProvider === 'internal')>社内AI</option>
                    </select>
                </div>
                <div id="gemini-model-field" @if ($aiProvider !== 'gemini') hidden @endif>
                    <label for="gemini_model">Geminiモデル</label>
                    <input id="gemini_model" type="text" name="gemini_model" value="{{ old('gemini_model') }}" placeholder="未入力時は共通設定を使用">
                </div>
            </div>
        </div>
    </div>

    <div class="create-actions">
        <a href="{{ route('platform.tenants.index') }}" class="platform-btn secondary">キャンセル</a>
        <button type="button" class="platform-btn" id="open-create-otp">DBを作成してテナントを追加</button>
    </div>
</form>
@include('platform.tenants._otp_modal', [
    'modalId' => 'create-otp-modal',
    'openButtonId' => 'open-create-otp',
    'targetFormId' => 'tenant-create-form',
    'purpose' => 'create',
    'tenantId' => null,
    'title' => 'テナントの追加',
    'warning' => 'データベースを作成します。メールに届いた6桁の確認コードを入力すると追加を実行します。',
    'executeLabel' => '確認して追加する',
    'danger' => false,
    'reopen' => $errors->has('otp_code'),
    'otpError' => $errors->first('otp_code'),
])
@include('platform.tenants._form_behavior')
@endsection
