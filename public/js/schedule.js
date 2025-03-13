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
  $('.regist_button').on('click', function () { 
    if (!regist_check()) {
      $('#schedule_regist_form').submit()
    }
    
  })
  function regist_check(){
    var error = false
    $('.errortextbox').removeClass('errortextbox')
    $('[data-required="true"]').each(function(){
      if ($(this).val() == '') {
        error = true
        $(this).addClass('errortextbox')
      }
    })
    if (error) {
      alert('未入力の項目があります')
    }
    return error
  }
});
