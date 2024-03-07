$('.loginText').keydown(function(event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化
  
      var currentIndex = $('.loginText').index(this);
      var nextInput = $('.loginText').eq(currentIndex + 1);
  
      if (nextInput.length === 0) {
        $('#loginForm').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });