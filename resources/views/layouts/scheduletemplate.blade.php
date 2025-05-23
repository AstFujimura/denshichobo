<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <title>@yield('title')</title>
    <link rel="icon" href="{{ asset(config('prefix.prefix').'/'.'icon/skett.ico') }}" id="favicon">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/admin01.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/admin001.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/schedulestyle.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/regist.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/jquery-ui.css') }}">
    <script src="{{ asset(config('prefix.prefix').'/'.'jquery/jquery-3.7.0.min.js')}}"></script>
    <script src="{{ asset(config('prefix.prefix').'/'.'jquery/laravelui.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-ui.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-ui_ja.js')}}"></script>
    <script src="{{asset(config('prefix.prefix').'/'.'jquery/pdf.js')}}"></script>
    <noscript>
        <meta http-equiv="refresh" content="0;URL=/jserror" />
    </noscript>



</head>

<header class="header001">
    <div class="logo01">
        <a href="{{route('scheduleget')}}" class="logoelement01">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/skett_logo_only.svg') }}" alt="Skett" class="skett_logo">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/skett.svg') }}" alt="Skett" class="skett_char">
        </a>
        @if (App\Models\Version::where('tameru', true)->first())
        <a href="{{route('topGet')}}" class="tameru_banner"><img src="{{ asset(config('prefix.prefix').'/'.'img/header/tameru_logo_only.svg') }}" alt="TAMERU"></a>
        @endif
        @if (App\Models\Version::where('フロー', true)->first())
        <a href="{{route('workflow')}}" class="tameru_banner"><img src="{{ asset(config('prefix.prefix').'/'.'img/header/rapid_logo_only.svg') }}" alt="Rapid"></a>
        @endif
        @if (App\Models\Version::where('名刺', true)->first())
        <a href="{{route('cardviewget')}}" class="tameru_banner">名</a>
        @endif
        <input type="hidden" id="server" value="{{config('prefix.server')}}">
        <input type="hidden" id="prefix" value="{{$prefix}}">
    </div>
    <span class="version">ver.5.0.0</span>
</header>
<div class="menu001">

    <a class="headerIcon001" href="{{route('scheduleget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/calendar_title.svg') }}" class="menuicon01" title="Top">
    </a>
    <a class="headerIcon001" href="{{route('scheduleregistget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/regist_title.svg') }}" class="menuicon01" title="Top">
    </a>
    <a class="headerIcon001" href="{{route('schedulemasterregistget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/schedule_title/master_title.svg') }}" class="menuicon01" title="Top">
    </a>
    @if (Auth::user()->管理 == '管理')

    @endif


    <a class="headerIcon001 rightmenue001" href="{{route('usersettingGet', ['system_type' => 'flow'])}}">
        <div class="usermenu01">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/user_edit_line.svg') }}" class="usermenuicon01"><span class="topusername01" id="topusername01">{{Auth::user()->name}}</span>
        </div>
    </a>
    <a class="headerIcon001 menue001">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/menu_fill.svg') }}" class="menuicon01 hamburger01" title="メニュー">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/close_line.svg') }}" class="menuicon01 hamburger01 hamburger01close" title="閉じる">
    </a>


</div>

<body>
    <div class="page-container01">
        <div class="sidebar01">

            <div class="icon01">
                <div class="user01" onclick="location.href='{{route('usersettingGet', ['system_type' => 'flow'])}}';">
                    <div class="usericon01">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/user_1_line.svg') }}" class="usericon01">
                    </div>
                    <div class="username01">
                        {{Auth::user()->name}}
                    </div>

                </div>

            </div>
            <a href="{{route('scheduleget')}}" class="button1_01">
                <div class="button1element01">
                    <div class="button1logo01">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/home_3_line.svg') }}" class="flow_right_icon">
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
<script src="{{ asset(config('prefix.prefix').'/'.'js/all.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/style.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/schedule.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/schedulefunction.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/usersetting.js') }}"></script>
@if ($server == 'onpre')
<script src="{{ asset(config('prefix.prefix').'/'.'js/onpre.js') }}"></script>
@else
<script src="{{ asset(config('prefix.prefix').'/'.'js/cloud.js') }}"></script>
@endif
</footer>


</html>