<body>
    <p>***このメールは自動送信メールです***</p>
    <p>{{$applicant_name}}さんが申請情報の承認を依頼しました</p>
    <p class="br"></p>

    <p>以下のURLから承認の操作を行ってください</p>
    <span>承認操作 :　</span><a href={{$url}}>{{$url}}</a>


    <style>
        p {
            color: black;
            font-size: 16px;
            font-weight: 200;
        }

        .br {
            height: 10px;
        }
    </style>
</body>