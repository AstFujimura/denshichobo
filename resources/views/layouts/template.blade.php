<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset(config('prefix.prefix').'/'.'icon/favicon.ico') }}" id="favicon">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/admin01.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/admin001.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/regist.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/jquery-ui.css') }}">
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-3.7.0.min.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-ui.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-ui_ja.js')}}"></script>
    <noscript>
        <meta http-equiv="refresh" content="0;URL=/jserror" />
    </noscript>



</head>


<header class="header001">
    <div class="logo01">
        <a href="{{route('topGet')}}" class="logoelement01">
            電子帳簿保存システム
        </a>
        <input type="hidden" id="server" value="{{config('prefix.server')}}">
        <input type="hidden" id="prefix" value="{{config('prefix.prefix')}}">
    </div>
    
    <span class="version">ver.2.7.2</span>

</header>
<div class="menu001">

    <div class="headerIcon001" onclick="location.href='{{route('topGet')}}';">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/home_3_line.svg') }}" class="menuicon01">
        <div class="iconmessage">Top</div>
    </div>
    <div class="headerIcon001" id="registpagebutton">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/pencil_2_line_gray.svg') }}" class="menuicon01">
        <div class="iconmessage">帳簿保存</div>
    </div>
    <div class="headerIcon001" onclick="location.href='{{route('question')}}';">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/question_line.svg') }}" class="menuicon01">
        <div class="iconmessage">ヘルプ</div>
    </div>

    <div class="headerIcon001 rightmenue001" onclick="location.href='{{route('usersettingGet')}}';">
        <div class="usermenu01">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/user_edit_line.svg') }}" class="usermenuicon01"><span class="topusername01" id="topusername01">{{Auth::user()->name}}</span>
        </div>
        <div class="iconmessage">ユーザー情報</div>
    </div>
    <div class="headerIcon001 menue001">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/menu_fill.svg') }}" class="menuicon01 hamburger01">
        <div class="iconmessage">メニュー</div>
        <img src="{{ asset(config('prefix.prefix').'/'.'img/close_line.svg') }}" class="menuicon01 hamburger01 hamburger01close">
        <div class="iconmessage">閉じる</div>
    </div>


</div>

<body>
    <div class="page-container01">
        <div class="sidebar01">

            <div class="icon01">
                <div class="user01">
                    <div class="usericon01">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/user_1_line.svg') }}" class="usericon01">
                    </div>
                    <div class="username01">
                        {{Auth::user()->name}}
                    </div>

                </div>

            </div>
            <div class="sidebarcontent01">
                <a href="{{route('topGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/home_3_line.svg') }}">
                        </div>
                        <div class="button1name01">
                            帳簿一覧
                        </div>

                    </div>

                </a>
                <a href="{{route('registGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/pencil_2_line.svg') }}">
                        </div>
                        <div class="button1name01">
                            帳簿保存
                        </div>

                    </div>

                </a>
                @if (Auth::user()->管理 == "管理")
                <a href="{{route('adminGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/edit_fill.svg') }}">
                        </div>
                        <div class="button1name01">
                            管理画面
                        </div>

                    </div>

                </a>
                @endif
                <a href="{{route('logout')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/open_door_line.svg') }}">
                        </div>
                        <div class="button1name01">
                            ログアウト
                        </div>

                    </div>

                </a>

            </div>

        </div>
        <main class="main01">


            <div class="maincontent01">
                <nav class="navbar">
                    @yield('menuebar')
                </nav>
                <div class="MenueBar">
                    @yield('menue')
                </div>
                <div class="MainElement">
                    @yield('main')
                </div>

            </div>
        </main>


    </div>

</body>
<footer>
    @yield('footer')
</footer>
<script src="{{ asset(config('prefix.prefix').'/'.'js/all.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/usersetting.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/style.js') }}"></script>
@if ($server == 'onpre')
<script src="{{ asset(config('prefix.prefix').'/'.'js/onpre.js') }}"></script>
@else
<script src="{{ asset(config('prefix.prefix').'/'.'js/cloud.js') }}"></script>
@endif



</footer>


</html>