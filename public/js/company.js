//企業に登録されている名刺情報の一覧を出すプルダウンを表示する

$(document).ready(function(){
    $('.pulltriangle').click(function(){
        if ($('#pulltriangle').is(':checked')){
            $(".pullscroll").animate({
                height: "0px" // アニメーション後の高さを指定
              }, 80); // アニメーションの時間をミリ秒で指定
            }
            else{
                var targetHeight = $(".companyMeishi").outerHeight();
                if (targetHeight>400){
                    targetHeight = 400
                }
                $(".pullscroll").animate({
                    height: targetHeight // アニメーション後の高さを指定
                  }, 400); // アニメーションの時間をミリ秒で指定
            }
    })

    
        // if ($(this).text().trim() !== ''){
        //     $("#detailtable").addClass('alertbox')
        // }


    



})




//企業の登録と編集の時のエラーチェック
$(document).ready(function() {
    $('#companyForm').submit(function(event) {
      event.preventDefault();
      var alert = false;

        // 必須項目が空欄の場合のエラーメッセージ
        
        //空白になっているidを格納する
        var inv = []
        var invmessage = []
  
        //入力されているidを格納する
        var vali = []
        var valimessage = []
  
        if ($('#companySP').val() == ''){
            inv.push('#companySP');
            invmessage.push('#required3');
            alert = true
        }
        else{
          vali.push('#companySP')
          valimessage.push('#required3');
      }
        if ($('#companyKanaSP').val() == ''){
          inv.push('#companyKanaSP');
          invmessage.push('#required4');
          alert = true
      }
        else{
          vali.push('#companyKanaSP')
          valimessage.push('#required4');
      }

          $(inv.join(",")).addClass("invalid");
          $(vali.join(",")).removeClass("invalid");
          $(invmessage.join(",")).addClass("errorsentence");
          $(valimessage.join(",")).removeClass("errorsentence");
          

    var CompanyKanaValue = $('#companyKanaSP').val();
    // var NameKatakanaValue = convertToKatakana(NameKanaValue);
    // var CompanyKatakanaValue = convertToKatakana(CompanyKanaValue);


    if (containsNonKatakana(CompanyKanaValue)) {
      $('#companyKanaSP').addClass("invalid")
      $('#katakana2').addClass("errorsentence")
      alert = true
    }else{
      $('#katakana2').removeClass("errorsentence")
    }


    //カタカナ以外が含まれているかを確認してもし含まれている場合はfalseを返す。空欄であればtrue
    function containsNonKatakana(str) {
      var katakanaRegex = /^[\u30A0-\u30FFー]+$/;
      var cleanedInput = str.replace(/\s/g, "");
      var judge = !katakanaRegex.test(cleanedInput);
      if (cleanedInput.trim() == ""){
        judge = false
      }
      return judge
    }
    
      

      var postal = $('#postal_codeSP').val();

      if (postal !== ""){
        var postalcode = postal.replace(/[━.*‐.*―.*－.*\-.*ー.*\-]/gi,'');
          if (!postalcode.match(/^([0-9]{7})$/)) {
            alert = true;
            $('#postal_codeSP').addClass("invalid");
            $('#postalerror').addClass("errorsentence")
        }else{
          $('#postal_codeSP').removeClass("invalid")
          $('#postalerror').removeClass("errorsentence")
          var formattedPostalCode = postalcode.replace(/^(\d{3})(\d{4})$/, '$1-$2');
          $('#postal_codeSP').val(formattedPostalCode)
        }
      }
      else{
        $('#postal_codeSP').removeClass("invalid")
        $('#postalerror').removeClass("errorsentence")

      }

  
  
  
  
  
    if(alert == false) {
      event.preventDefault(); // フォームのデフォルトの送信をキャンセル
      var title = $('#registbutton').val();
      
          if (title.trim() == '登録'){
            
          if (confirm("本当に登録しますか？")) {
            this.submit(); // フォームの送信を実行
          }
        }
        else {
          if (confirm("本当に変更しますか？")) {
            this.submit(); // フォームの送信を実行
        }
          }
    }
    });
  
  });