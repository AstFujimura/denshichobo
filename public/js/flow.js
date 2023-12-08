$(document).ready(function () {
  var Xcellcount = 3
  var Ycellcount = 6
  // グリッドのセルの値を指定
  const cellwidth = 80
  const cellheight = 50

  // 空白のセルの値を指定
  const gapcellwidth = 80
  const gapcellheight = 90



  $(".grid").css({
    "grid-template-columns": "repeat(" + (Xcellcount * 3) + ", " + cellwidth + "px)",
    "grid-template-rows": "repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
  })
  // 要素を作成する
  $(".element").each(function () {
    var gridcolumn = $(this).data("column")
    var gridrow = $(this).data("row")
    $(".grid").append('<div class="grid' + gridcolumn + '_' + gridrow + ' e"></div>')
    var Xstart = 3 * gridcolumn - 2
    var Xend = 3 * gridcolumn
    var Ystart = 4 * gridrow - 3
    var Yend = 4 * gridrow - 1

    $('.grid' + gridcolumn + '_' + gridrow).css({
      'grid-column': Xstart + '/' + Xend,
      'grid-row': Ystart + '/' + Yend,
    })
  })


  //フロー線を作成する
  $(".line").each(function () {
    var startcolumn = $(this).data("startcolumn")
    var startrow = $(this).data("startrow")
    var endcolumn = $(this).data("endcolumn")
    var endrow = $(this).data("endrow")

    createline(startcolumn, startrow, endcolumn, endrow)
  })

  function createline(startcolumn, startrow, endcolumn, endrow) {
    var Xstart = 3 * startcolumn - 1
    var Xend = 3 * endcolumn - 1
    var Ystart = 4 * startrow - 1
    var Yend = 4 * endrow - 3

    var lineclass = 'line' + startcolumn + '_' + startrow + '_' + endcolumn + '_' + endrow

    //ラインを引く要素の幅
    // 幅は負の数にならないように絶対値をとる
    var linewidth = Math.abs((endcolumn - startcolumn) * 3 * gapcellwidth)
    if (linewidth == 0){
      linewidth = 10
    }

    console.log(linewidth)
    //ラインを引く要素の高さ
    var lineheight = (endrow - startrow) * 2 * gapcellheight
    console.log(lineheight)

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
      return '<path d="M 0 0 a ' + linewidth + ' ' + lineheight + ' 0 0 0  ' + (linewidth / 2) + ' ' + (lineheight / 2) + ' a  ' + linewidth + ' ' + lineheight + ' 0 0 1  ' + (linewidth / 2) + ' ' + (lineheight / 2) + '" fill="none" stroke="black" stroke-width="2" />'
    }
    else if (startcolumn > endcolumn) {
      return '<path d="M ' + linewidth + ' 0 a ' + linewidth + ' ' + lineheight + ' 0 0 1  ' + -(linewidth / 2) + ' ' + (lineheight / 2) + ' a  ' + linewidth + ' ' + lineheight + ' 0 0 0  ' + -(linewidth / 2) + ' ' + (lineheight / 2) + '" fill="none" stroke="black" stroke-width="2" />'
    }
    else {
      return '<path d="M 0 0 v '+ lineheight + '" fill="none" stroke="black" stroke-width="5" />'
    }
  }

});
