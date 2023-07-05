@extends('layouts.template')

@section('title')
編集ページ
@endsection 

@section('menuebar')
<a href="{{route('topGet')}}" class="nav">名刺一覧</a> > <a href="/top/{{$user->id}}" class="nav">{{$user->名前}}さん詳細</a> >{{$user->名前}}さん編集
@endsection 

@section('menue')

@endsection

@section('main')
<div class="registcontainer">


<form class="form" action="{{route('editPost')}}" method="post" enctype="multipart/form-data" id="myForm">
        @csrf
        <input name="id" value="{{$user->id}}"  hidden>
        <div class="NameCompanyInformation">
              <div class="baseedit">
                
                <table class="detailtable">
                      <tr class="tablerow">
                        <td class="tabletitle regist">氏名<div class="ast">*</div></td>
                        <td class="tablevalue"><input type="text" name="name" value="{{$user->名前}}"  id="Name" class="input-field">
                          <span class="errorelement" id="required1">必須項目です</span>
                        </td>
                      </tr>
                      <tr class="tablerow">
                        <td class="tabletitle regist">カナ<div class="ast">*</div></td>
                        <td class="tablevalue"><input type="text" name="name_kana" value="{{$user->カナ名}}" id="NameKana" class="input-field">
                            <span class="errorelement" id="katakana1">すべてカタカナで入力してください</span>
                            <span class="errorelement" id="required2">必須項目です</span>
                        </td>
                      </tr>
                      <tr class="tablerow">
                        <td class="tabletitle regist">携帯電話番号</td>
                        <td class="tablevalue"><input type="text" name="phone-number" value="{{$user->携帯電話番号}}" id="phone-number" class="input-field">
                            <span class="errorelement" id="phone-numbererror">11桁の携帯電話番号を入力してください</span>
                        </td>
                      </tr>
                      <tr class="tablerow">
                            <td class="tabletitle regist">電話番号</td>
                            <td class="tablevalue"><input type="text" name="number" value="{{$user->電話番号}}"  id="number" class="input-field">
                                <span class="errorelement" id="numbererror">10桁の固定電話番号を入力してください</span>
                            </td>
                        </tr>
                      <tr class="tablerow">
                    <td class="tabletitle regist">部署名</td>
                    <td class="detailtablevalue"><input type="text" name="department" value="{{$user->部署名}}" id="department" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">役職名</td>
                    <td class="detailtablevalue"><input type="text" name="position" value="{{$user->役職}}" id="position" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                        <td class="tabletitle regist">E-mail</td>
                        <td class="tablevalue"><input type="text" name="email" value="{{$user->メールアドレス}}" id="email" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                            <td class="tabletitle regist">備考</td>
                            <td class="tablevalue"><input type="text" name="etc" value="{{$user->備考}}"  id="etc" class="input-field"></td>
                        </tr>
                </table>
                
              </div>

                
          
              <div   id="regist" class="button-container">
                    <div class="beforeimgBox">
                          @if ($user->名刺ファイル)
                            <img src="{{ Storage::url($user->名刺ファイル) }}" class="editpreviewImage">
                          @else
                            <div class="editmeishiImg">
                          名刺ファイルが登録されていません
                            </div>
                          @endif
                    </div>
                    <div class="arrow">
                      ↓
                    </div>
                <div class="top-element">
                    <div class="afterimgBox" id="image-preview">
                        <div class="imgChange">ファイルをドラッグ&ドロップ</div>
                    </div>
                    <input type="file" name="business_card" id="image-input">
                    <span class="MB"></span>
                </div>
                <div class="error">ファイルサイズが大きすぎます。最大1MB</div>

                </div>

              </div>
                
                <table class="detailtable">
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社名<div class="ast">*</div></td>
                    <td class="tablevalue"><input type="text" name="company_name" value="{{$user->companies->会社名}}" id="companySP" class="input-field">
                      <span class="errorelement" id="required3">必須項目です</span>
                    </td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社名カナ<div class="ast">*</div></td>
                    <td class="tablevalue"><input type="text" name="company_kana" value="{{$user->companies->会社名カナ}}" id="companyKanaSP" class="input-field">
                      <span class="errorelement" id="katakana2">すべてカタカナで入力してください</span>
                        <span class="errorelement" id="required4">必須項目です</span>
                    </td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">郵便番号</td>
                    <td class="detailtablevalue"><input type="text" name="postal_code" value="{{$user->companies->郵便番号}}" id="postal_codeSP" class="input-field">
                      <span class="errorelement" id="postalerror">7桁の郵便番号を入力してください</span>
                    </td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社所在地</td>
                    <td class="detailtablevalue"><input type="text" name="address" value="{{$user->companies->住所}}" id="addressSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">電話番号</td>
                    <td class="detailtablevalue"><input type="text" name="tel" value="{{$user->companies->電話番号}}" id="telSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">FAX番号</td>
                    <td class="detailtablevalue"><input type="text" name="fax" value="{{$user->companies->FAX番号}}" id="faxSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">URL</td>
                    <td class="detailtablevalue"><input type="text" name="URL" value="{{$user->companies->URL}}" id="URLSP" class="input-field"></td>
                  </tr>
                  <tr class="tablerow">
                    <td class="tabletitle regist">会社備考</td>
                    <td class="detailtablevalue"><input type="text" name="CompanyETC" value="{{$user->companies->会社備考}}" id="CompanyETCSP" class="input-field"></td>
                  </tr>
                </table>
                
                <input type="submit" value="変更" class="registbutton" id="registbutton">
                
  



</form>
        @endsection 
        @section('footer')
    @endsection 

