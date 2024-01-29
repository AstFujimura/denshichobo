$(document).ready(function () {
  var prefix = $('#prefix').val();

  // -------------書類管理画面----------------

  var adddocumentCount = 1000
  $(".documenttable_body").on("drop", function (event) {
    change_button_show()
  });
  $('#docu_addbutton').on("click", function (event) {
    change_button_show()

    $('.add').append(
      '<div class="documenttable_body new" id ="' + 'container' + adddocumentCount + '"><div class="admin_use"><input type="checkbox" class="docu_check" checked name ="' + 'addcheck' + adddocumentCount + '"></div><div class="admin_document"><input type="text" class="add_document" name="' + adddocumentCount + '"></div><div class="admin_document_delete"><div class="docu_delete_button" id ="' + adddocumentCount + '">削除</div></div></div>'
    )
    adddocumentCount++
  })

  //既存の書類の削除ボタンを押したとき
  $(".docu_delete_button").on("click", function () {
    $id = $(this).attr("id")
    if (confirm("本当に削除しますか")) {
      // FormDataをサーバーに送信
      $.ajax({
        url: prefix + '/admin/documentcheck/' + $id,
        type: 'GET',
        processData: false,
        contentType: false,
        success: function (response) {
          if (response) {
            alert("帳簿が保存されているため削除できません。")
          }
          else {
            var deletecontainer = 'container' + $id;

            $('#' + deletecontainer).remove();
          }
        }
      });
    }




  });
  $('input').on("change", function () {
    change_button_show()
  });

  //新しく追加した書類要素を削除するとき
  $('.add').on("click", ".docu_delete_button", function () {

    var deletecontainer = 'container' + $(this).attr("id");

    $('#' + deletecontainer).remove();
  });

  $('.docu_change_button').on("click", function () {
    var valueid = 'value' + $(this).attr("id").replace("change", "");
    var textid = 'text' + $(this).attr("id").replace("change", "");
    $('#' + valueid).addClass("document_open");
    $('#' + valueid).focus();
    $('#' + textid).removeClass("document_open");
  });

  $('.admin_document_value').on("blur", function () {
    var textid = 'text' + $(this).attr("id").replace("value", "");
    $('#' + textid).text($(this).val())
    $(this).removeClass("document_open");
    $('#' + textid).addClass("document_open");
  });

  //書類を送信する時
  $('#admin_document_form').on('submit', function (event) {
    event.preventDefault()
    var docuarray = []
    var order = 1
    $(".docu_past").each(function () {
      var id = $(this).attr("id").replace("container", "");
      var check = $(this).find("input[type='checkbox']")
      var document = $(this).find(".admin_document_value").val();
      var deleteobj = $(this).find(".docu_delete_button").text();
      var obj = {}
      obj.id = id

      var isChecked = check.prop("checked");
      if (isChecked) {
        obj.check = "check"
      }
      else {
        obj.check = ""
      }
      obj.document = document
      if (deleteobj != "削除") {
        obj.delete = "削除"
      }
      else {
        obj.delete = ""
      }
      obj.past = "past"
      obj.order = order
      order++
      docuarray.push(obj);

    });
    $(".new").each(function () {
      var id = $(this).attr("id").replace("container", "");
      var check = $(this).find("input[type='checkbox']")
      var document = $(this).find(".add_document").val();
      var deleteobj = $(this).find(".docu_delete_button").text();
      var obj = {}
      obj.id = id

      var isChecked = check.prop("checked");
      if (isChecked) {
        obj.check = "check"
      }
      else {
        obj.check = ""
      }
      obj.document = document
      if (deleteobj != "削除") {
        obj.delete = "削除"
      }
      else {
        obj.delete = ""
      }
      obj.past = "new"

      if (obj.document) {
        obj.order = order
        order++
        docuarray.push(obj);
      }

    });
    console.log(docuarray)
    if (confirm("本当に変更しますか。")) {
      // FormDataをサーバーに送信
      $.ajax({
        url: prefix + '/admin/document',
        type: 'POST',
        data: JSON.stringify(docuarray),
        contentType: "application/json",
        dataType: "json",
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val(),
        },
        success: function (response) {
          if (response == "成功") {
            $('#save').val("save");
            window.location.href = prefix + "/admin/document"
          }
          else {
          }
        }

      })
    }



  });








  // -------------グループ管理画面----------------

  var addgroupCount = 1000
  $(".grouptable_body").on("drop", function (event) {
    change_button_show()
  });
  $('#gr_addbutton').on("click", function (event) {
    change_button_show()

    $('.add').append(
      '<div class="grouptable_body new" id ="' + 'container' + addgroupCount + '"><div class="admin_group"><input type="text" class="add_group" name="' + addgroupCount + '"></div><div class="admin_group_delete"><div class="gr_delete_button" id ="' + addgroupCount + '">削除</div></div></div>'
    )
    addgroupCount++
  })

  //既存のグループの削除ボタンを押したとき
  $(".gr_delete_button").on("click", function () {
    $id = $(this).attr("id")
    if (confirm("本当に削除しますか")) {
      // FormDataをサーバーに送信
      $.ajax({
        url: prefix + '/admin/groupcheck/' + $id,
        type: 'GET',
        processData: false,
        contentType: false,
        success: function (response) {
          if (response) {
            alert("帳簿が保存されているため削除できません。")
          }
          else {
            var deletecontainer = 'container' + $id;

            $('#' + deletecontainer).remove();
          }
        }
      });
    }




  });
  $('input').on("change", function () {
    change_button_show()
  });

  //新しく追加したグループ要素を削除するとき
  $('.add').on("click", ".gr_delete_button", function () {

    var deletecontainer = 'container' + $(this).attr("id");

    $('#' + deletecontainer).remove();
  });

  $('.gr_change_button').on("click", function () {
    var valueid = 'value' + $(this).attr("id").replace("change", "");
    var textid = 'text' + $(this).attr("id").replace("change", "");
    $('#' + valueid).addClass("group_open");
    $('#' + valueid).focus();
    $('#' + textid).removeClass("group_open");
  });

  $('.admin_group_value').on("blur", function () {
    var textid = 'text' + $(this).attr("id").replace("value", "");
    $('#' + textid).text($(this).val())
    $(this).removeClass("group_open");
    $('#' + textid).addClass("group_open");
  });

  //グループを送信する時
  $('#admin_group_form').on('submit', function (event) {
    event.preventDefault()
    var grarray = []
    $(".gr_past").each(function () {
      var id = $(this).attr("id").replace("container", "");
      var check = $(this).find("input[type='checkbox']")
      var group = $(this).find(".admin_group_value").val();
      var deleteobj = $(this).find(".gr_delete_button").text();
      var obj = {}
      obj.id = id

      var isChecked = check.prop("checked");
      if (isChecked) {
        obj.check = "check"
      }
      else {
        obj.check = ""
      }
      obj.group = group
      if (deleteobj != "削除") {
        obj.delete = "削除"
      }
      else {
        obj.delete = ""
      }
      obj.past = "past"
      grarray.push(obj);

    });
    $(".new").each(function () {
      var id = $(this).attr("id").replace("container", "");
      var group = $(this).find(".add_group").val();
      var deleteobj = $(this).find(".gr_delete_button").text();
      var obj = {}
      obj.id = id


      obj.group = group
      if (deleteobj != "削除") {
        obj.delete = "削除"
      }
      else {
        obj.delete = ""
      }
      obj.past = "new"
      grarray.push(obj);


    });
    console.log(grarray)

    if (confirm("本当に変更しますか。")) {
      // FormDataをサーバーに送信
      $.ajax({
        url: prefix + '/admin/group/regist',
        type: 'POST',
        data: JSON.stringify(grarray),
        contentType: "application/json",
        dataType: "json",
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val(),
        },
        success: function (response) {
          if (response == "成功") {
            $('#save').val("save");
            window.location.href = prefix + "/admin/group/regist"
          }
          else {
          }
        }

      })
    }



  });



  // -------------グループ_役職管理画面----------------

  var addpositionCount = 1000

  $('#position_addbutton').on("click", function (event) {
    change_button_show()

    $('.add').append(
      '<div class="positiontable_body new" id ="' + 'container' + addpositionCount + '"><div class="admin_position"><input type="text" class="add_position" name="' + addpositionCount + '"></div><div class="admin_position_delete"><div class="position_delete_button" id ="' + addpositionCount + '">削除</div></div></div>'
    )
    addpositionCount++
  })

  //既存の役職の削除ボタンを押したとき
  $(".position_delete_button").on("click", function () {
    $id = $(this).attr("id").replace("delete", "")
    var deletebutton = $(this);
    if (confirm("本当に削除しますか")) {
      // FormDataをサーバーに送信
      $.ajax({
        url: prefix + '/admin/grouppositiondelete/' + $id,
        type: 'GET',
        processData: false,
        contentType: false,
        success: function (response) {

          deletebutton.closest(".position_past").remove();

        }
      });
    }

  });
  $(document).on("click", '.position_edit_button', function () {
    var inputelement = $(this).parent().parent().find("input")
    var textelement = $(this).parent().parent().find(".position_text")
    inputelement.addClass("position_open");
    inputelement.focus();
    textelement.removeClass("position_open");
  });

  $(document).on("blur", '.position_name_value', function () {
    var inputelement = $(this)
    var textelement = $(this).parent().find(".position_text")
    textelement.text(inputelement.val())
    inputelement.removeClass("position_open");
    textelement.addClass("position_open");
  });



  $('input').on("change", function () {
    change_button_show()
  });

  //新しく追加したグループ要素を削除するとき
  $('.add').on("click", ".position_delete_button", function () {

    var deletecontainer = 'container' + $(this).attr("id");

    $('#' + deletecontainer).remove();
  });


  //役職を送信する時
  $('#admin_position_form').on('submit', function (event) {
    event.preventDefault()
    var groupid = $('#groupid').val()
    var positionarray = []
    $(".position_past").each(function () {
      var id = $(this).find(".position_name_value").attr("id").replace("position", "")
      var position = $(this).find(".position_name_value").val();
      // var deleteobj = $(this).find(".gr_delete_button").text();
      var obj = {}
      obj.id = id

      obj.position = position
      // if (deleteobj != "削除") {
      //   obj.delete = "削除"
      // }
      // else {
      //   obj.delete = ""
      // }
      obj.past = "past"
      positionarray.push(obj);

    });
    $(".new").each(function () {
      var id = $(this).attr("id").replace("container", "");
      var position = $(this).find(".add_position").val();
      // var deleteobj = $(this).find(".gr_delete_button").text();
      var obj = {}
      obj.id = id


      obj.position = position
      // if (deleteobj != "削除") {
      //   obj.delete = "削除"
      // }
      // else {
      //   obj.delete = ""
      // }
      obj.past = "new"
      positionarray.push(obj);


    });

    if (confirm("本当に変更しますか。")) {
      // FormDataをサーバーに送信
      $.ajax({
        url: prefix + '/admin/groupposition/' + groupid,
        type: 'POST',
        data: JSON.stringify(positionarray),
        contentType: "application/json",
        dataType: "json",
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val(),
        },
        success: function (response) {
          if (response == "成功") {
            $('#save').val("save");
            window.location.href = prefix + "/admin/groupposition/" + groupid
          }
          else {
          }
        }

      })
    }



  });


  // -------------グループユーザー設定画面----------------

  //  ユーザー名の各セレクトボックスからすでに選択されているユーザーをselectedに変える
  $(".groupuser_select_user").each(function () {
    var userid = $(this).data("default_userid")
    $(this).find('option[value="' + userid + '"]').prop("selected", true)
  })

  //  役職名の各セレクトボックスからすでに選択されている役職をselectedに変える
  $(".groupuser_select_position").each(function () {
    var positionid = $(this).data("default_positionid")
    $(this).find('option[value="' + positionid + '"]').prop("selected", true)
  })

  // 追加ボタンを押したときダミーテーブルをコピーして行を追加する
  $("#groupuser_addbutton").on("click", function () {
    var dummytable = $(".dummy_table").clone()
    dummytable.removeClass("dummy_table")
    dummytable.addClass("correct_table")
    $(".groupusertable_body").append(dummytable)
    groupuser_reload()
    change_button_show()
  })

  // 削除ボタンを押したときその行を削除する
  $(document).on("click", ".groupuser_delete_button", function () {
    $(this).parent().parent().remove()
    groupuser_reload()
    change_button_show()
  })

  $(document).on("change", ".groupuser_select", function () {
    change_button_show()
  })

  $(document).on('submit', "#admin_group_user_form", function (event) {
    event.preventDefault()
    // 更新時にエラーのステータスをfalseにする
    $('#error').val("noneerror")
    $('.errorselect').removeClass('errorselect')
    var groupusercount = $("#groupusercount").val()
    var selectedValues = {}

    // 各セレクトボックスをループして重複を検出
    for (var i = 1; i <= groupusercount; i++) {
      var selectedValue = $('select[name="user' + i + '"]').val();


      // 選択された値がすでにオブジェクトに存在するかチェック
      // 存在する場合
      if (selectedValues[selectedValue]) {
        // エラーステータスをエラーに変える
        $('#error').val("error")
        // ユーザー名のセレクトボックスから重複する要素に対してエラークラスを付与
        $('.groupuser_select_user').filter(function () {
          return $(this).val().trim() === selectedValue;
        }).addClass("errorselect")

        
      } 
      // 選択された値がオブジェクトに存在しない場合
      // かつ空欄でない場合(空欄の場合はオブジェクトに追加しない)
      else if(selectedValue != ""){
        // オブジェクトに選択された値を追加
        selectedValues[selectedValue] = true;
      }
    }
    // エラーステータスがfalseの場合は確認ダイアログ後に送信
    if ($('#error').val() == "noneerror") {
      $("#save").val("save")
      if (confirm("本当に変更しますか")) {
        this.submit()
      }
    }
    // 重複する場合はアラート表示
    else {
      alert("ユーザー名が重複しています");
    }

  })

  // グループのユーザー設定画面においてテーブルの各行のユーザー名と役職名にそれぞれインデックス番号を付与する
  // ユーザー名は"user1" 役職名は"position1"といった命名方法で付与していく
  function groupuser_reload() {
    var groupusercount = 0
    $(".correct_table").each(function () {
      groupusercount = groupusercount + 1
      $(this).find(".groupuser_select_user").attr("name", "user" + groupusercount)
      $(this).find(".groupuser_select_position").attr("name", "position" + groupusercount)

    })
    $("#groupusercount").val(groupusercount)
  }


























  $(".sortable").sortable(
    {
      update: function () {
        change_button_show()
      }
    });
  $(".sortable").disableSelection();



  function change_button_show() {
    $(".document_change_button").show();
    $(".group_change_button").show();
    $(".position_change_button").show();
    $(".groupuser_change_button").show();
    $('#save').val("notsave");
  }

  $(window).on("beforeunload", function () {
    if ($("#save").val() == "notsave") {
      $('.savemessage').show();
      return "確認"
    }

  });





});
