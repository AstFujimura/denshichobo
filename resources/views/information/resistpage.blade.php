@extends('layouts.template')

@section('title')
登録ページ
@endsection 

@section('menuebar')
@endsection 

@section('menue')


@endsection

@section('main')
<div class="registcontainer">


<form class="form" action="{{route('registPost')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="formrow">
            @php
                $currentYear = date('Y');
                $currentMonth = date('m');
                $currentDate = date('d');
                $currentHour = date('H');
                $currentMinute = date('i');
                $startYear = $currentYear - 5;
                $endYear = $currentYear + 5;
            @endphp
            <select name="year">
                @for ($year = $startYear; $year <= $endYear; $year++)
                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
            <div>年</div>
            <select name="month">
                @for ($month = 1; $month <= 12; $month++)
                    <option value="{{ $month }}" {{ $month == $currentMonth ? 'selected' : '' }}>{{ $month }}</option>
                @endfor
            </select>
            <div>月</div>
            <select name="minute">
                @for ($minute = 1; $minute <= 31; $minute++)
                    <option value="{{ $minute }}" {{ $minute == $currentDate ? 'selected' : '' }}>{{ $minute }}</option>
                @endfor
            </select>
            <div>日</div>
        </div>
        <div class="formrow">
            <div>使用者</div>
            <input type="text" name="driver" value="{{Auth::user()->name}}"  class="input-field">
        </div>
        
        <div class="formrow">
            <div>出発時</div>
            <select name="hour">
                @for ($hour = 0; $hour <= 23; $hour++)
                    <option value="{{ $hour }}" {{ $hour == $currentHour ? 'selected' : '' }}>{{ $hour }}</option>
                @endfor
            </select>
            <div>時</div>
            <select name="minute">
                @for ($minute = 0; $minute <= 59; $minute++)
                    <option value="{{ $minute }}" {{ $minute == $currentMinute ? 'selected' : '' }}>{{ $minute }}</option>
                @endfor
            </select>
            <div>分</div>
        </div>
        <div class="formrow">
            <div>到着時</div>
            <select name="hour">
                @for ($hour = 0; $hour <= 23; $hour++)
                    <option value="{{ $hour }}" {{ $hour == $currentHour ? 'selected' : '' }}>{{ $hour }}</option>
                @endfor
            </select>
            <div>時</div>
            <select name="minute">
                @for ($minute = 0; $minute <= 59; $minute++)
                    <option value="{{ $minute }}" {{ $minute == $currentMinute ? 'selected' : '' }}>{{ $minute }}</option>
                @endfor
            </select>
            <div>分</div>
        </div>

        <div class="formrow">
            <div>訪問先</div>
            <input type="text" name="visit"  class="input-field">
        </div>
        
        <div class="formrow">
            <div>着メーター</div>
            <input type="text" name="mater"  class="input-field">
        </div>
        <div class="formrow">
            <div>給油</div>
            <input type="text" name="oil"  class="input-field">
        </div>
        <div class="formrow">
            <div>SS名</div>
            <input type="text" name="ss"  class="input-field">
        </div>

        <input type="submit" value="登録">


</form>
        @endsection 
        @section('footer')
    @endsection 

