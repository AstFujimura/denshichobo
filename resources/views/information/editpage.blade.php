@extends('layouts.template')

@section('title')
帳簿変更 | TAMERU
@endsection

@section('menuebar')
@endsection

@section('menue')


@endsection

@section('main')

<h2 class="pagetitle" id="{{$file->id}}">帳簿変更</h2>

<div class="droppreview">
    <form class="form" action="{{route('editPost',['path'=>$file->過去データID])}}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="droparea">
            ここにドラッグ＆ドロップ
        </div>

        <div>
            <input type="file" name="file" id="file">
            <span class="fileerrorelement">ファイルを選択してください</span>
        </div>
        <div class="red">※ファイル自体に変更がない場合はファイルを選択しないでください</div>
        <div class="input-container">
            <label class="label">取引日<span class="requirered">*</span></label>
            <div class="dateform">
                <input type="text" name="hiduke" class="input-field dateinputtext" value="{{$hiduke}}" id="hiduke" autocomplete="off">
                <span class="errorelement" id="required4">必須項目です</span>
                <span class="errorelement" id="dateformat">形式が不正です</span>
            </div>
        </div>

        <div class="input-container">
            <label class="label">金額<span class="requirered">*</span></label>
            <div>
                <input type="text" name="kinngaku" class="input-field kinngakuinput-field kinngakuedit" id="kinngaku" value="{{$file->金額}}">
                <span class="errorelement" id="required2">必須項目です</span>
                <span class="errorelement" id="kinngakuformat">形式が不正です</span>
            </div>

        </div>

        <div class="input-container">
            <label class="label">取引先<span class="requirered">*</span></label>
            <div class="torihikisakiinput">
                <input type="text" name="torihikisaki" class="input-field" id="torihikisaki" value="{{$file->取引先}}" autocomplete="off">
                <div class="registtorihikisakiselect" id="torihikisakiselect"></div>
                <span class="errorelement" id="required1">必須項目です</span>
                @error('torihikisaki')
                <span class="errorsentence">{{ $message }}</span>
                @enderror
            </div>

        </div>


        <div class="input-container">
            <label class="label">書類区分<span class="requirered">*</span></label>
            <div>
                <select name="syorui" class="input-field" id="syorui">
                    @foreach($documents as $document)
                    <option {{$document->selected}} value="{{ $document->id }}">{{ $document->書類 }}</option>
                    @endforeach
                </select>
            </div>

        </div>
        <div class="input-container">
            <label class="label">受領・提出<span class="requirered">*</span></label>
            <div>
                <select name="teisyutu" class="input-field" id="teisyutu">
                    <option {{$jyuryo}}>受領</option>
                    <option {{$teisyutu}}>提出</option>
                </select>
            </div>

        </div>
        <div class="input-container">
            <label class="label">保存方法<span class="requirered">*</span></label>
            <div>
                <select name="hozonn" class="input-field" id="hozonn">
                    <option {{$dennshi}}>電子保存</option>
                    <option {{$scan}}>スキャナ保存</option>
                </select>
            </div>

        </div>
        <div class="input-container">
            <label class="label">検索ワード</label>
            <div>
                <input type="text" name="kennsakuword" class="input-field" value="{{$file->備考}}" id="kennsakuword">
                @error('kennsakuword')
                <span class="errorsentence">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <div class="input-container">
            <label class="label">グループ</label>
            <div>
                <select name="group" class="input-field" id="group">
                    @foreach ($groups as $group)
                    <option {{$group->selected}} value="{{$group->id}}">{{$group->グループ名}}</option>
                    @endforeach
                    <option {{$selectstatus}} value="{{Auth::id()}}">指定なし</option>
                </select>

            </div>

        </div>


        <input type="hidden" value="{{$file->過去データID}}" id="id">
        <input type="submit" value="変更" id="registbutton" class="registbutton">
        <div class="deletebutton">削除</div>
    </form>

    <div class="previewbox">
        <div class="pastpreview">
            <div class="defaultpreview">
                {{$file->ファイル形式}}形式ファイル
            </div>
        </div>
        <div class="rightarrow">
            →
        </div>
        <div class="previewarea editpreviewarea">
            変更なし
        </div>

    </div>


</div>
@endsection
@section('footer')
@endsection