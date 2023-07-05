@extends('layouts.template')

@section('title')
詳細ページ
@endsection 

@section('menuebar')
<a href="{{route('topGet')}}" class="nav">名刺一覧</a> > {{$user->名前}}さん詳細
@endsection 

@section('menue')
<a href="/edit/{{$user->id}}" class="addbutton">
    <div class="addbuttonelement01">
        <div class="button1logo01">
        <img src="{{ asset('img/user_edit_line.svg') }}">
        </div>
        <div class="accordion1name01">
          編集
        </div>

    </div>
</a>

<form method="POST" action="{{route('delete')}}" onsubmit="return confirm_test()" class="addbutton">
  @csrf
  @method('DELETE')
    <input type="hidden" name="id" value="{{$user->id}}">
    <!-- <button type="submit" class="deletebutton"> 消去</button>  -->
    <button type="submit" class="addbuttonelement01">
        <div class="button1logo01">
        <img src="{{ asset('img/delete_line.svg') }}">
        </div>
        <div class="accordion1name01">
          削除
        </div>

</button>
</form> 
@endsection

@section('main')
            @if ($user->名刺ファイル)
            <div class="big">
            <img src="{{ Storage::url($user->名刺ファイル) }}"  class="bigImg">
            <div class="batsu">×</div>
            </div>            
            <div class="overlay"></div>
            @endif


      <div class="detailmain">
          <div class="NameCompanyInfo">
              <div>
                
                <table class="detailtable">
                      <tr class="detailtablerow">
                        <td class="detailtabletitle">氏名</td>
                        <td class="namedetailtablevalue">{{$user->名前}}</td>
                      </tr>
                      <tr class="detailtablerow">
                        <td class="detailtabletitle">カナ</td>
                        <td class="namedetailtablevalue">{{$user->カナ名}}</td>
                      </tr>
                      <tr class="detailtablerow">
                        <td class="detailtabletitle">携帯電話番号</td>
                        <td class="namedetailtablevalue">{{$user->携帯電話番号}}</td>
                      </tr>
                      <tr class="detailtablerow">
                        <td class="detailtabletitle">電話番号</td>
                        <td class="namedetailtablevalue">{{$user->電話番号}}</td>
                      </tr>
                      <tr class="detailtablerow">
                    <td class="detailtabletitle">部署名</td>
                    <td class="namedetailtablevalue">{{$user->部署名}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">役職名</td>
                    <td class="namedetailtablevalue">{{$user->役職}}</td>
                  </tr>
                  <tr class="detailtablerow">
                        <td class="detailtabletitle">E-mail</td>
                        <td class="namedetailtablevalue">{{$user->メールアドレス}}</td>
                  </tr>
                  <tr class="detailtablerow">
                        <td class="detailtabletitle">備考</td>
                        <td class="namedetailtablevalue">{{$user->備考}}</td>
                  </tr>
                </table>
                
              </div>

                
          
                <div class="meishi">
                  @if ($user->名刺ファイル)
                  <img src="{{ Storage::url($user->名刺ファイル) }}" id="meishi" class="meishiImg">
                  @else
                  <div class="meishiImg">
                  名刺ファイルが登録されていません
                  </div>
                  
                  @endif
                </div>

          </div>



            <div class="CompanyDetailInfo">
                <table class="detailtable">
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">会社名</td>
                    <td class="companydetailtablevalue">{{$user->companies->会社名}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">会社名カナ</td>
                    <td class="companydetailtablevalue">{{$user->companies->会社名カナ}}</td>
                  </tr>

                  <tr class="detailtablerow">
                    <td class="detailtabletitle">郵便番号</td>
                    <td class="companydetailtablevalue">{{$user->companies->郵便番号}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">会社所在地</td>
                    <td class="companydetailtablevalue">{{$user->companies->住所}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">代表電話番号</td>
                    <td class="companydetailtablevalue">{{$user->companies->電話番号}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">FAX番号</td>
                    <td class="companydetailtablevalue">{{$user->companies->FAX番号}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">URL</td>
                    <td class="companydetailtablevalue">
                    <form action="{{route('urlsearch')}}" method="POST" class="urlform">
                        @csrf
                            <button type="submit" name="Button" value="{{$user->companies->URL}}" class="hyperlink">{{$user->companies->URL}}</button>
                    </form>
                    </td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="detailtabletitle">会社備考</td>
                    <td class="companydetailtablevalue">{{$user->companies->会社備考}}</td>
                  </tr>
                </table>
                
            </div>










      </div>


      
      <div>
        
      </div>
      
      
      
   

           

        @endsection 
        @section('footer')
    @endsection 

    <script>
        function confirm_test() {
            var select = confirm("本当に{{$user->名前}}さんの情報を削除しますか？");
            return select;
        }
      </script>

