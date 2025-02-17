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
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/flowstyle.css') }}">
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
        <a href="{{route('workflow')}}" class="logoelement01">
            電子承認システム
        </a>
        @if (App\Models\Version::where('tameru', true)->first())
        <a href="{{route('topGet')}}" class="tameru_banner">TAMERU</a>
        @endif
        @if (App\Models\Version::where('名刺', true)->first())
        <a href="{{route('cardviewget')}}" class="tameru_banner">名刺管理システム</a>
        @endif
        <input type="hidden" id="server" value="{{config('prefix.server')}}">
        <input type="hidden" id="prefix" value="{{$prefix}}">
    </div>
    <span class="version">ver.3.0.1</span>
</header>
<div class="menu001">

    <a class="headerIcon001" href="{{route('workflow')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/home_3_line.svg') }}" class="menuicon01">
        <div class="iconmessage">Top</div>
    </a>
    <a class="headerIcon001" href="{{route('workflowapplicationget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_application.svg') }}" class="menuicon01">
        <div class="iconmessage">ワークフロー申請</div>
    </a>
    <a class="headerIcon001" href="{{route('workflowapprovalview')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_approve.svg') }}" class="menuicon01">
        <div class="iconmessage">承認</div>
    </a>
    <a class="headerIcon001" href="{{route('workflowviewget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_application_view.svg') }}" class="menuicon01">
        <div class="iconmessage">申請一覧</div>
    </a>
    <a class="headerIcon001" href="{{route('workflowmaster')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_checkview.svg') }}" class="menuicon01">
        <div class="iconmessage">閲覧一覧</div>
    </a>
    <a class="headerIcon001" href="{{route('workflowstampget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_stamp.svg') }}" class="menuicon01">
        <div class="iconmessage">印鑑設定</div>
    </a>
    @if (Auth::user()->管理 == '管理')
    <a class="headerIcon001" href="{{route('workflowmaster')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_flow.svg') }}" class="menuicon01">
        <div class="iconmessage">経路マスタ</div>
    </a>
    <a class="headerIcon001" href="{{route('mailsettingget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_mail.svg') }}" class="menuicon01">
        <div class="iconmessage">メール設定</div>
    </a>
    <a class="headerIcon001" href="{{route('categoryget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_category.svg') }}" class="menuicon01">
        <div class="iconmessage">カテゴリ設定</div>
    </a>
    <a class="headerIcon001" href="{{route('adminGet')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_admin.svg') }}" class="menuicon01">
        <div class="iconmessage">ユーザー設定</div>
    </a>
    @endif
    <a class="headerIcon001" href="{{route('question')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/question_line.svg') }}" class="menuicon01">
        <div class="iconmessage">ヘルプ</div>
    </a>

    <a class="headerIcon001 rightmenue001" href="{{route('usersettingGet', ['system_type' => 'flow'])}}">
        <div class="usermenu01">
            <img src="{{ asset(config('prefix.prefix').'/'.'img/user_edit_line.svg') }}" class="usermenuicon01"><span class="topusername01" id="topusername01">{{Auth::user()->name}}</span>
        </div>
        <div class="iconmessage">ユーザー情報</div>
    </a>
    <a class="headerIcon001 menue001">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/menu_fill.svg') }}" class="menuicon01 hamburger01">
        <div class="iconmessage">メニュー</div>
        <img src="{{ asset(config('prefix.prefix').'/'.'img/close_line.svg') }}" class="menuicon01 hamburger01 hamburger01close">
        <div class="iconmessage">閉じる</div>
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
            <a href="{{route('workflow')}}" class="button1_01">
                <div class="button1element01">
                    <div class="button1logo01">
                        <img src="{{ asset(config('prefix.prefix').'/'.'img/home_3_line.svg') }}" class="flow_right_icon">
                    </div>
                    <div class="button1name01">
                        ワークフロー
                    </div>

                </div>

            </a>
            <div class="sidebarcontent01">
                <a href="{{route('adminGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_application.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            ワークフロー申請
                        </div>

                    </div>

                </a>
                <a href="{{route('workflowapprovalview')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_approve.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            承認
                        </div>

                    </div>

                </a>


                <a href="{{route('workflowviewget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_application_view.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            申請一覧
                        </div>

                    </div>

                </a>
                <a href="{{route('workflowviewget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_checkview.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            閲覧一覧
                        </div>

                    </div>

                </a>
                <a href="{{route('workflowviewget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_stamp.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            印鑑設定
                        </div>

                    </div>

                </a>
                @if (Auth::user()->管理 == '管理')
                <a href="{{route('workflowmaster')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_flow.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            経路マスタ
                        </div>

                    </div>

                </a>
                <a href="{{route('workflowstampget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_mail.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                        メール設定
                        </div>

                    </div>

                </a>
                <a href="{{route('categoryget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_category.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            カテゴリ設定
                        </div>

                    </div>

                </a>
                <a href="{{route('mailsettingget')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_mail.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            メール設定
                        </div>

                    </div>

                </a>
                <a href="{{route('adminGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset(config('prefix.prefix').'/'.'img/header/header_admin.svg') }}" class="flow_right_icon">
                        </div>
                        <div class="button1name01">
                            ユーザー設定
                        </div>

                    </div>

                </a>
                @endif
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
<script src="{{ asset(config('prefix.prefix').'/'.'js/flow.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/flowfunction.js') }}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/usersetting.js') }}"></script>
@if ($server == 'onpre')
<script src="{{ asset(config('prefix.prefix').'/'.'js/onpre.js') }}"></script>
@else
<script src="{{ asset(config('prefix.prefix').'/'.'js/cloud.js') }}"></script>
@endif
</footer>


</html>