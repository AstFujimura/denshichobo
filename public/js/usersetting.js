$(document).ready(function() {
  $('.important_title').on('click',function(){
    //パスワード変更アコーディオンメニューを閉じた時はパスワードの空欄エラーを取る
    $('#oldpass').removeClass("invalid");
    $('#required3').removeClass("errorsentence");
    $('#newpass').removeClass("invalid");
    $('#required4').removeClass("errorsentence");
    $('#newpasscheck').removeClass("invalid");
    $('#required5').removeClass("errorsentence");
  });
  $('#usersetting').submit(function(event){
    event.preventDefault();
    const formData = new FormData();
    const name = $('#name').val();
    const email = $('#email').val();
    if (!name){
      $('#name').addClass("invalid");
      $('#required1').addClass("errorsentence");
    }
    else{
      $('#name').removeClass("invalid");
      $('#required1').removeClass("errorsentence");
    }
    if (!email){
      $('#email').addClass("invalid");
      $('#required2').addClass("errorsentence");
    }
    else{
      $('#email').removeClass("invalid");
      $('#required2').removeClass("errorsentence");
    }
    const oldpass = $('#oldpass').val();
    const newpass = $('#newpass').val();
    const newpasscheck = $('#newpasscheck').val();

    formData.append('name',name);
    formData.append('email',email);
    if($('.open').length){
      if (!oldpass){
        $('#oldpass').addClass("invalid");
        $('#required3').addClass("errorsentence");
      }
      else{
        $('#oldpass').removeClass("invalid");
        $('#required3').removeClass("errorsentence");
      }
      if (!newpass){
        $('#newpass').addClass("invalid");
        $('#required4').addClass("errorsentence");
      }
      else{
        $('#newpass').removeClass("invalid");
        $('#required4').removeClass("errorsentence");
      }
      if (!newpasscheck){
        $('#newpasscheck').addClass("invalid");
        $('#required5').addClass("errorsentence");
      }
      else{
        $('#newpasscheck').removeClass("invalid");
        $('#required5').removeClass("errorsentence");
      }
      if (newpass != newpasscheck){
        $('#newpass').addClass("invalid");
        $('#newpasscheck').addClass("invalid");
        $('#required4').removeClass("errorsentence");
        $('#required5').removeClass("errorsentence");
        $('.passcheck').addClass("errorsentence");
      }
      else{
        $('.passcheck').removeClass("errorsentence");
      }

      formData.append('oldpass',oldpass);
      formData.append('newpass',newpass);
    }


    if (!$('.invalid').length){
      $.ajax({
        url: '/usersetting',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val(),
         },
         success: function(response){
          if (response == "成功"){
            window.location.href = "/"
          }
          
          else {
            console.log(response);
         }
  
      }
    });

    }

});
});
  