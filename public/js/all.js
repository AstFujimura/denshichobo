$(document).ready(function() {
  if ($('.kinngakuedit').length != 0){
    kinngakucheck_change("kinngaku");   
  }
  //検索項目のalert
  $('.kinngakuTd').each(function() {
  var kinngaku =$(this).text()
  var result = kinngaku.replace(/(\d)(?=(\d\d\d)+$)/g, "$1,");
  $(this).text(result);
  });
  $('.hidukeTd').each(function() {
    var hiduke =$(this).text()
    var result = hiduke.replace(/(\d{4})(\d{2})(\d{2})/, '$1/$2/$3');
    $(this).text(result);
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
        $('.searchbutton').focus(); // 最後の入力欄でエンターキーを押すとフォームが送信される
        
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });


  //日付のフォーカスが外れた時にその中身を判定する。
  $('#startyear').blur(function() {
    datecheck_change("startyear");
  });
  $('#endyear').blur(function() {
    datecheck_change("endyear");
  });
  $('#hiduke').blur(function() {
    datecheck_change("hiduke");
  });

  $('.kinngakuinput').blur(function() {
    kinngakucheck_change("startkinngaku");
    kinngakucheck_change("endkinngaku");

  });

  //金額のフォームはインプットするたびにカンマが入るようにする
  $('#startkinngaku').on('input',function() {
   $(this).val(kinngaku_comma("startkinngaku"));
  });
  $('#endkinngaku').on('input',function() {
    $(this).val(kinngaku_comma("endkinngaku"));
   });

  $('#kinngaku').on('input',function() {
    $(this).val(kinngaku_comma("kinngaku"));
  });
  $('#kinngakuedit').on('input',function() {
    $(this).val(kinngaku_comma("kinngakuedit"));
  });
  

  

  $('.searchform').submit(function(event) {
    event.preventDefault();
    datecheck_change('startyear')
    datecheck_change('endyear')
    kinngakucheck_change("startkinngaku");
    kinngakucheck_change("endkinngaku");

      if(!$(".searcherror").length){
        this.submit(); // フォームの送信を実行
      }
  });
  // function submitbutton(alert,element){
  //   if(alert == false){
  //     element.submit(); // フォームの送信を実行
  //   }
  // }










  $('.form').submit(function(event) {
    //値の入力時に不正なデータがある場合はalertがtrueになる
    var alert = false;
    event.preventDefault();

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
      if ($('#hiduke').val() == ''){
        inv.push('#hiduke');
        invmessage.push('#required4');
        alert = true
      }
      else{
          vali.push('#hiduke')
          valimessage.push('#required4');
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
  if(alert == false && datacheck("hiduke","dateformat","kinngaku","kinngakuformat")) {
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


  $('.deletebutton').on('click',function(){
    $id = $("#id").val();
    if (confirm("本当に削除しますか?")){
      window.location.href = '/delete/'+$id;
    }
    

  });



});








function datecheck_change(id){

  var inputDate = $("#" + id).val().trim();
  


    if (inputDate == ""){
    }
    // else if (/[^0-9/]/.test(inputDate)) {
    //   searchalert = true
    // } 
    
    // 入力された日付が「yyyy/mm/dd」形式でない場合、指定の形式に変換する
    else if (!/^\d{4}\/\d{2}\/\d{2}$/.test(inputDate)) {
      // 「m/d」または「mm/dd」の形式の場合、年を現在の年に設定して指定の形式に変換する
      if (/^\d{1,2}\/\d{1,2}$/.test(inputDate)) {
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        var parts = inputDate.split('/');
        var month = parts[0];
        var day = parts[1];


        
        // 1桁の月と日には0を追加する
        if (month.length === 1) {
          month = '0' + month;
        }
        if (day.length === 1) {
          day = '0' + day;
        }
        
        if (month >=1 && month <= 12 && day >=1 && day <= 31){
          inputDate = currentYear + '/' + month + '/' + day;
          $("#" + id).removeClass("searcherror")
        }
        else{
          $("#" + id).addClass("searcherror")
        }
      }
      
      // 「yyyymmdd」形式の場合、指定の形式に変換する
      else if (/^\d{8}$/.test(inputDate)) {
        var year = inputDate.substr(0, 4);
        var month = inputDate.substr(4, 2);
        var day = inputDate.substr(6, 2);
        if (month >=1 && month <= 12 && day >=1 && day <= 31){
          inputDate = year + '/' + month + '/' + day;
          $("#" + id).removeClass("searcherror")
        }
        else{
          $("#" + id).addClass("searcherror")
        }
        
      }
      //「yyyy/m/d」形式などの場合、「yyyy/mm/dd」形式に変換する
      else if (/^\d{4}\/\d{1,2}\/\d{1,2}$/.test(inputDate)) {
        var parts = inputDate.split('/');
        var year = parts[0];
        var month = parts[1];
        var day = parts[2];
          // 1桁の月と日には0を追加する
          if (month.length === 1) {
            month = '0' + month;
          }
          if (day.length === 1) {
            day = '0' + day;
          }
          if (month >=1 && month <= 12 && day >=1 && day <= 31){
            inputDate = year + '/' + month + '/' + day;
            $("#" + id).removeClass("searcherror")
          }
          else{
            $("#" + id).addClass("searcherror")
          }
      }
      else if(!inputDate){
      }

      else{
        $("#" + id).addClass("searcherror")
      }

      var parts = inputDate.split('/');
      var year = parseInt(parts[0]);
      var month = parseInt(parts[1]);
      var day = parseInt(parts[2]);
  
  
  
      if (year >= 1980 && year <= 2030 && month >=1 && month <= 12 && day >=1 && day <= 31){
        var hyphenDate =inputDate.replace(/\//g,"-");
        var dateObject = new Date(hyphenDate);
        var formattedDate = dateObject.toISOString().split('T')[0];
  
        if (hyphenDate == formattedDate){
          $("#" + id).removeClass("searcherror")
        }
        else{
          $("#" + id).addClass("searcherror")
        }
      }
      else{
        $("#" + id).addClass("searcherror")
      }
          // 変換後の日付を入力フィールドに設定する
          $("#" + id).val(inputDate);
    }
}

function kinngaku_comma(id){
  var inputDate = $("#" + id).val().trim();
  var nonConnma = inputDate.replace(/,/g,"")
  var isNumeric = !isNaN(parseFloat(nonConnma)) && isFinite(nonConnma);
      //数値でなかった場合エラー
      if (!isNumeric){
        $("#" + id).addClass("searcherror")
      }
          //0から始まる数値の時にエラー,ただし0は許容
      else if(/^0.+/.test(nonConnma)){
        $("#" + id).addClass("searcherror")
      }
      else{
        $("#" + id).removeClass("searcherror")
      }
  var result = nonConnma.replace(/(\d)(?=(\d{3})+$)/g, "$1,");
  
  return result;
}


function kinngakucheck_change(id) {
  var inputDate = $("#" + id).val().trim();
    
    if (inputDate == ""){
      $("#" + id).removeClass("searcherror")
    }
    else{
      // 数値であるかをチェック
    var nonConnma = inputDate.replace(/,/g,"")
    var isNumeric = !isNaN(parseFloat(nonConnma)) && isFinite(nonConnma);
    

        // カンマがないことをチェック
    var hasNoComma = !/\,/.test(inputDate);
    // カンマが3桁ごとに適切に入っているかをチェック
    var isValidFormat = /^(\d{1,3},)*\d{1,3}$/.test(inputDate);

    //数値でなかった場合エラー
    if (!isNumeric){
      console.log("error")
      $("#" + id).addClass("searcherror")
    }
    //0から始まる数値の時にエラー,ただし0は許容
    else if(/^0.+/.test(inputDate)){
      $("#" + id).addClass("searcherror")
    }
    //カンマがない場合は変換する
    else if(hasNoComma){
      var result = inputDate.replace(/(\d)(?=(\d\d\d)+$)/g, "$1,");
      $("#" + id).val(result);
      $("#" + id).removeClass("searcherror")
    }
    // //カンマが入っておりかつ適切な位置にある場合は許容
    // else if(isValidFormat){
    //   var result = inputDate
    //   $("#" + id).val(result);
    //   $("#" + id).removeClass("searcherror")
    // }
    

    
    // //カンマが入っているが適切な位置にない場合はエラー
    // else{
    //   console.log("エラー")
    //   $("#" + id).addClass("searcherror")
    // }
    }
}

function datacheck(date,errordate,kinngaku,errorkinngaku){
  var dateval = $("#" + date).val();
  var datecheck = /^\d{4}\/\d{2}\/\d{2}$/.test(dateval)
  if (datecheck){
    $("#" + errordate).removeClass('errorsentence')
  }
  else{
    $("#" + errordate).addClass('errorsentence')
  }

  var kinngakuval = $("#" + kinngaku).val();
  kinngakuval = kinngakuval.replace(/,/g,"")
  var kinngakucheck = isPositiveNumber(kinngakuval)
  if (kinngakucheck){
    $("#" + errorkinngaku).removeClass('errorsentence')
  }
  else{
    $("#" + errorkinngaku).addClass('errorsentence')
  }
  if (datecheck && kinngakucheck){
    return true
  }
  else{
    return false
  }
  
}

function isPositiveNumber(value) {
  // 入力値が数値であり、1以上2100000000の値かどうかを判定する
  var parsedValue = parseFloat(value);
  return !isNaN(parsedValue) && parsedValue >= -2100000000 && parsedValue<= 2100000000;
}