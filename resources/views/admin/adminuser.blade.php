@extends('layouts.admintemplate')

@section('title')
ユーザー一覧 | TAMERU
@endsection

@section('menuebar')

@endsection

@section('menue')


@endsection


@section('main')
<h2>ユーザー一覧</h2>
<div class="bread_crumb_container">

    <div class="bread_crumb_content">
        <a>ユーザー一覧</a>
    </div>
    <div class="bread_crumb_content">
        <a href="{{ route('adminGet') }}">管理画面一覧</a>
    </div>

</div>
<div>
    <form action="{{route('adminregistPost')}}" method="post">
        @csrf
        <div class="admin_header_container">
            <div class="add_user_button">
                ＋ ログインアカウント追加
            </div>
            <div class="add_user_form">
                <div class="add_user_form_content">
                    <label for="user_name">ユーザー名</label>
                    <input name="user_name" type="text" placeholder="ユーザー名">
                </div>
                <div class="add_user_form_content">
                    <label for="display_name">表示名</label>
                    <input name="display_name" type="text" placeholder="表示名">
                </div>
                <div class="add_user_form_content">
                    <label for="email">メールアドレス</label>
                    <input name="email" type="text" placeholder="メールアドレス">
                </div>
                <div class="add_user_form_content">
                    <label for="password">パスワード</label>
                    <input name="password" type="password" placeholder="パスワード">
                </div>
                <div class="add_user_form_content">
                    <label for="admin">権限</label>
                    <select name="admin" id="admin">
                        <option value="一般">一般</option>
                        <option value="管理">管理</option>
                    </select>
                </div>
                <div class="add_user_form_content">
                    <label for="group">グループ</label>
                    <div>
                        <div class="add_user_form_content_group">
                            @foreach ($groups as $key => $group)
                            <label for="group_{{$key}}" class="add_user_form_content_group_label">
                                <input type="checkbox" name="group[]" value="{{$group->id}}" id="group_{{$key}}">
                                <span>
                                    {{$group->グループ名}}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                </div>
                <div class="add_user_submit_button">
                    追加する
                </div>
            </div>
        </div>
    </form>
    <div class="admin_top_table_header">
        <div data-cell="name">ユーザー名</div>
        <div data-cell="email">email</div>
        <div data-cell="status">権限</div>
        <div data-cell="change">編集</div>
        <div data-cell="delete">削除</div>
    </div>
    <div class="admin_top_table_body">
        @foreach ($users as $user)
        <div class="admin_top_table_element" data-delete_url="{{ route('adminDelete', ['id' => $user->id]) }}">
            <div class="cell_content" data-cell="name">{{$user->name}}</div>
            <div class="cell_content" data-cell="email">{{$user->email}}</div>
            <div class="cell_content" data-cell="status">{{$user->管理}}</div>
            <div class="cell_content icon_cell" data-cell="change">
                <div class="user_edit_button">
                    <img src="{{ asset($prefix.'/'.'img/edit.svg')}}" class="edit_icon">
                    <span>編集</span>
                </div>

            </div>
            <div class="cell_content icon_cell" data-cell="delete">
                <div class="user_delete_button">
                    <img src="{{ asset($prefix.'/'.'img/delete.svg')}}" class="delete_icon">
                    <span>削除</span>
                </div>
            </div>
            <div class="user_edit_container">
                <div class="user_setting_edit_content" data-url="{{ route('admineditPost', $user->id) }}">
                    <div class="user_form_content">
                        <label for="user_name">ユーザー名</label>
                        <input name="user_name" type="text" placeholder="ユーザー名" value="{{ $user->name }}">
                    </div>
                    <div class="user_form_content">
                        <label for="display_name">表示名</label>
                        <input name="display_name" type="text" placeholder="表示名" value="{{ $user->表示名 }}">
                    </div>
                    <div class="user_form_content">
                        <label for="email">メールアドレス</label>
                        <input name="email" type="text" placeholder="メールアドレス" value="{{ $user->email }}">
                    </div>
                    <div class="user_form_content">
                        <label for="admin">権限</label>
                        <select name="admin">
                            <option value="管理" @if ($user->管理 == "管理") selected @endif>管理</option>
                            <option value="一般" @if ($user->管理 == "一般") selected @endif>一般</option>
                        </select>
                    </div>
                    <div class="user_form_content" data-form_content="group">
                        <label for="group">グループ</label>
                        <div class="user_form_content_group">
                            @foreach ($groups as $key => $group)
                            <label for="group_{{$key}}_{{$user->id}}" class="user_form_content_group_label">
                                <input type="checkbox" name="group[]" value="{{$group->id}}"
                                    id="group_{{$key}}_{{$user->id}}" @if ($user->グループ->contains('グループID', $group->id))
                                checked @endif>
                                <span>
                                    {{$group->グループ名}}
                                </span>
                            </label>

                            @endforeach
                        </div>
                    </div>
                    <input name="user_id" type="hidden" value="{{ $user->id }}">
                    <form action="{{ route('adminresetPost', ['id' => $user->id]) }}" method="post">
                        @csrf
                        <div class="user_setting_password_reset_button">
                            パスワードリセット
                        </div>
                    </form>
                    

                    <div class="user_setting_edit_button_container">
                        <div class="user_setting_cancel_button">
                            キャンセル
                        </div>
                        <div class="user_setting_edit_button">
                            変更する
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
@section('footer')
@endsection