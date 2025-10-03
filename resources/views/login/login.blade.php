<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>ログインページ</title>
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/login.css') }}">



</head>
<input type="hidden" id="prefix" value="{{$prefix}}">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                @if (App\Models\Version::where('tameru', false)->first())
                <div class="card-header readbridge_card_header">
                    <h2>名刺管理システム ReadBridge ログインページ</h2>
                </div>
                @else
                <div class="card-header">
                    <h2>ログインページ</h2>
                </div>
                @endif
                <div class="card-body">
                    <form method="POST" action="{{route('loginPost')}}" id="loginForm">
                        @csrf

                        <div class="form-group">
                            <label for="name">ユーザー名</label>
                            <input id="name" type="text" name="name" class="loginText" required>
                        </div>

                        <div class="form-group">
                            <label for="password">パスワード</label>
                            <input id="password" type="password" name="password" class="loginText" required>
                        </div>
                        <div class="alert">
                            {{ session('error') }}
                        </div>

                        <div class="form-group">
                            @if (App\Models\Version::where('tameru', false)->first())
                            <button type="submit" class="btn btn-primary readbridge_btn">ログイン</button>
                            @else
                            <button type="submit" class="btn btn-primary">ログイン</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="produced_by">
    <span>Produced by <a href="https://ast-sys.co.jp/" target="_blank">astec</a></span>
</div>
<script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-3.7.0.min.js')}}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/login.js') }}"></script>