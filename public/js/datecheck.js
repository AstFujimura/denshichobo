$(document).ready(function () {

  $('#adminedit').on('submit', function (e) {
    e.preventDefault(); // フォームの送信を中止
    if (confirm("本当に変更しますか")) {
      this.submit();
    }
  });
  $('#admindelete').on('submit', function (e) {
    e.preventDefault(); // フォームの送信を中止
    if (confirm("本当に削除しますか")) {
      this.submit();
    }
  });
  $('#adminreset').on('submit', function (e) {
    e.preventDefault(); // フォームの送信を中止
    if (confirm("本当にパスワードをリセットしますか。現在のパスワードは使用できなくなります。")) {
      this.submit();
    }
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



  $('.searchform').submit(function (event) {
    event.preventDefault();
    datecheck_change('startyear')
    datecheck_change('endyear')
    kinngakucheck_change("startkinngaku");
    kinngakucheck_change("endkinngaku");
    date_start_end('startyear', 'endyear');
    kinngaku_start_end("startkinngaku", "endkinngaku");

    //金額や日付のフォーマットガ誤っている場合はsearcherror値の大証
    if (!$(".searcherror").length && !$(".invalid").length) {
      this.submit(); // フォームの送信を実行
    }
  });

  $('.form').submit(function (event) {
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

    if ($('#torihikisaki').val() == '') {
      inv.push('#torihikisaki');
      invmessage.push('#required1');
      alert = true
    }
    else {
      vali.push('#torihikisaki')
      valimessage.push('#required1');
    }
    if ($('#kinngaku').val() == '') {
      inv.push('#kinngaku')
      invmessage.push('#required2');
      alert = true
    }
    else {
      vali.push('#kinngaku')
      valimessage.push('#required2');
    }

    if ($('#syorui').val() == '') {
      inv.push('#syorui');
      invmessage.push('#required3');
      alert = true
    }
    else {
      vali.push('#syorui')
      valimessage.push('#required3');
    }
    if ($('#hiduke').val() == '') {
      inv.push('#hiduke');
      invmessage.push('#required4');
      alert = true
    }
    else {
      vali.push('#hiduke')
      valimessage.push('#required4');
    }

    $(inv.join(",")).addClass("invalid");
    $(vali.join(",")).removeClass("invalid");
    $(invmessage.join(",")).addClass("errorsentence");
    $(valimessage.join(",")).removeClass("errorsentence");

    if ($('#file').val() == '') {
      //登録ページの場合は必須条件
      //ただし、編集ページは任意のため
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

    datacheck("hiduke", "dateformat", "kinngaku", "kinngakuformat")

    //登録(変更)画面におけるフォームの確認
    if (!$('.errorsentence').length) {

      var title = $('#registbutton').val();

      if (title.trim() == '登録') {

        if (confirm("本当に登録しますか？")) {
          history.pushState(null, null, '/error/K183623');
          this.submit(); // フォームの送信を実行
        }
      }
      else {
        if (confirm("本当に変更しますか？")) {
          history.pushState(null, null, '/error/K183623');
          this.submit(); // フォームの送信を実行
        }
      }
    }
  });

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

  function datacheck(date, errordate, kinngaku, errorkinngaku) {
    var dateval = $("#" + date).val();
    //値が入っていない場合はほかのエラーチェックがあるためtrueを返す
    if (!dateval) {
      datecheck = true
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
    if (datecheck && kinngakucheck) {
      return true
    }
    else {
      return false
    }

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
