//エラーメッセージの実装（登録ページと編集ページに適用）

$(document).ready(function() {
    $('#admin-myForm').submit(function(event) {
      if ($('#name').val() == '' || $('#email').val() == '' || $('#password').val() == '') {
        $('#error-message').css('color','red');
        
        //空白になっているidを格納する
        var inv = []
  
        //入力されているidを格納する
        var vali = []
  
        if ($('#name').val() == ''){
            inv.push('#name');
        }
        else{
            vali.push('#name')
        }
        if ($('#email').val() == ''){
            inv.push('#email');
          }
          else{
              vali.push('#email')
          }
          if ($('#password').val() == ''){
            inv.push('#password');
          }
          else{
              vali.push('#password')
          }
          $(inv.join(",")).addClass("invalid");
          $(vali.join(",")).removeClass("invalid");
          
        event.preventDefault();
      
    }
    else{
      event.preventDefault(); // フォームのデフォルトの送信をキャンセル
      var title = $('title').text();
      
      if (title.trim() == '登録ページ'){
        
      if (confirm("本当に登録しますか？")) {
        this.submit(); // フォームの送信を実行
      }
    }
    else {
      console.log(title);
      if (confirm("本当に変更しますか？")) {
        this.submit(); // フォームの送信を実行
    }
      }
    }
    });
  
  });


  //エンターキーで次の入力欄への移動


$('.input-field').keydown(function(event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化
  
      var currentIndex = $('.input-field').index(this);
      var nextInput = $('.input-field').eq(currentIndex + 1);
  
      if (nextInput.length === 0) {
        $('#admin-myForm').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });