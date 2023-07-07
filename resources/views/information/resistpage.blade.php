@extends('layouts.template')

@section('title')
帳簿保存
@endsection 

@section('menuebar')
@endsection 

@section('menue')


@endsection

@section('main')



        <form class="form" action="{{route('registPost')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div>
                    <input type="file" name="file" id="file">
                </div>
                <div>
                    <label>日付</label>
                    <input type="text" name="date">
                </div>
                <div>
                <label>取引先</label>
                    <input type="text" name="torihikisaki">
                </div>
                <div>
                <label>金額</label>
                    <input type="text" name="kinngaku">
                </div>
                <div>
                <label>書類区分</label>
                    <input type="text" name="syorui">
                </div>


                <input type="submit" value="登録">
        </form>
        @endsection 
        @section('footer')
    @endsection 

