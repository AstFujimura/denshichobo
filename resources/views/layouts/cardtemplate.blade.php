<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset(config('prefix.prefix').'/'.'icon/favicon.ico') }}" id="favicon">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/admin01.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/admin001.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/cardstyle.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/regist.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/jquery-ui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/cropper.css') }}">
    <script src="{{ asset(config('prefix.prefix').'/'.'jquery/jquery-3.7.0.min.js')}}"></script>
    <script src="{{ asset(config('prefix.prefix').'/'.'jquery/laravelui.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-ui.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-ui_ja.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/pdf.js')}}"></script>
    <script src="{{ asset(config('prefix.prefix').'/'.'jquery/cropper-1.6.2.min.js') }}"></script>
    <noscript>
        <meta http-equiv="refresh" content="0;URL=/jserror" />
    </noscript>



</head>

<header class="header001">
    <div class="logo01">
        <a href="{{route('cardviewget')}}" class="logoelement01" id="cardtemplate">
            名刺管理システム
        </a>
        @if (App\Models\Version::where('tameru', true)->first())
        <a href="{{route('topGet')}}" class="tameru_banner">TAMERU</a>
        @endif
        @if (App\Models\Version::where('フロー', true)->first())
        <a href="{{route('workflow')}}" class="tameru_banner">電子承認</a>
        @endif
        <input type="hidden" id="server" value="{{config('prefix.server')}}">
        <input type="hidden" id="prefix" value="{{$prefix}}">
    </div>
    <span class="version">ver.4.0.0</span>
</header>
<div class="menu001">

    <a class="headerIcon001" href="{{route('cardviewget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/home_gray.svg') }}" class="menuicon01" title="Top">
    </a>
    <a class="headerIcon001" href="{{route('cardregistget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/card/regist_gray.svg') }}" class="menuicon01" title="名刺登録">
    </a>



    <div class="headerIcon001 rightmenue001" onclick="location.href='{{route('usersettingGet', ['system_type' => 'card'])}}';">
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
                <div class="user01" onclick="location.href='{{route('usersettingGet', ['system_type' => 'card'])}}';">
                    <div class="usericon01">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/user_1_line.svg') }}" class="usericon01">
                    </div>
                    <div class="username01">
                        {{Auth::user()->name}}
                    </div>

                </div>

            </div>

            <div class="sidebarcontent01">
                <a href="{{route('cardviewget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/card/home_gray.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            名刺管理システム
                        </div>

                    </div>

                </a>
                <a href="{{route('cardregistget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/card/regist_gray.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            名刺登録
                        </div>

                    </div>

                </a>


                <a href="{{route('logout')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/open_door_line.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            ログアウト
                        </div>

                    </div>

                </a>

            </div>

        </div>
        <main class="main01">

            @yield('main')

        </main>


    </div>

    @if (session('error'))
    <input type="hidden" id="error_message" value="{{session('error')}}">
    <!-- <div class="error_gray"></div> -->
    {{session()->forget('error')}}
    @endif
    @if (session('success'))
    <input type="hidden" id="success_message" value="{{session('success')}}">
    <!-- <div class="success_gray"></div> -->
    {{session()->forget('success')}}
    @endif
</body>
<footer>
    @yield('footer')
</footer>
<script src="{{ asset(config('prefix.prefix').'/'.'js/all.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/style.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/card.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/usersetting.js') }}"></script>
@if ($server == 'onpre')
<script src="{{ asset(config('prefix.prefix').'/'.'js/onpre.js') }}"></script>
@else
<script src="{{ asset(config('prefix.prefix').'/'.'js/cloud.js') }}"></script>
@endif
</footer>


</html>