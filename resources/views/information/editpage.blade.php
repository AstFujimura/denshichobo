@extends('layouts.template')

@section('title')
帳簿保存
@endsection 

@section('menuebar')
@endsection 

@section('menue')


@endsection

@section('main')

<h2>帳簿変更</h2>

        <form class="form" action="{{route('editPost',['path'=>$file->過去データID])}}" method="post" enctype="multipart/form-data">
                @csrf

                @php
                    $currentYear = date('Y');
                    $currentMonth = date('m');
                    $currentDate = date('d');
                    $currentHour = date('H');
                    $currentMinute = date('i');
                    $startYear = $currentYear - 5;
                    $endYear = $currentYear;
                    $selectedYear = $year_data;
                    $selectedMonth = $month_data;
                    $selectedDate = $date_data;
                @endphp
                <div class="droparea">
                    ここにドラッグ＆ドロップ
                </div>
                <div>
                    <input type="file" name="file" id="editfile">
                    <span class="fileerrorelement">ファイルを選択してください</span>
                </div>
                <div class="red">※ファイル自体に変更がない場合はファイルを選択しないでください</div>
                <div class="input-container">
                    <label class="label">書類作成（受領）日</label>
                    <div class="dateform">
                        <select name="year" class="input-field">
                            @for ($year = $startYear; $year <= $endYear; $year++)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                        <div>年</div>
                        <select name="month" class="input-field">
                            @for ($month = 1; $month <= 12; $month++)
                                <option value="{{ $month }}" {{ $month == $selectedMonth ? 'selected' : '' }}>{{ $month }}</option>
                            @endfor
                        </select>
                        <div>月</div>
                        <select name="day" class="input-field">
                            @for ($day = 1; $day <= 31; $day++)
                                <option value="{{ $day }}" {{ $day == $selectedDate ? 'selected' : '' }}>{{ $day }}</option>
                            @endfor
                        </select>
                        <div>日</div>
                    </div>
                </div>
                <div class="input-container">
                <label class="label">取引先</label>
                    <div>
                        <input type="text" name="torihikisaki" class="input-field" id="torihikisaki" value="{{$file->取引先}}">
                        <span class="errorelement" id="required1">必須項目です</span>
                    </div>
                    
                </div>
                <div class="input-container">
                <label class="label">金額</label>
                    <div>
                        <input type="text" name="kinngaku" class="input-field" id="kinngaku" value="{{$file->金額}}">
                        <span class="errorelement" id="required2">必須項目です</span>
                    </div>
                    
                </div>
                <div class="input-container">
                <label class="label">書類区分</label>
                    <div>
                        <input type="text" name="syorui" class="input-field" id="syorui" value="{{$file->書類}}">
                        <span class="errorelement" id="required3">必須項目です</span>
                    </div>
                    
                </div>


                <input type="submit" value="変更" id="registbutton"  class="registbutton">
        </form>
        @endsection 
        @section('footer')
    @endsection 

