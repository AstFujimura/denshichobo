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
    window.location.href = prefix + '/schedule?selected_group_id=' + selected_group_id
  })
  $('.search_date').on('change', function () {
    var search_date = $(this).val()
    var selected_group_id = $('#selected_group_id').val()
    window.location.href = prefix + '/schedule?selected_group_id=' + selected_group_id + '&base_date=' + search_date
  })


  // -------------スケジュール登録----------------

  // 登録ボタンを押したら、チェックをして、エラーがなかったら登録
  $('.regist_button').on('click', function () {
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
    return error
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

  // 削除ボタンを押したら、チェックをして、エラーがなかったら削除
  $('.delete_button').on('click', function () {
    if (confirm('本当に削除しますか？')) {
      $('#delete_flag').val('true')
      $('#schedule_regist_form').submit()
    }
  })
  

});
