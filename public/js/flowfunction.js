// グリッドを作成する
function creategrid(cellwidth, cellheight, gapcellwidth, gapcellheight) {
  var Xcellcount = $("#maxgrid").data("maxcolumn")
  var Ycellcount = $("#maxgrid").data("maxrow")
  $(".grid").css({
    "grid-template-columns": " 20px repeat(" + Xcellcount + ", " + cellwidth + "px " + cellwidth + "px " + gapcellwidth + "px)",
    "grid-template-rows": "40px repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
  })
}


// グリッド行の範囲変更
function modifygrid(maxcolumn, maxrow,cellwidth, cellheight, gapcellwidth, gapcellheight) {
  var Xcellcount = $("#maxgrid").data("maxcolumn")
  var Ycellcount = $("#maxgrid").data("maxrow")
  // 最大幅、高さの変更が必要な場合はcellの数を増やす
  if (maxcolumn + 1 > Xcellcount) {
    Xcellcount = maxcolumn + 1
    $("#maxgrid").data("maxcolumn",Xcellcount)
  }
  if (maxrow + 1 > Ycellcount) {
    Ycellcount = maxrow + 1
    $("#maxgrid").data("maxrow",Ycellcount)
  }
  $(".grid").css({
    "grid-template-columns": " 20px repeat(" + Xcellcount + ", " + cellwidth + "px " + cellwidth + "px " + gapcellwidth + "px)",
    "grid-template-rows": "40px repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
  })
}


function makeinputelement(gridcolumn, gridrow, last = "none") {
  $(".element_input").append('<input type="hidden" class="element" data-column="' + gridcolumn + '" data-row="' + gridrow + '" data-last="' + last + '">')
}

function reloadelement() {
  $(".e").remove()
  //フロー線を作成する
  $(".element").each(function () {
    var gridcolumn = $(this).data("column")
    var gridrow = $(this).data("row")
    var last = $(this).data("last")

    createelement(gridcolumn, gridrow, last)
  })
}




// 要素を作成する
function createelement(gridcolumn, gridrow, last = "none", status = "add") {


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



// inputタグの線の情報を記述、変更時にはlineのidを引数に入れる
// 削除時にはchangeに"delete"を入れる（デフォルトはchange）
function makeinputline(startcolumn, startrow, endcolumn, endrow, id = 0, change = "change") {
  $(".element_input").append(
    '<input type="hidden" class="line" data-startcolumn="' + startcolumn + '" data-startrow="' + startrow + '" data-endcolumn="' + endcolumn + '" data-endrow="' + endrow + '">'

  )
}


function reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight) {
  $(".l").remove()
  //フロー線を作成する
  $(".line").each(function () {
    var startcolumn = $(this).data("startcolumn")
    var startrow = $(this).data("startrow")
    var endcolumn = $(this).data("endcolumn")
    var endrow = $(this).data("endrow")

    createline(startcolumn, startrow, endcolumn, endrow, cellwidth, cellheight, gapcellwidth, gapcellheight)
  })
}


function createline(startcolumn, startrow, endcolumn, endrow, cellwidth, cellheight, gapcellwidth, gapcellheight) {

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

  $(".grid").append('<svg ' + WidthHeightView + '  class=" ' + lineclass + ' l ">' + createsvgpath(startcolumn, startrow, endcolumn, endrow, linewidth, lineheight, cellwidth, cellheight, gapcellwidth, gapcellheight) + '</svg>')
  $('.' + lineclass).css({
    'grid-column': Xstart + '/' + Xend,
    'grid-row': Ystart + '/' + Yend,
  })

}

function createsvgpath(startcolumn, startrow, endcolumn, endrow, linewidth, lineheight, cellwidth, cellheight, gapcellwidth, gapcellheight) {
  // 線が左上から右下に引かれる場合
  if (startcolumn < endcolumn) {
    // 列が2以上離れているとき
    if (endrow - startrow > 1) {
      return '<path d="M 0 0 a ' + (cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 0  ' + (cellwidth) + ' ' + (gapcellheight * 1.5) + ' a ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' 0 0 1 ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' v ' + ((endrow - startrow - 1) * (cellheight * 2) + (endrow - startrow - 2) * (gapcellheight * 2)) + ' a ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' 0 0 0 ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' a ' + (linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 1 ' + (linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
    }
    else {
      return '<path d="M 0 0 a ' + linewidth + ' ' + lineheight + ' 0 0 0  ' + (linewidth / 2) + ' ' + (lineheight / 2) + ' a  ' + linewidth + ' ' + lineheight + ' 0 0 1  ' + (linewidth / 2) + ' ' + (lineheight / 2) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
    }
  }
  // 線が右上から左上に引かれる場合
  else if (startcolumn > endcolumn) {
    // 列が2以上離れているとき
    if (endrow - startrow > 1) {
      return '<path d="M ' + linewidth + ' 0 a ' + -(cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 1  ' + -(cellwidth) + ' ' + (gapcellheight * 1.5) + ' a ' + (gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' 0 0 0 ' + -(gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' v ' + ((endrow - startrow - 1) * (cellheight * 2) + (endrow - startrow - 2) * (gapcellheight * 2)) + ' a ' + -(gapcellwidth / 2) + ' ' + (gapcellheight) + ' 0 0 1 ' + -(gapcellwidth / 2) + ' ' + (gapcellheight / 2) + ' a ' + (linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + ' 0 0 0 ' + -(linewidth - gapcellwidth - cellwidth) + ' ' + (gapcellheight * 1.5) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
    }
    else {
      return '<path d="M ' + linewidth + ' 0 a ' + linewidth + ' ' + lineheight + ' 0 0 1  ' + -(linewidth / 2) + ' ' + (lineheight / 2) + ' a  ' + linewidth + ' ' + lineheight + ' 0 0 0  ' + -(linewidth / 2) + ' ' + (lineheight / 2) + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
    }
  }
  // 真下に引かれる場合
  else {
    return '<path d="M 2.5 0 v ' + lineheight + '" fill="none" stroke="#a3a3a3" stroke-width="2" />'
  }
}







// 要素を検索したのちにルートを作成
function searchAndUpdateArrays(elementToSearch, elementToAppend, arrays) {
  var routecount = $("#route").data("routecount")
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
            $("#route").data("routecount", routecount)
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
        if (!arraysAreEqual(newArray, arrays)) {
          routecount = routecount + 1;
          arrays[routecount] = newArray;
          $("#route").data("routecount", routecount)
        }
      }
    }
  }

  return arrays
}

// 配列を比較して確認する中身が一致していたらtrueを返す
function arraysAreEqual(newArray, arrays) {

  // arraysオブジェクトの中身を見る
  // foreachを使わないのは途中でbreakがあるものにも対応できるようにするため
  for (const index in arrays) {
    const searchArray = arrays[index];

    if (searchArray.length === newArray.length) {
      let statusCheck = true;
      for (let i = 0; i < searchArray.length; i++) {
        if (searchArray[i] !== newArray[i]) {
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

// 線を結ぶときに元と先が子孫の関係になっていないかを確認
// もしなっていればtrueを返す
function rootcheck(fromElement, toElement, arrays) {
  for (const key in arrays) {
    if (arrays[key].includes(fromElement) && arrays[key].includes(toElement)) {
      return true
    }
  }
  return false
}

// 線を真横もしくは上の要素につないだ時要素を下にずらす。2列以上変更が必要な場合はこの関数を再帰的に呼び出す
// 関数を再帰的に呼び出すためデフォルトのstatusをinitialとして２回目以降はstatusをchangeにする
function change_line_element(startcolumn, startrow, endcolumn, endrow,arrays,status = "initial") {
  // 下に移動する分の値
  const changerow = startrow - endrow + 1
  // 現時点でのY方向のグリッドの最大値を取得
  var Ycellcount = $("#maxgrid").data("maxrow")
  // 移動する分Y方向の最大値を追加する
  $("#maxgrid").data("maxrow",Ycellcount+changerow)

  console.log(changerow)
  // 一回目は線を新規で作成する。この場合、線は真横もしくは上方向になるが後でずらすので問題なし
  if (status == "initial"){
    makeinputline(startcolumn, startrow, endcolumn, endrow) 
    searchAndUpdateArrays(startcolumn +"_"+startrow, endcolumn +"_"+endrow, arrays)
  }

  // 要素をそれぞれチェック
  $(".element").each(function () {
    var nowcolumn = $(this).data("column")
    var nowrow = $(this).data("row")
    var column_row = nowcolumn +"_"+ nowrow
    // 要素が線の先のカラムに該当し、線の先の列よりも下、もしくは横にある場合changerowだけ下に移動する
    if (nowcolumn == endcolumn && nowrow >= endrow) {
      $(this).data("row", nowrow + changerow)

      for (const index in arrays) {
        var indexelement = arrays[index].lastIndexOf(column_row)
        if (indexelement !== -1){
          arrays[index].splice(indexelement,1,nowcolumn + "_" + (nowrow + changerow))
        }
      }
    }
  })
  // 線をそれぞれチェック
  $(".line").each(function () {
    // 現在の行、列を取得
    var nowstartcolumn = $(this).data("startcolumn")
    var nowstartrow = $(this).data("startrow")
    var nowendcolumn = $(this).data("endcolumn")
    var nowendrow = $(this).data("endrow")
    if (nowstartcolumn == endcolumn && nowstartrow >= endrow) {
      $(this).data("startrow", nowstartrow + changerow)
    }
    if (nowendcolumn == endcolumn && nowendrow >= endrow) {
      $(this).data("endrow", nowendrow + changerow)
    }
  })
// inputタグから、要素や線をずらしたことによって線が真横、もしくは上方向に延びていないかをチェックする
  var lineresult = $('input').filter(function () {
    return $(this).data('startrow') >= $(this).data('endrow');
  }).first();
  // 該当した場合は再帰的にこの関数をもう一度呼び出す。第5引数はinitialではなくchange
  if (lineresult.length !== 0){
    change_line_element(lineresult.data('startcolumn'), lineresult.data('startrow'), lineresult.data('endcolumn'), lineresult.data('endrow'),arrays,status = "change")
  }
  return arrays
}






