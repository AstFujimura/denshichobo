@extends('layouts.admintemplate')

@section('title')
管理者ページ | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')

@endsection

@section('main')
<h2>書類管理</h2>

<form action="{{route('admindocumentPost')}}" method="post" enctype="multipart/form-data" id="admin_document_form">
    @csrf
    <div class="documenttable">
        <div class="documenttable_header">
            <div class="admin_use">有効</div>
            <div class="admin_document">書類</div>
            <div class="admin_document_change">名称変更</div>
            <div class="admin_document_delete">削除</div>
        </div>
        <div class="sortable">
            @foreach($documents as $document)

            <div class="documenttable_body docu_past" id="container{{$document->id}}">
                @if ($document->check == "check")
                <div class="admin_use"><input type="checkbox" class="docu_check" checked id="check{{$document->id}}"></div>
                @else
                <div class="admin_use"><input type="checkbox" class="docu_check" id="check{{$document->id}}"></div>
                @endif

                
                <div class="admin_document">
                    <div class="admin_document_text document_open" id="text{{$document->id}}">{{$document->書類}}</div>
                    <input type="text" value="{{$document->書類}}" class="admin_document_value" id="value{{$document->id}}">
                </div>
                <div class="admin_document_change">
                    <div class="docu_change_button" id="change{{$document->id}}">変更</div>
                </div>
                <div class="admin_document_delete">
                    <div class="docu_delete_button" id="{{$document->id}}">削除</div>
                </div>
            </div>
            @endforeach

        </div>

    </div>
    <div class="docu_addbutton" id="docu_addbutton">
        + 追加
    </div>
    <div class="add">

    </div>
    <button class="document_change_button">
        更新
    </button>
    <input type="hidden" id="save" value="save"><span class="savemessage">※更新ボタンを押して変更を反映させてください</span>

</form>




@endsection
@section('footer')
@endsection