// 個人アイコンを返す
function person_icon() {
  return '<svg class="flow_img person_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 432 410" xml:space="preserve" overflow="hidden"><g transform="translate(-828 -707)"><path d="M828 912C828 798.782 924.707 707 1044 707 1163.29 707 1260 798.782 1260 912 1260 1025.22 1163.29 1117 1044 1117 924.707 1117 828 1025.22 828 912Z" fill="#0F9ED5" fill-rule="evenodd"/><g><g><g><path d="M1099 840.125C1099 870.501 1074.38 895.125 1044 895.125 1013.62 895.125 989 870.501 989 840.125 989 809.749 1013.62 785.125 1044 785.125 1074.38 785.125 1099 809.749 1099 840.125Z" fill="#FFFFFF"/><path d="M1154 1018.88 1154 963.875C1154 955.625 1149.88 947.375 1143 941.875 1127.88 929.5 1108.62 921.25 1089.38 915.75 1075.62 911.625 1060.5 908.875 1044 908.875 1028.88 908.875 1013.75 911.625 998.625 915.75 979.375 921.25 960.125 930.875 945 941.875 938.125 947.375 934 955.625 934 963.875L934 1018.88 1154 1018.88Z" fill="#FFFFFF"/></g></g></g></g></svg>'
}
// グループアイコンを返す
function group_icon() {
  return '<svg  class="flow_img group_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 432 410"  xml:space="preserve" overflow="hidden"><g transform="translate(-1672 -730)"><path d="M1672 935C1672 821.782 1768.71 730 1888 730 2007.29 730 2104 821.782 2104 935 2104 1048.22 2007.29 1140 1888 1140 1768.71 1140 1672 1048.22 1672 935Z" fill="#F2AA84" fill-rule="evenodd"/><g><g><g><path d="M1853.62 980.75 1922.37 980.75 1922.37 1028.88 1853.62 1028.88Z" fill="#FFFFFF"/><path d="M1853.62 795.125 1922.37 795.125 1922.37 843.25 1853.62 843.25Z" fill="#FFFFFF"/><path d="M1750.5 980.75 1819.25 980.75 1819.25 1028.88 1750.5 1028.88Z" fill="#FFFFFF"/><path d="M1956.75 980.75 2025.5 980.75 2025.5 1028.88 1956.75 1028.88Z" fill="#FFFFFF"/><path d="M1894.87 905.125 1894.87 857 1881.12 857 1881.12 905.125 1778 905.125 1778 967 1791.75 967 1791.75 918.875 1881.12 918.875 1881.12 967 1894.87 967 1894.87 918.875 1984.25 918.875 1984.25 967 1998 967 1998 905.125Z" fill="#FFFFFF"/></g></g></g></g></svg>'
}
// 未設定アイコンを返す
function none_icon() {
  return '<svg class="flow_img none_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 432 410" xml:space="preserve" overflow="hidden"><defs><clipPath id="clip0"><rect x="2539" y="722" width="432" height="410"/></clipPath></defs><g clip-path="url(#clip0)" transform="translate(-2539 -722)"><path d="M2539 927C2539 813.781 2635.71 722 2755 722 2874.29 722 2971 813.781 2971 927 2971 1040.22 2874.29 1132 2755 1132 2635.71 1132 2539 1040.22 2539 927Z" fill="#AEAEAE" fill-rule="evenodd"/><g><g><g><path d="M125.285 23.6667 158.715 23.6667 155.934 191.966 128.066 191.966 125.285 23.6667Z" fill="#FFFFFF" transform="matrix(1.16197 0 0 1 2590 783)"/><path d="M165.667 233.708C165.667 246.779 155.071 257.375 142 257.375 128.929 257.375 118.333 246.779 118.333 233.708 118.333 220.638 128.929 210.042 142 210.042 155.071 210.042 165.667 220.638 165.667 233.708Z" fill="#FFFFFF" transform="matrix(1.16197 0 0 1 2590 783)"/></g></g></g></g></svg>'
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
    change_icon(focusid)
    element.append('<div class="none_setting">未設定</div>')
    element.parent().find(".element_authorizer_number").text("")
  }
  else {
    change_icon(focusid)
    element_authorizer_reload(focusid)
    change_detail(focusid)
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
  // 詳細情報を取得
  var detail = $("#" + column + "_" + row).find(".detail_container")
  // 要素の個人の情報を一旦削除する
  detail.html("")
  $('.person[data-id="' + id + '"]').each(function () {
    element.append('<div class="person_element">' + $(this).data('person_name') + '</div>')
  })
  // inputの数が0であり申請者に当たらない場合、未設定の文字をいれる
  if ($('.person[data-id="' + id + '"]').length == 0 && id != 10000) {
    element.append('<div class="none_setting">未設定</div>')
    change_icon(id)
  }
  // 申請者の場合
  else if (id == 10000) {
    element.append('<div class="applicant">申請者</div>')
  }
  // inputの数が1以上ある場合
  else {
    change_icon(id)
    element_authorizer_reload(id)
    change_detail(id)
  }
}
function change_icon(id) {
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + id)
  var authorizer = inputelement.data("authorizer")
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のflow_img_box(アイコンの入れ物)を取得
  var icon = $("#" + column + "_" + row).find(".flow_img_box").find(".flow_img_element")

  if (authorizer == "person") {
    if ($('.person[data-id = "' + id + '"]').length != 0) {
      icon.html(person_icon())
    }
    else {
      icon.html(none_icon())
    }
  }
  else if (authorizer == "group") {
    if ($('.group[data-id="' + id + '"]').length == 0) {
      icon.html(none_icon())
    }
    else if (inputelement.data("group_parameter") == "0" || inputelement.data("group_required_number") == "0") {
      icon.html(none_icon())
    }
    else if (inputelement.data("group_parameter") < inputelement.data("group_required_number")) {
      icon.html(none_icon())
    }
    else {
      icon.html(group_icon())
    }
  }
  // if (icon_name == "none") {
  //   icon.html(none_icon())
  // }
  // else if (icon_name == "person") {
  //   icon.html(person_icon())
  // }
  // else if (icon_name == "group") {
  //   icon.html(group_icon())
  // }
}

function element_authorizer_reload(id) {
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + id)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のauthorizer_container(入れ物)を取得
  var element = $("#" + column + "_" + row).find(".element_authorizer_number")

  if (inputelement.data("authorizer") == "person") {
    var authorizer_number = $('.person[data-id="' + id + '"]').length
    if (authorizer_number == 0) {
      element.text("")
      $('.parameter').text('0')
    }
    else {
      element.text(authorizer_number + "人")
      $('.parameter').text(authorizer_number)
    }
  }
  else if (inputelement.data("authorizer") == "group") {
    element.text(inputelement.data('group_parameter') + "人")
  }
}


function change_detail(id) {
  var inputelement = $("#" + id)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のdetail_container(入れ物)を取得
  var detail_container = $("#" + column + "_" + row).find(".detail_container")
  if (inputelement.data('authorizer') == "person") {
    var detail = inputelement.data("person_required_number")
    if (detail == "all") {
      detail_container.html('<div class="detail_element">全員の承認</div>')
    }
    else {
      detail_container.html('<div class="detail_element">うち' + detail + '人承認</div>')
    }
  }
  else if (inputelement.data('authorizer') == "group") {
    var detail = inputelement.data("group_required_number")
    detail_container.html('<div class="detail_element">うち' + detail + '人承認</div>')
  }

}
// フォーカスが当たったり、文字が変更されたときに
// サーバーに非同期で通信してユーザー情報を取得する
function ajax_flowuserlist(searchtext, inputtext) {
  var prefix = $('#prefix').val();
  $.ajax({
    url: prefix + '/flowuserlist',
    method: 'GET',
    data: { search: searchtext },
    success: function (response) {
      inputtext.parent().find(".flow_user_list").empty();

      if (response.length == 0) {
        $(".gray").show()
        $(".person_container").addClass("person_container_status_gray")
        inputtext.parent().find(".flow_user_list").show()
        inputtext.parent().find(".flow_user_list").append('<div class="nothing">該当なし</div>');
      }
      else {
        $.each(response, function (index, user) {
          $(".gray").show()
          // person_containerはデフォルトではoverflow:hiddenとなっているがフォーカスの時は
          // 選択肢の要素が隠れてしまうので一時的にoverflow:visibleにするクラスを付与していた
          $(".person_container").addClass("person_container_status_gray")
          inputtext.parent().find(".flow_user_list").show()
          inputtext.parent().find(".flow_user_list").append('<div class="userelement">' + user.name + '</div>');
        });
      }

    }
  });
}
// 承認者個人のフォーカスが外れた時、グレーエリア、リスト、フォーカスのクラスの削除を行う
function remove_focus() {
  $(".gray").hide()
  // person_containerはデフォルトではoverflow:hiddenとなっているがフォーカスの時は
  // 選択肢の要素が隠れてしまうので一時的にoverflow:visibleにするクラスを付与していた
  // それを外す
  $(".person_container").removeClass("person_container_status_gray")
  $(".flow_user_list").hide()
  $(".person_text").removeClass("person_text_focus")

}


// group_selectからgroupのinputの情報の更新を行う。
// focus情報をもとに選択中の要素だけの更新も行う
// グループに該当しないpostはinputから消去
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
  // グループのインプットタグを一旦削除する
  // 要素のグループの情報を一旦削除する
  $('input[class="group"][data-id="' + focusid + '"]').remove()
  element.html("")

  var group = $('.group_select').val()
  var groupid = $('.group_select').find("option:selected").data("group_id")
  if (group == "") {
    element.append('<div class="none_setting">未設定</div>')
    element.parent().find(".element_authorizer_number").text("")
    group_parameter_reload()
    // $(".element_input").append('<input type="hidden" class="group" data-id="' + focusid + '" data-group_id= "' + groupid + '" data-group_count ="0">')
  }
  else {
    var group_count = $('.group_select').find("option:selected").data("group_count")
    $(".element_input").append('<input type="hidden" class="group" data-id="' + focusid + '" data-group_id= "' + groupid + '" data-group_name="' + group + '" data-group_count ="' + group_count + '">')
    element.append('<div class="group_element">' + group + '</div><div class="group_select_method_container"></div>')
  }



  if (($('.group[data-id="' + focusid + '"]').length == 0)) {

  }
  // 選択されているinputのフォーカスidに当てはまるものを順に参照していく
  $('.post[data-id="' + focusid + '"]').each(function () {
    // その役職が現在選択されているグループに該当しなかった場合inputを消去する
    if ($('.position[groupid="' + groupid + '"]').data("positionid") != $(this).data("positionid")) {
      $(this).remove()
    }
  })
  element_authorizer_reload(focusid)
  group_select_method_reload(focusid)
  // 役職の一覧を更新
  positionreload()

  change_icon(focusid)
}
// グループのセレクトボックスの値をinputから取得して表示する
function group_select(focusid) {
  var group = $('input[class="group"][data-id="' + focusid + '"]').data('group_name')
  $('.group_select').find('option').first().prop('selected', true)
  $('.group_select').find('option').each(function () {
    if ($(this).val() === group) {
      $(this).prop('selected', true)
    }
  })
}
// グループの選択方法の値をinputから取得して表示する
function group_select_method(focusid) {
  var inputelement_select_method = $('#' + focusid).data('select_method')
  $('#' + inputelement_select_method).click()
  // 「申請者が選択」の場合は選択可能人数などのラジオボタンなどを変更する
  if (inputelement_select_method == "byapplicant") {
    var group_authorizer_number = $('input[class="byapplicant"][data-id="' + focusid + '"]').data('group_choice_number')
    // 選択可能人数が無制限の場合
    if (group_authorizer_number == "all") {
      $('#choice_limit1').click()
    }
    // 選択人数を指定する場合
    else {
      $('#choice_limit2').click()
      $('#group_choice_number').val(group_authorizer_number)
    }
  }
  else if (inputelement_select_method == "postchoice") {
    positionreload()
  }
}
// 引数にいれたidの要素だけinputの情報をもとに要素を更新する
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
    // var group = $('.group_select').val()
    // var groupid = $('.group_select').find("option:selected").data("group_id")
    // $(".element_input").append('<input type="hidden" class="group" data-id="' + id + '" data-group_id= "' + groupid + '"  data-group_name="' + group + '">')
    element.append('<div class="none_setting">未設定</div>')
    change_icon(id)
  }
  // 申請者の場合
  else if (id == 10000) {
    element.append('<div class="applicant">申請者</div>')
  }
  // inputの数が1以上ある場合
  else {
    element.append('<div class="group_element">' + $('.group[data-id="' + id + '"]').data('group_name') + '</div><div class="group_select_method_container"></div>')
    element_authorizer_reload(id)
    group_select_method_reload(id)
    change_detail(id)
    change_icon(id)
  }
}
// 承認者の個人・グループのラジオボタンが切り替わったときの動作
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
  // 切り替わったラジオボタンが個人の場合
  if (person_group == "person") {
    if ($("#" + column + "_" + row).find(".authorizer_container").find(".none_setting").length != 0) {
      // flow_img_box.html('<div class="flow_img_element">' + none_icon() + '</div><div class="element_authorizer_number"></div>')
      personreload(focusid)
    }
    else {
      // flow_img_box.html('<div class="flow_img_element">' + person_icon() + '</div><div class="element_authorizer_number"></div>')
      personreload(focusid)
    }

  }
  // 切り替わったラジオボタンがグループの場合
  else if (person_group == "group") {
    // flow_img_box.html('<div class="flow_img_element">' + group_icon() + '</div><div class="element_authorizer_number"></div>')
    group_select(focusid)
    group_select_method(focusid)
    groupreload(focusid)

  }
}

// 承認者_個人の承認人数のラジオボタンが切り替わったとき、また条件指定の人数が変更されたとき
// inputの承認人数を更新する
function change_person_required_number() {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + focusid)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  if ($('#authorizer_condition1').prop('checked')) {
    inputelement.data("person_required_number", "all")
    inputelement.attr("data-person_required_number", "all")
  }
  // 条件指定にチェックがあった場合は指定人数に値があるかないかで条件分岐
  else if ($('#authorizer_condition2').prop('checked')) {
    // 承認者の最大人数を取得(現在選択中の要素の承認者のテキストボックスの数と同じ)
    var max_number = $(".parameter").text()
    // 初期値がないもしくは指定人数が0人の場合
    // 最大人数の値をinputに更新、さらにテキストボックスの値に追加
    if (!$('#person_required_number').val() || $('#person_required_number').val() == 0) {
      inputelement.data("person_required_number", max_number)
      inputelement.attr("data-person_required_number", max_number)
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
      inputelement.data("person_required_number", $('#person_required_number').val())
      inputelement.attr("data-person_required_number", $('#person_required_number').val())

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



  if (inputelement.data("person_required_number") == "all") {
    $('#authorizer_condition1').click()
  }
  else {
    $('#authorizer_condition2').click()
    $('#person_required_number').val(inputelement.data("person_required_number"))
  }
}

// 承認者グループの選択のラジオボタンが変更されたときinputのdata-select_methodを変更する
function change_group_select_method(method) {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")
  // 要素のインプットタグを取得
  // そのcolumnとrowも取得
  var inputelement = $("#" + focusid)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  inputelement.data("select_method", method)
  inputelement.attr("data-select_method", method)

  if (method == "nolimit") {
    group_parameter_reload()
    group_select_method_reload(focusid)
  }
  else if (method == "byapplicant") {
    byapplicant_create()
    group_parameter_reload()
    group_select_method_reload(focusid)

  }
  else if (method == "postchoice") {
    positionreload()
    group_parameter_reload()
    group_select_method_reload(focusid)
  }
  change_icon(focusid)
}
// 承認者グループの申請者が選択をチェックした場合、inputに選択人数を作成する(デフォルトは無制限)
// フォーカスがあたって要素の切り替わりの際にもこの関数が呼ばれるため
// 既存のinput情報から選択可能人数のラジオボタンを選択する
function byapplicant_create() {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")

  if ($('input[class="byapplicant"][data-id = "' + focusid + '" ]').length == 0) {
    $(".element_input").append(
      '<input type="hidden" class="byapplicant" data-id = "' + focusid + '"  data-group_choice_number = "all" >'
    )
  }

  var byapplicant = $('input[class="byapplicant"][data-id = "' + focusid + '" ]').data("group_choice_number")
  if (byapplicant == "all") {
    $('#choice_limit1').click()
  }
  else {
    $('#choice_limit2').click()
  }

}
// 承認者グループのグループ名が変わった時と「役職から選択」にチェックがされたとき
// inputの役職一覧からグループに該当する役職を並べて表示する
// 表示された役職にinputの情報からチェックしていく
// 母数を更新する
function positionreload() {
  $('.post_choice_container').html("")
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")
  // 選択中のグループidを取得
  var groupid = $('.group[data-id = "' + focusid + '"]').data("group_id")
  var positioncount = 0
  // inputからグループに該当する役職をそれぞれ参照していく
  $('.position[data-groupid = "' + groupid + '"]').each(function () {
    positioncount = positioncount + 1
    // チェックボックスを表示していく
    $('.post_choice_container').append(
      '<div><input type="checkbox" name="post_choice" class="post_choice" id="positionid' + positioncount + '" value="' + $(this).data("positionid") + '"><label for="positionid' + positioncount + '">' + $(this).val() + '</label></div>'
    )
  })

  // 表示されているチェックボックスの値を順に参照していく
  $('.post_choice').each(function () {
    // inputの情報からチェックボックスに該当するidがあった場合チェックボックスにチェックをつける
    if ($('.post[data-id="' + focusid + '"][data-positionid="' + $(this).val() + '"]').length != 0) {
      $(this).prop("checked", true)
    }
    // inputの情報からチェックボックスに該当するidがあった場合チェックボックスにチェックをつける
    else {
      $(this).prop("checked", false)
    }
  })
  group_parameter_reload()
}
// 「役職から選択」の中の役職のチェックボックスにチェックがされたときに
// 役職選択のインプットタグを作成する
// 母数を更新する
function positioninputcreate() {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")
  $('.post[data-id="' + focusid + '"]').remove()
  $('.post_choice:checked').each(function () {
    $('.element_input').append(
      '<input type="hidden" class="post" data-id="' + focusid + '" data-positionid="' + $(this).val() + '">'
    )
  })
  group_select_method_reload(focusid)
  group_parameter_reload()
  change_icon(focusid)

}

// グループの選択方法が変わった際承認人数の母数を変更する
function group_parameter_reload() {
  // 現在選択中の要素のidを取得
  var focusid = $("#focus").data("id")
  var inputelement = $('#' + focusid)

  var method = $('#' + focusid).data('select_method')
  var group_element = $('.group[data-id="' + focusid + '"]')
  if (group_element.length != 0) {
    var group_count = group_element.data("group_count")
  }
  else {
    var group_count = "0"
  }


  // 限定無しの場合
  if (method == "nolimit") {
    // 該当するグループがインプットにある場合
    if (group_element.length != 0) {
      $('.group_parameter').text(group_count)
      inputelement.data('group_parameter', group_count)
      inputelement.attr('data-group_parameter', group_count)
    }
    // inputに値がない場合(空のグループが選択されている場合)
    else {
      $('.group_parameter').text(0)
      inputelement.data('group_parameter', "0")
      inputelement.attr('data-group_parameter', "0")
    }
  }
  else if (method == "byapplicant") {
    var byapplicant_element = $('.byapplicant[data-id="' + focusid + '"]')
    // 選択人数が無制限の場合
    // パラメーターはグループの数を母数にする
    if (byapplicant_element.data("group_choice_number ") == "all") {
      $('.group_parameter').text(group_count)
      inputelement.data('group_parameter', group_count)
      inputelement.attr('data-group_parameter', group_count)
    }
    // 選択人数の指定がありそれがグループの数より少ない場合
    // パラメーターは指定された選択人数を母数にする
    else if (byapplicant_element.data("group_choice_number") < group_count) {
      $('.group_parameter').text(byapplicant_element.data("group_choice_number"))
      inputelement.data('group_parameter', byapplicant_element.data("group_choice_number"))
      inputelement.attr('data-group_parameter', byapplicant_element.data("group_choice_number"))
    }
    // それ以外(選択人数の指定がグループの数より多い場合)
    // パラメーターはグループの数を母数にする
    else {
      $('.group_parameter').text(group_count)
      inputelement.data('group_parameter', group_count)
      inputelement.attr('data-group_parameter', group_count)
    }
  }
  else if (method == "postchoice") {
    var position_total_count = 0
    $('.post[data-id="' + focusid + '"]').each(function () {
      var positionid = $(this).data("positionid")
      var position_count = $('.position[data-positionid="' + positionid + '"]').data("position_count")
      position_total_count = position_total_count + position_count
    })
    $('.group_parameter').text(position_total_count)
    inputelement.data('group_parameter', position_total_count)
    inputelement.attr('data-group_parameter', position_total_count)
  }
  element_authorizer_reload(focusid)

}
function group_select_method_reload(id) {
  var inputelement = $('#' + id)
  var column = inputelement.data("column")
  var row = inputelement.data("row")
  // 要素のauthorizer_container(入れ物)を取得
  var group_select_method_container = $("#" + column + "_" + row).find(".group_select_method_container")
  group_select_method_container.html("")
  if (inputelement.data("select_method") == "byapplicant") {
    group_select_method_container.append(
      '<div class="byapplicant_container">申請者が選択</div>'
    )
  }
  else if (inputelement.data("select_method") == "postchoice") {
    var length = $('.post[data-id="' + id + '"]').length
    $('.post[data-id="' + id + '"]').each(function () {
      group_select_method_container.append(
        '<div class="postchoice_container">' + $('.position[data-positionid = "' + $(this).data("positionid") + '"]').val() + '</div>'
      )
    })

  }


}

function group_required_number_reload(id){
  var inputelement = $('#' + id)
  var group_required_number = inputelement.data("group_required_number")
  $("#group_authorizer_number").val(group_required_number)
}




// 承認者グループ、申請者が選択の選択可能人数が変更された際、inputに情報を更新する
function change_choice_number(status) {
  var focusid = $("#focus").data("id")
  var byapplicant = $('input[class="byapplicant"][data-id = "' + focusid + '" ]')
  // 選択可能人数が無制限に変更されたとき
  if (status == "all") {
    byapplicant.data('group_choice_number', "all")
    byapplicant.attr('data-group_choice_number', "all")
  }
  // 選択可能人数が「選択人数指定」に変更されたとき
  // ※選択人数指定のデフォルトの値は1とするためラジオボタンが切り替わったときはテキストボックスも1に変更する
  else if (status == "select") {
    // フォーカスが当たって自動でラジオボタンが変更されたときもこの関数が実行されるため
    // 現状の選択人数の値を確認してgroup_choice_numberがallつまり初期状態か無制限からの切り替えの場合実行
    if (byapplicant.data('group_choice_number') == 'all') {
      byapplicant.data('group_choice_number', "1")
      byapplicant.attr('data-group_choice_number', "1")

      $('#group_choice_number').val("1")
    }

  }
  else if (status == 'number') {
    // 選択人数指定が0以下の場合は値を1にする
    if ($('#group_choice_number').val() < 1) {
      byapplicant.data('group_choice_number', 1)
      byapplicant.attr('data-group_choice_number', 1)
      $('#group_choice_number').val("1")
    }
    // 選択人数指定が1以上の場合は値をそのまま入れる
    else if ($('#group_choice_number').val() >= 1) {
      byapplicant.data('group_choice_number', $('#group_choice_number').val())
      byapplicant.attr('data-group_choice_number', $('#group_choice_number').val())
    }
    // inputに値がなかった場合は値を1にする
    else {
      byapplicant.data('group_choice_number', 1)
      byapplicant.attr('data-group_choice_number', 1)
      $('#group_choice_number').val("1")
    }

  }
  group_parameter_reload()
}
// グループの承認人数が変更されたときにinputに情報を入れる
// 母数を超えた時に処理を行う
// 
function change_group_authorizer_number() {
  var focusid = $("#focus").data("id")

  var inputelement = $("#" + focusid)
  var group_authorizer_number = $('#group_authorizer_number').val()
  // 母数をチェックする
  var group_parameter = $('.group_parameter').text()
  if (group_authorizer_number < 1) {
    $('#group_authorizer_number').val("1")
    inputelement.data("group_required_number", "1")
    inputelement.attr("data-group_required_number", "1")
  }
  else if (group_authorizer_number > group_parameter) {
    $('#group_authorizer_number').val(group_parameter)
    inputelement.data("group_required_number", group_parameter)
    inputelement.attr("data-group_required_number", group_parameter)
  }
  else {
    inputelement.data("group_required_number", group_authorizer_number)
    inputelement.attr("data-group_required_number", group_authorizer_number)
  }
  change_detail(focusid)
  change_icon(focusid)


}




function makeinputelement(gridcolumn, gridrow, nowelementid, last = "none") {
  $(".element_input").append('<input type="hidden" id="' + nowelementid + '" class="element" data-column="' + gridcolumn + '" data-row="' + gridrow + '" data-last="' + last + '" data-authorizer = "person" data-person_required_number="all" data-group_parameter="0" data-group_required_number="0" data-select_method ="nolimit" >')
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
      "<div class='authorizer_container'><div class='applicant'>申請者</div></div><div class='detail_container'></div>"
    )
  }
  // それ以外
  else {
    console.log($(".element"))
    if ($('input[data-column = "' + gridcolumn + '"][data-row = "' + gridrow + '"]').data('authorizer') == 'person') {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="flow_img_box"><div class="flow_img_element">' + person_icon() + '</div><div class="element_authorizer_number"></div></div><div class="authorizer_container"><div class="none_setting">未設定</div></div><div class="detail_container"><div class="detail_element"></div></div>'
      )
      console.log('個人')
    }
    else if ($('input[data-column = "' + gridcolumn + '"][data-row = "' + gridrow + '"]').data('authorizer') == 'group') {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="flow_img_box"><div class="flow_img_element">' + group_icon() + '</div><div class="element_authorizer_number"></div></div><div class="authorizer_container"><div class="none_setting">未設定</div></div><div class=detail_container><div class="detail_element"></div></div>'
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
      $(".person_content").append('<div class="person_box"><input type="text" class="person_text"><div class="batsu_button">×</div><div class="flow_user_list"></div></div>')
    }
    // インプットタグを参照しテキストボックスを追加
    $('input[class="person"][data-id="' + focusid + '"]').each(function () {
      $(".person_content").append('<div class="person_box"><input type="text" class="person_text" value="' + $(this).data("person_name") + '"><div class="batsu_button">×</div><div class="flow_user_list"></div></div>')
    })
  }
  // フォーカスしている要素が個人の場合はラジオボタン「グループ」をクリック
  else if ($('#' + focusid).data('authorizer') == "group") {
    $("#authorizer2").click()
    group_select(focusid)
    group_select_method(focusid)
    group_required_number_reload(focusid)
  }

  element_authorizer_reload(focusid)



}



