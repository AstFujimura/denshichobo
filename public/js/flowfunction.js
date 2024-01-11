// 個人アイコンを返す
function person_icon() {
  return '<svg class="flow_img" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 432 410" xml:space="preserve" overflow="hidden"><g transform="translate(-828 -707)"><path d="M828 912C828 798.782 924.707 707 1044 707 1163.29 707 1260 798.782 1260 912 1260 1025.22 1163.29 1117 1044 1117 924.707 1117 828 1025.22 828 912Z" fill="#0F9ED5" fill-rule="evenodd"/><g><g><g><path d="M1099 840.125C1099 870.501 1074.38 895.125 1044 895.125 1013.62 895.125 989 870.501 989 840.125 989 809.749 1013.62 785.125 1044 785.125 1074.38 785.125 1099 809.749 1099 840.125Z" fill="#FFFFFF"/><path d="M1154 1018.88 1154 963.875C1154 955.625 1149.88 947.375 1143 941.875 1127.88 929.5 1108.62 921.25 1089.38 915.75 1075.62 911.625 1060.5 908.875 1044 908.875 1028.88 908.875 1013.75 911.625 998.625 915.75 979.375 921.25 960.125 930.875 945 941.875 938.125 947.375 934 955.625 934 963.875L934 1018.88 1154 1018.88Z" fill="#FFFFFF"/></g></g></g></g></svg>'
}
// グループアイコンを返す
function group_icon() {
  return '<svg class="flow_img" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 432 410" xml:space="preserve" overflow="hidden"><defs><clipPath id="clip0"><rect x="1672" y="723" width="432" height="410"/></clipPath></defs><g clip-path="url(#clip0)" transform="translate(-1672 -723)"><path d="M1672 928C1672 814.781 1768.71 723 1888 723 2007.29 723 2104 814.781 2104 928 2104 1041.22 2007.29 1133 1888 1133 1768.71 1133 1672 1041.22 1672 928Z" fill="#F2AA84" fill-rule="evenodd"/><g><g><g><path d="M1853.62 980.75 1922.37 980.75 1922.37 1028.88 1853.62 1028.88Z" fill="#FFFFFF"/><path d="M1853.62 795.125 1922.37 795.125 1922.37 843.25 1853.62 843.25Z" fill="#FFFFFF"/><path d="M1750.5 980.75 1819.25 980.75 1819.25 1028.88 1750.5 1028.88Z" fill="#FFFFFF"/><path d="M1956.75 980.75 2025.5 980.75 2025.5 1028.88 1956.75 1028.88Z" fill="#FFFFFF"/><path d="M1894.87 905.125 1894.87 857 1881.12 857 1881.12 905.125 1778 905.125 1778 967 1791.75 967 1791.75 918.875 1881.12 918.875 1881.12 967 1894.87 967 1894.87 918.875 1984.25 918.875 1984.25 967 1998 967 1998 905.125Z" fill="#FFFFFF"/></g></g></g></g></svg>'
}
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
function modifygrid(maxcolumn, maxrow, cellwidth, cellheight, gapcellwidth, gapcellheight) {
  var Xcellcount = $("#maxgrid").data("maxcolumn")
  var Ycellcount = $("#maxgrid").data("maxrow")
  // 最大幅、高さの変更が必要な場合はcellの数を増やす
  if (maxcolumn + 1 > Xcellcount) {
    Xcellcount = maxcolumn + 1
    $("#maxgrid").data("maxcolumn", Xcellcount)
    $("#maxgrid").attr("data-maxcolumn", Xcellcount)
  }
  if (maxrow + 1 > Ycellcount) {
    Ycellcount = maxrow + 1
    $("#maxgrid").data("maxrow", Ycellcount)
    $("#maxgrid").attr("data-maxrow", Ycellcount)
  }
  $(".grid").css({
    "grid-template-columns": " 20px repeat(" + Xcellcount + ", " + cellwidth + "px " + cellwidth + "px " + gapcellwidth + "px)",
    "grid-template-rows": "40px repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
  })
}

function nowelementcount() {
  var count = $(".e").length
  return count

}

// person_textからpersonのinputの情報の更新を行う。
// focus情報をもとに選択中の要素だけの更新も行う
function change_person() {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")

  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + focusid)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のauthorizer_container(入れ物)を取得
  var element = $("#" + column + "_" + row).find(".authorizer_container")
  // 個人のインプットタグを一旦削除する
  // 要素の個人の情報を一旦削除する
  $('input[class="person"][data-id="' + focusid + '"]').remove()
  element.html("")

  $(".person_text").each(function () {
    if ($(this).val() != "") {
      $(".element_input").append('<input type="hidden" class="person" data-id="' + focusid + '" data-person_name="' + $(this).val() + '">')
      element.append('<div class="person_element">' + $(this).val() + '</div>')
    }
  })
  if (($('.person[data-id="' + focusid + '"]').length == 0)) {
    element.append('<div class="none_setting">未設定</div>')
    element.parent().find(".element_authorizer_number").text("")
  }
  else {
    person_number_change(focusid)
  }
}

// 引数にいれたidの要素だけinputの情報をもとに更新する
function personreload(id) {
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + id)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のauthorizer_container(入れ物)を取得
  var element = $("#" + column + "_" + row).find(".authorizer_container")
  // 要素の個人の情報を一旦削除する
  element.html("")
  $('.person[data-id="' + id + '"]').each(function () {
    element.append('<div class="person_element">' + $(this).data('person_name') + '</div>')
  })
  // inputの数が0であり申請者に当たらない場合、未設定の文字をいれる
  if ($('.person[data-id="' + id + '"]').length == 0 && id != 10000) {
    element.append('<div class="none_setting">未設定</div>')
  }
  // 申請者の場合
  else if (id == 10000) {
    element.append('<div class="applicant">申請者</div>')
  }
  // inputの数が1以上ある場合
  else {
    person_number_change(id)
  }
}

function person_number_change(id) {
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + id)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のauthorizer_container(入れ物)を取得
  var element = $("#" + column + "_" + row).find(".authorizer_container")
  var authorizer_number = $('.person[data-id="' + id + '"]').length
  if (inputelement.data("authorizer") == "person") {
    if (authorizer_number == 0) {
      element.parent().find(".element_authorizer_number").text("")
    }
    else {
      element.parent().find(".element_authorizer_number").text(authorizer_number + "人")
    }
  }


  $('.parameter').text(authorizer_number)
}
// group_selectからgroupのinputの情報の更新を行う。
// focus情報をもとに選択中の要素だけの更新も行う
function change_group() {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")

  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + focusid)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のauthorizer_container(入れ物)を取得
  var element = $("#" + column + "_" + row).find(".authorizer_container")
  // 個人のインプットタグを一旦削除する
  // 要素の個人の情報を一旦削除する
  $('input[class="group"][data-id="' + focusid + '"]').remove()
  element.html("")

  var group = $('.group_select').val()
  $(".element_input").append('<input type="hidden" class="group" data-id="' + focusid + '" data-group_name="' + group + '">')
  element.append('<div class="group_element">' + group + '</div>')


  if (($('.group[data-id="' + focusid + '"]').length == 0)) {
    // element.append('<div class="none_setting">未設定</div>')
    // element.parent().find(".element_authorizer_number").text("")
  }
  person_number_change(focusid)
}
function group_select(focusid) {
  var group = $('input[class="group"][data-id="' + focusid + '"]').data('group_name')
  $('.group_select').find('option').first().prop('selected', true)
  $('.group_select').find('option').each(function () {
    if ($(this).val() === group) {
      $(this).prop('selected', true)
    }
  })
}
// 引数にいれたidの要素だけinputの情報をもとに更新する
function groupreload(id) {
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + id)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のauthorizer_container(入れ物)を取得
  var element = $("#" + column + "_" + row).find(".authorizer_container")
  // 要素の個人の情報を一旦削除する
  element.html("")


  // inputの数が0であり申請者に当たらない場合、セレクトボックスの初期値の値をinputに入れる
  if ($('.group[data-id="' + id + '"]').length == 0 && id != 10000) {
    var group = $('.group_select').val()
    $(".element_input").append('<input type="hidden" class="group" data-id="' + id + '" data-group_name="' + group + '">')
    element.append('<div class="group_element">' + group + '</div>')

  }
  // 申請者の場合
  else if (id == 10000) {
    element.append('<div class="applicant">申請者</div>')
  }
  // inputの数が1以上ある場合
  else {
    element.append('<div class="group_element">' + $('.group[data-id="' + id + '"]').data('group_name') + '</div>')
    person_number_change(id)
  }
}

function change_authorizer(person_group) {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")

  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + focusid)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のflow_img_box(アイコンの入れ物)を取得
  var flow_img_box = $("#" + column + "_" + row).find(".flow_img_box")
  if (person_group == "person") {
    flow_img_box.html(person_icon() + '<div class="element_authorizer_number"></div>')
    personreload(focusid)
  }
  else if (person_group == "group") {
    flow_img_box.html(group_icon() + '<div class="element_authorizer_number"></div>')
    group_select(focusid)
    groupreload(focusid)

  }
}
// 承認者_個人の承認人数のラジオボタンが切り替わったとき、また条件指定の人数が変更されたとき
// inputの承認人数を更新する
function change_person_required_number(){
    // 現在選択中の要素のidを取得
    var focusid = $("#focus").data("id")
    // 要素のインプットタグを取得
    // そのcolumnとrowも取得
    var inputelement = $("#" + focusid)
    var column = inputelement.data("column")
    var row = inputelement.data("row")
    if ($('#authorizer_condition1').prop('checked')){
      inputelement.data("person_required_number","all")
      inputelement.attr("data-person_required_number","all")
    }
    // 条件指定にチェックがあった場合は指定人数に値があるかないかで条件分岐
    else if ($('#authorizer_condition2').prop('checked')){
      // 承認者の最大人数を取得(現在選択中の要素の承認者のテキストボックスの数と同じ)
      var max_number = $(".parameter").text()
      // 初期値がないもしくは指定人数が0人の場合
      // 最大人数の値をinputに更新、さらにテキストボックスの値に追加
      if (!$('#person_required_number').val() || $('#person_required_number').val() == 0){
        inputelement.data("person_required_number",max_number)
        inputelement.attr("data-person_required_number",max_number)
        $('#person_required_number').val(max_number)
      }
      // 承認者の人数がテキストボックスで値が入っている場合(0人を除く)
      else {

        // 人数がマイナスになる場合
        if ($('#person_required_number').val() < 0) {
          $('#person_required_number').val(0)
        }
        // 人数が承認者のリストの数を超えた時
        else if ($('#person_required_number').val() > $(".parameter").text()) {
          $('#person_required_number').val($(".parameter").text())
        }
        inputelement.data("person_required_number",$('#person_required_number').val())
        inputelement.attr("data-person_required_number",$('#person_required_number').val())

      }

    }
}
// 要素のフォーカスが切り替わったときなどラジオボタンの値を変更する必要があるとき
// チェックボックスと条件指定の場合はそのテキストボックスを変更する
function person_required_number_reload() {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + focusid)
  var column = inputelement.data("column")
  var row = inputelement.data("row")

  

  if (inputelement.data("person_required_number") == "all"){
    $('#authorizer_condition1').click()
  }
  else {
    $('#authorizer_condition2').click()
    $('#person_required_number').val(inputelement.data("person_required_number"))
  }
}


function change_group_select_method(){
  
}




function makeinputelement(gridcolumn, gridrow, nowelementid, last = "none") {
  $(".element_input").append('<input type="hidden" id="' + nowelementid + '" class="element" data-column="' + gridcolumn + '" data-row="' + gridrow + '" data-last="' + last + '" data-authorizer = "person" data-person_required_number="all" data-select_method ="nolimit" >')
}

function reloadelement() {
  $(".e").remove()

  $(".element").each(function () {
    var gridcolumn = $(this).data("column")
    var gridrow = $(this).data("row")
    var last = $(this).data("last")
    console.log(gridcolumn, gridrow)
    createelement(gridcolumn, gridrow, last)
    if ($(this).data('authorizer') == "person") {
      personreload($(this).attr("id"))
    }
    else if ($(this).data('authorizer') == "group") {
      groupreload($(this).attr("id"))
    }
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
      "<div class='authorizer_container'><div class='applicant'>申請者</div></div>"
    )
  }
  // それ以外
  else {
    console.log($(".element"))
    if ($('input[data-column = "' + gridcolumn + '"][data-row = "' + gridrow + '"]').data('authorizer') == 'person') {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="flow_img_box">' + person_icon() + '<div class="element_authorizer_number"></div></div><div class="authorizer_container"><div class="none_setting">未設定</div></div>'
      )
      console.log('個人')
    }
    else if ($('input[data-column = "' + gridcolumn + '"][data-row = "' + gridrow + '"]').data('authorizer') == 'group') {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="flow_img_box">' + group_icon() + '<div class="element_authorizer_number"></div></div><div class="authorizer_container"><div class="none_setting">未設定</div></div>'
      )
      console.log('グループ')
    }

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
function change_line_element(startcolumn, startrow, endcolumn, endrow, arrays, status = "initial") {
  // 下に移動する分の値
  const changerow = startrow - endrow + 1
  // 現時点でのY方向のグリッドの最大値を取得
  var Ycellcount = $("#maxgrid").data("maxrow")
  // 移動する分Y方向の最大値を追加する
  $("#maxgrid").data("maxrow", Ycellcount + changerow)
  $("#maxgrid").attr("data-maxrow", Ycellcount + changerow)

  console.log(changerow)
  // 一回目は線を新規で作成する。この場合、線は真横もしくは上方向になるが後でずらすので問題なし
  if (status == "initial") {
    makeinputline(startcolumn, startrow, endcolumn, endrow)
    searchAndUpdateArrays(startcolumn + "_" + startrow, endcolumn + "_" + endrow, arrays)
  }

  // 要素をそれぞれチェック
  $(".element").each(function () {
    var nowcolumn = $(this).data("column")
    var nowrow = $(this).data("row")
    var column_row = nowcolumn + "_" + nowrow
    // 要素が線の先のカラムに該当し、線の先の列よりも下、もしくは横にある場合changerowだけ下に移動する
    if (nowcolumn == endcolumn && nowrow >= endrow) {
      // 要素のinputタグを変更
      $(this).data("row", nowrow + changerow)
      $(this).attr("data-row", nowrow + changerow)


      for (const index in arrays) {
        var indexelement = arrays[index].lastIndexOf(column_row)
        if (indexelement !== -1) {
          arrays[index].splice(indexelement, 1, nowcolumn + "_" + (nowrow + changerow))
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
      $(this).attr("data-startrow", nowstartrow + changerow)
    }
    if (nowendcolumn == endcolumn && nowendrow >= endrow) {
      $(this).data("endrow", nowendrow + changerow)
      $(this).attr("data-endrow", nowendrow + changerow)
    }
  })
  // inputタグから、要素や線をずらしたことによって線が真横、もしくは上方向に延びていないかをチェックする
  var lineresult = $('input').filter(function () {
    return $(this).data('startrow') >= $(this).data('endrow');
  }).first();
  // 該当した場合は再帰的にこの関数をもう一度呼び出す。第5引数はinitialではなくchange
  if (lineresult.length !== 0) {
    change_line_element(lineresult.data('startcolumn'), lineresult.data('startrow'), lineresult.data('endcolumn'), lineresult.data('endrow'), arrays, status = "change")
  }
  return arrays
}

// フォーカスを当てる
function focus_right_side_menu(column, row) {
  // 右側メニューを表示
  $('.right_side_menu').addClass("right_side_menu_open")
  var focus = $('input[class="element"]').filter(function () {
    return (($(this).data('column') == column) && ($(this).data('row') == row));
  }).first();
  var focusid = focus.attr("id")
  // 現在選択情報のインプットタグを更新
  $('#focus').data("id", focusid)
  $('#focus').attr("data-id", focusid)
  // フォーカスしている要素が個人の場合はラジオボタン「個人」をクリック
  if ($('#' + focusid).data('authorizer') == "person") {
    $("#authorizer1").click()
    // 個人のテキストボックスを削除
    $('.person_box').remove()
    // 該当のidに個人のリストが一つもない場合は空のテキストボックスを一つ追加
    if ($('input[class="person"][data-id="' + focusid + '"]').length == 0) {
      $(".person_content").append('<div class="person_box"><input type="text" class="person_text"><div class="batsu_button">×</div></div>')
    }
    // インプットタグを参照しテキストボックスを追加
    $('input[class="person"][data-id="' + focusid + '"]').each(function () {
      $(".person_content").append('<div class="person_box"><input type="text" class="person_text" value="' + $(this).data("person_name") + '"><div class="batsu_button">×</div></div>')
    })
    person_number_change(focusid)
  }
  // フォーカスしている要素が個人の場合はラジオボタン「グループ」をクリック
  else if ($('#' + focusid).data('authorizer') == "group") {
    $("#authorizer2").click()
    group_select(focusid)
  }



}



