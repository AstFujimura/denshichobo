<html lang="ja">
 
<head>
    <meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ログインページ</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/login.css') }}">


 
</head>
<input type="hidden" id="prefix" value="{{$prefix}}">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>ログインページ</h2>
                </div>
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
                            <button type="submit" class="btn btn-primary">ログイン</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>
