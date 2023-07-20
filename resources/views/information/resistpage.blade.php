@extends('layouts.template')

@section('title')
帳簿保存
@endsection 

@section('menuebar')
@endsection 

@section('menue')


@endsection

@section('main')

<h2>帳簿保存</h2>

        <form class="form" action="{{route('registPost')}}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="droparea">
                    ここにドラッグ＆ドロップ
                </div>
                <div>
                    <input type="file" name="file" id="file">
                    <span class="fileerrorelement">ファイルを選択してください</span>
                </div>
                <div class="input-container">
                    <label class="label">書類作成（受領）日<span class ="requirered">*</span> </label>
                    <div class="dateform">
                        <input type="text" name="hiduke" class="input-field dateinputtext" id="hiduke" placeholder="2023/01/01">
                        <span class="errorelement" id="required4">必須項目です</span>
                        <span class="errorelement" id="dateformat">形式が不正です</span>
                    </div>
                </div>
                <div class="input-container">
                    <label  class="label">金額<span class ="requirered">*</span></label>
                    <div>
                        <input type="text" name="kinngaku" class="input-field kinngakuinput-field" id="kinngaku" placeholder="3,000">
                        <span class="errorelement" id="required2">必須項目です</span>
                        <span class="errorelement" id="kinngakuformat">形式が不正です</span>
                    </div>
                    
                </div>
                <div class="input-container">
                    <label  class="label">取引先<span class ="requirered">*</span></label>
                    <div>
                        <input type="text" name="torihikisaki" class="input-field" id="torihikisaki">
                        <span class="errorelement" id="required1">必須項目です</span>
                    </div>
                    
                </div>

                <div class="input-container">
                    <label  class="label">書類区分<span class ="requirered">*</span></label>
                    <div>
                        <select name="syorui" class="input-field">
                            <option>請求書</option>
                            <option>納品書</option>
                            <option>契約書</option>
                            <option>見積書</option>
                        </select>
                        <span class="errorelement" id="required3">必須項目です</span>
                    </div>
                    
                </div>
                <div class="input-container">
                    <label  class="label">保存方法<span class ="requirered">*</span></label>
                    <div>
                        <select name="hozonn" class="input-field">
                            <option>電子保存</option>
                            <option>スキャナ保存</option>
                        </select>
                    </div>
                    
                </div>
                <div class="input-container">
                    <label  class="label">検索ワード</label>
                    <div>
                        <input type="text" name="kennsakuword" class="input-field" id="kennsakuword">
                    </div>
                    
                </div>



                <input type="submit" value="登録" id="registbutton" class="registbutton">
        </form>
        @endsection 
        @section('footer')
    @endsection 

