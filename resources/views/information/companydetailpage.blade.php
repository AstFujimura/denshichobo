@extends('layouts.template')

@section('title')
詳細ページ
@endsection 

@section('menuebar')
<a href="{{route('companyGet')}}" class="nav">会社一覧 </a> > {{$company->会社名}}詳細
@endsection 

@section('menue')
  <a href="/company/edit/{{$company->会社コード}}" class="addbutton">
    <div class="addbuttonelement01">
        <div class="button1logo01">
        <img src="{{ asset('img/user_edit_line.svg') }}">
        </div>
        <div class="accordion1name01">
          編集
        </div>

    </div>
</a>

<form method="POST" action="{{route('companydelete')}}" onsubmit="return confirm_test()" class="addbutton">
  @csrf
  @method('DELETE')
    <input type="hidden" name="id" value="{{$company->会社コード}}">
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







          <div class="CompanyDetailInfo">
                
                <table class="detailtable">
                  <tr class="detailtablerow">
                    <td class="tabletitle">会社名</td>
                    <td class="companydetailtablevalue">{{$company->会社名}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="tabletitle">会社名カナ</td>
                    <td class="companydetailtablevalue">{{$company->会社名カナ}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="tabletitle">郵便番号</td>
                    <td class="companydetailtablevalue">{{$company->郵便番号}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="tabletitle">会社所在地</td>
                    <td class="companydetailtablevalue">{{$company->住所}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="tabletitle">電話番号</td>
                    <td class="companydetailtablevalue">{{$company->電話番号}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="tabletitle">FAX番号</td>
                    <td class="companydetailtablevalue">{{$company->FAX番号}}</td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="tabletitle">URL</td>
                    <td class="companydetailtablevalue">
                      <form action="{{route('urlsearch')}}" method="POST" class="urlform">
                          @csrf
                              <button type="submit" name="Button" value="{{$company->URL}}" class="hyperlink">{{$company->URL}}</button>
                      </form>
                    </td>
                  </tr>
                  <tr class="detailtablerow">
                    <td class="tabletitle">会社備考</td>
                    <td class="companydetailtablevalue">{{$company->会社備考}}</td>
                  </tr>
                </table>
            </div>




            <div class="CompanyDetailInfo">
                <div class="alert">
                    {{ session('error') }}
                </div>

                <table class="detailtable" id="detailtable">
                  <tr class="detailtablerow">
                    <td class="tabletitle">登録件数</td>
                    <td class="detailtablevalue ">{{$count}}件<input type="checkbox" id="pulltriangle" hidden><label for="pulltriangle" class="pulltriangle" >▼</label></td>

                  </tr>
                </table>
                <div class="pullscroll">
                  <table class="detailtable companyMeishi">
                      <thead>
                        <tr class="CompanyMeishiPullTitle">
                            <td class="PullValue">名前</td>
                            <td class="PullValue">カナ名</td>
                            <td class="PullValue">部署名</td>
                            <td class="PullValue">役職</td>
                        </tr>
                      </thead>
                    @foreach ($user as $data)
                      <tr onclick="window.location.href='/top/{{$data->id}}';" class="CompanyMeishiPull">
                        <td class="PullValue">{{$data->名前}}</td>
                        <td class="PullValue">{{$data->カナ名}}</td>
                        <td class="PullValue">{{$data->部署名}}</td>
                        <td class="PullValue">{{$data->役職}}</td>
                      </tr>
                    @endforeach
                  </table>
                </div>

                  
            </div>











      </div>

      
      
   

           

        @endsection 
        @section('footer')
    @endsection 

    <script>
        function confirm_test() {
            var select = confirm("本当に{{$company->会社名}}の情報を削除しますか？");
            return select;
        }
      </script>

