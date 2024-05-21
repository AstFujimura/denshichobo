$(document).ready(function () {

  var prefix = $('#prefix').val();

  // ワークフローマスタ登録・編集画面の時
  if ($('#edit').length != 0 || $('#regist').length != 0) {
    // 画面表示時に画面サイズを取得してgridcontainerの幅を修正
    grid_resize()
    $(window).on("resize", function () {
      // 画面サイズ変更時に画面サイズを取得してgridcontainerの幅を修正
      grid_resize()
    });
    function grid_resize() {
      var screenWidth = $(window).width();
      var grid_max_width = screenWidth - 715
      $('.grid_container').css({
        "max-width": grid_max_width + "px"
      })
    }


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

    // 要素の消去
    $(document).on('click', '.e_delete', function () {
      arrays = e_delete($(this).parent().data('column'), $(this).parent().data('row'), arrays)
      reloadelement()
      reloadline(cellwidth, cellheight, gapcellwidth, gapcellheight)
      $("#route").data("routecount", Object.keys(arrays).length)
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


    var scale = 1
    // マスター登録画面の拡大縮小ボタンを押したとき
    $('.zoom').on('click', function () {
      if ($(this).attr("id") == "zoom_in") {
        scale += 0.1
      }
      else if ($(this).attr("id") == "zoom_out") {
        scale -= 0.1
      }
      console.log(scale)
      scalechange(scale)
    })
    function scalechange(scale) {
      var translate_percent = (1 - scale) * -50;
      $(".grid").css({
        'transform': 'scale(' + scale + ')',
        'transform-origin': ' top left'
      })
    }



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
    const cellwidth = 130
    const cellheight = 60

    // 空白のセルの値を指定
    const gapcellwidth = 30
    const gapcellheight = 10

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



  // --------ワークフローマスタ一覧---------------------


  $('.flow_master_delete_button').on('click', function () {
    var flowname = $(this).parent().parent().find('.flow_master_flowname').text()
    if (confirm("「" + flowname + "」を削除します。よろしいですか。")) {
      window.location.href = $(this).data("location")
    }
  })





















  // -------------ワークフロー申請----------------

  $('.category_input').on('change', function () {
    $.ajax({
      url: prefix + '/workflow/category/info/' + $(this).val(),
      type: 'get',
      dataType: 'json',
      success: function (response) {
        $('.flow_application_area').html('')
        $.each(response, function (index, array) {
          application_input_item(array)

        })

        $('.application_form_date').datepicker({
          changeMonth: true,
          changeYear: true,
          duration: 300,
          showAnim: 'show',
          showOn: 'button', // 日付をボタンクリックでのみ表示する
          buttonImage: prefix + '/img/calendar_2_line.svg', // カスタムアイコンのパスを指定
          buttonImageOnly: true, // テキストを非表示にする
        })


      },
      error: function () {
      }

    })
  })
  //ファイルが登録されたときにその項目のIDをkeyにイメージを含んだ要素をimgobjectに格納する
  var imgobject = {};

  $(document).on('dragover', '.flow_application_droparea', function (event) {
    event.preventDefault();
    $(this).addClass("dragover");
  });
  $(document).on('dragleave', '.flow_application_droparea', function (event) {
    event.preventDefault();
    $(this).removeClass("dragover");
  });

  $(document).on('drop', '.flow_application_droparea', function (event) {
    event.preventDefault();
    var item_id = $(this).find('.file_input').data('id')
    $(this).removeClass("dragover");
    var File = event.originalEvent.dataTransfer.files[0];
    $(this).find(".file_input").prop("files", event.originalEvent.dataTransfer.files);
    // ファイルのタイプを取得
    var fileType = File.type;

    // 画像をプレビューとして表示する
    if (fileType.startsWith("image/")) {
      var reader = new FileReader();
      reader.onload = function (e) {
        imgobject[item_id] = '<img src="' + e.target.result + '" class="">'
        // $('.flow_application_preview_container').html();
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

      imgobject[item_id] = embed
      // $('.flow_application_preview_container').html(embed);

      $(this).parent().find('.flow_application_preview_button').show()

    }
    else {
      $(this).parent().find('.flow_application_preview_button').hide()
    }
  });

  // ドラッグアンドドロップではなくクリックからファイルを選択して
  // ファイルを変更したとき
  $(document).on('change', '.file_input', function () {
    var input = this;
    var item_id = $(this).data('id')

    if (input.files && input.files[0]) {
      if (this.files[0].type.startsWith("image/")) {
        var reader = new FileReader();

        reader.onload = function (e) {
          imgobject[item_id] = '<img src="' + e.target.result + '" class="">'
          // $('.flow_application_preview_container').html('<img src="' + e.target.result + '" class="previewImage">');
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
        imgobject[item_id] = embed
        // $('.flow_application_preview_container').html(embed);

        $(this).parent().parent().find('.flow_application_preview_button').show()
      }

      else {
        $(this).parent().parent().find('.flow_application_preview_button').hide()
      }


    }

  });

  // プレビューボタンを押したとき
  $(document).on("click", ".flow_application_preview_button", function () {
    $('.flow_application_preview_container').show()
    $('.flow_application_gray').show()
    var item_id = $(this).data('id')
    console.log(imgobject)
    $('.flow_application_preview_container').html(imgobject[item_id])
    $('.flow_application_gray').show()
  })

  // grayエリアをクリックしてプレビューを閉じるとき
  $(".flow_application_gray").on("click", function () {
    $('.flow_application_preview_container').hide()
    $('.flow_application_gray').hide()
  })

  // エンターを押して次のフォームのフォーカスに移る
  $(document).on("keydown", '.application_form_text', function (event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      if (!$(this).hasClass('text_area_content')) {
        event.preventDefault(); // デフォルトのエンターキーの動作を無効化


        var currentIndex = $('.application_form_text').index(this);
        var nextInput = $('.application_form_text').eq(currentIndex + 1);

        if (nextInput.length === 0) {
          $('.flow_application_form').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される

        } else {
          nextInput.focus(); // 次の入力欄にフォーカスを移動
        }
      }

    }
  });

  $(document).on('blur', '.application_form_date', function () {
    flow_application_date_format($(this))
  })

  // フロー申請画面にてつぎへボタンを押してフォーム送信をする場合
  $(".flow_application_form").on("submit", function (e) {
    e.preventDefault()
    // 必須項目のチェックを行う
    if (!flow_application_required_check()) {
      pointer_img_create()
      this.submit()

    }
  });







  // -----------経路選択------------------


  if ($("#flow_choice").length != 0) {
    if ($('.flow_choice_select').val() != "") {
      viewonlyworkflow(prefix, $('.flow_choice_select').val())
      $('.unselected').hide()
      $('.selected').show()
    }
    else {
      $('.unselected').show()
      $('.selected').hide()
    }
  }



  // フロー選択
  $('.flow_choice_select').on('change', function () {
    viewonlyworkflow(prefix, $(this).val())
    if ($('.flow_choice_select').val() != "") {
      $('.unselected').hide()
      $('.selected').show()
    }
    else {
      viewonlyreset()
      $('.unselected').show()
      $('.selected').hide()
    }
  })

  $('#flow_application_choice_form').on('submit', function (e) {
    e.preventDefault()
    if ($('.flow_choice_select').val() != "") {
      this.submit()
    }
    else {
      alert('経路を選択してください')
    }
  })



  // -----------確認画面------------------
  if ($("#flow_confirm").length != 0) {
    // グリッドのセルの値を指定
    const cellwidth = 120
    const cellheight = 60

    // 空白のセルの値を指定
    const gapcellwidth = 10
    const gapcellheight = 10
    var m_flow_id = $("#m_flow_id").val()
    $.ajax({
      url: prefix + '/viewonlyworkflow/' + m_flow_id,
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

    $.ajax({
      url: prefix + '/viewonlymetaworkflow/' + m_flow_id,
      type: 'get',
      dataType: 'json',
      success: function (response) {
        $('.view_condition_start_price_value').text(response["startprice"])
        $('.view_condition_end_price_value').text(response["endprice"])
        group_object_create(response["group_objects"]);
        console.log(response)
      },
      error: function () {
      }

    })

    // プレビューボタンを押したとき
    $(".approve_preview_button").on("click", function () {
      $('.approve_preview_container').show()
      $('.approve_gray').show()
      var ID = $(this).data("id");
      preview_img_get(prefix, ID,"t_flow_after")

    })
  }

  $('.condition_accordion_trigger').on('click', function () {
    $('.condition_accordion').toggleClass('condition_accordion_close')
  })


  // ----------申請印押印--------承認印押印-----------
  if ($('#applicationstamp').length != 0 || $('#approvestamp').length != 0) {

    if ($('#applicationstamp').length != 0) {
      var status = "application"
    }
    else if ($('#approvestamp').length != 0) {
      var status = "approve"
    }

    var ID = $('#t_flow_id').val()
    approval_setting_pdf(prefix, ID, status)
    var user_id = $('#user_id').val()
    if ($('#server').val() == "cloud") {
      $.ajax({
        url: prefix + '/workflow/stamp/img/' + user_id, // データを取得するURLを指定
        method: 'GET',
        cache: false, // キャッシュを無効にする
        dataType: "json",
        success: function (response) {
          if (response.Type.startsWith('image/')) {
            var img = $('<img>');
            img.attr('src', response.path);
            img.attr('width', '100%');
            img.attr('height', '600px');
            img.addClass('origin');

            $('#inputs').append(img);
          }
        }
      });
    }
    else {
      $.ajax({
        url: prefix + '/workflow/stamp/img/' + user_id, // データを取得するURLを指定
        method: 'GET',
        cache: false, // キャッシュを無効にする
        xhrFields: {
          responseType: 'blob' // ファイルをBlobとして受け取る
        },
        success: function (response) {
          // 取得したファイルデータを使ってPDFを表示
          var Url = URL.createObjectURL(response);
          if (response.type.startsWith('image/')) {
            var img = $('<img>');
            img.attr('src', Url);
            // img.attr('height', '600px');
            img.addClass('origin');

            $('#inputs').append(img);
          }


        },
        error: function (xhr, status, error) {
          console.error(error); // エラー処理
        }
      });

    }


    var dragging = false; // ドラッグ中かどうかのフラグ
    var offsetX, offsetY; // ドラッグ開始位置と要素の左上端との差
    var nowX = 0, nowY = 0; // 現在の要素の相対位置を保持
    var clicked = false

    var width, height, pdf_canvas_width, pdf_canvas_height
    var scrollX,scrollY

    $(document).on('click', '.canvas_container', function (event) {
      if (!clicked) {
        offsetX = canvas_offset("x")
        offsetY = canvas_offset("y")
        nowX = event.pageX - offsetX - 18
        nowY = event.pageY - offsetY - 18
        

        width = $('#width').val()
        height = $('#height').val()
        pdf_canvas_width = $(".pdf_canvas").width()
        pdf_canvas_height = $(".pdf_canvas").height()
        var clonedElement = $('.origin').first().clone()
        clonedElement.addClass('application_stamp')
        clonedElement.removeClass('origin')
        clonedElement.css({
          left: nowX,
          top: nowY,
          width: 9.5 / width * 100 + "%"
        });
        $(this).append(clonedElement)
        clicked = true
        $('#left').val(nowX * width / pdf_canvas_width)
        $('#top').val(nowY * width / pdf_canvas_height)
      }

    })


    // 要素をクリックしたときの処理
    $(document).on("mousedown", ".application_stamp", function (event) {
      event.preventDefault(); // ブラウザのデフォルトのドラッグ動作を停止
      var position = $(this).parent();
      offsetX = canvas_offset("x")
      offsetY = canvas_offset("y")
      dragging = true; // ドラッグ開始
      $(this).data("dragging", true)
      $(this).attr("data-dragging", true)
    })
    // ドラッグ中の処理
    $(document).on("mousemove", function (event) {

      if (dragging) {
        // ドラッグ中の座標を取得し、要素を移動
        offsetX = canvas_offset("x")
        offsetY = canvas_offset("y")
        nowX = event.pageX - offsetX - 18;
        nowY = event.pageY - offsetY - 18;
        $(".application_stamp").css({ left: nowX, top: nowY });
      }
    })
    // ドラッグ終了時の処理
    $(document).on("mouseup", function () {
      if (dragging) {
        dragging = false; // ドラッグ終了
        $(".application_stamp").data("dragging", false)
        $(".application_stamp").attr("data-dragging", false)
        $(".application_stamp").css({ cursor: "default", opacity: 1 }); // スタイルを元に戻す
        $('#left').val(nowX * width / pdf_canvas_width)
        $('#top').val(nowY * height / pdf_canvas_height)
      }
    });


  }



  // ---------申請一覧--------------------

  $('.flow_tab').on("click", function () {
    $('.tab_focus').removeClass('tab_focus')
    $(this).addClass('tab_focus')

    var tabname = $(this).data("tabname")
    $('.open_tab').removeClass('open_tab')
    $('.' + tabname).addClass('open_tab')
    $('#status').val(tabname)
  })

  // 承認画面
  if ($("#approve_phase").length != 0) {
    // グリッドのセルの値を指定
    const cellwidth = 120
    const cellheight = 60

    // 空白のセルの値を指定
    const gapcellwidth = 10
    const gapcellheight = 10
    var m_flow_id = $("#m_flow_id").val()
    $.ajax({
      url: prefix + '/viewonlyworkflow/' + m_flow_id,
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

        add_status_message()
      },
      error: function () {
      }

    })

    if ($('#stamp_status').val() == "true") {
      $('.unselected').hide()
      $('.selected').show()
    }
    else {
      $('.unselected').show()
      $('.selected').hide()
    }

  }

  // -------承認一覧----------------------
  if ($('.flow_search_box').length != 0) {

    // セレクトボックスのデフォルトの値を合わせる
    $('select').each(function () {
      var data_id = $(this).data("id")
      if (data_id) {
        $(this).find('option[value=' + data_id + ']').attr('selected', true)
      }


    })

    $('.search_form_date').datepicker({
      changeMonth: true,
      changeYear: true,
      duration: 300,
      showAnim: 'show',
      showOn: 'button', // 日付をボタンクリックでのみ表示する
      buttonImage: prefix + '/img/calendar_2_line.svg', // カスタムアイコンのパスを指定
      buttonImageOnly: true, // テキストを非表示にする
    })
    $(document).on('blur', '.search_form_date', function () {
      flow_application_date_format($(this))
    })
    $('.flow_search_input').keydown(function (event) {
      if (event.keyCode === 13) { // エンターキーのキーコードは 13
        event.preventDefault(); // デフォルトのエンターキーの動作を無効化


        var currentIndex = $('.flow_search_input').index(this);
        var nextInput = $('.flow_search_input').eq(currentIndex + 1);

        if (nextInput.length === 0) {
          // 申請日が最後のためエンターを押したときにフォーカスが外れる前に
          // submitされてしまうため明示的にフォーカスを外して日付のフォーマットを行う
          $('.flow_search_input').blur()
          $('#search_form').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される

        } else {
          nextInput.focus(); // 次の入力欄にフォーカスを移動
        }
      }
    });
  }


  // -------承認----------------------

  //ダウンロードボタンを押したとき
  $('.approve_download').on('click', function () {
    window.location.href = $(this).attr("id")
  });

  // プレビューボタンを押したとき
  $(".approve_preview_button").on("click", function () {
    $('.approve_preview_container').show()
    $('.approve_gray').show()
    var ID = $(this).data("id");
    var type = $(this).data("type")
    preview_img_get(prefix, ID,type)

  })

  // grayエリアをクリックしてプレビューを閉じるとき
  $(".approve_gray").on("click", function () {
    $('.approve_preview_container').hide()
    $('.approve_gray').hide()
  })

  // 承認画面の場合
  if ($('#approve_phase').length != 0) {
    // 承認状況のリストをホバーした時にviewの要素の背景色を変化させる
    $('.approve_condition_tbody_tr').hover(
      function () {
        // マウスが要素に入ったときの処理
        // 例：要素の背景色を変更する
        $(this).addClass('approve_hover');
        var front_point = $(this).data("front_point")
        $("#" + front_point).addClass("approve_hover")
      },
      function () {
        // マウスが要素から出たときの処理
        // 例：要素の背景色を元に戻す
        $(this).removeClass('approve_hover');
        var front_point = $(this).data("front_point")
        $("#" + front_point).removeClass("approve_hover")
      }
    )

    // 逆もしかり、viewの要素の背景色をホバーしたときにリストの背景色を変化させる
    // eは動的に作成した要素であるため違う書き方をしている
    $(document).on('mouseenter', '.e', function () {
      // マウスが要素に入ったときの処理
      $(this).addClass('approve_hover');
      var front_point = $(this).attr("id");
      $('[data-front_point="' + front_point + '"]').addClass("approve_hover");
    });

    $(document).on('mouseleave', '.e', function () {
      // マウスが要素から出たときの処理
      $(this).removeClass('approve_hover');
      var front_point = $(this).attr("id");
      $('[data-front_point="' + front_point + '"]').removeClass("approve_hover");
    });

    // // 「承認する」ボタンを押したとき
    // $("#approvalbutton").on("click", function () {
    //   $("#result").val("approve");
    //   if (confirm("申請を承認します。よろしいですか")) {
    //     $("#approve_form").submit();
    //   }
    // })
    // 「承認印を押す」ボタンを押したとき
    $("#stamp_approvalbutton").on("click", function () {
      $("#result").val("stamp_approve");
      var comment = $("#approvecomment").val();
      $("#result").val("stamp_approve");
      window.location.href = prefix + "/workflow/approval/stamp/" + $('#t_approval_id').val() + "?comment=" + comment
    })
    // // 「却下する」ボタンを押したとき
    // $("#rejectbutton").on("click", function () {
    //   $("#result").val("reject");
    //   if (confirm("申請を却下します。よろしいですか")) {
    //     $("#approve_form").submit();
    //   }
    // })




  }



  // ---------メール設定--------------------
  // エンターを押して次のフォームのフォーカスに移る
  $('.mail_setting_form_text').keydown(function (event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化


      var currentIndex = $('.mail_setting_form_text').index(this);
      var nextInput = $('.mail_setting_form_text').eq(currentIndex + 1);

      if (nextInput.length === 0) {
        $('#mail_setting_post').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される

      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });

  $('#mail_setting_mail').on('blur', function () {
    $('#mail_setting_username').val($(this).val())
  })
  $('#mail_setting_post').on('submit', function (e) {
    e.preventDefault()
    if (!mail_setting_required_check('regist')) {
      this.submit()
    }
  })


  $('.test_send_button').on('click', function () {
    if (!mail_setting_required_check('test')) {

      const formData = new FormData();

      //フォームの内容をformdataにappendしてデータの作成
      formData.append('name', $("#mail_setting_name").val()),
        formData.append('mail', $("#mail_setting_mail").val()),
        formData.append('host', $("#mail_setting_host").val()),
        formData.append('port', $("#mail_setting_port").val()),
        formData.append('username', $("#mail_setting_username").val()),
        formData.append('password', $("#mail_setting_password").val()),
        formData.append('test_mail', $("#mail_setting_test_mail").val())

      $.ajax({
        url: prefix + '/workflow/mail/test',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val(),
        },
        success: function (response) {
          alert(response)
        },
        error: function () {
          window.location.href = prefix + '/workflowerror/E53827';
        }

      })
    }
  })


  $('.category_pen_icon').on('click', function () {
    // 表示されているdivを非表示にする
    $(this).parent().find('.category_setting_name').hide()
    var inputelement = $(this).parent().find('.category_setting_input')
    // inputをhiddenからtextに変え、フォーカスを当てる
    inputelement.attr('type', 'text').focus()
    // 文字数を取得して最後にカーソルを移動させる
    var len = inputelement.val().length
    inputelement[0].setSelectionRange(len, len)
  })

  $('.category_approval_setting_icon').on('click', function () {
    // 表示されているdivを非表示にする
    var data_id = $(this).parent().data('category_id')
    window.location.href = prefix + '/workflow/category/approval/setting/' + data_id;
  })

  // フォーカスが外れた時
  $('.category_setting_input').on('blur', function () {
    // 名称に変更があった時
    // かつ空欄でない時
    if ($(this).val() != $(this).parent().find('.category_setting_name').text().trim() && $(this).val() != "") {

      // カテゴリ名が有効である場合
      if (!category_validate_check($(this).val())) {
        // 変更を承認した時
        // 非同期でカテゴリ名を変更する
        if (confirm('「' + $(this).parent().find('.category_setting_name').text().trim() + '」を「' + $(this).val() + '」に変更しますか。')) {
          $.ajax({
            url: prefix + '/workflow/category/change/' + $(this).attr('name') + '/' + $(this).val(),
            type: 'GET',
            processData: false,
            contentType: false,
            success: function (response) {
              if (response == "変更") {
                window.location.href = prefix + '/workflow/category';
              }
              else {
                alert(response)
                window.location.href = prefix + '/workflow/category';
              }
            },
            error: function () {
              window.location.href = prefix + '/workflowerror/E53827';
            }

          })

        }
        // 変更を承認しなかったときinputの文字をもとに戻す
        else {
          $(this).val($(this).parent().find('.category_setting_name').text().trim())
        }
      }
      // カテゴリ名が無効である場合inputの文字をもとに戻す
      else {
        $(this).val($(this).parent().find('.category_setting_name').text().trim())
      }
    }
    // 空欄を元の文字に戻す
    else {
      $(this).val($(this).parent().find('.category_setting_name').text().trim())
    }
    $(this).parent().find('.category_setting_name').show()
    $(this).attr('type', 'hidden')
  })


  // フォーカスに当たった状態でエンターキーが押されたらフォーカスを外す
  $(document).on('keydown', function (event) {
    if (event.which === 13) { // Enter キーが押された場合
      if ($(document.activeElement).hasClass('category_setting_input')) {
        // フォーカスが当たっている場合
        $(document.activeElement).blur()
      }
    }
  });
  //カテゴリ要素をクリックしたとき
  $('.category_setting_content').on('click', function (event) {
    // 名称変更ボタンと承認設定ボタンとインプット要素をクリックしたときは除く
    if ($(event.target).closest('.category_pen_icon').length > 0 || $(event.target).closest('.category_approval_setting_icon').length > 0 || $(event.target).closest('.category_setting_input').length > 0) {
      return;
    }
    window.location.href = prefix + '/workflow/category/detail/' + $(this).data("category_id");
  })


  // カテゴリ詳細編集ページ
  if ($('#category_detail').length != 0) {
    sortable_reload()


    // セレクトボックスのデフォルトの値を合わせる
    $('.category_detail_optional_select').each(function () {
      var data_type = $(this).data("type")
      $(this).find('option[value=' + data_type + ']').attr('selected', true)

    })
    // 型をひとつづつ確認
    $('.type').each(function () {
      max_input_reload($(this), "new")
      price_condition_check($(this))
    })

    // デフォルトの項目を選択不可にする
    // 削除ボタンも消去する
    change_disable()



    // 追加ボタンを押したときの動作
    $('#category_detail_optional_add_button').on('click', function () {
      // 要素をクローンする
      var clonedElement = $('.category_detail_optional_content').first().clone();
      var maxid = $('#optional_max').val()
      clonedElement.data('id', maxid)
      clonedElement.attr('data-id', maxid)

      clonedElement.data('default', 0)
      clonedElement.attr('data-default', 0)
      // インプットのname属性を適切なIDに変更する
      clonedElement.find(".input_element").each(function () {
        var name = $(this).attr("name")
        var replacedString = name.replace(/\d+/g, maxid);
        $(this).attr("name", replacedString)
        // 項目名は空欄にする
        if ($(this).attr('type') == "text") {
          $(this).val('')
        }
      });
      clonedElement.find("label").attr("for", "radio" + maxid)
      clonedElement.find("[name='price']").attr("id", "radio" + maxid)
      clonedElement.find("[name='price']").val(maxid)

      maxid = parseInt(maxid) + 1
      $('#optional_max').val(maxid)

      // クローンされた要素を追加する
      $('.category_detail_sortable').append(clonedElement);
      change_disable()
      // 再度順番並び替え
      change_items_order()
      sortable_reload()
    })

    // 型のセレクトボックスが変更されたときの動作
    $(document).on('change', '.category_detail_optional_type .category_detail_optional_select', function () {
      max_input_reload($(this), "change")
      price_condition_check($(this))
      check_reset($(this))

    })

    $(document).on('click', '.category_detail_optional_delete_button', function () {
      var id = $(this).parent().parent().data('id')
      if (id < 50000) {
        // 既存の項目の削除リストをinputに登録
        delete_items(id)
      }
      $(this).parent().parent().remove()
      // 再度順番並び替え
      change_items_order()
    })
    $(document).on('change', '[name="price"]', function () {
      $select_element = $(this).val()
      $('[name="price"]').each(function () {
        if ($(this).val() != $select_element) {
          $(this).prop("checked", false)
        }
      })
    })
  }


  // -----承認設定------------------------

  if ($('#approval_setting').length != 0) {

    if ($('#approval_setting_issue').prop("checked")) {
      var ID = $('#category_id').val()
      approval_setting_pdf(prefix, ID)
    }

    // 承認用紙を発行するのチェックが変わった時
    $(document).on('change', '#approval_setting_issue', function (event) {
      if ($(this).prop('checked')) {
        $('.approval_setting_droparea').removeClass("display_none");
        $('.approval_setting_detail_button').removeClass("display_none");
        if ($('#approval_setting_file').prop('files').length == 0) {
          $('.approval_setting_detail_button').addClass("disable");
          $('#status').val("error")
        }
      }
      else {
        $('.approval_setting_droparea').addClass("display_none");
        $('.approval_setting_change_file').addClass("display_none");
        $('.approval_setting_detail_button').addClass("display_none");
        $('#status').val("empty")
      }

    });
    // $(document).on('change', '#approval_setting_file', function (event) {
    //   $('.approval_setting_detail_button').removeClass("display_none");
    // });

    $(document).on('dragover', '.approval_setting_droparea', function (event) {
      event.preventDefault();
      $(this).addClass("dragover");
    });
    $(document).on('dragleave', '.approval_setting_droparea', function (event) {
      event.preventDefault();
      $(this).removeClass("dragover");
    });

    $(document).on('change', '#approval_setting_file', function (event) {

      var input = this;

      if (input.files && input.files[0]) {
        if (this.files[0].type === "application/pdf") {
          $('.approval_setting_detail_button').removeClass("disable");
          var file = input.files[0];
          var fileReader = new FileReader();

          fileReader.onload = function () {
            var typedarray = new Uint8Array(this.result);
            displayPdf(typedarray);
          };

          fileReader.readAsArrayBuffer(file);
          $('#status').val('change')
        }
        // PDF以外は受け付けない
        else {
          $(this).val("")
          alert('pdfをインポートしてください')
          $('.approval_setting_detail_button').addClass("disable");
        }

      }
      pointer_reset()

    });
    function pointer_reset() {
      $('.preview_item_batsu').click()
    }

    $(document).on('drop', '.approval_setting_droparea', function (event) {
      event.preventDefault();
      $(this).removeClass("dragover");
      var File = event.originalEvent.dataTransfer.files[0];
      // ファイルのタイプを取得
      var fileType = File.type;
      // PDFがインポートされた場合
      if (fileType === "application/pdf") {
        $(this).find(".file_input").prop("files", event.originalEvent.dataTransfer.files);
        $('.approval_setting_detail_button').removeClass("disable");
        var file = event.target.files[0];
        var fileReader = new FileReader();

        fileReader.onload = function () {
          var typedarray = new Uint8Array(this.result);
          displayPdf(typedarray);
        };

        fileReader.readAsArrayBuffer(file);
        $('#status').val('change')
      }
      // PDF以外は受け付けない
      else {
        alert('pdfをインポートしてください')
        $('.approval_setting_detail_button').addClass("display_none");
      }
      pointer_reset()
    })

    $(document).on('click', ".approval_setting_detail_button", function () {
      $('.approval_setting_detail_container').removeClass("display_none");
      $('[name="pointer_array[]"]').each(function () {
        var pointer_num = $(this).val()
        var pointertext = $('.preview_test_str[data-pointer_id="' + pointer_num + '"]').find('.preview_test_str_input').val()
        var page = $('[name="page' + pointer_num + '"]').val()
        pointer_create(pointer_num, pointertext, page)
        focus_cancel()
      })
    })

    $(document).on('click', ".preview_control_close_button", function () {
      $('.approval_setting_detail_container').addClass("display_none");
    })



    var dragging = false; // ドラッグ中かどうかのフラグ
    var offsetX, offsetY; // ドラッグ開始位置と要素の左上端との差
    var nowX = 0, nowY = 0; // 現在の要素の相対位置を保持
    var width, height, pdf_canvas_width, pdf_canvas_height

    
    $("#category_approval_setting_form").on('submit', function (event) {
      event.preventDefault();
      if (approval_setting_submit_check()) {
        alert('PDFファイルをインポートしてください')
      }
      else {
        if (parseInt($('#width').val()) > parseInt($('#height').val())) {
          $('#p_l').val('L')
        }
        else {
          $('#p_l').val('P')
        }
        this.submit()
      }
    })





    $(document).on('click', '.preview_control_item', function () {


      var pointer_num = $('#pointer_num').val()
      var pointertext = $(this).find(".preview_control_item_title").text();
      // ここは後程変える
      var page = 1

      pointer_input_create($(this).data('optional_id'), pointer_num)
      pointer_create(pointer_num, pointertext, page)


      var item_pointer =
        $(`<div class="preview_test_str" data-pointer_id="` + pointer_num + `">
      <input type="text" class="preview_test_str_input" value="`+ pointertext + `">
      <div class="preview_item_batsu">×</div>
    </div>`)
      item_pointer.insertAfter($(this))



      pointer_num = parseInt(pointer_num) + 1
      $('#pointer_num').val(pointer_num)

    })

    $(document).on('click', '.preview_item_batsu', function () {
      var pointer_id = $(this).parent().data("pointer_id")
      $("[data-pointer_id='" + pointer_id + "']").remove()
    })


    // 要素をクリックしたときの処理
    $(document).on("mousedown", ".optional_item", function (event) {
      width = $('#width').val()
      height = $('#height').val()
      pdf_canvas_width = $(".pdf_canvas").width()
      pdf_canvas_height = $(".pdf_canvas").height()

      event.preventDefault()
      focus_optional_item($(this))
      dragging = true; // ドラッグ開始
      $(this).data("dragging", true)
      $(this).attr("data-dragging", true)
      var element = $(this);
      var position = element.position();
      offsetX = event.pageX - position.left;
      offsetY = event.pageY - position.top;

      // ドラッグ中に選択された要素の振る舞いを変更する場合は、ここにコードを追加

      // ドラッグ中に選択された要素のスタイルを変更する例
      element.css({
        cursor: "move", // カーソルを移動アイコンに変更
        opacity: 0.5 // 要素を半透明にする
      });
    });

    // ドラッグ中の処理
    $(document).on("mousemove", function (event) {
      if (dragging) {
        // ドラッグ中の座標を取得し、要素を移動
        nowX = event.pageX - offsetX;
        nowY = event.pageY - offsetY;
        $(".optional_item[data-dragging='true']").css({ left: nowX, top: nowY });
      }
    });

    // ドラッグ終了時の処理
    $(document).on("mouseup", function () {
      if (dragging) {
        dragging = false; // ドラッグ終了
        var dragging_pointer_id = $(".optional_item[data-dragging='true']").data("pointer_id")
        $(".optional_item[data-dragging='true']").data("dragging", false)
        $(".optional_item[data-dragging='true']").attr("data-dragging", false)
        $(".optional_item").css({ cursor: "default", opacity: 1 }); // スタイルを元に戻す
        
        var top_input = $('input[data-prop="top"][data-pointer_id="' + dragging_pointer_id + '"]');
        var left_input = $('input[data-prop="left"][data-pointer_id="' + dragging_pointer_id + '"]');
        top_input.val(nowY * height / pdf_canvas_height);
        left_input.val(nowX * width / pdf_canvas_width);
      }
    });

    $('.preview_main_container').on('click', function (event) {

      var targetElement = $(event.target);

      var optional_item = targetElement.closest('.optional_item');

      // 要素をクリックしていない場合すべてのフォーカスを解除
      if (optional_item.length == 0) {
        focus_cancel()
      }

    })

    $("#font_size_input").on('change', function () {
      change_font_size()
    })


  }


  // ----------印鑑設定-------------------
  if ($('#stamp_setting').length != 0) {

    var fonturl = $('#font').val()

    // var font = new FontFace('HGR', 'url(' + fonturl + ')')
    // font.load().then(function (loadedFont) {
    //   document.fonts.add(loadedFont);
    var canvas = $("<canvas>").attr("id", "stamp_canvas").attr("class", "stamp_canvas").attr("width", 400).attr("height", 400);
    var context = canvas[0].getContext("2d"); // 2Dコンテキストを取得
    var centerX = canvas[0].width / 2; // 中心のX座標
    var centerY = canvas[0].height / 2; // 中心のY座標
    var radius = 195; // 円の半径
    var startAngle = 0; // 開始角度
    var endAngle = Math.PI * 2; // 終了角度（360度）
    var anticlockwise = false; // 反時計回り

    context.beginPath(); // パスの開始
    context.arc(centerX, centerY, radius, startAngle, endAngle, anticlockwise); // 円を描く
    context.strokeStyle = 'red'; // 塗りつぶしの色を赤に設定
    context.lineWidth = 10; // 輪郭の幅を設定
    context.stroke(); // 輪郭を描く
    context.closePath(); // パスの終了
    context.save();
    // context.font = "245px HGR";
    // context.fillStyle = "red"
    // context.scale(1, 0.8); // 水平方向に拡大、垂直方向に縮小
    // context.textBaseline = "top";
    // context.fillText('藤', centerX - 100, 0)
    // context.fillText('村', centerX - 122, 240)
    $(".flow_stamp_preview").append(canvas[0])



    // Canvasを画像ファイルとして保存
    // var imageData = canvas[0].toDataURL("image/png"); // PNG形式で画像データを取得
    // var link = document.createElement('a'); // a要素を作成
    // link.download = 'canvas_image.png'; // ダウンロード時のファイル名を指定
    // link.href = imageData; // 画像データをリンク先に設定
    // link.click(); // リンクをクリックしてダウンロードを開始


    // 画面表示時に入力された文字に関するプロパティとプレビューを作成
    str_container_create()
    // その後input情報をもとにプレビューのリロード
    stamp_preview_reload()

    $('.flow_stamp_bold_button').on('click', function () {

    })

    $('.flow_stamp_lettter_change_button').on('click', function () {
      str_container_create()

    })
    $(document).on("input", '.stamp_slider', function () {
      stamp_preview_reload()
    })
    $('.flow_stamp_font_select').on('change', function () {
      stamp_preview_reload()

    })
    $("#stamp_regist").on('submit', function (event) {

      event.preventDefault()

      create_stamp_img().then(function (imageData) {
        $("#stamp_img").val(imageData);
        $("#stamp_regist")[0].submit();
      })
    })
  }
});
