@extends('layouts.template')

@section('title')
帳簿保存 | TAMERU
@endsection

@section('menuebar')
@endsection

@section('menue')


@endsection

@section('main')

<h2 id="regist" class="pagetitle">帳簿保存</h2>

<div class="droppreview">
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
            <label class="label">取引日<span class="requirered">*</span> </label>
            <div class="dateform">
                <input type="text" name="hiduke" class="input-field dateinputtext" id="hiduke" autocomplete="off">
                <span class="errorelement" id="required1">必須項目です</span>
                <span class="errorelement" id="dateformat">形式が不正です</span>
            </div>
        </div>
        <div class="input-container">
            <label class="label">金額<span class="requirered">*</span></label>
            <div>
                <input type="text" name="kinngaku" class="input-field kinngakuinput-field" id="kinngaku">
                <span class="errorelement" id="required2">必須項目です</span>
                <span class="errorelement" id="kinngakuformat">形式が不正です</span>
            </div>

        </div>
        <div class="input-container">
            <label class="label">取引先<span class="requirered">*</span></label>
            <div class="torihikisakiinput">
                <input type="text" name="torihikisaki" class="input-field" id="torihikisaki" autocomplete="off">
                <div class="registtorihikisakiselect" id="torihikisakiselect"></div>
                <span class="errorelement" id="required3">必須項目です</span>
                <span class="errorelement" id="torihikiformat">形式が不正です</span>
            </div>

        </div>

        <div class="input-container">
            <label class="label">書類区分<span class="requirered">*</span></label>
            <div>
                <select name="syorui" class="input-field" id="syorui">
                    @foreach($documents as $document)
                    <option value="{{ $document->id }}">{{ $document->書類 }}</option>
                    @endforeach
                </select>
                <span class="errorelement" id="required4">必須項目です</span>
            </div>

        </div>
        <div class="input-container">
            <label class="label">受領・提出<span class="requirered">*</span></label>
            <div>
                <select name="teisyutu" class="input-field" id="teisyutu">
                    <option>受領</option>
                    <option>提出</option>
                </select>
            </div>

        </div>
        <div class="input-container">
            <label class="label">保存方法<span class="requirered">*</span></label>
            <div>
                <select name="hozonn" class="input-field" id="hozonn">
                    <option>電子保存</option>
                    <option>スキャナ保存</option>
                </select>
            </div>

        </div>
        <div class="input-container">
            <label class="label">検索ワード</label>
            <div>
                <input type="text" name="kennsakuword" class="input-field" id="kennsakuword">
                <span class="errorelement" id="kennsakuwordformat">形式が不正です</span>
            </div>

        </div>
        <div class="input-container">
            <label class="label">グループ</label>
            <div>
                <select name="group" class="input-field" id="group">
                    @foreach ($groups as $group)
                    <option value="{{$group->id}}">{{$group->グループ名}}</option>
                    @endforeach
                    <option value="{{Auth::id()}}">指定なし</option>
                </select>

            </div>

        </div>



        <input type="submit" value="登録" id="registbutton" class="registbutton">

    </form>

    <div class="previewarea registpreviewarea">
        プレビュー
    </div>

</div>
@endsection
@section('footer')
@endsection