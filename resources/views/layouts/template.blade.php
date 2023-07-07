
<html lang="ja">

<head>
    <meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/admin01.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/regist.css') }}">
    <script src="{{asset('jquery/jquery-3.7.0.min')}}"></script>
    

 
</head>
 

<body>
    <div class="page-container01">
        <div class="sidebar01">
            <div class="logo01">
                <a href="{{route('topGet')}}" class="logoelement01">
                    電子帳簿保存システム
                </a>
                 <div class="sidebarbatsu01">
                    <img src="{{ asset('img/close_line.svg') }}">
                </div>
            </div>
            <div class="icon01">
                <div class="user01">
                    <div class="usericon01">
                        <img src="{{ asset('img/user_1_line.svg') }}" class="usericon01">
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
                        <img src="{{ asset('img/pencil_2_line.svg') }}">
                        </div>
                        <div class="button1name01">
                          帳簿一覧
                        </div>

                    </div>

                </a>
                <a href="{{route('registGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/pencil_2_line.svg') }}">
                        </div>
                        <div class="button1name01">
                          帳簿保存
                        </div>

                    </div>

                </a>


                <a class="accordion1content01">
                    <div class="button1element01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/user_add_2_fill.svg') }}">
                        </div>
                        <div class="accordion1name01">
                          ユーザー登録
                        </div>

                    </div>
                </a>
                <a href="{{route('logout')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset('img/open_door_line.svg') }}">
                        </div>
                        <div class="button1name01">
                          ログアウト
                        </div>

                    </div>

                </a>

            </div>

        </div>
        <main class="main01">
            <header class="header01">
                <div class="headerIcon01">
                    <img src="{{ asset('img/menu_fill.svg') }}" class="menuicon01">
                </div>
                

            </header>
            
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
<footer >
    @yield('footer')
    </footer>
    <script src="{{ asset('js/all.js') }}"></script>
    <script src="{{ asset('js/admin01.js') }}"></script>
</footer>

 
</html>
