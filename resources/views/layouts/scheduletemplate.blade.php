<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <title>@yield('title')</title>
    <link rel="icon" href="{{ tenant_asset('icon/skett.ico') }}" id="favicon">
    <link rel="stylesheet" type="text/css" href="{{ tenant_asset('css/admin01.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ tenant_asset('css/admin001.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ tenant_asset('css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ tenant_asset('css/schedulestyle.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ tenant_asset('css/regist.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ tenant_asset('css/jquery-ui.css') }}">
    <script src="{{ tenant_asset('jquery/jquery-3.7.0.min.js')}}"></script>
    <script src="{{ tenant_asset('jquery/laravelui.js')}}"></script>
    <script src="{{tenant_asset('jquery/jquery-ui.js')}}"></script>
    <script src="{{tenant_asset('jquery/jquery-ui_ja.js')}}"></script>
    <script src="{{tenant_asset('jquery/pdf.js')}}"></script>
    <noscript>
        <meta http-equiv="refresh" content="0;URL=/jserror" />
    </noscript>



</head>
@include('partials.support_banner')

<header class="header001">
    <div class="logo01">
        <a href="{{route('scheduleget')}}" class="logoelement01">
            <img src="{{ tenant_asset('img/header/skett_logo_only.svg') }}" alt="Skett" class="skett_logo">
            <img src="{{ tenant_asset('img/header/skett.svg') }}" alt="Skett" class="skett_char">
        </a>
        <input type="hidden" id="server" value="{{config('prefix.server')}}">
        <input type="hidden" id="prefix" value="{{$prefix}}">
    </div>
    @include('partials.app_version')
    @include('partials.app_launcher')
</header>
<div class="menu001">

    <a class="headerIcon001" href="{{route('scheduleget')}}">
        <img src="{{ tenant_asset('img/schedule_title/calendar_title.svg') }}" class="menuicon01" title="Top">
    </a>
    <a class="headerIcon001" href="{{route('scheduleregistget')}}">
        <img src="{{ tenant_asset('img/schedule_title/regist_title.svg') }}" class="menuicon01" title="スケジュール登録">
    </a>
    <a class="headerIcon001" href="{{route('schedulemasterregistget')}}">
        <img src="{{ tenant_asset('img/schedule_title/master_title.svg') }}" class="menuicon01" title="予定マスタ登録">
    </a>
    @if (Auth::user()->管理 == '管理')

    @endif


    <a class="headerIcon001 rightmenue001" href="{{route('usersettingGet', ['system_type' => 'flow'])}}">
        <div class="usermenu01">
            <img src="{{ tenant_asset('img/user_edit_line.svg') }}" class="usermenuicon01"><span class="topusername01" id="topusername01">{{Auth::user()->表示名}}</span>
        </div>
    </a>
    <a class="headerIcon001 menue001">
        <img src="{{ tenant_asset('img/menu_fill.svg') }}" class="menuicon01 hamburger01" title="メニュー">
        <img src="{{ tenant_asset('img/close_line.svg') }}" class="menuicon01 hamburger01 hamburger01close" title="閉じる">
    </a>


</div>

<body>
    <div class="page-container01">
        <div class="sidebar01">

            <div class="icon01">
                <div class="user01" onclick="location.href='{{route('usersettingGet', ['system_type' => 'flow'])}}';">
                    <div class="usericon01">
                        <img src="{{ tenant_asset('img/user_1_line.svg') }}" class="usericon01">
                    </div>
                    <div class="username01">
                        {{Auth::user()->表示名}}
                    </div>

                </div>

            </div>
            <a href="{{route('scheduleget')}}" class="button1_01">
                <div class="button1element01">
                    <div class="button1logo01">
                        <img src="{{ tenant_asset('img/home_3_line.svg') }}" class="flow_right_icon">
                    </div>
                    <div class="button1name01">
                        スケジュール
                    </div>

                </div>

            </a>
            <div class="sidebarcontent01">
               
                <a href="{{route('logout')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ tenant_asset('img/open_door_line.svg') }}" class="flow_right_icon">
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
    <div class="error_gray"></div>
    {{session()->forget('error')}}
    @endif
    @if (session('success'))
    <input type="hidden" id="success_message" value="{{session('success')}}">
    <div class="success_gray"></div>
    {{session()->forget('success')}}
    @endif
</body>
<footer>
    @yield('footer')
</footer>
<script src="{{ tenant_asset('js/all.js') }}"></script>
<script src="{{ tenant_asset('js/style.js') }}"></script>
<script src="{{ tenant_asset('js/schedule.js') }}"></script>
<script src="{{ tenant_asset('js/schedulefunction.js') }}"></script>
<script src="{{ tenant_asset('js/usersetting.js') }}"></script>
<script src="{{ tenant_asset('js/storage.js') }}"></script>
</footer>


</html>