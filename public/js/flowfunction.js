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
    "grid-template-rows": "20px repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
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
    "grid-template-rows": "20px repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
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
      inputelement.data("person_parameter", "0")
      inputelement.attr("data-person_parameter", "0")
    }
    else {
      element.text(authorizer_number + "人")
      $('.parameter').text(authorizer_number)
      inputelement.data("person_parameter", authorizer_number)
      inputelement.attr("data-person_parameter", authorizer_number)
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
  var duplicationarray = []
  $(".person").each(function () {
    duplicationarray.push($(this).data("person_name"))
  })
  duplicationarray = duplicationarray.filter(function (item) {
    return item !== searchtext;
  });

  $.ajax({
    url: prefix + '/flowuserlist',
    method: 'GET',
    data: {
      search: searchtext,
      duplicationarray: duplicationarray
    },
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
  group_required_number_reload(focusid)
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
  // else if (method == "byapplicant") {
  //   byapplicant_create()
  //   group_parameter_reload()
  //   group_select_method_reload(focusid)

  // }
  else if (method == "postchoice") {
    positionreload()
    group_parameter_reload()
    group_select_method_reload(focusid)
  }
  change_icon(focusid)
}


// // 承認者グループの申請者が選択をチェックした場合、inputに選択人数を作成する(デフォルトは無制限)
// // フォーカスがあたって要素の切り替わりの際にもこの関数が呼ばれるため
// // 既存のinput情報から選択可能人数のラジオボタンを選択する
// function byapplicant_create() {
//   // 現在選択中の要素のidを取得
//   var focusid = $("#focus").data("id")

//   if ($('input[class="byapplicant"][data-id = "' + focusid + '" ]').length == 0) {
//     $(".element_input").append(
//       '<input type="hidden" class="byapplicant" data-id = "' + focusid + '"  data-group_choice_number = "all" >'
//     )
//   }

//   var byapplicant = $('input[class="byapplicant"][data-id = "' + focusid + '" ]').data("group_choice_number")
//   if (byapplicant == "all") {
//     $('#choice_limit1').click()
//   }
//   else {
//     $('#choice_limit2').click()
//   }

// }


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
  // if (inputelement.data("select_method") == "byapplicant") {
  //   group_select_method_container.append(
  //     '<div class="byapplicant_container">申請者が選択</div>'
  //   )
  // }
  if (inputelement.data("select_method") == "postchoice") {
    var length = $('.post[data-id="' + id + '"]').length
    $('.post[data-id="' + id + '"]').each(function () {
      group_select_method_container.append(
        '<div class="postchoice_container">' + $('.position[data-positionid = "' + $(this).data("positionid") + '"]').val() + '</div>'
      )
    })

  }


}

function group_required_number_reload(id) {
  var inputelement = $('#' + id)
  var group_required_number = inputelement.data("group_required_number")
  $("#group_authorizer_number").val(group_required_number)
}




// // 承認者グループ、申請者が選択の選択可能人数が変更された際、inputに情報を更新する
// function change_choice_number(status) {
//   var focusid = $("#focus").data("id")
//   var byapplicant = $('input[class="byapplicant"][data-id = "' + focusid + '" ]')
//   // 選択可能人数が無制限に変更されたとき
//   if (status == "all") {
//     byapplicant.data('group_choice_number', "all")
//     byapplicant.attr('data-group_choice_number', "all")
//   }
//   // 選択可能人数が「選択人数指定」に変更されたとき
//   // ※選択人数指定のデフォルトの値は1とするためラジオボタンが切り替わったときはテキストボックスも1に変更する
//   else if (status == "select") {
//     // フォーカスが当たって自動でラジオボタンが変更されたときもこの関数が実行されるため
//     // 現状の選択人数の値を確認してgroup_choice_numberがallつまり初期状態か無制限からの切り替えの場合実行
//     if (byapplicant.data('group_choice_number') == 'all') {
//       byapplicant.data('group_choice_number', "1")
//       byapplicant.attr('data-group_choice_number', "1")

//       $('#group_choice_number').val("1")
//     }

//   }
//   else if (status == 'number') {
//     // 選択人数指定が0以下の場合は値を1にする
//     if ($('#group_choice_number').val() < 1) {
//       byapplicant.data('group_choice_number', 1)
//       byapplicant.attr('data-group_choice_number', 1)
//       $('#group_choice_number').val("1")
//     }
//     // 選択人数指定が1以上の場合は値をそのまま入れる
//     else if ($('#group_choice_number').val() >= 1) {
//       byapplicant.data('group_choice_number', $('#group_choice_number').val())
//       byapplicant.attr('data-group_choice_number', $('#group_choice_number').val())
//     }
//     // inputに値がなかった場合は値を1にする
//     else {
//       byapplicant.data('group_choice_number', 1)
//       byapplicant.attr('data-group_choice_number', 1)
//       $('#group_choice_number').val("1")
//     }

//   }
//   group_parameter_reload()
// }
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
  $(".element_input").append('<input type="hidden" id="' + nowelementid + '" class="element" data-column="' + gridcolumn + '" data-row="' + gridrow + '" data-last="' + last + '" data-authorizer = "person" data-person_required_number="all" data-group_parameter="0" data-group_required_number="1" data-select_method ="nolimit" >')
}

function reloadelement() {
  $(".e").remove()

  $(".element").each(function () {
    var gridcolumn = $(this).data("column")
    var gridrow = $(this).data("row")
    createelement(gridcolumn, gridrow)
    if ($(this).data('authorizer') == "person") {
      personreload($(this).attr("id"))
    }
    else if ($(this).data('authorizer') == "group") {
      groupreload($(this).attr("id"))
    }
  })
}


// 要素を削除する(要素の行と列を引数に)
// 削除済のarraysを返す(削除できない場合は変更なしでarraysを返す)
function e_delete(column, row, arrays) {


  // 初めの地点だった場合は削除しない(1_2の地点)
  if (column == 1 && row == 2) {
    alert('初めのフロー地点は削除できません')
  }
  else {
    // 削除できるかの判定(deleteerrorがtrueの場合は削除できない)
    var deleteerror = false

    // 削除する要素の次につながる線をeach文で取得
    // 削除する要素が最後の場合はeach文で拾わないため必然的にdeleteerrorはfalseになる
    $('.line[data-startcolumn=' + column + '][data-startrow=' + row + ']').each(function () {
      var endcolumn = $(this).data('endcolumn')
      var endrow = $(this).data('endrow')
      // 削除する要素の次の要素が削除する要素にしかつながっていない場合
      if ($('.line[data-endcolumn=' + endcolumn + '][data-endrow=' + endrow + ']').length == 1) {
        deleteerror = true
      }
    })
    // 削除できない場合
    if (deleteerror) {
      alert('次のフロー地点への経路が確保できないためこの要素は削除できません。')
    }
    // 削除できる場合
    else {
      if (confirm('本当にこのフロー地点を削除しますか')) {
        arrays = delete_exe(column, row, arrays)
        console.log(arrays)
      }
    }
  }

  return arrays
}
function delete_exe(column, row, arrays) {
  // idを取得
  var e_id = $('.element[data-column=' + column + '][data-row=' + row + ']').attr('id')
  if ($('#focus').data('id') == e_id) {
    var defaultid = $('.element[data-column = "1"][data-row = "2"]').attr('id')
    $('#focus').data('id', defaultid)
    $('#focus').attr('data-id', defaultid)
  }
  // 要素の消去
  $('.element[data-column=' + column + '][data-row=' + row + ']').remove()
  // 削除要素からのびる線を削除
  $('.line[data-startcolumn=' + column + '][data-startrow=' + row + ']').remove()
  // 削除要素へのびる線を削除
  $('.line[data-endcolumn=' + column + '][data-endrow=' + row + ']').remove()
  // 削除要素に関連する情報を削除
  $('input[data-id=' + e_id + ']').remove()

  // arraysの変更
  for (var key in arrays) {
    var index = arrays[key].indexOf(column + '_' + row);
    // 要素がある場合
    if (index !== -1) {
      if (index === arrays[key].length - 1 && index != 1) {
        // 要素が最後にあり、かつ2番目でない(1_1→2_2のように二つしかないような場合を除く)その要素のみを削除
        arrays[key].splice(index, 1);
      } else {
        // 要素が最後以外にある場合はその行を削除
        delete arrays[key];
      }
    }
  }

  newarrays = {}

  var index = 1;
  for (var key in arrays) {
    newarrays[index] = arrays[key]
    index++
  }
  console.log(newarrays)
  return newarrays
}

// 要素を作成する
function createelement(gridcolumn, gridrow, status = "add") {


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



  // 1_1の場合は申請者
  if (gridcolumn == 1 && gridrow == 1) {
    $('.grid' + gridcolumn + '_' + gridrow).append(
      "<div class='authorizer_container'><div class='applicant'>申請者</div></div><div class='detail_container'></div>"
    )
  }
  // それ以外
  else {
    if ($('input[data-column = "' + gridcolumn + '"][data-row = "' + gridrow + '"]').data('authorizer') == 'person') {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="e_delete">×</div><div class="flow_img_box"><div class="flow_img_element">' + person_icon() + '</div><div class="element_authorizer_number"></div></div><div class="authorizer_container"><div class="none_setting">未設定</div></div><div class="detail_container"><div class="detail_element"></div></div>'
      )
    }
    else if ($('input[data-column = "' + gridcolumn + '"][data-row = "' + gridrow + '"]').data('authorizer') == 'group') {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="e_delete">×</div><div class="flow_img_box"><div class="flow_img_element">' + group_icon() + '</div><div class="element_authorizer_number"></div></div><div class="authorizer_container"><div class="none_setting">未設定</div></div><div class=detail_container><div class="detail_element"></div></div>'
      )
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

function errorcheck() {
  var error = false
  $(".errortextbox").removeClass("errortextbox")
  $(".erroraccordion").removeClass("erroraccordion")
  $(".errorelement").removeClass("errorelement")

  var category_name = $('#category_name_text').val()
  if (category_name == "") {
    $("#category_name_text").addClass("errortextbox")

    alert("カテゴリーを選択してください")
    error = true
  }

  var flow_name = $('#flow_name_text').val()
  if (flow_name == "") {
    $("#flow_name_text").addClass("errortextbox")

    alert("承認フロー名を入力してください")
    error = true
  }

  var isChecked = false;
  $('.group_checkbox').each(function () {
    if ($(this).is(':checked')) {
      isChecked = true;
      return false; // ループを中断
    }
  });
  if (!isChecked) {
    $('.accordion_menu_group').addClass('erroraccordion')
    alert("グループのうち1つ以上にチェックを入れてください")
    error = true
  }

  if ($(".none_icon").length != 0) {
    $(".none_icon").parent().parent().parent().addClass("errorelement")
    alert("条件を満たしていないフローがあります。")
    error = true
  }

  // 金額の値がどちらも入っておりかつ下限金額が上限金額を上回っているときにエラーメッセージをだす
  if (parseFloat($('#start_flow_price').val()) > parseFloat($('#end_flow_price').val()) && $('#start_flow_price').val() != "" && $('#end_flow_price').val() != "") {
    $("#start_flow_price").addClass("errortextbox")
    $("#end_flow_price").addClass("errortextbox")
    alert("金額の範囲が不正です")
    error = true
  }
  if ($('#start_flow_price').val() < 0 || $('#start_flow_price').val() > 2000000000) {
    $("#start_flow_price").addClass("errortextbox")
    alert("下限金額は0円以上2,000,000,000円以下にしてください")
    error = true
  }
  if ($('#end_flow_price').val() < 0 || $('#end_flow_price').val() > 2000000000) {
    $("#end_flow_price").addClass("errortextbox")
    alert("上限金額は0円以上2,000,000,000円以下にしてください")
    error = true
  }
  return error
}

//ワークフロー申請の時の必須項目のチェック
function flow_application_required_check() {
  var error = false
  // データ属性にrequired= trueになっている要素を順番に参照
  $('[data-required="true"]').each(function () {
    // 初めにエラーのcssを削除
    $(this).removeClass('errortextbox')
    // もし空欄だった場合、エラーのcssを付与
    if ($(this).val() == "") {
      $(this).addClass('errortextbox')
      error = true
    }
  })
  if (error) {
    alert("必須項目を入力してください")
  }
  return error
}

//ワークフロー申請の時の日付項目のチェック
function flow_application_date_check() {
  error = false
  var datestr = $('#flow_application_date').val()
  var specifiedDate = new Date(datestr)
  // gettimeで1970年1月1日0:00からの時間をint型で取得できるため
  // 数字でなかった場合(日付型になっていない場合)はisNaNがtrueとなる
  if (isNaN(specifiedDate.getTime())) {
    $('#flow_application_date').addClass("errortextbox")
    alert('日付が不正な形式です')
    error = true
  }
  return error
}
// 日付の型に変更する
function flow_application_date_format(element) {
  var datestr = element.val()
  datestr = datestr.replaceAll("-", "/")
  console.log(datestr)
  // 指定された日付
  var specifiedDate = new Date(datestr);
  // 現在の年号
  var currentYear = new Date().getFullYear();

  // 正規表現を使用して日付形式をチェックするパターン
  var datePatternYear = /^\d{4}\/\d{1,2}\/\d{1,2}$/;
  var datePatternNoneYear = /^\d{1,2}\/\d{1,2}$/;

  // 入力された文字列が日付形式であるかどうかを確認
  if (datePatternYear.test(datestr)) {
    if (isNaN(specifiedDate.getTime())) {
      element.val("")
    }
    else {
      element.val(specifiedDate.toLocaleDateString())
    }
  }
  else if (datePatternNoneYear.test(datestr)) {
    // 月日を分断
    var components = datestr.split('/');
    // 現在の年号をつけた年月日を作成
    var newDate = new Date(currentYear, components[0] - 1, components[1])
    if (isNaN(newDate.getTime())) {
      element.val("")
    }
    else {
      element.val(newDate.toLocaleDateString())
    }

  }
  else {
    element.val("")
  }
}

function viewonlyreset() {
  $('.view_condition_group_element').html("")
  $('.view_condition_start_price_value').text("")
  $('.view_condition_end_price_value').text("")
  $('.view_grid').html("")
}
// 読み取り専用のフローとメタ情報を表示する
function viewonlyworkflow(prefix, flow_id) {
  // グリッドのセルの値を指定
  const cellwidth = 120
  const cellheight = 60

  // 空白のセルの値を指定
  const gapcellwidth = 10
  const gapcellheight = 10
  $.ajax({
    url: prefix + '/viewonlyworkflow/' + flow_id,
    type: 'get',
    dataType: 'json',
    success: function (response) {
      $("#maxgrid_column").val(response[1])
      $("#maxgrid_row").val(response[2])
      createviewgrid(cellwidth, cellheight, gapcellwidth, gapcellheight)
      view_create_element(response[0])
      view_create_line(response[3], cellwidth, cellheight, gapcellwidth, gapcellheight)
      view_create_approval(response[4])
    },
    error: function () {
    }

  })

  $.ajax({
    url: prefix + '/viewonlymetaworkflow/' + flow_id,
    type: 'get',
    dataType: 'json',
    success: function (response) {
      var startprice = response["startprice"]
      var endprice = response["endprice"]
      if (startprice == "0") {
        $('.view_condition_start_price_value').text("下限無し")
      }
      else {
        $('.view_condition_start_price_value').text(response["startprice"])
      }
      if (endprice == "2000000000") {
        $('.view_condition_end_price_value').text("上限無し")
      }
      else {
        $('.view_condition_end_price_value').text(response["endprice"])
      }


      group_object_create(response["group_objects"]);
      console.log(response)
    },
    error: function () {
    }

  })
}
// 読み取り専用のgrid作成
function createviewgrid(cellwidth, cellheight, gapcellwidth, gapcellheight) {
  var Xcellcount = $("#maxgrid_column").val()
  var Ycellcount = $("#maxgrid_row").val()
  $(".view_grid").css({
    "grid-template-columns": " 20px repeat(" + Xcellcount + ", " + cellwidth + "px " + cellwidth + "px " + gapcellwidth + "px)",
    "grid-template-rows": "40px repeat(" + Ycellcount + ", " + cellheight + "px " + cellheight + "px " + gapcellheight + "px " + gapcellheight + "px)"
  })
}


// 読み取り専用の要素作成
function view_create_element(element_object) {
  $(".e").remove()
  $.each(element_object, function (index, array) {
    var gridcolumn = array["column"]
    var gridrow = array["row"]

    $(".view_grid").append('<div class="grid' + gridcolumn + '_' + gridrow + ' e" id="' + gridcolumn + '_' + gridrow + '" data-column="' + gridcolumn + '" data-row="' + gridrow + '"></div>')
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
    // 申請者の場合
    if (array["person_group"] == 0) {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="approval_container">申請者</div>'
      )
    }
    // 個人の場合
    else if (array["person_group"] == 1) {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="approval_img">' + person_icon() + '</div><div class="approval_container"></div><div class="approval_number">' + array["required"] + '人承認</div>'
      )
    }
    // グループの場合
    else {
      $('.grid' + gridcolumn + '_' + gridrow).append(
        '<div class="approval_img">' + group_icon() + '</div><div class="approval_container"></div><div class="approval_number">' + array["required"] + '人承認</div>'
      )
    }

  });
}

// 読み取り専用の線作成
function view_create_line(lineobject, cellwidth, cellheight, gapcellwidth, gapcellheight) {
  $(".l").remove()
  $.each(lineobject, function (index, array) {
    var startcolumn = array["startcolumn"]
    var startrow = array["startrow"]
    var endcolumn = array["endcolumn"]
    var endrow = array["endrow"]

    var Xstart = 3 * startcolumn
    var Xend = 3 * endcolumn
    var Ystart = 4 * startrow
    var Yend = 4 * endrow

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

    $(".view_grid").append('<svg ' + WidthHeightView + '  class=" ' + lineclass + ' l ">' + createsvgpath(startcolumn, startrow, endcolumn, endrow, linewidth, lineheight, cellwidth, cellheight, gapcellwidth, gapcellheight) + '</svg>')
    $('.' + lineclass).css({
      'grid-column': Xstart + '/' + Xend,
      'grid-row': Ystart + '/' + Yend,
    })
  })

};

function view_create_approval(approval_object) {
  var prefix = $('#prefix').val();
  $.each(approval_object, function (index, array) {
    console.log(array)
    var element = $('.grid' + array["column"] + '_' + array["row"])
    var approval_container = element.find(".approval_container")

    // 個人の場合
    if (array["person_group"] == 1) {
      approval_container.append('<div>' + array["user"] + '</div>')
    }
    // グループ　限定無しの場合
    else if (array["person_group"] == 2) {
      approval_container.append('<div class="group_class">' + array["group"] + '</div>')
    }
    // // グループ　申請者が選択の場合
    // else if (array["person_group"] == 3) {
    //   if (approval_container.find('.group_class').length === 0) {
    //     approval_container.append('<div class="group_class">' + array["group"] + '</div>')
    //   }
    //   // 新しい select 要素を作成
    //   var selectElement = $('<select>', {
    //     'id': array["id"],
    //     'name': array["id"],
    //     'class': "select_class"
    //   });

    //   // オプションを追加
    //   selectElement.append($('<option>', {
    //     'value': ''
    //   }));
    //   console.log(prefix + '/flowgrouplist/' + array)
    //   $.ajax({
    //     url: prefix + '/flowgrouplist/' + array["groupid"],
    //     type: 'get',
    //     dataType: 'json',
    //     success: function (response) {

    //       $.each(response, function (index, array) {

    //         selectElement.append($('<option>', {
    //           'value': array["id"], // グループID
    //           'text': array["name"] // グループ名
    //         }));
    //       })
    //     },
    //     error: function () {
    //     }

    //   })

    //   approval_container.append(
    //     selectElement
    //   )

    // }
    // グループ　役職から選択の場合
    else if (array["person_group"] == 4) {
      if (approval_container.find('.group_class').length === 0) {
        approval_container.append('<div class="group_class">' + array["group"] + '</div>')
      }



      approval_container.append('<div class="position_class">' + array["group"] + '</div>')
    }
  })


}

function group_object_create(groupobjects) {
  $('.view_condition_group_element').html('')
  $.each(groupobjects, function (index, object) {
    $('.view_condition_group_element').append(
      '<div class="view_condition_group">' + object + '</div>'
    )
  })
}

// 承認状況画面において結果のマークをviewに反映する
function add_status_message() {
  $(".e").each(function () {
    var front_point = $(this).attr("id");
    var each_status = $('[data-front_point="' + front_point + '"]').find(".approve_condition_status").text().trim()
    var point_status = $('[data-front_point="' + front_point + '"]').data("point_status")
    console.log(each_status)
    // 申請の場合
    if (each_status == "申請") {
      var status_mark = $('<div>').text('申請')
      status_mark.addClass('status_mark')
      status_mark.addClass('applicant_mark')
    }
    // 今回の自分の場合
    // else if (each_status == "承認待ち"){
    //   var status_mark = $('<div>').text('承認待ち')
    //   status_mark.addClass('status_mark')
    //   status_mark.addClass('approve_wait_mark')
    // }
    // フロー地点で承認済みの場合
    else if (point_status == 0) {
      var status_mark = $('<div>').text('承認済')
      status_mark.addClass('status_mark')
      status_mark.addClass('approved_mark')
    }
    // フロー地点で却下の場合
    else if (point_status == 999) {
      var status_mark = $('<div>').text('却下')
      status_mark.addClass('status_mark')
      status_mark.addClass('reject_mark')
    }
    // フロー地点でまだ承認人数に達していない場合
    else if (point_status < 0) {
      // 承認者が1人以上いる場合
      var approve_count = $('[data-front_point="' + front_point + '"]').find(".approve_condition_status[data-each_status='4']").length

      // 承認人数に達していないが
      if (approve_count != 0) {
        var status_mark = $('<div>').text(approve_count + '人承認')
        status_mark.addClass('status_mark')
        status_mark.addClass('approve_ongoing_mark')
      }
    }
    $(this).append(status_mark)
  })




}

// メール設定の必須項目のチェック

//ワークフロー申請の時の必須項目のチェック
function mail_setting_required_check(status) {
  var error = false
  if (status == "test") {
    // テスト送信の場合は受信用メールアドレスも必須項目とする
    $('#mail_setting_test_mail').data('required', 'true')
    $('#mail_setting_test_mail').attr('data-required', 'true')
  }

  // データ属性にrequired= trueになっている要素を順番に参照
  $('[data-required="true"]').each(function () {
    // 初めにエラーのcssを削除
    $(this).removeClass('errortextbox')
    // もし空欄だった場合、エラーのcssを付与
    if ($(this).val() == "") {
      $(this).addClass('errortextbox')
      error = true
    }
  })
  if (error) {
    alert("必須項目を入力してください")
  }
  // エラーメッセージ表示後に受信用メールアドレスを任意にしておく
  $('#mail_setting_test_mail').data('required', 'false')
  $('#mail_setting_test_mail').attr('data-required', 'false')
  return error
}


// カテゴリ名を変更する時のバリデーションチェック
function category_validate_check(value) {
  var error = false
  $('.category_setting_name').each(function () {
    if ($(this).text().trim() == value) {
      error = true
      alert('カテゴリ名が重複しているため変更できません。')
      return error
    }
  })
  return error
}

// デフォルトのカテゴリの選択をdisableにする
// 削除ボタンも消去する
function change_disable() {

  $('.category_detail_optional_content').each(function () {

    if ($(this).attr('data-id') <= 6) {
      $(this).find(".category_detail_optional_select").attr('disabled', true)
      $(this).find(".category_detail_optional_number").attr('disabled', true)

      $(this).find('.category_detail_optional_delete_button').removeClass('display_flex')
    }
    else {
      $(this).find(".category_detail_optional_select").attr('disabled', false)
      $(this).find(".category_detail_optional_number").attr('disabled', false)
      $(this).find('.category_detail_optional_delete_button').addClass('display_flex')
    }
  })
}

// 並べ替えた時にその順番をinputの格納
function change_items_order() {
  var order = ""
  $('.category_detail_optional_content').each(function () {
    if (order == "") {
      order = $(this).data('id')
    }
    else {
      order += '_' + $(this).data('id')
    }
  })
  $('#order').val(order)
}

// 既存の項目の削除リストをinputに登録
function delete_items(delete_id) {
  var deleteitems = $('#delete').val()

  if (deleteitems == "") {
    deleteitems = delete_id
  }
  else {
    deleteitems += '_' + delete_id
  }
  $('#delete').val(deleteitems)
}

// 最大の値のinputタグを表示するかどうか
// status: change→そのページ内で型が変更された場合
//         new→ページで初めて表示される場合
function max_input_reload(element, status) {
  if (element.val() == 1) {
    element.parent().parent().find('.category_detail_optional_number').attr('type', 'number')
    // 変更される時はデフォルトで文字列は30にしておく
    if (status == "change") {
      element.parent().parent().find('.category_detail_optional_number').val(30)
    }
  }
  else if (element.val() == 2) {
    element.parent().parent().find('.category_detail_optional_number').attr('type', 'number')
    // 変更される時はデフォルトで数値は20億にしておく
    if (status == "change") {
      element.parent().parent().find('.category_detail_optional_number').val(2000000000)
    }
  }
  else {
    element.parent().parent().find('.category_detail_optional_number').attr('type', 'hidden')
  }
}
function application_input_item(item) {
  console.log(item["id"])

  var content = `<div class="application_form_content">
    <div class="application_form_label">
    `+ item["項目名"] + `
    </div>`

  switch (item["型"]) {
    case 1:
      if (item["最大"] <= 100) {
        content += `<input type="text" name="item` + item["id"] + `" id="" class="application_form_text text_long_content" data-required="` + item["必須項目"] + `">`
      }
      else {
        content += `<textarea name="item` + item["id"] + `" id="" class="application_form_text text_area_content" data-required="` + item["必須項目"] + `"></textarea>`
      }
      break;
    case 2:
      content += `<input type="number" name="item` + item["id"] + `" id="" class="application_form_text text_short_content" data-required="` + item["必須項目"] + `">`
      break;
    case 3:
      content += `<input type="text" name="item` + item["id"] + `" id="" class="application_form_text application_form_date text_short_content" data-required="` + item["必須項目"] + `">`

      break;
    case 4:
      content += `
      <div class="flow_application_droparea">
      <p>ここにドラッグ＆ドロップ</p>
      <input type="file" name="item` + item["id"] + `" id="" class="file_input" data-required="` + item["必須項目"] + `">
      </div>
      <div class="flow_application_preview_button">プレビュー</div>
      `
      break;
    // 他の条件に対する処理を追加できます
    default:
    // デフォルトの処理
  }

  content += '</div>'
  $('.flow_application_area').append(content)
}