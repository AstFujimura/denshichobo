$(document).ready(function () {

  //   var selectedOption = $('#select').val();
  // //ページが表示されたとき
  // deleteselect(selectedOption);
  // //一覧と検索画面において選択肢に応じて件数表示を変える




  // $('#select').on('change', function() {
  //   // 選択されたオプションの値を取得

  //   var selectedOption = $(this).val();
  //   deleteselect(selectedOption);

  // });
  // function deleteselect(selectedOption){
  //   if (selectedOption == "有効データ"){
  //     $(".notdeletecount").addClass("selected");
  //     $(".count").removeClass("selected");
  //     $(".deletecount").removeClass("selected");

  //     $(".delete_table").removeClass("table_selected")
  //     $(".top_table_body").addClass("table_selected")
  //     $('#deleteOrzenken').val("yukou")
  //   }
  //   else if (selectedOption == "削除データ"){
  //     $(".notdeletecount").removeClass("selected");
  //     $(".count").removeClass("selected");
  //     $(".deletecount").addClass("selected");

  //     $(".delete_table").addClass("table_selected")
  //     $(".top_table_body").removeClass("table_selected")
  //     $('#deleteOrzenken').val("delete")
  //   }
  //   else if (selectedOption == "全件データ"){
  //     $(".notdeletecount").removeClass("selected");
  //     $(".count").addClass("selected");
  //     $(".deletecount").removeClass("selected");

  //     $(".delete_table").addClass("table_selected")
  //     $(".top_table_body").addClass("table_selected")
  //     $('#deleteOrzenken').val("zenken")
  //   }

  // };
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
        url: '/admin/documentcheck/' + $id,
        type: 'GET',
        processData: false,
        contentType: false,
        success: function (response) {
          if (response) {
            alert("帳簿が保存されているため削除できません。")
          }
          else {
            var deletecontainer = 'container' + $id;
            console.log(deletecontainer);

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

  $('#admin_document_form').on('submit', function (event) {
    event.preventDefault()
    var docuarray = []
    var order = 1
    $(".past").each(function () {
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
    console.log(docuarray);

    if (confirm("本当に変更しますか。")) {
      // FormDataをサーバーに送信
      $.ajax({
        url: '/admin/document',
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
            window.location.href = "/admin/document"
          }
          else {
            console.log(response)
          }
        }

      })
    }



  });

  $(".sortable").sortable(
    {
      update: function () {
        change_button_show()
      }
    });
  $(".sortable").disableSelection();



  function change_button_show() {
    $(".document_change_button").show();
    $('#save').val("notsave");
  }

  $(window).on("beforeunload", function () {
    if ($("#save").val() == "notsave") {
      $('.savemessage').show();
      return "確認"
    }

  });







});