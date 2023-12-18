$(document).ready(function () {
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
  // 範囲を一つ広げる
  Xcellcount = Xcellcount + 1
  Ycellcount = Ycellcount + 1

  // グリッドのセルの値を指定
  const cellwidth = 150
  const cellheight = 50

  // 空白のセルの値を指定
  const gapcellwidth = 80
  const gapcellheight = 20

  // 画面表示された段階でグリッドを作成
  creategrid()

  reloadline()

  // グリッドを作成する
  function creategrid() {
    $(".grid").css({
      "grid-template-columns": " 20px repeat(" + Xcellcount + ", " + cellwidth + "px " + cellwidth + "px " + gapcellwidth + "px)",
      "grid-template-rows": "40px repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
    })
  }

  // グリッドの範囲変更
  function modifygrid(maxcolumn, maxrow) {
    // 最大幅、高さの変更が必要な場合はcellの数を増やす
    if (maxcolumn + 1 > Xcellcount) {
      Xcellcount = maxcolumn + 1
    }
    if (maxrow + 1 > Ycellcount) {
      Ycellcount = maxrow + 1
    }
    // グリッドのcss変更
    creategrid()
  }





  // 要素を作成する
  $(".element").each(function () {
    var gridcolumn = $(this).data("column")
    var gridrow = $(this).data("row")
    var last = $(this).data("last")
    createelement(gridcolumn, gridrow, last)
  })
  function createelement(gridcolumn, gridrow, last = "none") {

    $(".grid").append('<div class="grid' + gridcolumn + '_' + gridrow + ' e" id="' + gridcolumn + '_' + gridrow + '" data-column="' + gridcolumn + '" data-row="' + gridrow + '"></div>')
    var Xstart = 3 * gridcolumn - 1
    var Xend = 3 * gridcolumn + 1
    var Ystart = 4 * gridrow - 2
    var Yend = 4 * gridrow
    $('.grid' + gridcolumn + '_' + gridrow).css({
      'grid-column': Xstart + '/' + Xend,
      'grid-row': Ystart + '/' + Yend,
      'user-select': "none",
      'z-index': "10"
    })

    if (last == "last") {
      $('.grid' + gridcolumn + '_' + gridrow).addClass("last")
    }

    // 1_1の場合は申請者
    if (gridcolumn == 1 && gridrow == 1) {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        "<div>申請者</div>"
      )
    }
    // それ以外
    else {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class=".grid' + gridcolumn + '_' + gridrow + ' grid_content"><div class="grid_index">1</div><select class="grid_name_container"><option>aa</option></select></div></div>'
      )
      $('.grid' + gridcolumn + '_' + gridrow).append('<div class="add_button grid_add_button" data-coordinates="' + gridcolumn + '_' + gridrow + '">追加</div>　<div class="grid_delete_button" data-coordinates="' + gridcolumn + '_' + gridrow + '">削除</div>')

    }


  }


  function reloadline() {
    $(".l").remove()
    //フロー線を作成する
    $(".line").each(function () {
      var startcolumn = $(this).data("startcolumn")
      var startrow = $(this).data("startrow")
      var endcolumn = $(this).data("endcolumn")
      var endrow = $(this).data("endrow")

      createline(startcolumn, startrow, endcolumn, endrow)
    })
  }


  function createline(startcolumn, startrow, endcolumn, endrow) {
    var Xstart = 3 * startcolumn
    var Xend = 3 * endcolumn
    var Ystart = 4 * startrow
    var Yend = 4 * endrow - 2

    var lineclass = 'line' + startcolumn + '_' + startrow + '_' + endcolumn + '_' + endrow

    //ラインを引く要素の幅
    // 幅は負の数にならないように絶対値をとる
    var linewidth = Math.abs(((endcolumn - startcolumn) * 2 * cellwidth) + ((endcolumn - startcolumn) * 1 * gapcellwidth))
    if (linewidth == 0) {
      linewidth = 10
    }

    //ラインを引く要素の高さ
    var lineheight = (endrow - startrow) * 2 * gapcellheight + (endrow - startrow - 1) * 2 * cellheight

    // widthとheightとviewboxを格納する変数
    var WidthHeightView = 'width="' + linewidth + 'px" height="' + lineheight + 'px" viewBox="0 0 ' + linewidth + ' ' + lineheight + '"'

    $(".grid").append('<svg ' + WidthHeightView + '  class=" ' + lineclass + ' l ">' + createsvgpath(startcolumn, startrow, endcolumn, endrow, linewidth, lineheight) + '</svg>')
    $('.' + lineclass).css({
      'grid-column': Xstart + '/' + Xend,
      'grid-row': Ystart + '/' + Yend,
    })
  }

  function createsvgpath(startcolumn, startrow, endcolumn, endrow, linewidth, lineheight) {
    // 線が左上から右下に引かれる場合
    if (startcolumn < endcolumn) {
      if (endrow - startrow > 1) {
        return '<path d="M 0 0 a ' + (cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 0  ' + (cellwidth) + ' ' + (gapcellheight * 1.5) + ' a ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' 0 0 1 ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' v ' + ((endrow - startrow - 1) * (cellheight * 2) + (endrow - startrow - 2) * (gapcellheight * 2)) + ' a ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' 0 0 0 ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' a ' + (linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 1 ' + (linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
      }
      else {
        return '<path d="M 0 0 a ' + linewidth + ' ' + lineheight + ' 0 0 0  ' + (linewidth / 2) + ' ' + (lineheight / 2) + ' a  ' + linewidth + ' ' + lineheight + ' 0 0 1  ' + (linewidth / 2) + ' ' + (lineheight / 2) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
      }
    }
    else if (startcolumn > endcolumn) {
      // 列が2以上離れているとき
      if (endrow - startrow > 1) {
        return '<path d="M ' + linewidth + ' 0 a ' + -(cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 1  ' + -(cellwidth) + ' ' + (gapcellheight * 1.5) + ' a ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' 0 0 0 ' + -(gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' v ' + ((endrow - startrow - 1) * (cellheight * 2) + (endrow - startrow - 2) * (gapcellheight * 2)) + ' a ' + -(gapcellwidth / 2) + ' ' + (gapcellheight) + ' 0 0 1 ' + -(gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' a ' + (linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 0 ' + -(linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
      }
      else {
        return '<path d="M ' + linewidth + ' 0 a ' + linewidth + ' ' + lineheight + ' 0 0 1  ' + -(linewidth / 2) + ' ' + (lineheight / 2) + ' a  ' + linewidth + ' ' + lineheight + ' 0 0 0  ' + -(linewidth / 2) + ' ' + (lineheight / 2) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
      }
    }
    else {
      return '<path d="M 2.5 0 v ' + lineheight + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
    }
  }

  //ルートの数(新規の場合は1となる)
  var routecount = $("#route").data("routecount")
  var arrays = {
    "1": ["1_1", "1_2"]
  }

  // 要素を検索したのちにルートを作成
  function searchAndUpdateArrays(elementToSearch, elementToAppend) {
    // すべての配列に対して検索と更新を行う
    for (const wholeindex in arrays) {
      const myArray = arrays[wholeindex];
      const lastIndex = myArray.lastIndexOf(elementToSearch);

      // 線の元の要素が最後尾にある場合
      if (lastIndex !== -1 && lastIndex === myArray.length - 1) {
        // 線の先の要素が分岐またはそれ以降の要素が分岐する可能性を考慮する
        // 例えば1_1, 1_2, 1_3, 1_4 と 1_1, 1_2, 1_3, 2_4というルートがあり新たに2_2から1_3という線を結ぼうとしたとき
        // ルートは終端だけを考えると2ルート増えることになる。
        // ここでは1ルートは要素を追加して更新、もう1ルートは新たな配列を追加する必要がある。
        // この最初の要素追加用のルートを示す番号を宣言する(addroutenum)
        let addroutenum = 1;
        // 線の先の要素が既存でなかった場合はtorouteがfalse
        let toroute = false;

        for (const index in arrays) {
          // 線の先の要素が配列の中の何番目かを格納、なければnumは-1となる
          const num = arrays[index].lastIndexOf(elementToAppend);
          // インデックスが存在し、線の先が最後の要素でない場合、対象要素以降の部分配列を返す
          if (num !== -1 && num < arrays[index].length - 1) {
            const afterArray = arrays[index].slice(num + 1);
            const newArray = myArray.slice();
            newArray.push(elementToAppend);
            newArray.push(...afterArray);
            // addroutenumが1の場合は既存のルートに後の要素を結合させる
            if (addroutenum === 1) {
              arrays[wholeindex] = newArray;
              addroutenum = addroutenum + 1;
            }
            // addroutenumが2以上の場合は新たなルートとして配列を追加する
            else {
              routecount = routecount + 1;
              arrays[routecount] = newArray;
            }
            toroute = true;
          }
          // インデックスが存在し、最後の要素である場合
          else if (num !== -1 && num === arrays[index].length - 1) {
            const newArray = myArray.slice();
            newArray.push(elementToAppend);
            arrays[wholeindex] = newArray;
            toroute = true;
          }
        }
        // 線の先の要素が新規だった場合
        if (!toroute) {
          const newArray = myArray.slice();
          newArray.push(elementToAppend);
          arrays[wholeindex] = newArray;
        }
      }
      // 線の元の要素が最後尾以外にある場合、新しい配列を作成
      else if (lastIndex !== -1 && lastIndex < myArray.length - 1) {


        for (const index in arrays) {
          const newArray = myArray.slice(0, lastIndex + 1);
          const num = arrays[index].lastIndexOf(elementToAppend);

          // 要素が存在し、線の先が最後の要素でない場合、対象要素以降の部分配列を返す
          if (num !== -1 && num < arrays[index].length - 1) {
            const afterArray = arrays[index].slice(num + 1);
            newArray.push(elementToAppend);
            newArray.push(...afterArray);
          }
          // 要素が存在し、最後の要素である場合
          else if (num !== -1 && num === arrays[index].length - 1) {
            newArray.push(elementToAppend);
          }
          // 線の先の要素が新規だった場合
          else {
            newArray.push(elementToAppend);
          }


          // 例えば1_1, 2_2, 2_3と1_1, 2_2, 3_3というルートがあり新たに2_2から4_3という線を結ぼうとしたとき
          // 2_2から派生するルートとして1_1, 2_2, 4_3というルートが二つできることになってしまう。
          // これを阻止するために新規にルートを追加するときに既存のルートがないかをチェックする必要がある
          if (!arraysAreEqual(newArray)) {
            routecount = routecount + 1;
            arrays[routecount] = newArray;
          }
        }
      }
    }
  }

  // 配列を比較して確認する中身が一致していたらtrueを返す
  function arraysAreEqual(newArray) {

    // arraysオブジェクトの中身を見る
    // foreachを使わないのは途中でbreakがあるものにも対応できるようにするため
    for (const index in arrays) {
      const searchArray = arrays[index];

      if (searchArray.length === newArray.length) {
        let statusCheck = true;
        for (let i = 0; i < searchArray.length; i++) {
          if (searchArray[i] !== newArray[i]) {
            console.log(searchArray[i], newArray[i]);
            statusCheck = false;
            break;
          }
        }

        if (statusCheck) {
          return true;
        }
      }
    }
    return false;
  }

  // 要素含まれているルートの要素を配列に格納して返す関数
  // arraysには要素をルートのすべての配列が格納されたオブジェクト
  // targetValueには検索する要素
  function findAndStoreElements(arrays, targetValue) {
    const resultArray = [];

    for (const key in arrays) {
      const currentArray = arrays[key];

      if (currentArray.includes(targetValue)) {
        arrays[key].forEach((element) => {
          // resultArrayに重複要素がないかを確認
          if (!resultArray.includes(element)) {
            resultArray.push(element);
          }
        });
      }
    }

    return resultArray;
  }

  function rootcheck(fromElement,toElement){
    for (const key in arrays) {
      if (arrays[key].includes(fromElement) && arrays[key].includes(toElement)){
        return true
      }
    }
    return false
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
        
        else if (rootcheck(toElement.attr('id'),linedata[0] + '_' +linedata[1])){
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

    // 既存の要素に線をつなげる場合
    if (eElement.length == 1) {
      linedata[2] = $(eElement[0]).data("column")
      linedata[3] = $(eElement[0]).data("row")
      // インプットタグから線がすでにあるかを確認
      var lineresult = $('input').filter(function () {
        return $(this).data('startcolumn') === linedata[0] && $(this).data('startrow') === linedata[1] && $(this).data('endcolumn') === linedata[2] && $(this).data('endrow') === linedata[3];
      });
      // 線がない場合はインプットタグを作成して
      if (lineresult.length == 0) {
        $(".element_input").append(
          '<input type="hidden" class="line" data-startcolumn="' + linedata[0] + '" data-startrow="' + linedata[1] + '" data-endcolumn="' + linedata[2] + '" data-endrow="' + linedata[3] + '">'

        )
        reloadline()
        $("#" + linedata[0] + '_' + linedata[1]).removeClass("last")
        searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3])
        console.log(arrays)
      }



    }
    // 新規の要素を作成する場合
    else if (targetElement.hasClass('d')) {
      linedata[2] = targetElement.data("column")
      linedata[3] = targetElement.data("row")
      modifygrid(linedata[2], linedata[3])
      $(".element_input").append(
        '<input type="hidden" class="line" data-startcolumn="' + linedata[0] + '" data-startrow="' + linedata[1] + '" data-endcolumn="' + linedata[2] + '" data-endrow="' + linedata[3] + '">'
      )
      reloadline()
      $("#" + linedata[0] + '_' + linedata[1]).removeClass("last")
      createelement(linedata[2], linedata[3], "last")
      searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3])
      console.log(arrays)
    }
    $('.e').removeClass("blue")
    $('.d').remove()

  });



});
