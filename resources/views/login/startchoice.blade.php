<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>機能選択画面</title>
    <link rel="stylesheet" type="text/css" href="{{ tenant_asset('css/login.css') }}">



</head>
<input type="hidden" id="prefix" value="{{$prefix}}">

<div class="start_choice_container">
    <a class="start_choice_element" href="{{route('topGet')}}">
        <img src="{{ tenant_asset('img/header/tameru_logo.svg') }}" alt="TAMERU"
            class="tameru_logo">

    </a>
    <a class="start_choice_element" href="{{route('workflow')}}">
        <img src="{{ tenant_asset('img/header/rapid_logo.svg') }}" alt="Rapid" class="rapid_logo">
    </a>
    @if (feature_enabled('schedule'))
    <a class="start_choice_element" href="{{route('scheduleget')}}">
        <img src="{{ tenant_asset('img/header/skett_logo.svg') }}" alt="TAMERU"
            class="tameru_logo">

    </a>
    @endif
    @if (feature_enabled('card'))
    <a class="start_choice_element" href="{{route('cardviewget')}}">
        <img src="{{ tenant_asset('img/header/readbridge_logo.svg') }}" alt="TAMERU"
            class="tameru_logo">

    </a>
    @endif
</div>
<script src="{{tenant_asset('jquery/jquery-3.7.0.min.js')}}"></script>
<script src="{{ tenant_asset('js/login.js') }}"></script>