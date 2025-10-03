<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>機能選択画面</title>
    <link rel="stylesheet" type="text/css" href="{{ asset(config('prefix.prefix').'/'.'css/login.css') }}">



</head>
<input type="hidden" id="prefix" value="{{$prefix}}">

<div class="start_choice_container">
    <a class="start_choice_element" href="{{route('topGet')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/tameru_logo.svg') }}" alt="TAMERU"
            class="tameru_logo">

    </a>
    <a class="start_choice_element" href="{{route('workflow')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/rapid_logo.svg') }}" alt="Rapid" class="rapid_logo">
    </a>
    @if (App\Models\Version::where('スケジュール', true)->first())
    <a class="start_choice_element" href="{{route('scheduleget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/skett_logo.svg') }}" alt="TAMERU"
            class="tameru_logo">

    </a>
    @endif
    @if (App\Models\Version::where('名刺', true)->first())
    <a class="start_choice_element" href="{{route('cardviewget')}}">
        <img src="{{ asset(config('prefix.prefix').'/'.'img/header/readbridge_logo.svg') }}" alt="TAMERU"
            class="tameru_logo">

    </a>
    @endif
</div>
<script src="{{asset(config('prefix.prefix').'/'.'jquery/jquery-3.7.0.min.js')}}"></script>
<script src="{{ asset(config('prefix.prefix').'/'.'js/login.js') }}"></script>