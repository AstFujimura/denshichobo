@extends('layouts.admintemplate')

@section('title')
管理者ページ
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
            <div class="admin_use">使用</div>
            <div class="admin_document">書類</div>
            <div class="admin_document_delete">削除</div>
        </div>
        @foreach($documents as $document)

        <div class="documenttable_body past" id="container{{$document->id}}">
            @if ($document->check == "check")
            <div class="admin_use"><input type="checkbox" class="docu_check" checked id="check{{$document->id}}"></div>
            @else
            <div class="admin_use"><input type="checkbox" class="docu_check" id="check{{$document->id}}"></div>
            @endif

            <div class="admin_document">{{$document->書類}}</div>
            <div class="admin_document_delete">
                <div class="docu_delete_button" id="{{$document->id}}">削除</div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="docu_addbutton" id="docu_addbutton">
        + 追加
    </div>
    <div class="add">

    </div>
    <button class="docu_change_button">
        変更
</button>

</form>




@endsection
@section('footer')
@endsection