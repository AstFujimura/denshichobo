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



<form class="form" action="{{route('companyregistPost')}}" method="post" enctype="multipart/form-data" id="companyForm">
        @csrf
              <div class="iconcontainer">
                <img src="{{ asset('img/company.svg') }}" class="icon">
                <span class="basetitle">
                  企業登録
                </span>

              </div>
                
                <table class="detailtable">
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社名<div class="ast">*</div></td>
                    <td class="tablevalue"><input type="text" name="company_name" id="companySP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社名カナ<div class="ast">*</div></td>
                    <td class="tablevalue"><input type="text" name="company_kana" id="companyKanaSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">郵便番号</td>
                    <td class="detailtablevalue"><input type="text" name="postal_code" id="postal_codeSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社所在地</td>
                    <td class="detailtablevalue"><input type="text" name="address" id="addressSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">電話番号</td>
                    <td class="detailtablevalue"><input type="text" name="tel" id="telSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">FAX番号</td>
                    <td class="detailtablevalue"><input type="text" name="fax" id="faxSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">URL</td>
                    <td class="detailtablevalue"><input type="text" name="URL" id="URLSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社備考</td>
                    <td class="detailtablevalue"><input type="text" name="CompanyETC" id="CompanyETCSP" class="input-field"></td>
                  </tr>
                </table>
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

