$(document).ready(function () {

  var prefix = $('#prefix').val();
  $('.schedule_input_date').datepicker({
    changeMonth: true,
    changeYear: true,
    duration: 300,
    showAnim: 'show',
    showOn: 'button', // 日付をボタンクリックでのみ表示する
    buttonImage: prefix + '/img/calendar_2_line.svg', // カスタムアイコンのパスを指定
    buttonImageOnly: true, // テキストを非表示にする
  })
  $('.search_date').datepicker({
    changeMonth: true,
    changeYear: true,
    duration: 300,
    showAnim: 'show',
    showOn: 'button', // 日付をボタンクリックでのみ表示する
    buttonImage: prefix + '/img/calendar_2_line.svg', // カスタムアイコンのパスを指定
    buttonImageOnly: true, // テキストを非表示にする
  })

  // -----------------------------スケジュール----------------
  $('.schedule_group_select').on('change', function () {
    var selected_group_id = $(this).val()
    var group_type = $(this).find('option[value="' + selected_group_id + '"]').data('group_type')
    if (group_type == 'mygroup') {
      window.location.href = prefix + '/schedule?selected_group_id=' + selected_group_id + '&group_type=mygroup'
    }
    else {
      window.location.href = prefix + '/schedule?selected_group_id=' + selected_group_id + '&group_type=commongroup'
    }
  })
  $('.search_date').on('change', function () {
    var search_date = $(this).val()
    var selected_group_id = $('#selected_group_id').val()
    window.location.href = prefix + '/schedule?selected_group_id=' + selected_group_id + '&base_date=' + search_date
  })

  // -----------------------------月スケジュール----------------
  $('.month_user_select').on('change', function () {
    var selected_user_id = $(this).val()
    window.location.href = prefix + '/schedule/month?user_id=' + selected_user_id
  })


  // -------------スケジュール登録----------------
  if ($('#schedule_regist').length != 0) {
    // 登録ボタンを押したら、チェックをして、エラーがなかったら登録
    $('#regist_button').on('click', function () {
      if (!regist_check()) {
        $('#schedule_regist_form').submit()
      }

    })
    // 登録チェック
    function regist_check() {
      var error = false
      $('.errortextbox').removeClass('errortextbox')
      $('[data-required="true"]').each(function () {
        if ($(this).val() == '') {
          error = true
          $(this).addClass('errortextbox')
        }
      })
      if (error) {
        alert('未入力の項目があります')
        return error
      }
      // 次に時刻の設定のチェック
      var start_time_hour = $('#start_time_hour').val()
      var start_time_minute = $('#start_time_minute').val()
      var end_time_hour = $('#end_time_hour').val()
      var end_time_minute = $('#end_time_minute').val()
      // 開始時間の時間が未入力だったら、開始時間の時間と分を未入力にする
      if (start_time_hour == '-' || start_time_minute == '-') {
        $('#start_time_hour').val('-')
        $('#start_time_minute').val('-')
      }
      // 終了時間の時間が未入力だったら、終了時間の時間と分を未入力にする
      if (end_time_hour == '-' || end_time_minute == '-') {
        $('#end_time_hour').val('-')
        $('#end_time_minute').val('-')
      }
      if (parseInt(start_time_hour) > parseInt(end_time_hour)) {
        alert('時刻の設定が不正です')
        error = true
      }
      else if (parseInt(start_time_minute) > parseInt(end_time_minute)) {
        alert('時刻の設定が不正です')
        error = true
      }

      // 次に参加者が一人以上いるかどうかのチェック
      if ($('#inputs').find('input[name="user_id[]"]').length == 0) {
        alert('参加者が一人以上いる必要があります')
        error = true
      }
      return error
    }



    // 削除ボタンを押したら、チェックをして、エラーがなかったら削除
    $('.delete_button').on('click', function () {
      if (confirm('本当に削除しますか？')) {
        $('#delete_flag').val('true')
        $('#schedule_regist_form').submit()
      }
    })
  }

  // -----------------------------個人グループ作成ページ----------------
  if ($('#schedule_group_regist').length != 0) {
    $('#regist_button').on('click', function () {
      if (!schedulegroupregist_check()) {
        $('#schedule_group_regist_form').submit()
      }
    })
    $('#delete_button').on('click', function () {
      if (confirm('本当に削除しますか？')) {
        $('#delete_flag').val('true')
        $('#schedule_group_regist_form').submit()
      }
    })


    // 登録チェック
    function schedulegroupregist_check() {
      var error = false
      $('.errortextbox').removeClass('errortextbox')
      $('[data-required="true"]').each(function () {
        if ($(this).val() == '') {
          error = true
          $(this).addClass('errortextbox')
        }
      })
      if (error) {
        alert('未入力の項目があります')
        return error
      }
      //参加者が一人以上いるかどうかのチェック
      if ($('#inputs').find('input[name="user_id[]"]').length == 0) {
        alert('参加者が一人以上いる必要があります')
        error = true
      }
      return error
    }
  }

  // -----------------------------繰り返し登録ページ----------------
  if ($('#schedule_regular_regist').length != 0) {
    regular_frequency_reload()
    $('#regular_frequency').on('change', function () {
      regular_frequency_reload()
    })
    function regular_frequency_reload() {
      var regular_frequency = $('#regular_frequency').val()
      if (regular_frequency == '0') {
        $('#regular_frequency_day_detail').addClass('display_none')
        $('#regular_frequency_date_detail').addClass('display_none')
      }
      else if (regular_frequency == '1') {
        $('#regular_frequency_day_detail').removeClass('display_none')
        $('#regular_frequency_date_detail').addClass('display_none')
      }
      else if (regular_frequency == '2') {
        $('#regular_frequency_day_detail').addClass('display_none')
        $('#regular_frequency_date_detail').removeClass('display_none')
      }
    }
    $('#regist_button').on('click', function () {
      if (!scheduleregularregist_check()) {
        $('#schedule_regular_regist_form').submit()
      }
    })
    $('#delete_button').on('click', function () {
      if (confirm('本当に削除しますか？')) {
        $('#delete_flag').val('true')
        $('#schedule_regular_regist_form').submit()
      }
    })
    // 登録チェック
    function scheduleregularregist_check() {
      var error = false
      $('.errortextbox').removeClass('errortextbox')
      $('[data-required="true"]').each(function () {
        if ($(this).val() == '') {
          error = true
          $(this).addClass('errortextbox')
        }
      })
      if (error) {
        alert('未入力の項目があります')
        return error
      }
      // 次に期間のチェック
      var start_date = $('#start_date').val()
      var end_date = $('#end_date').val()
      if (end_date != '') {
        if (start_date > end_date) {
          $('#start_date').addClass('errortextbox')
          $('#end_date').addClass('errortextbox')
          alert('期間の設定が不正です')
          error = true
          return error
        }
      }





      // 次に時刻の設定のチェック
      var start_time_hour = $('#start_time_hour').val()
      var start_time_minute = $('#start_time_minute').val()
      var end_time_hour = $('#end_time_hour').val()
      var end_time_minute = $('#end_time_minute').val()
      // 開始時間の時間が未入力だったら、開始時間の時間と分を未入力にする
      if (start_time_hour == '-' || start_time_minute == '-') {
        $('#start_time_hour').val('-')
        $('#start_time_minute').val('-')
      }
      // 終了時間の時間が未入力だったら、終了時間の時間と分を未入力にする
      if (end_time_hour == '-' || end_time_minute == '-') {
        $('#end_time_hour').val('-')
        $('#end_time_minute').val('-')
      }
      if (parseInt(start_time_hour) > parseInt(end_time_hour)) {
        alert('時刻の設定が不正です')
        error = true
      }
      else if (parseInt(start_time_minute) > parseInt(end_time_minute)) {
        alert('時刻の設定が不正です')
        error = true
      }

      // 次に参加者が一人以上いるかどうかのチェック
      if ($('#inputs').find('input[name="user_id[]"]').length == 0) {
        alert('参加者が一人以上いる必要があります')
        error = true
      }
      return error
    }

  }

  // -----------------------------期間登録ページ----------------
  if ($('#schedule_term_regist').length != 0) {

    $('#regist_button').on('click', function () {
      if (!scheduletermregist_check()) {
        $('#schedule_term_regist_form').submit()
      }
    })
    $('#delete_button').on('click', function () {
      if (confirm('本当に削除しますか？')) {
        $('#delete_flag').val('true')
        $('#schedule_term_regist_form').submit()
      }
    })
    // 登録チェック
    function scheduletermregist_check() {
      var error = false
      $('.errortextbox').removeClass('errortextbox')
      $('[data-required="true"]').each(function () {
        if ($(this).val() == '') {
          error = true
          $(this).addClass('errortextbox')
        }
      })
      if (error) {
        alert('未入力の項目があります')
        return error
      }
      // 次に期間のチェック
      var start_date = $('#start_date').val()
      var end_date = $('#end_date').val()
      if (start_date > end_date) {
        $('#start_date').addClass('errortextbox')
        $('#end_date').addClass('errortextbox')
        alert('日付の設定が不正です')
        error = true
        return error
      }

      if (start_date == end_date) {

        // 次に時刻の設定のチェック
        var start_time_hour = $('#start_time_hour').val()
        var start_time_minute = $('#start_time_minute').val()
        var end_time_hour = $('#end_time_hour').val()
        var end_time_minute = $('#end_time_minute').val()
        // 開始時間の時間が未入力だったら、開始時間の時間と分を未入力にする
        if (start_time_hour == '-' || start_time_minute == '-') {
          $('#start_time_hour').val('-')
          $('#start_time_minute').val('-')
        }
        // 終了時間の時間が未入力だったら、終了時間の時間と分を未入力にする
        if (end_time_hour == '-' || end_time_minute == '-') {
          $('#end_time_hour').val('-')
          $('#end_time_minute').val('-')
        }
        if (parseInt(start_time_hour) > parseInt(end_time_hour)) {
          alert('時刻の設定が不正です')
          error = true
        }
        else if (parseInt(start_time_minute) > parseInt(end_time_minute)) {
          alert('時刻の設定が不正です')
          error = true
        }

        // 次に参加者が一人以上いるかどうかのチェック
        if ($('#inputs').find('input[name="user_id[]"]').length == 0) {
          alert('参加者が一人以上いる必要があります')
          error = true
        }
        return error
      }
    }

  }
  // 開始時間の時間を選択したら、開始時間の分と終了時間の時間を自動で選択
  $('#start_time_hour').on('change', function () {
    var start_time_hour = $(this).val()
    var start_time_minute = $('#start_time_minute').val()
    var end_time_hour = $('#end_time_hour').val()
    var end_time_minute = $('#end_time_minute').val()
    if (start_time_hour != '-') {
      if (start_time_minute == '-') {
        $('#start_time_minute option[value="0"]').prop('selected', true)
      }
      if (end_time_hour == '-' && end_time_minute == '-') {
        $('#end_time_hour option[value="' + (parseInt(start_time_hour) + 1) + '"]').prop('selected', true)
        $('#end_time_minute option[value="0"]').prop('selected', true)
      }
    }

  })
  // 終了時間の時間を選択したら、終了時間の分を自動で選択
  $('#end_time_hour').on('change', function () {
    var end_time_hour = $('#end_time_hour').val()
    var end_time_minute = $('#end_time_minute').val()
    if (end_time_hour != '-') {
      if (end_time_minute == '-') {
        $('#end_time_minute option[value="0"]').prop('selected', true)
      }
    }

  })
  // スケジュール登録ページまたは個人グループ作成ページの場合
  // 参加者や所属ユーザーを追加
  if ($('#schedule_regist').length != 0 || $('#schedule_group_regist').length != 0 || $('#schedule_regular_regist').length != 0 || $('#schedule_term_regist').length != 0) {
    // 参加者のチェックボックスを押したら、参加者のリストに追加
    $(document).on('change', '.candidate_checkbox', function () {
      if ($(this).is(':checked')) {
        var check_element = $(this)
        var candidate_name = check_element.next('.candidate_name').text()
        var user_id = check_element.data('user_id')
        $('#inputs').append('<input type="hidden" name="user_id[]" value="' + user_id + '">')

        $('.join_member_container').append(
          '<div class="join_member_element" data-user_id="' + user_id + '">' +
          '<span class="join_member_name">' + candidate_name + '</span>' +
          '<span class="join_member_delete_button" data-user_id="' + user_id + '">×</span>' +
          '</div>'
        )
      }
      else {
        var check_element = $(this)
        var user_id = check_element.data('user_id')
        $('#inputs').find('input[value="' + user_id + '"]').remove()
        $('.join_member_container').find('.join_member_element[data-user_id="' + user_id + '"]').remove()
      }
    })
    // 参加者の削除ボタンを押したら、参加者のリストから削除
    $('.join_member_container').on('click', '.join_member_delete_button', function () {
      var user_id = $(this).data('user_id')
      $('#inputs').find('input[value="' + user_id + '"]').remove()
      $('.join_member_container').find('.join_member_element[data-user_id="' + user_id + '"]').remove()
      // チェックボックスを解除   
      $('.candidate_checkbox[data-user_id="' + user_id + '"]').prop('checked', false)
    })

    // 候補者の取得
    $('.group_candidate_select').on('change', function () {
      var group_id = $(this).val()
      $.ajax({
        url: prefix + '/schedule/candidate',
        type: 'GET',
        data: {
          group_id: group_id
        },
        success: function (response) {
          $('.candidate_list_container').empty()
          response.forEach(function (user) {
            if ($('#inputs').find('input[value="' + user.id + '"]').length != 0) {
              var checked = 'checked'
            }
            else {
              var checked = ''
            }
            $('.candidate_list_container').append('<label for="user_' + user.id + '" class="candidate_list_element">' +
              '<input type="checkbox" class="candidate_checkbox" name="candidate_checkbox" data-user_id="' + user.id + '" id="user_' + user.id + '" ' + checked + '>' +
              '<span class="candidate_name">' + user.name + '</span>' +
              '</label>'
            )
          })
        }
      })
    })
  }

  // ----------------予定マスタ登録ページの場合-------------
  if ($('#schedulemaster_regist').length != 0) {
    schedulemaster_preview_reload()
    $(document).on('change', '.schedule_master_text_input', function () {
      schedulemaster_preview_reload()
    })
    $(document).on('change', '.schedule_master_color_input', function () {
      schedulemaster_preview_reload()
    })
    function schedulemaster_preview_reload() {
      $('.schedule_master_preview').each(function () {
        var id = $(this).data('id')
        var schedule_name = $('#schedule_name' + id).val()
        var background_color = $('#background_color' + id).val()
        $(this).css('background-color', background_color)
        $(this).text(schedule_name)
      })
    }

    // 登録ボタンを押したら、チェックをして、エラーがなかったら登録
    $('#regist_button').on('click', function () {
      if (!schedulemaster_regist_check()) {
        if (confirm('本当に変更しますか？')) {
          $('#schedulemaster_regist_form').submit()
        }
      }

    })
    function schedulemaster_regist_check() {
      var error = false
      $('.errortextbox').removeClass('errortextbox')
      $('[data-required="true"]').each(function () {
        if ($(this).val() == '') {
          error = true
          $(this).addClass('errortextbox')
        }
      })
      if (error) {
        alert('未入力の項目があります')
        return error
      }
      return error
    }

    $('.schedule_add_button').on('click', function () {
      var now_count = $('#now_count').val()
      var new_count = parseInt(now_count) + 1
      $('#now_count').val(new_count)
      $('.inputs').append('<input type="hidden" name="plan_id[]" value="' + new_count + '">')
      var new_row = '<tr>' +
        '<td>' +
        '<input class="schedule_master_text_input" type="text" name="schedule_name' + new_count + '" id="schedule_name' + new_count + '" data-id="' + new_count + '" data-required="true">' +
        '</td>' +
        '<td>' +
        '<input class="schedule_master_color_input" type="color" name="background_color' + new_count + '" id="background_color' + new_count + '" data-id="' + new_count + '" >' +
        '</td>' +
        '<td>' +
        '<div class="schedule_master_preview" data-id="' + new_count + '"></div>' +
        '</td>' +
        '<td>' +
        '<div class="delete_button" data-id="' + new_count + '">×</div>' +
        '</td>' +
        '</tr>'
      $('.schedule_master_table').append(new_row)
    })
    // 削除ボタンを押したら、チェックをして、エラーがなかったら削除
    $(document).on('click', '.delete_button', function () {
      var id = $(this).data('id')
      $(this).parent().parent().remove()

      $('.inputs').find('input[value="' + id + '"]').remove()

      $('.inputs').append('<input type="hidden" name="delete_plan_id[]" value="' + id + '">')

    })
  }

});
