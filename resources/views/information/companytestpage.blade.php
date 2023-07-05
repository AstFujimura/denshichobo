@extends('layouts.template')

@section('title')
企業登録ページ
@endsection 

@section('menuebar')
企業登録ページ
@endsection 

@section('menue')


@endsection

@section('main')
<div class="registcontainer">


<form class="form" action="{{route('companyregistPost')}}" method="post" enctype="multipart/form-data" id="myForm">
        @csrf
    <div id="past" class="past">
        <div class="form-title">会社情報</div>


        
        <div id="company-form" style="display: block;">
            <div class="registform">
            <div class="label">会社名 <div class="ast">*</div></div>
            <div><input type="text" name="company_name" id="companySP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">会社カナ <div class="ast" >*</div></div>
            <div><input type="text" name="company_kana" id="companyKanaSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">電話番号</div>
            <div><input type="text" name="tel" id="telSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">郵便番号</div>
            <div><input type="text" name="postal_code" id="postal_codeSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">住所</div>
            <div><input type="text" name="address" id="addressSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">FAX番号</div>
            <div><input type="text" name="fax" id="faxSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">URL</div>
            <div><input type="text" name="URL" id="URLSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">会社備考</div>
            <div><input type="text" name="CompanyETC" id="CompanyETCSP" class="input-field"></div>
            </div>
            <div class="redmessage" id="error-message">
                    必須項目が入力されていません
            </div>
            <input type="submit" value="登録" class="registbutton" id="registbutton">
        </div>
    </div>
    



</form>
        @endsection 
        @section('footer')
    @endsection 

