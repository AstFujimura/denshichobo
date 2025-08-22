$(document).ready(function () {
  var prefix = $('#prefix').val();

  // -------------ユーザー管理画面----------------
  $('.add_user_button').on('click', function () {
    $('.admin_header_container').toggleClass('add_user_form_open');
    $('.user_setting_edit_content').removeClass('user_edit_container_open');
  });

  var display_name_flag = false;
  $('.add_user_form_content input[name="user_name"]').on('input', function () {
    var user_name = $(this).val();
    if (display_name_flag == false) {
      $('.add_user_form_content input[name="display_name"]').val(user_name);

    }
  });

  $('.add_user_form_content input[name="display_name"]').on('input', function () {
    var display_name = $(this).val();
    if (display_name != "") {
      display_name_flag = true;
    }
    else {
      display_name_flag = false;
    }
  });

  $(document).on('click', '.user_setting_password_reset_button', function (e) {
    if (confirm('パスワードをリセットします。よろしいですか')) {
      var form = $(this).closest('form');
      form.submit();
    }
  });

  $(document).on('click', '.add_user_submit_button', function (e) {
    var add_user_form = $(this).closest('.add_user_form');
    if (confirm('ログインアカウントを追加します。よろしいですか')) {
      var data = new FormData();
      data.append('user_name', add_user_form.find('[name="user_name"]').val());
      data.append('display_name', add_user_form.find('[name="display_name"]').val());
      data.append('email', add_user_form.find('[name="email"]').val());
      data.append('password', add_user_form.find('[name="password"]').val());
      data.append('admin', add_user_form.find('[name="admin"]').val());
      add_user_form.find('input[name="group[]"]:checked').each(function () {
        data.append('group[]', $(this).val());
      });
      var url = $(this).closest('form').attr('action');
      $.ajax({
        url: url,
        type: 'POST',
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: {
          'X-CSRF-TOKEN': $('[name="_token"]').val()
        },
        data: data,
        success: function (response) {
          console.log(response);
          if (response.error) {
            alert(response.error);
          }
          else {
            alert(response.success);
            window.location.reload();
          }
        }
      });
    }
  });

  $(document).on('click', '.user_setting_edit_button', function (e) {
    var user_setting_edit_content = $(this).closest('.user_setting_edit_content');
    var user_name = user_setting_edit_content.find('[name="user_name"]').val();
    var display_name = user_setting_edit_content.find('[name="display_name"]').val();
    var email = user_setting_edit_content.find('[name="email"]').val();
    var admin = user_setting_edit_content.find('[name="admin"]').val();
    var group = user_setting_edit_content.find('[name="group[]"]:checked').map(function () {
      return $(this).val();
    }).get();

    if (confirm('ログインアカウントを変更します。よろしいですか')) {
      var data = new FormData();
      data.append('user_name', user_name);
      data.append('display_name', display_name);
      data.append('email', email);
      data.append('admin', admin);
      // グループをループで追加
      group.forEach(function (g) {
        data.append('group[]', g);
      });
      $.ajax({
        url: user_setting_edit_content.data('url'),
        type: 'POST',
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: {
          'X-CSRF-TOKEN': $('[name="_token"]').val()
        },
        data: data,
        success: function (response) {
          if (response.error) {
            alert(response.error);
          }
          else {
            alert(response.success);
            window.location.reload();
          }
        }
      });
    };
  });





  // ユーザー編集ボタンを押したときに編集するフォームコンテナを表示する
  $(document).on('click', '.user_edit_button', function (e) {
    $('.admin_header_container').removeClass('add_user_form_open');
    $('.user_setting_edit_content').removeClass('user_edit_container_open');
    $(this).closest('.admin_top_table_element').find('.user_setting_edit_content').addClass('user_edit_container_open');
  });
  // キャンセルボタンを押したときに編集フォームを閉じる
  $(document).on('click', '.user_setting_cancel_button', function (e) {
    $('.admin_header_container').removeClass('add_user_form_open');
    $('.user_setting_edit_content').removeClass('user_edit_container_open');
  });
  // 削除ボタンを押したときに削除フォームを表示する
  $(document).on('click', '.user_delete_button', function (e) {
    if (confirm('本当に削除しますか')) {
      var delete_url = $(this).closest('.admin_top_table_element').data('delete_url');
      var url = delete_url;
      $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        headers: {
          'X-CSRF-TOKEN': $('[name="_token"]').val()
        },
        success: function (response) {
          if (response.error) {
            alert(response.error);
          }
          else {
            alert(response.success);
            window.location.reload();
          }
        }
      });
    }
  });



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
    var grouptable_body = $(this).closest('.grouptable_body');
    grouptable_body.find('.admin_group_text').removeClass("group_open");
    grouptable_body.find('.admin_group_value').addClass("input_open");
    grouptable_body.find('.admin_group_value').focus();
  });

  $('.admin_group_value').on("blur", function () {
    var grouptable_body = $(this).closest('.grouptable_body');
    grouptable_body.find('.admin_group_text').text($(this).val())
    grouptable_body.find('.admin_group_value').removeClass("input_open");
    grouptable_body.find('.admin_group_text').addClass("group_open");
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
      else if (selectedValue != "") {
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
