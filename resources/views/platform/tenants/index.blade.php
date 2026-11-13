@extends('platform.layout')

@section('title', 'テナント一覧')

@section('content')
<div class="platform-card">
    <div class="platform-page-heading">
        <div>
            <h1>テナント一覧</h1>
            <p class="platform-muted">顧客環境、利用機能、AI設定を一元管理します。</p>
        </div>
        <div class="platform-page-actions">
            <span class="platform-count">全 {{ $tenants->total() }} 件</span>
            @if (Auth::guard('platform')->user()->can_provision)
                <a href="{{ route('platform.tenants.create') }}" class="platform-btn">テナント追加</a>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('platform.tenants.index') }}" class="search-row">
        <label for="tenant-search" class="search-label">テナント検索</label>
        <input id="tenant-search" type="search" name="q" value="{{ $q }}" placeholder="顧客名・テナントコード・URLプレフィックス・DB名">
        <button type="submit" class="platform-btn">検索</button>
        @if ($q !== '')
            <a href="{{ route('platform.tenants.index') }}" class="platform-btn secondary">クリア</a>
        @endif
    </form>

    <div class="platform-table-wrap">
        <table class="platform-table tenant-table">
            <thead>
            <tr>
                <th>顧客名</th>
                <th>テナントコード</th>
                <th>URLプレフィックス</th>
                <th>データベース</th>
                <th>状態</th>
                <th>保存方式</th>
                <th>利用機能</th>
                <th><span class="visually-hidden">操作</span></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($tenants as $tenant)
                @php
                    $statusLabels = [
                        'provisioning' => '準備中',
                        'active' => '利用中',
                        'suspended' => '停止中',
                        'archived' => '保管済み',
                    ];
                    $featureConfig = config('tenancy.features', []);
                    $enabledFeatures = $tenant->features
                        ->where('enabled', true)
                        ->map(fn ($feature) => $featureConfig[$feature->feature_key]['label'] ?? $feature->feature_key);
                @endphp
                <tr>
                    <td>
                        <a class="tenant-name" href="{{ route('platform.tenants.show', $tenant) }}">{{ $tenant->name }}</a>
                    </td>
                    <td><code>{{ $tenant->code }}</code></td>
                    <td><code>/{{ $tenant->prefix }}</code></td>
                    <td><code>{{ $tenant->db_database }}</code></td>
                    <td><span class="badge {{ $tenant->status }}">{{ $statusLabels[$tenant->status] ?? $tenant->status }}</span></td>
                    <td>{{ $tenant->storage_mode === 'cloud' ? 'クラウド' : 'ローカル' }}</td>
                    <td>
                        <div class="feature-tags">
                            @forelse ($enabledFeatures as $featureLabel)
                                <span>{{ $featureLabel }}</span>
                            @empty
                                <span class="platform-muted">なし</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="table-action">
                        <a href="{{ route('platform.tenants.show', $tenant) }}">詳細</a>
                        @if (Auth::guard('platform')->user()->can_provision)
                            <a href="{{ route('platform.tenants.show', $tenant) }}#delete-tenant">削除</a>
                        @endif
                        @if ($tenant->status === 'provisioning' && ! $tenant->provisioned_at && Auth::guard('platform')->user()->can_provision)
                            <form method="POST" action="{{ route('platform.tenants.discard', $tenant) }}" onsubmit="return confirm(@json('準備中の「'.$tenant->name.'」を取り消します。作成途中のデータベースがある場合は削除します。この操作は元に戻せません。'))">
                                @csrf
                                <button type="submit" class="platform-btn danger compact">取り消す</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="platform-empty">テナントが登録されていません。</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $tenants->onEachSide(1)->links('platform.pagination') }}
</div>
@endsection
