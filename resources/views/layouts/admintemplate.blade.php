<html lang="ja">
 @if (Auth::check())
<head>
    <meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/detail.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/regist.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/admin01.css') }}">
    

 
</head>
 

<body>
    <div class="page-container01">
        <div class="sidebar01">
            <div class="logo01">
                 <a href="{{route('topGet')}}" class="logoelement01">
                    名刺管理システム
                </a>
                <div class="sidebarbatsu01">
                    ×
                </div>
            </div>
            <div class="icon01">
                <div class="user01">
                    <div class="usericon01">
                        <img src="{{ asset('img/user_1_line.svg') }}" class="usericon01">
                    </div>
                    <div class="username01">
                        ゲスト
                    </div>

                </div>

            </div>
            <div class="sidebarcontent01">
                <a href="{{route('topGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/IDcard_line.svg') }}">
                        </div>
                        <div class="button1name01">
                          名刺管理システム
                        </div>

                    </div>

                </a>
                <div class="button1_01 accordion1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                            <img src="{{ asset('img/laptop_line.svg') }}">
                        </div>
                        <div class="button1name01">
                            管理画面
                        </div>
                        <div class="allow01">
                            <img src="{{ asset('img/left_line.svg') }}">
                        </div>

                    </div>
                </div>
                <a href="{{route('adminpageGet')}}" class="accordion1content01">
                    <div class="button1element01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/user_4_fill.svg') }}">
                        </div>
                        <div class="accordion1name01">
                          ユーザー一覧
                        </div>

                    </div>
                </a>
                <a href="{{route('adminregistGet')}}" class="accordion1content01">
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
                <nav class="admin-navbar">
                  @yield('menuebar')
                </nav>
                <div class="admin-MenueBar">
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="{{ asset('js/all.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    <script src="{{ asset('js/admin01.js') }}"></script>
</footer>

@else
<p>※ログインしていません<a href="{{route('loginGet')}}">ログイン</a>
@endif
 
</html>
