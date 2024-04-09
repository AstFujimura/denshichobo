@extends('layouts.flowtemplate')

@section('title')
管理者ページ
@endsection




@section('main')
<div class="MainElement">

    <h2 class="pagetitle"><img src="{{ asset(config('prefix.prefix').'/'.'img/flow_title/mail.svg') }}" alt="" class="title_icon">メール設定</h2>
    <div class="mail_setting_container">
        <form action="{{route('mailsettingpost')}}" id="mail_setting_post" method="post">
            @csrf
            <div class="mail_setting_button_content">
                <a href="{{route('workflow')}}" class="back_button " id="flow_next_button">
                    <img src="{{ asset(config('prefix.prefix').'/'.'img/button/home_back.svg') }}" alt="" class="button_icon">
                    トップへもどる
                </a>
                <button class="next_button flow_next_button" id="flow_next_button">
                    登録
                </button>
            </div>
            <div class="mail_setting_main_content">
                <div class="mail_setting_section">
                    <div class="mail_setting_subtitle">
                        送信用ユーザー情報
                    </div>
                    <div class="mail_setting_form_content ">
                        <div class="mail_setting_form_label">
                            名前<span class="red">*</span>
                        </div>
                        <input type="text" name="name" value="{{$M_mail->name ?? ''}}" id="mail_setting_name" class="mail_setting_form_text text_semilong_content" data-required="true" autocomplete="off">
                    </div>
                    <div class="mail_setting_form_content ">
                        <div class="mail_setting_form_label">
                            電子メールアドレス<span class="red">*</span>
                        </div>
                        <input type="mail" name="mail" value="{{$M_mail->mail ?? ''}}" id="mail_setting_mail" class="mail_setting_form_text text_semilong_content" data-required="true" autocomplete="off">
                    </div>
                    <div class="mail_setting_subtitle">
                        通知用送信サーバー
                    </div>
                    <div class="mail_setting_form_content ">
                        <div class="mail_setting_form_label">
                            送信メールサーバー(SMTP)<span class="red">*</span>
                        </div>
                        <input type="text" name="host" value="{{$M_mail->host ?? ''}}" id="mail_setting_host" class="mail_setting_form_text text_semilong_content" data-required="true" autocomplete="off">
                    </div>
                    <div class="mail_setting_form_content ">
                        <div class="mail_setting_form_label">
                            送信サーバーのポート番号<span class="red">*</span>
                        </div>
                        <input type="number" value="{{$M_mail->port ?? '587'}}" name="port" id="mail_setting_port" class="mail_setting_form_text" data-required="true" autocomplete="off">
                    </div>
                    <div class="mail_setting_subtitle">
                        メールサーバーへのログオン情報
                    </div>
                    <div class="mail_setting_form_content ">
                        <div class="mail_setting_form_label">
                            アカウント名<span class="red">*</span>
                        </div>
                        <input type="text" name="username"  value="{{$M_mail->username ?? ''}}" id="mail_setting_username" class="mail_setting_form_text text_semilong_content" data-required="true" autocomplete="off">
                    </div>
                    <div class="mail_setting_form_content ">
                        <div class="mail_setting_form_label">
                            パスワード<span class="red">*</span>
                        </div>
                        <input type="password" name="password" id="mail_setting_password" class="mail_setting_form_text text_semilong_content" data-required="true" autocomplete="off">
                    </div>
                </div>
                <div class="mail_setting_section">
                    <div class="mail_setting_subtitle">
                        テスト送信
                    </div>
                    <div class="mail_setting_form_content ">
                        <div class="mail_setting_form_label">
                            受信用メールアドレス
                        </div>
                        <div class="mail_setting_test_send_content">
                            <input type="text" name="test_mail"  value="{{$M_mail->test_mail ?? ''}}" id="mail_setting_test_mail" class="mail_setting_form_text text_semilong_content" data-required="false" autocomplete="off">
                            <div class="test_send_button">
                                テスト送信
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@section('footer')
@endsection