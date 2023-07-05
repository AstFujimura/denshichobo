@extends('layouts.template')

@section('title')
登録ページ
@endsection 

@section('menuebar')
登録ページ
@endsection 

@section('menue')

<input type="button" value="過去データ参照" class="pastbutton" id="pastbutton">
<button  onclick = "location.href='{{route('topGet')}}';" class="backbutton"> もどる</button>

@endsection

@section('main')
<div class="registcontainer">


<form action="{{route('registPost')}}" method="post" enctype="multipart/form-data" id="myForm">
        @csrf
    <div class="form-container">
        <div class="input-container">
            <div class="registform">
            <div class="label">会社名 <div class="ast">*</div></div>
            <div><input type="text" name="company_name" id="company" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">会社カナ <div class="ast" >*</div></div>
            <div><input type="text" name="company_kana" id="companyKana" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">名前 <div class="ast" >*</div></div>
            <div><input type="text" name="name" id="Name" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">カナ名 <div class="ast">*</div></div>
            <div><input type="text" name="name_kana" id="NameKana" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">電話番号</div>
            <div><input type="text" name="tel" id="tel" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">部署名</div>
            <div><input type="text" name="department" id="department" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">役職</div>
            <div><input type="text" name="position" id="position" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">郵便番号</div>
            <div><input type="text" name="postal_code" id="postal_code" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">住所</div>
            <div><input type="text" name="address" id="address" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">FAX番号</div>
            <div><input type="text" name="fax" id="fax" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">メールアドレス</div>
            <div><input type="text" name="email" id="email" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">担当者</div>
            <div><input type="text" name="person_in_charge" id="person_in_charge" class="input-field"></div>
            </div>
            <div class="registform">
            <div class="label">入力者</div>
            <div><input type="text" name="input_person" id="input_person" class="input-field"></div>
            </div>

            <div class="redmessage" id="error-message">
                必須項目が入力されていません
            </div>
        </div>
        <!-- input-container終わり -->
            <div type="submit"  id="regist" class="button-container">
      
                <div class="top-element">
                    <div class="imgBox" id="image-preview"></div>
                    <input type="file" name="business_card" id="image-input">
                </div>

                
                  
           
                <input type="submit" value="登録" class="registbutton">
            </div>
    </div>

       </form>
<div id="past" class="past" style="display: none;">
<div id="pulldown" class="pulldown">
    <div class="selecttitle">
        <input id="pullcheck" type="checkbox" class="pullcheck">
        <label for="pullcheck" class="selectcontainer"><span id="selectedPull" class="selectedPull">--選択してください--</span><span class="triangle">▼</span></label>
    </div>
    <div class="selectmenue">
            @foreach ($users as $user)
            <button class="pullOption">
                <span class="company">{{$user->会社名}}</span>
                <span class="name">{{$user->名前}}</span>
                <span class="companyKana hidden">{{$user->会社名カナ}}</span>
                <span class="NameKana hidden">{{$user->カナ名}}</span>
                <span class="tel hidden">{{$user->電話番号}}</span>
                <span class="department hidden">{{$user->部署名}}</span>
                <span class="position hidden">{{$user->役職}}</span>
                <span class="postal_code hidden">{{$user->郵便番号}}</span>
                <span class="address hidden">{{$user->住所}}</span>
                <span class="fax hidden">{{$user->FAX番号}}</span>
                <span class="email hidden">{{$user->メールアドレス}}</span>
                <span class="person_in_charge hidden">{{$user->担当者}}</span>
                <span class="input_person hidden">{{$user->入力者}}</span>
            </button>
            @endforeach
    </div>
</div>


            <div class="reference">
                <div class="label">会社名 </div>
                <div class="referenceElement"><input type="checkbox" name="checkbox" id="companyCB"><span class="ref" id="companySP"></span></div>
            </div>
            <div class="reference">
                <div class="label">会社カナ </div>
                <div class="referenceElement"><input type="checkbox" name="company_kana" id="companyKanaCB" ><span class="ref" id="companyKanaSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">名前 </div>
                <div class="referenceElement"><input type="checkbox" name="name" id="NameCB" ><span class="ref" id="NameSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">カナ名 </div>
                <div class="referenceElement"><input type="checkbox" name="name_kana" id="NameKanaCB" ><span class="ref" id="NameKanaSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">電話番号</div>
                <div class="referenceElement"><input type="checkbox" name="tel" id="telCB" ><span class="ref" id="telSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">部署名</div>
                <div class="referenceElement"><input type="checkbox" name="department" id="departmentCB" ><span class="ref" id="departmentSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">役職</div>
                <div class="referenceElement"><input type="checkbox" name="position" id="positionCB" ><span class="ref" id="positionSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">郵便番号</div>
                <div class="referenceElement"><input type="checkbox" name="postal_code" id="postal_codeCB" ><span class="ref" id="postal_codeSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">住所</div>
                <div class="referenceElement"><input type="checkbox" name="address" id="addressCB" ><span class="ref" id="addressSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">FAX番号</div>
                <div class="referenceElement"><input type="checkbox" name="fax" id="faxCB" ><span class="ref" id="faxSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">メールアドレス</div>
                <div class="referenceElement"><input type="checkbox" name="email" id="emailCB" ><span class="ref" id="emailSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">担当者</div>
                <div class="referenceElement"><input type="checkbox" name="person_in_charge" id="person_in_chargeCB" ><span class="ref" id="person_in_chargeSP"></span></div>
            </div>
            <div class="reference">
                <div class="label">入力者</div>
                <div class="referenceElement"><input type="checkbox" name="input_person" id="input_personCB" ><span class="ref" id="input_personSP"></span></div>
            </div>

            <input type="button" value="←追加" id="addButton" class="additionbutton">



       </div>
</div>
        @endsection 
        @section('footer')
    @endsection 

