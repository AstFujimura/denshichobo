$(document).ready(function() {
  var prefix = $('#prefix').val();
      //prefixがある場合
      if (prefix !== ""){
        prefix = '/'+prefix
    }
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
    errorformreset(["name","email"]);
    errorsentencereset(["required1","required2","required3","userformat","usercheck","emailformat","passwordformat"]);
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

    fourBytecheck("name", "userformat")
    fourBytecheck("email", "emailformat")





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
        $('#passformat').removeClass("errorsentence");
        $('#passcheckformat').removeClass("errorsentence");
      }
      else{
        $('.passcheck').removeClass("errorsentence");

        passcheck('newpass','passformat');
        passcheck('newpasscheck','passcheckformat');
      }
      //送信時には一旦「パスワードが違います」の文言を消す
      $('.differencepass').removeClass("errorsentence");

      formData.append('oldpass',oldpass);
      formData.append('newpass',newpass);
    }


    if (!$('.invalid').length){

      var nameval = $('#name').val();
      var change = "change"
      var id = $("#userID").val();

      //ユーザー名に重複がないかを非同期で問い合わせる
      $.ajax({
        url: prefix + '/usercheck',
        type: 'get',
        data: {
          username: nameval,
          change: change,
          id: id
        },
        success: function (response) {
          //ユーザー名が重複している場合
          if (response == "重複") {
            $("#usercheck").addClass('errorsentence')
            $("#name").addClass('invalid')
          }
          else {
            //登録画面におけるフォームの確認
            if (!$('.errorsentence').length) {
  
              if (confirm("情報を変更しますよろしいですか")){
                $.ajax({
                  url: prefix+'/usersetting',
                  type: 'POST',
                  data: formData,
                  processData: false,
                  contentType: false,
                  headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                   },
                   success: function(response){
                    if (response == "成功"){
                      window.location.href = prefix
                    }
                    
                    else if(response == "パスワードが違います") {
                      $('#oldpass').addClass("invalid");
                      $('.differencepass').addClass("errorsentence");
                   }
            
                }
              });
              }
  
            }
          }
        }
      });




    }

});
function passcheck(passdata, errorpassdata) {
  var passval = $("#" + passdata).val();
  var hasUpperCase = /[A-Z]/.test(passval); // 大文字が含まれているか
  var hasLowerCase = /[a-z]/.test(passval); // 小文字が含まれているか
  var hasNumber = /\d/.test(passval); // 数字が含まれているか
  var isLengthValid = passval.length >= 8 // 8文字以上かどうか


  //値が入っていない場合はほかのエラーチェックがあるためtrueを返す
  if (!passval) {
    var passval = true
  }
  //hasRequiredCharsがtrueの場合はパスワードが有効
  else if (!hasUpperCase ||!hasLowerCase ||!hasNumber || !isLengthValid) {
    $("#" + errorpassdata).addClass('errorsentence')
    $("#" + passdata).addClass('invalid')
  }
  else {
    $("#" + errorpassdata).removeClass('errorsentence')
    $("#" + passdata).removeClass('invalid')
  }

}

function errorformreset(errorform){
  errorform.forEach(function(element){
    $('#'+ element).removeClass("invalid");
  })
}
function errorsentencereset(errorsentence){
  errorsentence.forEach(function(element){
    $('#'+ element).removeClass("errorsentence");
  })
}

function fourBytecheck(fourBytedate, errorfourBytedate) {
  var fourByteval = $("#" + fourBytedate).val();
  var fourBytecheck = /[\ud800-\udbff][\udc00-\udfff]/g.test(fourByteval);
  //4バイト文字が含まれていたらエラー
  if (!fourBytecheck) {
    $("#" + errorfourBytedate).removeClass('errorsentence')
    // $("#" + kinngaku).removeClass('invalid')
  }
  else {
    $("#" + errorfourBytedate).addClass('errorsentence')
    $("#" + fourBytedate).addClass('invalid')
  }

}




});
  