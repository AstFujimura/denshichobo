$(document).ready(function() {

    $('#adminedit').on('submit',function(e){
        e.preventDefault(); // フォームの送信を中止
        if (confirm("本当に変更しますか")){
            this.submit();
        }
    });
    $('#admindelete').on('submit',function(e){
        e.preventDefault(); // フォームの送信を中止
        if (confirm("本当に削除しますか")){
            this.submit();
        }
    });
    $('#adminreset').on('submit',function(e){
        e.preventDefault(); // フォームの送信を中止
        if (confirm("本当にパスワードをリセットしますか。現在のパスワードは使用できなくなります。")){
            this.submit();
        }
    });

    $('.title').on('click',function(){
        $('.title').toggleClass('close')
        $('.importantelement').toggleClass('open')
    });
});

