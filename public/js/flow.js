$(document).ready(function () {

  var prefix = $('#prefix').val();

  // ワークフローマスタ登録・編集画面の時
  if ($('#edit').length != 0 || $('#regist').length != 0) {


    // 編集画面の時グループカウントをセレクトボックスから取得する
    if ($('#edit').length != 0) {
      $('.group').each(function () {
        var group_count = $('.group_select').find('[data-group_id="' + $(this).data("group_id") + '"]').data("group_count")
        $(this).data("group_count", group_count)
        $(this).attr("data-group_count", group_count)
      })
    }






    // アコーディオンメニューを押したとき
    $(".accordion_menu_title").on("click", function () {
      $(this).toggleClass("accordion_menu_title_open")
      $(this).parent().find(".accordion_content").toggleClass("accordion_content_open")
    })

    // 承認者を個人かグループで選ぶかを選択し要素の表示非表示を行う
    $('.authorizer').on("click", function () {
      var focusid = $("#focus").data("id")
      // 承認者_個人が選択されたとき
      if ($('#authorizer1').prop('checked')) {
        $('.person_container').addClass("person_container_open")
        $('.group_container').removeClass("group_container_open")
        // 要素のインプットタグのdata-authorizerを変更する
        $('#' + focusid).data("authorizer", "person")
        $('#' + focusid).attr("data-authorizer", "person")
        change_authorizer("person")
      }
      // 承認者_グループが選択されたとき
      else if ($('#authorizer2').prop('checked')) {
        $('.group_container').addClass("group_container_open")
        $('.person_container').removeClass("person_container_open")
        $('#' + focusid).data("authorizer", "group")
        $('#' + focusid).attr("data-authorizer", "group")
        change_authorizer("group")
      }
    })

    // 承認者_個人のテキストボックスの文字が変わったとき
    $(document).on("change", ".person_text", function () {
      change_person()

    })

    $(document).on("focus", ".person_text", function () {
      ajax_flowuserlist($(this).val(), $(this))
      $(this).addClass("person_text_focus")
    })
    $(document).on("input", ".person_text", function () {
      ajax_flowuserlist($(this).val(), $(this))
      $(this).addClass("person_text_focus")
    })
    // $(document).on("blur", ".person_text", function () {
    //   var text = $(this).text()
    // })
    // フォーカスが当たっているときにkeydownイベントをリッスン
    $(document).keydown(function (e) {
      // キーがTabキーであるかつフォーカスが当たっているとき
      if (e.which === 9 && $('.person_text').is(':focus')) {
        // ここにTabキーが押されたときに実行したい処理を記述
        $(".gray").click()
      }
    });





    // 承認者_グループのテキストボックスの文字が変わったとき
    $(document).on("change", ".group_select", function () {
      change_group()
    })





    // 承認者_個人での承認者で全員の承認もしくは条件指定の場合の表示非表示
    $('.authorizer_condition').on("change", function () {
      if ($('#authorizer_condition1').prop('checked')) {
        $('#person_authorizer_number_container').removeClass("autorizer_number_container_open")
      }
      else if ($('#authorizer_condition2').prop('checked')) {
        $('#person_authorizer_number_container').addClass("autorizer_number_container_open")
      }
      change_person_required_number()
    })
    // 承認者_個人での条件指定での承認者の数が変更されたとき
    $('#person_required_number').on("change", function () {
      change_person_required_number()
    })
    // 承認者_グループの選択方法での要素の表示非表示
    $('.choice_method').on("change", function () {
      // 限定無しにチェックされた場合
      if ($('#nolimit').prop('checked')) {
        $('.choice_container').removeClass("choice_container_open")
        $('.post_choice_container').removeClass("post_choice_container_open")
        change_group_select_method("nolimit")
      }
      // // 申請者が選択にチェックされた場合
      // else if ($('#byapplicant').prop('checked')) {
      //   $('.choice_container').addClass("choice_container_open")
      //   $('.post_choice_container').removeClass("post_choice_container_open")
      //   change_group_select_method("byapplicant")
      // }
      // 役職から選択にチェックされた場合
      else if ($('#postchoice').prop('checked')) {
        $('.post_choice_container').addClass("post_choice_container_open")
        $('.choice_container').removeClass("choice_container_open")
        change_group_select_method("postchoice")
      }
    })

    // // 承認者_グループでの選択可能人数での無制限もしくは選択人数指定の場合の表示非表示
    // $('.choice_limit').on("change", function () {
    //   if ($('#choice_limit1').prop('checked')) {
    //     $('#group_authorizer_number_container').removeClass("autorizer_number_container_open")
    //     change_choice_number('all')
    //   }
    //   else if ($('#choice_limit2').prop('checked')) {
    //     $('#group_authorizer_number_container').addClass("autorizer_number_container_open")
    //     change_choice_number('select')
    //   }
    // })
    // // 選択人数指定が変わった時
    // $('#group_choice_number').on("change", function () {
    //   change_choice_number('number')
    // })

    // 承認者グループの役職選択の中のチェックボックスが変わった時
    $(document).on("change", ".post_choice", function () {
      positioninputcreate()
    })
    // 承認者グループの承認人数が変わった時
    $('#group_authorizer_number').on("change", function () {
      change_group_authorizer_number()
    })

    // クリックしたときの場所により判定を行う
    // 要素をクリックしたとき、それ以外の時で挙動を分ける
    $(document).on('click', function (event) {
      var targetElement = $(event.target);

      // closestメソッドを使用して、特定の親要素を取得
      var eElement = targetElement.closest('.e');
      var rightElement = targetElement.closest('.right_side_menu');
      var batsuElement = targetElement.closest('.batsu_button');

      var classname = targetElement.attr("class")
      // 要素をクリックした場合は要素にfocusクラスを付与する
      if (eElement.length == 1) {
        if (eElement.attr("id") !== "1_1") {
          $(".e").removeClass("focus")
          eElement.addClass("focus")
          console.log(eElement.data("column"))
          console.log(eElement.data("row"))
          focus_right_side_menu(eElement.data("column"), eElement.data("row"))
          person_required_number_reload()
        }
        else {
          $(".e").removeClass("focus")
          $('.right_side_menu').removeClass("right_side_menu_open")
        }
      }
      // 右側メニューの内容をクリックしたとき
      else if (rightElement.length == 1) {
        // ×ボタンを押したとき
        if (batsuElement.length == 1) {
          if ($(".batsu_button").length != 1) {
            batsuElement.parent().remove();
            change_person()
            change_person_required_number()
          }


        }
        else if (targetElement.attr("class") == "userelement") {
          var person_name = targetElement.text()
          targetElement.parent().parent().find('.person_text').val(person_name)
          remove_focus()
          change_person()
        }
        else if (targetElement.attr("class") == "gray") {
          remove_focus()
        }
      }


      else {
        $(".e").removeClass("focus")
        $('.right_side_menu').removeClass("right_side_menu_open")
        remove_focus()
      }

    })

    // 承認者_個人の追加(+)ボタンを押したときの挙動
    $('.plus_button').on('click', function () {
      $(".person_content").append('<div class="person_box"><input type="text" class="person_text"><div class="batsu_button">×</div><div class="flow_user_list"></div></div>')
    })

    // 承認者_個人の削除(×)ボタンを押したときの挙動
    // $(document).on('click', '.batsu_button', function () {
    //   $(this)
    // })








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

    $("#maxgrid").data("maxcolumn", Xcellcount + 1)
    $("#maxgrid").data("maxrow", Ycellcount + 1)
    // グリッドのセルの値を指定
    const cellwidth = 150
    const cellheight = 80

    // 空白のセルの値を指定
    const gapcellwidth = 40
    const gapcellheight = 15

    // 画面表示された段階でグリッドを作成
    creategrid(cellwidth, cellheight, gapcellwidth, gapcellheight)

    reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)

    reloadelement()

    // オブジェクトの宣言
    var arrays
    // ワークフローの編集の場合
    // 非同期通信でオブジェクトを取得する
    if ($("#edit").length != 0) {
      $.ajax({
        url: prefix + '/flowobject/' + $("#flow_master_id").val(),
        type: 'get',
        dataType: 'json',
        success: function (response) {
          $("#route").data("routecount", Object.keys(response).length)
          $("#route").attr("data-routecount", Object.keys(response).length)
          arrays = response
        },
        error: function () {
          arrays = {
            "1": ["1_1", "1_2"]
          }
        }

      })
    }
    // 新規登録の場合
    // 初期状態のオブジェクトを作成する
    else {
      arrays = {
        "1": ["1_1", "1_2"]
      }
    }


    // 現在の最新のid番号を格納する
    var nowelementid = 10000

    // 現在の要素をカウントする関数
    nowelementid = nowelementid + nowelementcount()

    //lineのスタートとエンドの位置を格納
    var linedata = [];
    $(document).on('mousedown', function (event) {

      var mousedownTarget = $(event.target).closest('.e')

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
          // 新規追加予定場所にすでに要素があった場合
          // 候補カラムを右に一つずらす
          if ($('#' + gridcolumn + "_" + gridrow).length != 0) {
            gridcolumn = gridcolumn + 1
          }
          // 新規追加予定場所が縦線の通過点である場合
          // 候補カラムを右に一つずらす
          else if ($('.line[data-endcolumn="' + gridcolumn + '"]').length != 0) {
            var lineresult = $('.line').filter(function () {
              return $(this).data('startcolumn') == gridcolumn && $(this).data('endcolumn') == gridcolumn && $(this).data('startrow') < gridrow && $(this).data('endrow') > gridrow;
            });
            if (lineresult.length != 0) {
              gridcolumn = gridcolumn + 1
            }
            else {
              status = true
            }
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
            arrays = searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3], arrays)
            console.log(arrays)
          }
          // 真横もしくは上の要素につないだ時
          // 影響を及ぼす線と要素のinputタグを変更して描画しなおす
          else {
            // 線と要素を変更してarraysの最新情報を返すので、代入する
            arrays = change_line_element(linedata[0], linedata[1], linedata[2], linedata[3], arrays)
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
        modifygrid(linedata[2], linedata[3], cellwidth, cellheight, gapcellwidth, gapcellheight)
        // 線のインプットタグを追加・要素の作成
        makeinputline(linedata[0], linedata[1], linedata[2], linedata[3])
        reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)

        $("#" + linedata[0] + '_' + linedata[1]).removeClass("last")
        $("#" + linedata[2] + '_' + linedata[3]).addClass("last")
        $(".last").each(function () {
          console.log($(this).attr("id"))
        })
        // 要素のinputタグを作成
        makeinputelement(linedata[2], linedata[3], nowelementid)
        // 最新の要素idを1増やす
        nowelementid = nowelementid + 1
        // 要素を再生成
        reloadelement()
        arrays = searchAndUpdateArrays(linedata[0] + "_" + linedata[1], linedata[2] + "_" + linedata[3], arrays)
        console.log(arrays)
        // 新規作成の際にはidがかぶってしまい直後のfocusクラスの付与が正常に動かないため
        // ここで明示的にdクラスの要素を消去する
        $('.d').remove()
        $("#" + linedata[2] + '_' + linedata[3]).addClass("focus")
        focus_right_side_menu(linedata[2], linedata[3])
        person_required_number_reload()
      }
      $('.e').removeClass("blue")
      $('.d').remove()

    });




    $('#workflowform').on("submit", function (event) {
      event.preventDefault()

      //メタ情報の必須項目のエラーチェックを行うerrorcheck()ではエラーがあればtrueを返す
      // またエラーメッセージなども表示する
      if (!errorcheck()) {
        // 個人のユーザーを配列に格納していく
        var userarray = []
        $(".person").each(function () {
          userarray.push($(this).data("person_name"))
        })
        $.ajax({
          url: prefix + '/flowusercheck',
          method: 'GET',
          data: {
            userarray: userarray
          },
          success: function (response) {
            if (response[0]) {
              alert("ユーザー名が重複しています。")
            }
            if (response[1]) {
              alert(response[2] + "というユーザーが登録されていません")
            }
            // エラーがない場合
            // 送信
            if (!response[0] && !response[1]) {


              // -------------- arraysのinputタグを作成する-------------------------------

              var uniqueLastElements = new Set(); // 重複を許容しないSetを使用
              $('.arrayinfo').remove()
              $.each(arrays, function (key, values) {
                $.each(values, function (index, value) {
                  $('<input>', {
                    type: 'hidden',
                    class: 'arrayinfo',
                    name: 'array' + key + '[]',
                    value: value
                  }).appendTo('.element_input');
                });
                var lastElement = values[values.length - 1];
                uniqueLastElements.add(lastElement);
              });
              $('<input>', {
                type: 'hidden',
                class: 'arrayinfo',
                name: 'arraycount',
                value: Object.keys(arrays).length
              }).appendTo('.element_input');
              $('<input>', {
                type: 'hidden',
                class: 'arrayinfo',
                name: 'lastelementcount',
                value: uniqueLastElements.size
              }).appendTo('.element_input');


              // -----------------------------------------------------------------------------


              // フォームのデータを取得する
              var formData = new FormData($("#workflowform")[0]);
              formData.append('arrays', JSON.stringify(arrays));

              var elements = {}

              $('.element').each(function () {
                var elementinfo_id = $(this).attr("id")
                var elementinfo = {}
                elementinfo.point = $(this).data('column') + "_" + $(this).data('row')
                elementinfo.authorizer = $(this).data('authorizer')
                if ($(this).data('authorizer') == "person") {
                  elementinfo.parameter = $(this).data('person_parameter')
                  // もし全員承認の場合は母数を数値として代入する
                  if ($(this).data('person_required_number') == "all") {
                    elementinfo.required_number = elementinfo.parameter
                  }
                  else {
                    elementinfo.required_number = $(this).data('person_required_number')
                  }
                }
                else if ($(this).data('authorizer') == "group") {
                  elementinfo.parameter = $(this).data('group_parameter')
                  elementinfo.required_number = $(this).data('group_required_number')
                }
                if (elementinfo.parameter >= elementinfo.required_number) {
                  elementinfo.approvable = true
                }
                else {
                  elementinfo.approvable = false
                }

                elementinfo.select_method = $(this).data('select_method')
                var person_array = []
                $('.person[data-id="' + elementinfo_id + '"]').each(function () {
                  person_array.push($(this).data("person_name"))
                })
                elementinfo.person_name = person_array
                var group_id = $('.group[data-id="' + elementinfo_id + '"]').data("group_id")
                elementinfo.group_id = group_id
                var choice_number = $('.byapplicant[data-id="' + elementinfo_id + '"]').data("group_choice_number")
                elementinfo.group_choice_number = choice_number
                var positionarray = []
                $('.post[data-id="' + elementinfo_id + '"]').each(function () {
                  positionarray.push($(this).data("positionid"))
                })
                elementinfo.position = positionarray
                elements[elementinfo_id] = elementinfo;
              })
              formData.append('elements', JSON.stringify(elements));
              $.ajax({
                url: prefix + '/workflowregist',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                  'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                },
                success: function (response) {
                  window.location.href = prefix + '/workflow/master'
                },
                error: function () {
                  console.error('laravelエラー');
                }

              })
            }
          },
          error: function () {
            alert("予期せぬエラーが発生しました。")
          }
        });
      }


    })
  }

  // -------------ワークフロー申請----------------

  $('.flow_application_droparea').on('dragover', function (event) {
    event.preventDefault();
    $(this).addClass("dragover");
  });
  $('.flow_application_droparea').on('dragleave', function (event) {
    event.preventDefault();
    $(this).removeClass("dragover");
  });

  $('.flow_application_droparea').on('drop', function (event) {
    event.preventDefault();
    $(this).removeClass("dragover");
    var File = event.originalEvent.dataTransfer.files[0];
    $('#file').prop("files", event.originalEvent.dataTransfer.files);
    // ファイルのタイプを取得
    var fileType = File.type;

    // 画像をプレビューとして表示する
    if (fileType.startsWith("image/")) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('.flow_application_preview_container').html('<img src="' + e.target.result + '" class="">');
        // $('.previewarea').addClass("previewopen");
      };
      reader.readAsDataURL(File);
      $('.flow_application_preview_button').show()

    }
    // PDFをプレビューとして表示する
    else if (fileType === "application/pdf") {
      var pdfUrl = URL.createObjectURL(File);
      var embed = $('<embed>');
      embed.attr('src', pdfUrl);
      embed.attr('width', '100%');
      embed.attr('height', '100%'); // 適切な高さを指定

      $('.flow_application_preview_container').html(embed);
      // $('.previewarea').addClass("previewopen");
      $('.flow_application_preview_button').show()

    }
    else {
      $('.flow_application_preview_button').hide()
    }
  });

  // ドラッグアンドドロップではなくクリックからファイルを選択して
  // ファイルを変更したとき
  $('#file').change(function () {
    var input = this;

    if (input.files && input.files[0]) {
      if (this.files[0].type.startsWith("image/")) {
        var reader = new FileReader();

        reader.onload = function (e) {
          $('.flow_application_preview_container').html('<img src="' + e.target.result + '" class="previewImage">');
          // $('.previewarea').addClass("previewopen");
        };


        reader.readAsDataURL(this.files[0]);
        $('.flow_application_preview_button').show()
      }
      // PDFをプレビューとして表示する
      else if (this.files[0].type === "application/pdf") {
        var pdfUrl = URL.createObjectURL(this.files[0]);
        var embed = $('<embed>');
        embed.attr('src', pdfUrl);
        embed.attr('width', '100%');
        embed.attr('height', '100%'); // 適切な高さを指定

        $('.flow_application_preview_container').html(embed);
        // $('.previewarea').addClass("previewopen");
        $('.flow_application_preview_button').show()
      }

      else {
        $('.flow_application_preview_button').hide()
      }


    }

  });

  // プレビューボタンを押したとき
  $(".flow_application_preview_button").on("click", function () {
    $('.flow_application_preview_container').show()
    $('.flow_application_gray').show()
  })

  // grayエリアをクリックしてプレビューを閉じるとき
  $(".flow_application_gray").on("click", function () {
    $('.flow_application_preview_container').hide()
    $('.flow_application_gray').hide()
  })

  // フロー申請画面にてつぎへボタンを押してフォーム送信をする場合
  $(".flow_application_form").on("submit", function () {
    flow_application_required_check()
  });











  // フロー選択
  $('.flow_choice_select').on('change', function () {
    // グリッドのセルの値を指定
    const cellwidth = 120
    const cellheight = 60

    // 空白のセルの値を指定
    const gapcellwidth = 10
    const gapcellheight = 10
    var flow_id = $(this).val()
    $.ajax({
      url: prefix + '/viewonlyworkflow/' + flow_id,
      type: 'get',
      dataType: 'json',
      success: function (response) {
        $("#maxgrid_column").val(response[1])
        $("#maxgrid_row").val(response[2])
        console.log(response)
        createviewgrid(cellwidth, cellheight, gapcellwidth, gapcellheight)
        view_create_element(response[0])
        view_create_line(response[3], cellwidth, cellheight, gapcellwidth, gapcellheight)
        view_create_approval(response[4])
      },
      error: function () {
      }

    })



  })

  // 確認画面
  if ($("#flow_confirm").length != 0) {
    // グリッドのセルの値を指定
    const cellwidth = 120
    const cellheight = 60

    // 空白のセルの値を指定
    const gapcellwidth = 10
    const gapcellheight = 10
    var flow_id = $("#flowid").val()
    $.ajax({
      url: prefix + '/viewonlyworkflow/' + flow_id,
      type: 'get',
      dataType: 'json',
      success: function (response) {
        $("#maxgrid_column").val(response[1])
        $("#maxgrid_row").val(response[2])
        console.log(response)
        createviewgrid(cellwidth, cellheight, gapcellwidth, gapcellheight)
        view_create_element(response[0])
        view_create_line(response[3], cellwidth, cellheight, gapcellwidth, gapcellheight)
        view_create_approval(response[4])
      },
      error: function () {
      }

    })
  }



  // ---------申請一覧--------------------

  $('.flow_tab').on("click", function () {
    $('.tab_focus').removeClass('tab_focus')
    $(this).addClass('tab_focus')
  })

});