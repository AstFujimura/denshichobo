@extends('layouts.admintemplate')

@section('title')
名刺管理システム 管理者ページ
@endsection 

@section('menuebar')
<a href="{{route('adminpageGet')}}" class="nav">ユーザー一覧</a>>{{$user->name}}さん詳細
@endsection 

@section('menue')

<a href="/admin/edit/{{$user->id}}" class="addbutton">
    <div class="addbuttonelement01">
        <div class="button1logo01">
        <img src="{{ asset('img/user_edit_line.svg') }}">
        </div>
        <div class="accordion1name01">
          編集
        </div>

    </div>
</a>
<form method="POST" action="{{route('adminDelete')}}" onsubmit="return confirm_test()" class="addbutton">
  @csrf
  @method('DELETE')
    <input type="hidden" name="id" value="{{$user->id}}">
    <!-- <button type="submit" class="deletebutton"> 消去</button>  -->
    <button type="submit" class="addbuttonelement01">
        <div class="button1logo01">
        <img src="{{ asset('img/delete_line.svg') }}">
        </div>
        <div class="accordion1name01">
          消去
        </div>

</button>
</form> 
@endsection


@section('main')
                    
<div class="baseinfo">
                <div class="basetitle">
                  基本情報
                </div>
                    <table class="nameTEL">
                      <tr class="admin-tablerow">
                        <td class="tabletitle">ユーザー名</td>
                        <td class="tablevalue">{{$user->name}}</td>
                      </tr>
                      <tr class="admin-tablerow">
                        <td class="tabletitle">email</td>
                        <td class="tablevalue">{{$user->email}}</td>
                      </tr>
                      <tr class="admin-tablerow">
                        <td class="tabletitle">管理者権限</td>
                        <td class="tablevalue">{{$user->管理者権限}}</td>
                      </tr>
                    </table>
              </div>

                    
                    
@endsection 
    @section('footer')
    @endsection 

    <script>
        function confirm_test() {
            var select = confirm("本当に{{$user->name}}さんの情報を消去しますか");
            return select;
        }
      </script>

