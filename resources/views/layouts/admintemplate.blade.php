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
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/adminstyle.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/regist.css') }}">
    <script src="{{ asset(config('prefix.prefix').'/'.'jquery/jquery-3.7.0.min.js')}}"></script>
    <script src="{{ asset(config('prefix.prefix').'/'.'jquery/laravelui.js')}}"></script>
    <noscript>
        <meta http-equiv="refresh" content="0;URL=/jserror" />
    </noscript>



</head>

<header class="header001">
    <div class="logo01">
        <a href="{{route('adminGet')}}" class="logoelement01">
            管理画面
        </a>
        @if (App\Models\Version::where('tameru', true)->first())
        <a href="{{route('topGet')}}" class="tameru_banner"><img src="{{ asset(config('prefix.prefix').'/'.'img/header/tameru_logo_only.svg') }}" alt="TAMERU"></a>
        @endif
        @if (App\Models\Version::where('フロー', true)->first())
        <a href="{{route('workflow')}}" class="tameru_banner"><img src="{{ asset(config('prefix.prefix').'/'.'img/header/rapid_logo_only.svg') }}" alt="TAMERU"></a>
        @endif
        <input type="hidden" id="server" value="{{config('prefix.server')}}">
        <input type="hidden" id="prefix" value="{{$prefix}}">
    </div>
    <span class="version">ver.3.0.0</span>
</header>
<div class="menu001">

    <a class="headerIcon001" href="{{route('adminGet')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/edit_fill.svg') }}" class="menuicon01" title="ユーザ一覧">
    </a>
    <a class="headerIcon001" href="{{route('adminregistGet')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/user_add_2_fill.svg') }}" class="menuicon01" title="ユーザー登録">
    </a>
    <a class="headerIcon001" href="{{route('admingroupregistGet')}}" >
        <img src="{{ asset(config('prefix.prefix').'/'.'img/group.svg') }}" class="menuicon01" title="グループ登録・編集">
    </a>
    <a class="headerIcon001" href="{{route('admindocumentGet')}}" >
        <img src="{{ asset(config('prefix.prefix').'/'.'img/document_2_line.svg') }}" class="menuicon01" title="書類管理">
    </a>
    <a class="headerIcon001" href="{{route('question')}}" target="_blank">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/question_line.svg') }}" class="menuicon01" title="ヘルプ">
    </a>

    <a class="headerIcon001 rightmenue001" href="{{route('usersettingGet')}}" onclick="location.href='{{route('usersettingGet')}}';">
        <div class="usermenu01">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/user_edit_line.svg') }}" class="usermenuicon01"><span class="topusername01" id="topusername01">{{Auth::user()->name}}</span>
        </div>
    </a>
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
                <div class="user01" onclick="location.href='{{route('usersettingGet')}}';">
                    <div class="usericon01">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/user_1_line.svg') }}" class="usericon01">
                    </div>
                    <div class="username01">
                        {{Auth::user()->name}}
                    </div>

                </div>

            </div>
            <a href="{{route('topGet')}}" class="button1_01">
                <div class="button1element01">
                    <div class="button1logo01">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/home_3_line.svg') }}">
                    </div>
                    <div class="button1name01">
                        電子帳簿システム
                    </div>

                </div>

            </a>
            <div class="sidebarcontent01">
                <a href="{{route('adminGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/edit_fill.svg') }}">
                        </div>
                        <div class="button1name01">
                            ユーザー一覧
                        </div>

                    </div>

                </a>
                <a href="{{route('adminregistGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/user_add_2_fill.svg') }}">
                        </div>
                        <div class="button1name01">
                            ユーザー追加
                        </div>

                    </div>

                </a>


                <a href="{{route('admindocumentGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/document_2_line.svg') }}">
                        </div>
                        <div class="button1name01">
                            書類管理
                        </div>

                    </div>

                </a>

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
<script src="{{ asset(config('prefix.prefix').'/'.'js/style.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/admin.js') }}"></script>
@if ($server == 'onpre')
<script src="{{ asset(config('prefix.prefix').'/'.'js/onpre.js') }}"></script>
@else
<script src="{{ asset(config('prefix.prefix').'/'.'js/cloud.js') }}"></script>
@endif
</footer>


</html>