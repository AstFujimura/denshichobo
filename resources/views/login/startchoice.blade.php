<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>機能選択画面</title>
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/login.css') }}">



</head>
<input type="hidden" id="prefix" value="{{$prefix}}">

<div class="start_choice_container">
    <a class="start_choice_element" href="{{route('workflow')}}">
        電子承認システム
    </a>
    <a class="start_choice_element" href="{{route('topGet')}}">
        TAMERU
    </a>
</div>
<script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-3.7.0.min.js')}}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/login.js') }}"></script>