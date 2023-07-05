@extends('layouts.template')

@section('title')
編集ページ
@endsection 

@section('menuebar')
<a href="{{route('companyGet')}}" class="nav">会社一覧 </a> > <a href="/company/{{$company->会社コード}}" class="nav">{{$company->会社名}}詳細</a> > {{$company->会社名}}編集
@endsection 

@section('menue')


@endsection

@section('main')
<div class="registcontainer">


<form class="form" action="{{route('companyeditPut')}}" method="post" enctype="multipart/form-data" id="companyForm">
        <input type="hidden" name="id" value="{{$company->会社コード}}">
        @csrf
        @method('PUT')

                
                <table class="detailtable">
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社名<div class="ast">*</div></td>
                    <td class="tablevalue"><input type="text" name="company_name" value="{{$company->会社名}}" id="companySP" class="input-field">
                      <span class="errorelement" id="required3">必須項目です</span>
                    </td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社名カナ<div class="ast">*</div></td>
                    <td class="tablevalue"><input type="text" name="company_kana" value="{{$company->会社名カナ}}" id="companyKanaSP" class="input-field">
                        <span class="errorelement" id="katakana2">すべてカタカナで入力してください</span>
                        <span class="errorelement" id="required4">必須項目です</span>
                    </td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">郵便番号</td>
                    <td class="detailtablevalue"><input type="text" name="postal_code" value="{{$company->郵便番号}}" id="postal_codeSP" class="input-field">
                        <span class="errorelement" id="postalerror">7桁の郵便番号を入力してください</span>
                    </td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社所在地</td>
                    <td class="detailtablevalue"><input type="text" name="address" value="{{$company->住所}}" id="addressSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">電話番号</td>
                    <td class="detailtablevalue"><input type="text" name="tel" value="{{$company->電話番号}}" id="telSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">FAX番号</td>
                    <td class="detailtablevalue"><input type="text" name="fax" value="{{$company->FAX番号}}" id="faxSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">URL</td>
                    <td class="detailtablevalue"><input type="text" name="URL" value="{{$company->URL}}" id="URLSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社備考</td>
                    <td class="detailtablevalue"><input type="text" name="CompanyETC" value="{{$company->会社備考}}" id="CompanyETCSP" class="input-field"></td>
                  </tr>
                </table>
                <div class="redmessage" id="error-message">
                    必須項目が入力されていません
                </div>
                <input type="submit" value="変更" class="registbutton" id="registbutton">
            </div>

            
  
<!-- 
<div id="past" class="past">

        
        <div id="company-form" style="display: block;">
            <div class="registform">
            <div class="label">会社名 <div class="ast">*</div></div>
            <div><input type="text" name="company_name" value="{{$company->会社名}}" id="companySP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">会社カナ <div class="ast" >*</div></div>
            <div><input type="text" name="company_kana" value="{{$company->会社名カナ}}" id="companyKanaSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">電話番号</div>
            <div><input type="text" name="tel" value="{{$company->電話番号}}" id="telSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">郵便番号</div>
            <div><input type="text" name="postal_code" value="{{$company->郵便番号}}" id="postal_codeSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">住所</div>
            <div><input type="text" name="address" value="{{$company->住所}}" id="addressSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">FAX番号</div>
            <div><input type="text" name="fax" value="{{$company->FAX番号}}" id="faxSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">URL</div>
            <div><input type="text" name="URL" value="{{$company->URL}}"  id="URLSP" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">会社備考</div>
            <div><input type="text" name="CompanyETC" value="{{$company->会社備考}}"  id="CompanyETCSP" class="input-field"></div>
            </div>
            <input type="hidden" name="id" value="{{$company->会社コード}}">

            <input type="submit" value="変更" class="edit-button" id="registbutton">
        </div>
</div> -->
</form>
        @endsection 
        @section('footer')
    @endsection 

