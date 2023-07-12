$(document).ready(function() {
  $('.kinngakuTd').each(function() {
  $kinngaku =$(this).text()
  $result = $kinngaku.replace(/(\d)(?=(\d\d\d)+$)/g, "$1,");
  console.log($kinngaku)
  $(this).text($result);
  });
  $('.hidukeTd').each(function() {
    $kinngaku =$(this).text()
    $result = $kinngaku.replace(/(\d{4})(\d{2})(\d{2})/, '$1/$2/$3');
    console.log($kinngaku)
    $(this).text($result);
    });





  $('.input-field').keydown(function(event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化
  
      var currentIndex = $('.input-field').index(this);
      var nextInput = $('.input-field').eq(currentIndex + 1);
  
      if (nextInput.length === 0) {
        $('.form').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });

  $('.searchinputtext').keydown(function(event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化

  
      var currentIndex = $('.searchinputtext').index(this);
      var nextInput = $('.searchinputtext').eq(currentIndex + 1);
  
      if (nextInput.length === 0) {
        $('.searchform').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });

  $('.searchinputtext').blur(function() {
    var inputDate = $(this).val().trim();
    
    // 日付の形式が「x/x」または「xx/xx」の場合、現在の年を追加する
    if (/\d{1,2}\/\d{1,2}/.test(inputDate)) {
      var currentDate = new Date();
      var currentYear = currentDate.getFullYear();
      inputDate = currentYear + '/' + inputDate;
    }
    
    // 入力された日付を指定の形式に変換する
    var formattedDate = inputDate.replace(/(\d{4})(\d{2})(\d{2})/, '$1/$2/$3');
    
    // 変換後の日付を入力フィールドに設定する
    $(this).val(formattedDate);
  });




  $('.form').submit(function(event) {
    event.preventDefault();
    var alert = false;

    // 必須項目が空欄の場合のエラーメッセージ
 
      
      //空白になっているidを格納する
      var inv = []
      var invmessage = []

      //入力されているidを格納する
      var vali = []
      var valimessage = []

      if ($('#torihikisaki').val() == ''){
        inv.push('#torihikisaki');
        invmessage.push('#required1');
        alert = true
      }
      else{
          vali.push('#torihikisaki')
          valimessage.push('#required1');
      }
      if ($('#kinngaku').val() == ''){
        inv.push('#kinngaku')
        invmessage.push('#required2');
        alert = true
      }
      else{
          vali.push('#kinngaku')
          valimessage.push('#required2');
      }

      if ($('#syorui').val() == ''){
          inv.push('#syorui');
          invmessage.push('#required3');
          alert = true
      }
      else{
          vali.push('#syorui')
          valimessage.push('#required3');
      }

        $(inv.join(",")).addClass("invalid");
        $(vali.join(",")).removeClass("invalid");
        $(invmessage.join(",")).addClass("errorsentence");
        $(valimessage.join(",")).removeClass("errorsentence");
      
      if($('#file').val() == ''){
        $('#file').addClass("invalid")
        $('.fileerrorelement').addClass("errorsentence");
        alert = true
      }
      else{
        $('#file').removeClass("invalid")
        $('.fileerrorelement').removeClass("errorsentence");
      }

    //登録(変更)画面におけるフォームの確認
  if(alert == false) {
    var title = $('#registbutton').val();
    
        if (title.trim() == '登録'){
          
        if (confirm("本当に登録しますか？")) {
          this.submit(); // フォームの送信を実行
        }
      }
      else {
        if (confirm("本当に変更しますか？")) {
          this.submit(); // フォームの送信を実行
      }
        }
  }
  });

  $('.droparea').on('dragover', function(event) {
    event.preventDefault();
    $(this).addClass("dragover");
  });
  $('.droparea').on('drop', function(event) {
    event.preventDefault();
    $(this).removeClass("dragover");
    var File = event.originalEvent.dataTransfer.files[0];
    $('#file').prop("files", event.originalEvent.dataTransfer.files);
  });



});