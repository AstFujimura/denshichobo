$(document).ready(function () {

  // アコーディオンメニューを押したとき
  $(".accordion_menu_title").on("click", function () {
    $(this).toggleClass("accordion_menu_title_open")
    $(this).parent().find(".accordion_content").toggleClass("accordion_content_open")
  })

  // 承認者を個人かグループで選ぶかを選択し要素の表示非表示を行う
  $('.authorizer').on("click", function () {
    var focusid = $("#focus").data("id")
    // 承認者_個人が選択されたとき
    if ($('#authorizer1').prop('checked')) {
      $('.person_container').addClass("person_container_open")
      $('.group_container').removeClass("group_container_open")
      // 要素のインプットタグのdata-authorizerを変更する
      $('#' + focusid).data("authorizer", "person")
      $('#' + focusid).attr("data-authorizer", "person")
      change_authorizer("person")
    }
    // 承認者_グループが選択されたとき
    else if ($('#authorizer2').prop('checked')) {
      $('.group_container').addClass("group_container_open")
      $('.person_container').removeClass("person_container_open")
      $('#' + focusid).data("authorizer", "group")
      $('#' + focusid).attr("data-authorizer", "group")
      change_authorizer("group")
    }
  })

  // 承認者_個人のテキストボックスの文字が変わったとき
  $(document).on("change", ".person_text", function () {
    change_person()

  })

  $(document).on("focus", ".person_text", function () {
    ajax_person()

  })


  // 承認者_グループのテキストボックスの文字が変わったとき
  $(document).on("change", ".group_select", function () {
    change_group()

  })





  // 承認者_個人での承認者で全員の承認もしくは条件指定の場合の表示非表示
  $('.authorizer_condition').on("change", function () {
    if ($('#authorizer_condition1').prop('checked')) {
      $('#person_authorizer_number_container').removeClass("autorizer_number_container_open")
    }
    else if ($('#authorizer_condition2').prop('checked')) {
      $('#person_authorizer_number_container').addClass("autorizer_number_container_open")
    }
    change_person_required_number()
  })
  // 承認者_個人での条件指定での承認者の数が変更されたとき
  $('#person_required_number').on("change", function () {
    change_person_required_number()
  })
  // 承認者_グループの選択方法での要素の表示非表示
  $('.choice_method').on("change", function () {
    if ($('#choice_method1').prop('checked')) {
      $('.choice_container').removeClass("choice_container_open")
      $('.post_choice_container').removeClass("post_choice_container_open")
    }
    else if ($('#choice_method2').prop('checked')) {
      $('.choice_container').addClass("choice_container_open")
      $('.post_choice_container').removeClass("post_choice_container_open")
    }
    else if ($('#authorizer2').prop('checked')) {
      $('.post_choice_container').addClass("post_choice_container_open")
      $('.choice_container').removeClass("choice_container_open")
    }
  })

  // 承認者_グループでの選択可能人数での無制限もしくは選択人数指定の場合の表示非表示
  $('.choice_limit').on("change", function () {
    if ($('#choice_limit1').prop('checked')) {
      $('#group_authorizer_number_container').removeClass("autorizer_number_container_open")
    }
    else if ($('#choice_limit2').prop('checked')) {
      $('#group_authorizer_number_container').addClass("autorizer_number_container_open")
    }
  })

  // クリックしたときの場所により判定を行う
  // 要素をクリックしたとき、それ以外の時で挙動を分ける
  $(document).on('click', function (event) {
    var targetElement = $(event.target);

    // closestメソッドを使用して、特定の親要素を取得
    var eElement = targetElement.closest('.e');
    var rightElement = targetElement.closest('.right_side_menu');
    var batsuElement = targetElement.closest('.batsu_button');

    console.log(eElement.attr("class"))
    // 要素をクリックした場合は要素にfocusクラスを付与する
    if (eElement.length == 1) {
      if (eElement.attr("id") !== "1_1") {
        $(".e").removeClass("focus")
        eElement.addClass("focus")
        console.log(eElement.data("column"))
        console.log(eElement.data("row"))
        focus_right_side_menu(eElement.data("column"), eElement.data("row"))
        person_required_number_reload()
      }
      else {
        $(".e").removeClass("focus")
        $('.right_side_menu').removeClass("right_side_menu_open")
      }
    }
    // 右側メニューの内容をクリックしたとき
    else if (rightElement.length == 1) {
      // ×ボタンを押したとき
      if (batsuElement.length == 1) {
        if ($(".batsu_button").length != 1) {
          batsuElement.parent().remove();
          change_person()
          change_person_required_number()
        }

      }
    }
    else {
      $(".e").removeClass("focus")
      $('.right_side_menu').removeClass("right_side_menu_open")
    }

  })

  // 承認者_個人の追加(+)ボタンを押したときの挙動
  $('.plus_button').on('click', function () {
    $(".person_content").append('<div class="person_box"><input type="text" class="person_text"><div class="batsu_button">×</div></div>')
  })

  // 承認者_個人の削除(×)ボタンを押したときの挙動
  // $(document).on('click', '.batsu_button', function () {
  //   $(this)
  // })

  // 承認者_個人のinputが変更されたときの挙動
  $(document).on('change', '.person_text', function () {
    $('.person_text')
  })








  // グリッドの範囲
  var Xcellcount = 1
  var Ycellcount = 1
  // グリッドの範囲を決定する
  $('.element').each(function () {
    if ($(this).data("column") > Xcellcount) {
      Xcellcount = $(this).data("column")
    }
    if ($(this).data("row") > Ycellcount) {
      Ycellcount = $(this).data("row")
    }
  })

  $("#maxgrid").data("maxcolumn", Xcellcount + 1)
  $("#maxgrid").data("maxrow", Ycellcount + 1)
  // グリッドのセルの値を指定
  const cellwidth = 150
  const cellheight = 50

  // 空白のセルの値を指定
  const gapcellwidth = 40
  const gapcellheight = 15

  // 画面表示された段階でグリッドを作成
  creategrid(cellwidth, cellheight, gapcellwidth, gapcellheight)

  reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)

  reloadelement()


  var arrays = {
    "1": ["1_1", "1_2"]
  }

  // 現在の最新のid番号を格納する
  var nowelementid = 10000

  // 現在の要素をカウントする関数
  nowelementid = nowelementid + nowelementcount()

  //lineのスタートとエンドの位置を格納
  var linedata = [];
  $(document).on('mousedown', function (event) {

    var mousedownTarget = $(event.target)

    if (mousedownTarget.hasClass("e")) {

      // ドラッグ中のデータをリセット
      linedata = [];
      linedata[0] = mousedownTarget.data("column")
      linedata[1] = mousedownTarget.data("row")

      //各要素をチェック
      $(".e").each(function () {
        $(this).addClass("blue")
        var toElement = $(this)
        var lineresult = $('input').filter(function () {
          return $(this).data('startcolumn') === linedata[0] && $(this).data('startrow') === linedata[1] && $(this).data('endcolumn') === toElement.data('column') && $(this).data('endrow') === toElement.data('row');
        });
        // ドラッグ元とドラッグ先が同じであるか
        if (toElement.attr("id") == linedata[0] + '_' + linedata[1]) {
          toElement.removeClass("blue")
        }
        // 既存の線が存在するか
        else if (lineresult.length != 0) {
          toElement.removeClass("blue")
        }

        else if (rootcheck(toElement.attr('id'), linedata[0] + '_' + linedata[1], arrays)) {
          toElement.removeClass("blue")
        }

        else if (linedata[0] == toElement.data("column")) {
          //真上に要素があった場合
          if (linedata[1] > $(this).data("row")) {
            toElement.removeClass("blue")
          }
          //ドラッグ元とドラッグ先の間に要素があるかを確認
          for (let row = (linedata[1] + 1); row < $(this).data('row'); row++) {
            if ($('#' + linedata[0] + "_" + row).length != 0) {
              toElement.removeClass("blue")
            }
          }

        }
      })

      var status = false
      var gridcolumn = linedata[0]
      var gridrow = linedata[1] + 1

      do {
        if ($('#' + gridcolumn + "_" + gridrow).length != 0) {
          gridcolumn = gridcolumn + 1
        }
        else {
          status = true
        }
      }
      while (!status)

      $(".grid").append('<div class="drag' + gridcolumn + '_' + gridrow + ' d" id="' + gridcolumn + '_' + gridrow + '" data-column="' + gridcolumn + '" data-row="' + gridrow + '">ドラッグ&ドロップで追加</div>')
      var Xstart = 3 * gridcolumn - 1
      var Xend = 3 * gridcolumn + 1
      var Ystart = 4 * gridrow - 2
      var Yend = 4 * gridrow
      $('.drag' + gridcolumn + '_' + gridrow).css({
        'grid-column': Xstart + '/' + Xend,
        'grid-row': Ystart + '/' + Yend,
        'user-select': "none",
        "z-index": "10",
        "line-height": cellheight * 2 + "px"
      })

    }

  });


  $(document).on('mousemove', '.d', function (event) {

    $(this).addClass("yellow")
  });

  $(document).on('mouseleave', '.d', function (event) {

    $(this).removeClass("yellow")
  });

  // ドラッグ終了時にイベントを解除
  $(document).on('mouseup', function (event) {

    var targetElement = $(event.target);
    // closestメソッドを使用して、特定の親要素を取得
    var eElement = targetElement.closest('.blue');
    //ルートの数(新規の場合は1となる)
    var routecount = $("#route").data("routecount")
    // 既存の要素に線をつなげる場合
    if (eElement.length == 1) {
      linedata[2] = $(eElement[0]).data("column")
      linedata[3] = $(eElement[0]).data("row")
      // インプットタグから線がすでにあるかを確認
      var lineresult = $('input').filter(function () {
        return $(this).data('startcolumn') === linedata[0] && $(this).data('startrow') === linedata[1] && $(this).data('endcolumn') === linedata[2] && $(this).data('endrow') === linedata[3];
      });
      // 線がない場合はインプットタグを作成する
      if (lineresult.length == 0) {
        // つないだ要素が上から下につないでいるとき
        if (linedata[1] < linedata[3]) {
          makeinputline(linedata[0], linedata[1], linedata[2], linedata[3])
          reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)
          $("#" + linedata[0] + '_' + linedata[1]).removeClass("last")
          arrays = searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3], arrays)
          console.log(arrays)
        }
        // 真横もしくは上の要素につないだ時
        // 影響を及ぼす線と要素のinputタグを変更して描画しなおす
        else {
          // 線と要素を変更してarraysの最新情報を返すので、代入する
          arrays = change_line_element(linedata[0], linedata[1], linedata[2], linedata[3], arrays)
          creategrid(cellwidth, cellheight, gapcellwidth, gapcellheight)
          reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)
          reloadelement()
        }

      }



    }
    // 新規の要素を作成する場合
    else if (targetElement.hasClass('d')) {
      linedata[2] = targetElement.data("column")
      linedata[3] = targetElement.data("row")
      // グリッドの最大幅を変更
      modifygrid(linedata[2], linedata[3], cellwidth, cellheight, gapcellwidth, gapcellheight)
      // 線のインプットタグを追加・要素の作成
      makeinputline(linedata[0], linedata[1], linedata[2], linedata[3])
      reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)

      $("#" + linedata[0] + '_' + linedata[1]).removeClass("last")
      $("#" + linedata[2] + '_' + linedata[3]).addClass("last")
      $(".last").each(function () {
        console.log($(this).attr("id"))
      })
      // 要素のinputタグを作成
      makeinputelement(linedata[2], linedata[3], nowelementid)
      // 最新の要素idを1増やす
      nowelementid = nowelementid + 1
      // 要素を再生成
      reloadelement()
      arrays = searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3], arrays)
      console.log(arrays)
      // 新規作成の際にはidがかぶってしまい直後のfocusクラスの付与が正常に動かないため
      // ここで明示的にdクラスの要素を消去する
      $('.d').remove()
      $("#" + linedata[2] + '_' + linedata[3]).addClass("focus")
      focus_right_side_menu(linedata[2], linedata[3])
      person_required_number_reload()
    }
    $('.e').removeClass("blue")
    $('.d').remove()

  });






});
