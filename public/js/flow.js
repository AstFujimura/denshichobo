$(document).ready(function () {

$(".accordion_menu_title").on("click",function(){
  console.log(";lakd")
  $(this).toggleClass("accordion_menu_title_open")
  $(this).parent().find(".accordion_content").toggleClass("accordion_content_open")
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

  $("#maxgrid").data("maxcolumn",Xcellcount + 1)
  $("#maxgrid").data("maxrow",Ycellcount + 1)
  // グリッドのセルの値を指定
  const cellwidth = 150
  const cellheight = 50

  // 空白のセルの値を指定
  const gapcellwidth = 80
  const gapcellheight = 20

  // 画面表示された段階でグリッドを作成
  creategrid(cellwidth, cellheight, gapcellwidth, gapcellheight)

  reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)

  reloadelement()


  var arrays = {
    "1": ["1_1", "1_2"]
  }

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
          arrays = searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3], arrays, routecount)
          console.log(arrays)
        }
        // 真横もしくは上の要素につないだ時
        // 影響を及ぼす線と要素のinputタグを変更して描画しなおす
        else {
          change_line_element(linedata[0], linedata[1], linedata[2], linedata[3])
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
      modifygrid(linedata[2],linedata[3],cellwidth, cellheight, gapcellwidth, gapcellheight)
      // 線のインプットタグを追加・要素の作成
      makeinputline(linedata[0], linedata[1], linedata[2], linedata[3])
      reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)

      $("#" + linedata[0] + '_' + linedata[1]).removeClass("last")
      makeinputelement(linedata[2], linedata[3])
      reloadelement()
      arrays = searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3], arrays, routecount)
      console.log(arrays)
    }
    $('.e').removeClass("blue")
    $('.d').remove()

  });



});
