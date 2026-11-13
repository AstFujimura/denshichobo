<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理画面') — Binder管理画面</title>
    <link rel="stylesheet" href="{{ asset('css/platform.css') }}">
</head>
<body>
<div class="platform-shell">
    <div class="platform-top">
        <a class="platform-brand" href="{{ route('platform.tenants.index') }}">
            <span class="platform-brand-mark">B</span>
            <span>Binder管理画面</span>
        </a>
        <div class="platform-nav">
            <a href="{{ route('platform.tenants.index') }}">テナント管理</a>
            <span class="platform-user">{{ Auth::guard('platform')->user()->display_name ?: Auth::guard('platform')->user()->name }}</span>
            <form method="POST" action="{{ route('platform.logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="platform-btn secondary">ログアウト</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="platform-alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="platform-alert error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="platform-alert error">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>
</body>
</html>
