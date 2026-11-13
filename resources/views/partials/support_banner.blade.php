@php
    $support = session('platform_support');
@endphp
@if (!empty($support['active']))
<style>
.platform-support-banner {
  position: sticky; top: 0; z-index: 9999;
  display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
  padding: 10px 16px; background: #c2410c; color: #fff; font-size: 14px;
}
.platform-support-banner form { margin: 0; }
.platform-support-banner button {
  border: 0; border-radius: 6px; padding: 6px 12px; cursor: pointer;
  background: #fff; color: #7c2d12; font-weight: 600;
}
</style>
<div class="platform-support-banner">
    <div>
        [サポート中]
        platformユーザー「{{ $support['platform_admin_name'] ?? 'unknown' }}」が
        「{{ Auth::user()->表示名 ?: Auth::user()->name }}」として表示しています
        （{{ ($support['mode'] ?? '') === 'l2' ? 'L2代理' : 'L1入場' }}）
    </div>
    <form method="POST" action="{{ route('support.exit') }}">
        @csrf
        <button type="submit">サポート終了</button>
    </form>
</div>
@endif
