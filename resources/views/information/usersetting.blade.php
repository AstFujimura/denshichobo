@extends('layouts.'.$system_type.'template')

@section('title')
TAMERU ~電子帳簿保存
@endsection

@section('menuebar')
@endsection

@section('menue')
@endsection

@section('main')
<div class="usersetting-page">
    <h2 class="pagetitle usersettingtitle">ユーザー設定</h2>
    <p class="usersetting-page__lead">{{ $user->name }} さんの情報を変更できます。</p>

    <form action="{{ route('usersettingPost') }}" method="post" enctype="multipart/form-data" id="usersetting" class="usersetting-form">
        @csrf
        <div class="usersetting-form__section">
            <div class="usersetting-form__field">
                <label class="usersetting-form__label" for="name">
                    ユーザー名<span class="requirered">*</span>
                </label>
                <input type="text" name="name" class="usersetting-form__input input-field" id="name" value="{{ old('name', $user->name) }}" autocomplete="username">
                <span class="errorelement usersetting-form__error" id="required1">必須項目です</span>
                <span class="errorelement usersetting-form__error" id="userformat">形式が不正です</span>
                <span class="errorelement usersetting-form__error" id="usercheck">ユーザー名が重複しています</span>
            </div>

            <div class="usersetting-form__field">
                <label class="usersetting-form__label" for="displayname">
                    表示名<span class="requirered">*</span>
                </label>
                <input type="text" name="displayname" class="usersetting-form__input input-field" id="displayname" value="{{ old('displayname', $user->表示名) }}" autocomplete="nickname">
                <span class="errorelement usersetting-form__error" id="required15">必須項目です</span>
            </div>

            <div class="usersetting-form__field">
                <label class="usersetting-form__label" for="email">
                    メールアドレス<span class="requirered">*</span>
                </label>
                <input type="text" name="email" class="usersetting-form__input input-field" id="email" value="{{ old('email', $user->email) }}" autocomplete="email">
                <span class="errorelement usersetting-form__error" id="required2">必須項目です</span>
                <span class="errorelement usersetting-form__error" id="emailformat">形式が不正です</span>
            </div>

            <div class="usersetting-form__field usersetting-form__field--checkbox">
                <label class="usersetting-form__checkbox" for="mail">
                    <input type="checkbox" name="mail" class="mailcheck_input" id="mail" value="1" {{ old('mail', $user->メール許可) ? 'checked' : '' }}>
                    <span>メール通知を許可する</span>
                </label>
            </div>
        </div>

        <div class="usersetting-form__password important">
            <button type="button" class="important_title usersetting-form__password-toggle" aria-expanded="false" aria-controls="usersetting-password-fields">
                パスワードを変更する
            </button>
            <div class="importantelement usersetting-form__password-fields" id="usersetting-password-fields">
                <div class="usersetting-form__field">
                    <label class="usersetting-form__label" for="oldpass">
                        現パスワード<span class="requirered">*</span>
                    </label>
                    <input type="password" name="oldpass" class="usersetting-form__input input-field" id="oldpass" autocomplete="current-password">
                    <span class="errorelement usersetting-form__error" id="required3">必須項目です</span>
                    <span class="errorelement usersetting-form__error differencepass">パスワードが違います</span>
                </div>
                <div class="usersetting-form__field">
                    <label class="usersetting-form__label" for="newpass">
                        新パスワード<span class="requirered">*</span>
                    </label>
                    <input type="password" name="newpass" class="usersetting-form__input input-field" id="newpass" autocomplete="new-password">
                    <span class="errorelement usersetting-form__error" id="required4">必須項目です</span>
                    <span class="errorelement usersetting-form__error passcheck">同じパスワードを入力してください</span>
                    <span class="errorelement usersetting-form__error" id="passformat">大文字,小文字,数字を含めた8文字以上にしてください</span>
                </div>
                <div class="usersetting-form__field">
                    <label class="usersetting-form__label" for="newpasscheck">
                        新パスワード確認<span class="requirered">*</span>
                    </label>
                    <input type="password" name="newpass_confirm" class="usersetting-form__input input-field" id="newpasscheck" autocomplete="new-password">
                    <span class="errorelement usersetting-form__error" id="required5">必須項目です</span>
                    <span class="errorelement usersetting-form__error passcheck">同じパスワードを入力してください</span>
                    <span class="errorelement usersetting-form__error" id="passcheckformat">大文字,小文字,数字を含めた8文字以上にしてください</span>
                </div>
            </div>
        </div>

        <input type="hidden" name="system_type" id="system_type" value="{{ $system_type }}">
        <div class="usersetting-form__actions">
            <button type="submit" class="usersettingbutton">変更</button>
        </div>
    </form>
</div>
<input type="hidden" id="userID" value="{{ $user->id }}">
@endsection

@section('footer')
@endsection
