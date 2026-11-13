<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン — Binder管理画面</title>
    <link rel="stylesheet" href="{{ asset('css/platform.css') }}">
</head>
<body>
<div class="platform-login-wrap">
    <div class="platform-login">
        <div class="platform-login-brand">
            <span class="platform-brand-mark">B</span>
            <h1>Binder管理画面</h1>
        </div>
        <p>管理者アカウントでログインしてください。</p>

        @if (session('error'))
            <div class="platform-alert error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('platform.login.post') }}">
            @csrf
            <label for="name">ユーザー名</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>

            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" required>

            <button type="submit" class="platform-btn block">ログイン</button>
        </form>
    </div>
</div>
</body>
</html>
