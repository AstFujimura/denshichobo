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

                <div class="droparea">
                    ここにドラッグ＆ドロップ
                </div>
                <div>
                    <input type="file" name="file" id="editfile">
                    <span class="fileerrorelement">ファイルを選択してください</span>
                </div>
                <div class="red">※ファイル自体に変更がない場合はファイルを選択しないでください</div>
                <div class="input-container">
                    <label class="label">書類作成（受領）日<span class ="requirered">*</span></label>
                    <div class="dateform">
                        <input type="text" name="hiduke" class="input-field dateinputtext" value="{{$hiduke}}" id="hiduke">
                        <span class="errorelement" id="required4">必須項目です</span>
                        <span class="errorelement" id="dateformat">形式が不正です</span>
                    </div>
                </div>

                <div class="input-container">
                <label class="label">金額<span class ="requirered">*</span></label>
                    <div>
                        <input type="text" name="kinngaku" class="input-field kinngakuinput-field kinngakuedit" id="kinngaku" value="{{$file->金額}}">
                        <span class="errorelement" id="required2">必須項目です</span>
                        <span class="errorelement" id="kinngakuformat">形式が不正です</span>
                    </div>
                    
                </div>

                <div class="input-container">
                <label class="label">取引先<span class ="requirered">*</span></label>
                    <div>
                        <input type="text" name="torihikisaki" class="input-field" id="torihikisaki" value="{{$file->取引先}}">
                        <span class="errorelement" id="required1">必須項目です</span>
                    </div>
                    
                </div>

      
                <div class="input-container">
                    <label  class="label">書類区分<span class ="requirered">*</span></label>
                    <div>
                        <select name="syorui" class="input-field">
                            <option {{$seikyusyo}}>請求書</option>
                            <option {{$nohinnsyo}}>納品書</option>
                            <option {{$keiyakusyo}}>契約書</option>
                            <option {{$mitumorisyo}}>見積書</option>
                        </select>
                    </div>
                    
                </div>
                <div class="input-container">
                    <label  class="label">保存方法<span class ="requirered">*</span></label>
                    <div>
                        <select name="hozonn" class="input-field">
                            <option {{$dennshi}}>電子保存</option>
                            <option {{$scan}}>スキャナ保存</option>
                        </select>
                    </div>
                    
                </div>
                <div class="input-container">
                    <label  class="label">検索ワード</label>
                    <div>
                        <input type="text" name="kennsakuword" class="input-field" value="{{$file->備考}}" id="kennsakuword">
                    </div>
                    
                </div>


                <input type="hidden" value="{{$file->id}}" id="id">
                <input type="submit" value="変更" id="registbutton"  class="registbutton">
                <div class="deletebutton">削除</div>
        </form>
        @endsection 
        @section('footer')
    @endsection 

