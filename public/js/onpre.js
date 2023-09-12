$(document).ready(function () {
  var prefix = $('#prefix').val();

  $('#myForm').submit(function (event) {
    //値の入力時に不正なデータがある場合はalertがtrueになる
    var alert = false;
    event.preventDefault();

    // 必須項目が空欄の場合のエラーメッセージ

    var requiredarray = ["name", "email", "password"];

    emptycheck(requiredarray);


    fourBytecheck("name", "userformat")
    fourBytecheck("email", "emailformat")
    passcheck("password", "passwordformat")
    // usercheck("name", "usercheck")
    if ($("#password").val() != $("#newpassword").val() ){
      $("#password").addClass('invalid')
      $("#newpassword").addClass('invalid')
      $("#newpasswordformat").addClass('errorsentence')
    }
    else{
      $("#newpassword").removeClass('invalid')
      $("#newpasswordformat").removeClass('errorsentence')
    }



    //登録画面におけるフォームの確認
    if (!$('.errorsentence').length) {

      var title = $('#registbutton').val();


      if (confirm("本当に登録しますか？")) {
        history.pushState(null, null, "/"+ prefix+'/error/K183623');
        this.submit(); // フォームの送信を実行
      }

    }
  });








  //管理者画面の編集画面において一般ユーザーに変更する際に残りの管理者の数をカウントしてエラーを出すコード
  $('#admin-myForm').on('submit', function (e) {
    $("#changeerror").removeClass("errorsentence");
    var id = $('#userid').val();
    var admin = $('#admin').val();
    var submitButton = this;



    e.preventDefault(); // フォームの送信を中止

    //値の入力時に不正なデータがある場合はalertがtrueになる
    var alert = false;

    // 必須項目が空欄の場合のエラーメッセージ

    var requiredarray = ["name", "email"];

    emptycheck(requiredarray);


    fourBytecheck("name", "userformat")
    fourBytecheck("email", "emailformat")
    usercheck("name", "usercheck")

    //変更画面におけるフォームの確認
    if (!$('.errorsentence').length) {
      if (admin == "一般") {
        $.ajax({
          url: "/"+ prefix + '/admincheck/' + id,
          type: 'get',
          processData: false,
          contentType: false,
          success: function (response) {
            //残りの管理ユーザーが1人以上いる場合は続行する。
            if (response > 0) {
              if (confirm("本当に変更しますか")) {
                history.pushState(null, null, "/"+ prefix+'/error/K183623');
                submitButton.submit()
              }
            }
            else {
              $("#changeerror").addClass("errorsentence");
            }
          }
        });
      }
      else if (admin == "管理") {
        if (confirm("本当に変更しますか")) {
          history.pushState(null, null, "/"+ prefix+'/error/K183623');
          submitButton.submit()
        }
      }
    }
  });





  //管理画面に置いてユーザーを削除する時
  $('#admindelete').on('submit', function (e) {
    $("#deleteerror").removeClass("errorsentence");
    e.preventDefault(); // フォームの送信を中止
    var id = $('#userid').val();
    var form = this; // フォーム要素を保持
    $.ajax({
      url: "/"+ prefix +'/admincheck/' + id,
      type: 'get',
      processData: false,
      contentType: false,
      success: function (response) {
        //残りの管理ユーザーが1人以上いる場合は続行する。
        if (response > 0) {
          if (confirm("本当に削除しますか")) {
            form.submit();
          }
        }
        else {
          $("#deleteerror").addClass("errorsentence");
        }
      }
    });

  });






  $('#adminreset').on('submit', function (e) {
    e.preventDefault(); // フォームの送信を中止
    if (confirm("本当にパスワードをリセットしますか。現在のパスワードは使用できなくなります。")) {
      this.submit();
    }
  });




  $('.title').on('click', function () {
    $("#deleteerror").removeClass("errorsentence");
  });



  if ($('.kinngakuedit').length != 0) {
    kinngakucheck_change("kinngaku");
  }
  //検索項目のalert
  $('.kinngakuTd').each(function () {
    var kinngaku = $(this).text()
    var result = kinngaku.replace(/(\d)(?=(\d\d\d)+$)/g, "$1,");
    $(this).text(result);
  });
  $('.hidukeTd').each(function () {
    var hiduke = $(this).text()
    var result = hiduke.replace(/(\d{4})(\d{2})(\d{2})/, '$1/$2/$3');
    $(this).text(result);
  });



  //日付のフォーカスが外れた時にその中身を判定する。
  $('#startyear').blur(function () {
    datecheck_change("startyear");
  });
  $('#endyear').blur(function () {
    datecheck_change("endyear");
  });
  $('#hiduke').blur(function () {
    datecheck_change("hiduke");
  });

  $('.kinngakuinput').blur(function () {
    kinngakucheck_change("startkinngaku");
    kinngakucheck_change("endkinngaku");

  });
  $('#kinngaku').blur(function () {
    $(this).val(kinngaku_comma("kinngaku"));
  });
  $('#kinngakuedit').blur(function () {
    $(this).val(kinngaku_comma("kinngakuedit"));
  });








  $("#torihikisaki").on("focus", function () {
    $("#torihikisakiselect").show()
    var searchText = $(this).val();
    torihikiselect(searchText, "torihikisakiselect")
  });
  var isComposing = false; // 日本語入力などの変換中かどうかのフラグ

  $("#torihikisaki").on('compositionstart', function () {
    isComposing = true;
  });

  $("#torihikisaki").on('compositionend', function () {
    var searchText = $(this).val();
    isComposing = false;
    torihikiselect(searchText, "torihikisakiselect")
  });
  $("#torihikisaki").on("input", function () {
    var searchText = $(this).val();
    if (!isComposing) {
      // 入力操作の終了時に履歴を更新
      torihikiselect(searchText, "torihikisakiselect")
    }

  });
  $("#torihikisakiselect").on("click", ".torihikisakielement", function () {
    var torihikisaki = $(this).text();
    $('#torihikisaki').val(torihikisaki);
    $("#torihikisakiselect").hide()
    $('#torihikisaki').focus();
  });
  $(document).on("click", function (event) {
    var target = $(event.target);
    if (!target.is("#torihikisaki, #torihikisakiselect")) {
      $("#torihikisakiselect").hide()
    }
  });
  // torihikisakiのキーアップイベント（Enterキー）
  $("#torihikisaki").keydown(function (e) {
    if (e.keyCode === 13) { // Enterキー
      $("#torihikisakiselect").hide();
    }
  });




  //searchTextには取引先の検索ワード
  //torihikisakiselectには表示するセレクトボックスのid
  function torihikiselect(searchText, torihikisakiselect) {
    $.ajax({
      url: '/' + prefix + '/torihikisaki/',
      method: 'GET',
      data: { search: searchText },
      success: function (response) {
        $('#' + torihikisakiselect).empty();

        if (response == "該当なし") {
          $('#' + torihikisakiselect).append('<div class="gaitounashi">該当なし</div>');
        }
        else {
          $.each(response, function (index, clients) {
            $('#' + torihikisakiselect).append('<div class="torihikisakielement">' + clients.取引先 + '</div>');
          });
        }

      }
    });
  }




  //検索ボタンを押したとき
  $('.searchform').submit(function (event) {

    event.preventDefault();
    datecheck_change('startyear')
    datecheck_change('endyear')
    kinngakucheck_change("startkinngaku");
    kinngakucheck_change("endkinngaku");
    date_start_end('startyear', 'endyear');
    kinngaku_start_end("startkinngaku", "endkinngaku");

    //金額や日付のフォーマットが誤っている場合はsearcherror値の対象
    if (!$(".searcherror").length && !$(".invalid").length) {
      $('.loader').show();

      // 1秒遅らせてフォームの送信を実行する
      setTimeout(function () {
        $('.searchform')[0].submit(); // フォームの送信を実行
      }, 500);

    }
  });

  $('.form').submit(function (event) {
    //値の入力時に不正なデータがある場合はalertがtrueになる
    var alert = false;
    event.preventDefault();

    // 必須項目が空欄の場合のエラーメッセージ

    var requiredarray = ["hiduke", "kinngaku", "torihikisaki"];

    emptycheck(requiredarray);



    if ($('#file').val() == '') {
      //登録ページの場合は必須条件
      //ただし、編集ページは任意のため空白も許容する
      if ($('#regist').length) {
        $('#file').addClass("invalid")
        $('.fileerrorelement').addClass("errorsentence");
        alert = true
      }
    }
    else {
      $('#file').removeClass("invalid")
      $('.fileerrorelement').removeClass("errorsentence");
    }

    hidukecheck("hiduke", "dateformat")
    numcheck("kinngaku", "kinngakuformat")
    fourBytecheck("torihikisaki", "torihikiformat")
    fourBytecheck("kennsakuword", "kennsakuwordformat")



    //登録(変更)画面におけるフォームの確認
    if (!$('.errorsentence').length) {

      var title = $('#registbutton').val();

      if (title.trim() == '登録') {

        if (confirm("本当に登録しますか？")) {
          history.pushState(null, null, "/" + prefix + '/error/K183623');
          this.submit(); // フォームの送信を実行
        }
      }
      else {
        if (confirm("本当に変更しますか？")) {
          history.pushState(null, null, "/" + prefix + '/error/K183623');
          this.submit(); // フォームの送信を実行
        }
      }
    }
  });

  function getExtension(fileName) {
    var lastDotIndex = fileName.lastIndexOf('.');
    if (lastDotIndex === -1 || lastDotIndex === 0) {
      return ""; // 拡張子がない場合は空文字を返す
    }
    return fileName.substring(lastDotIndex + 1);
  }


  function kinngaku_comma(id) {
    var inputDate = $("#" + id).val().trim();
    var nonConnma = inputDate.replace(/,/g, "")
    var isNumeric = !isNaN(parseFloat(nonConnma)) && isFinite(nonConnma);
    //数値でなかった場合エラー
    if (!isNumeric) {
      $("#" + id).addClass("searcherror")
    }
    //0から始まる数値の時にエラー,ただし0は許容
    else if (/^0.+/.test(nonConnma)) {
      $("#" + id).addClass("searcherror")
    }
    else {
      $("#" + id).removeClass("searcherror")
    }
    var result = nonConnma.replace(/(\d)(?=(\d{3})+$)/g, "$1,");

    return result;
  }


  function kinngakucheck_change(id) {
    var inputDate = $("#" + id).val().trim();

    if (inputDate == "") {
      $("#" + id).removeClass("searcherror")
    }
    else {
      // 数値であるかをチェック
      var nonConnma = inputDate.replace(/,/g, "")
      var isNumeric = !isNaN(parseFloat(nonConnma)) && isFinite(nonConnma);


      // カンマがないことをチェック
      var hasNoComma = !/\,/.test(inputDate);
      // カンマが3桁ごとに適切に入っているかをチェック
      var isValidFormat = /^(\d{1,3},)*\d{1,3}$/.test(inputDate);

      //数値でなかった場合エラー
      if (!isNumeric) {
        $("#" + id).addClass("searcherror")
      }
      //0から始まる数値の時にエラー,ただし0は許容
      else if (/^0.+/.test(inputDate)) {
        $("#" + id).addClass("searcherror")
      }
      //カンマがない場合は変換する
      else if (hasNoComma) {
        var result = inputDate.replace(/(\d)(?=(\d\d\d)+$)/g, "$1,");
        $("#" + id).val(result);
        $("#" + id).removeClass("searcherror")
      }

    }
  }

  //emptyarrayは配列であり必須項目のinputtextのid名を格納する
  //前提としてエラーメッセージにはrequired1のようにrequiredと数字を付与する
  function emptycheck(emptyarray) {
    //空白になっているidを格納する
    var inv = []
    var invmessage = []

    //入力されているidを格納する
    var vali = []
    var valimessage = []

    for (let i = 0; i < emptyarray.length; i++) {
      if ($('#' + emptyarray[i]).val() == '') {
        inv.push('#' + emptyarray[i]);
        invmessage.push('#required' + (i + 1));
        alert = true
      }
      else {
        vali.push('#' + emptyarray[i])
        valimessage.push('#required' + (i + 1));
      }
    }

    $(inv.join(",")).addClass("invalid");
    $(vali.join(",")).removeClass("invalid");
    $(invmessage.join(",")).addClass("errorsentence");
    $(valimessage.join(",")).removeClass("errorsentence");

  }

  //不正な形式だった場合はfalse
  //date:日付のinputタグのid
  //errordate:日付のエラーメッセージのid
  //kinngaku:金額のinputタグのid
  //errorkinngaku:金額のエラーメッセージのid

  function hidukecheck(date, errordate) {
    var dateval = $("#" + date).val();
    //値が入っていない場合はほかのエラーチェックがあるためtrueを返す
    if (!dateval) {
      var datecheck = true
    }
    else {
      var datecheck = /^\d{4}\/\d{2}\/\d{2}$/.test(dateval)
    }

    if (datecheck) {
      $("#" + errordate).removeClass('errorsentence')
      // $("#" + date).removeClass('invalid')
    }
    else {
      $("#" + errordate).addClass('errorsentence')
      $("#" + date).addClass('invalid')
    }
  }
  function numcheck(kinngaku, errorkinngaku) {
    var kinngakuval = $("#" + kinngaku).val();
    kinngakuval = kinngakuval.replace(/,/g, "")
    var kinngakucheck = isPositiveNumber(kinngakuval)
    if (kinngakucheck) {
      $("#" + errorkinngaku).removeClass('errorsentence')
      // $("#" + kinngaku).removeClass('invalid')
    }
    else {
      $("#" + errorkinngaku).addClass('errorsentence')
      $("#" + kinngaku).addClass('invalid')
    }
  }
  function fourBytecheck(fourBytedate, errorfourBytedate) {
    var fourByteval = $("#" + fourBytedate).val();
    var fourBytecheck = /[\ud800-\udbff][\udc00-\udfff]/g.test(fourByteval);
    //4バイト文字が含まれていたらエラー
    if (!fourBytecheck) {
      $("#" + errorfourBytedate).removeClass('errorsentence')
      // $("#" + kinngaku).removeClass('invalid')
    }
    else {
      $("#" + errorfourBytedate).addClass('errorsentence')
      $("#" + fourBytedate).addClass('invalid')
    }

  }

  function passcheck(passdata, errorpassdata) {
    var passval = $("#" + passdata).val();
    var hasUpperCase = /[A-Z]/.test(passval); // 大文字が含まれているか
    var hasLowerCase = /[a-z]/.test(passval); // 小文字が含まれているか
    var hasNumber = /\d/.test(passval); // 数字が含まれているか
    var isLengthValid = passval.length >= 8 // 8文字以上かどうか


    //値が入っていない場合はほかのエラーチェックがあるためtrueを返す
    if (!passval) {
      var passval = true
    }
    //hasRequiredCharsがtrueの場合はパスワードが有効
    else if (!hasUpperCase || !hasLowerCase || !hasNumber || !isLengthValid) {
      $("#" + errorpassdata).addClass('errorsentence')
      $("#" + passdata).addClass('invalid')
    }
    else {
      $("#" + errorpassdata).removeClass('errorsentence')
      $("#" + passdata).removeClass('invalid')
    }

  }

  //エラーメッセージを出して重複がなければtrue返す
  function usercheck(namedata, errornamedata) {
    var nameval = $("#" + namedata).val();
    var change = ""
    var id = $("#userID").val();
    if ($("#admineditpage").length) {
      change = "change"
    }
    $.ajax({
      url: "/"+ prefix +'/usercheck',
      type: 'get',
      data: {
        username: nameval,
        change: change,
        id: id
      },
      success: function (response) {
        //ユーザー名が重複している場合
        if (response == "重複") {
          $("#" + errornamedata).addClass('errorsentence')
          $("#" + namedata).addClass('invalid')
          return true
        }
        else {
          $("#" + errornamedata).removeClass("errorsentence");
          return false
        }
      }
    });
  }



  function isPositiveNumber(value) {
    //値が入っていない場合はほかのエラーチェックがあるためtrueを返す
    if (!value) {
      return true
    }
    else {
      // 入力値が数値であり、1以上2100000000の値かどうかを判定する
      var parsedValue = parseFloat(value);
      return !isNaN(parsedValue) && parsedValue >= -2100000000 && parsedValue <= 2100000000;
    }

  }

  //日付の前後が正しいかを判定し不正の場合はエラーを出す
  function date_start_end(start, end) {
    if ($('#' + start).val() && $('#' + end).val()) {
      var startdate = parseInt($('#' + start).val().replace(/\//g, ""))
      var enddate = parseInt($('#' + end).val().replace(/\//g, ""))

      if (startdate <= enddate) {
        $('#' + start).removeClass('invalid')
        $('#' + end).removeClass('invalid')
        return true
      }
      else {
        $('#' + start).addClass('invalid')
        $('#' + end).addClass('invalid')
        alert("日付の範囲を確認してください")
        return false
      }
    }
    else {
      $('#' + start).removeClass('invalid')
      $('#' + end).removeClass('invalid')
      return true
    }
  }

  //金額の前後が正しいかを判定し不正の場合はエラーを出す
  function kinngaku_start_end(start, end) {
    if ($('#' + start).val() && $('#' + end).val()) {
      var startdate = parseInt($('#' + start).val().replace(/,/g, ""))
      var enddate = parseInt($('#' + end).val().replace(/,/g, ""))
      if (startdate <= enddate) {
        $('#' + start).removeClass('invalid')
        $('#' + end).removeClass('invalid')
        return true
      }
      else {
        $('#' + start).addClass('invalid')
        $('#' + end).addClass('invalid')
        alert("金額の範囲を確認してください")
        return false
      }
    }
    else {
      $('#' + start).removeClass('invalid')
      $('#' + end).removeClass('invalid')
      return true
    }
  }

  function datecheck_change(id) {

    var inputDate = $("#" + id).val().trim();



    if (inputDate == "") {
      $("#" + id).removeClass("searcherror")
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
        inputDate = currentYear + '/' + month + '/' + day;
      }

      // 「yyyymmdd」形式の場合、指定の形式に変換する
      else if (/^\d{8}$/.test(inputDate)) {
        var year = inputDate.substr(0, 4);
        var month = inputDate.substr(4, 2);
        var day = inputDate.substr(6, 2);

        inputDate = year + '/' + month + '/' + day;
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
        inputDate = year + '/' + month + '/' + day;
      }

      if (exist_date(inputDate, 1980, 2030)) {
        $("#" + id).removeClass("searcherror")
        // 変換後の日付を入力フィールドに設定する
        $("#" + id).val(inputDate);
      }
      else {
        $("#" + id).addClass("searcherror")
      }
    }

    //入力された値がyyyy/mm/ddであるとき
    else {
      if (exist_date(inputDate, 1980, 2030)) {
        $("#" + id).removeClass("searcherror")
      }
      else {
        $("#" + id).addClass("searcherror")
      }
    }



  }
  //yyyy/mm/ddの形式が本当に存在するか確かめる。(例)2023/02/29はfalseを返す
  //第二,三引数には指定する範囲の年を入れる。
  function exist_date(date, startyear, endyear) {
    //yyyy/mm/dd形式になっている場合
    if (/^\d{4}\/\d{2}\/\d{2}$/.test(date)) {
      var parts = date.split('/');
      var year = parseInt(parts[0]);
      var month = parseInt(parts[1]);
      var day = parseInt(parts[2]);

      if (year >= startyear && year <= endyear && month >= 1 && month <= 12 && day >= 1 && day <= 31) {
        var hyphenDate = date.replace(/\//g, "-");
        var dateObject = new Date(hyphenDate);
        var formattedDate = dateObject.toISOString().split('T')[0];

        if (hyphenDate == formattedDate) {
          return true
        }
        else {
          return false
        }
      }
      else {
        return false
      }
    }
    //yyyy/mm/dd形式になっていない場合
    else {
      return false
    }

  }


});
