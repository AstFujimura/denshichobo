
<html lang="ja">
 @if (Auth::check())
<head>
    <meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/admin01.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/regist.css') }}">
    

 
</head>
 

<body>
    <div class="page-container01">
        <div class="sidebar01">
            <div class="logo01">
                <a href="{{route('topGet')}}" class="logoelement01">
                    車両管理システム
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
                        <img src="{{ asset('img/steering_wheel_fill.svg') }}" class="icon">
                        </div>
                        <div class="button1name01">
                          運転日報
                        </div>

                    </div>

                </a>
                <a href="{{route('registGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/pencil_2_line.svg') }}">
                        </div>
                        <div class="button1name01">
                          日報登録
                        </div>

                    </div>

                </a>
                <a href="{{route('distanceGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/car_line.svg') }}">
                        </div>
                        <div class="button1name01">
                          走行距離
                        </div>

                    </div>

                </a>
                <a href="{{route('registGet')}}" class="button1_01">
                    <div class="button1element01">
                        <div class="button1logo01">
                        <img src="{{ asset('img/settings_4_line.svg') }}">
                        </div>
                        <div class="button1name01">
                          車両情報
                        </div>

                    </div>

                </a>
                @if (Auth::user()->管理者権限 == "あり")
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
                @endif


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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/all.js') }}"></script>
    <script src="{{ asset('js/admin01.js') }}"></script>
</footer>

@else
<p>※ログインしていません<a href="{{route('loginGet')}}">ログイン</a>
@endif
 
</html>
