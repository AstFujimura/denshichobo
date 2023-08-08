$(document).ready(function() { 
  $('.wholecontainer').on('click',function(){
    $(this).fadeOut();
    $('.previewcontainer').fadeOut();
  });

  $('.previewbutton').on('click',function(){
    $('.wholecontainer').fadeIn();
    $('.previewcontainer').fadeIn();
    // containerクラス内の要素を削除
    $(".previewcontainer").empty();
    var ID = $(this).attr("id");
    $.ajax({
      url: '/img/'+ID, // データを取得するURLを指定
      method: 'GET',
      xhrFields: {
        responseType: 'blob' // ファイルをBlobとして受け取る
      },
      success: function(response) {
          // 取得したファイルデータを使ってPDFを表示
          var Url = URL.createObjectURL(response);
          if (response.type === 'application/pdf'){
            var embed  = $('<embed>');
            embed.attr('src', Url);
            embed.attr('width', '100%');
            embed.attr('height', '100%');
            embed.attr('type', 'application/pdf');
            embed.addClass('imgset');
  
            $('.previewcontainer').append(embed);
          }
          else if(response.type.startsWith('image/')) {
            var img = $('<img>');
            img.attr('src', Url);
            img.attr('width', '100%');
            img.attr('height', '100%');
            img.addClass('imgset');
  
            $('.previewcontainer').append(img);
          }
          

      },
      error: function(xhr, status, error) {
          console.error(error); // エラー処理
      }
  });

});








  $('.input-field').keydown(function(event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化

  
      var currentIndex = $('.input-field').index(this);
      var nextInput = $('.input-field').eq(currentIndex + 1);
  
      if (nextInput.length === 0) {
        $('.form').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される
        $('#admin-myForm').submit(); 
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });

  $('.searchinputtext').keydown(function(event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化

      
  
      var currentIndex = $('.searchinputtext').index(this);
      var nextInput = $('.searchinputtext').eq(currentIndex + 1);
  
      if (nextInput.length === 0) {
        $('.searchbutton').focus(); // 最後の入力欄でエンターキーを押すとフォームが送信される
        
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });







  $('.droparea').on('dragover', function(event) {
    event.preventDefault();
    $(this).addClass("dragover");
  });

  $('.droparea').on('drop', function(event) {
    event.preventDefault();
    $(this).removeClass("dragover");
    var File = event.originalEvent.dataTransfer.files[0];
    $('#file').prop("files", event.originalEvent.dataTransfer.files);
    // ファイルのタイプを取得
    var fileType = File.type;

    // 画像をプレビューとして表示する
    if (fileType.startsWith("image/")) {
        var reader = new FileReader();
        reader.onload = function(e) {
          $('.previewarea').html('<img src="' + e.target.result + '" class="previewImg">');
          $('.previewarea').addClass("previewopen");
        };
        reader.readAsDataURL(File);
      }
          // PDFをプレビューとして表示する
    else if (fileType === "application/pdf") {
      var pdfUrl = URL.createObjectURL(File);
      var embed = $('<embed>');
      embed.attr('src', pdfUrl);
      embed.attr('width', '100%');
      embed.attr('height', '600px'); // 適切な高さを指定

      $('.previewarea').html(embed);
      $('.previewarea').addClass("previewopen");
    }
  });
    
  $('#file').change(function() {
    var input = this;
    
    if (input.files && input.files[0]) {
      if (this.files[0].type.startsWith("image/")){
        var reader = new FileReader();
  
        reader.onload = function(e) {
          $('.previewarea').html('<img src="' + e.target.result + '" class="previewImage">');
          $('.previewarea').addClass("previewopen");
        };
        
  
      reader.readAsDataURL(this.files[0]);

      }
      // PDFをプレビューとして表示する
      else if (this.files[0].type === "application/pdf") {
        var pdfUrl = URL.createObjectURL(this.files[0]);
        var embed = $('<embed>');
        embed.attr('src', pdfUrl);
        embed.attr('width', '100%');
        embed.attr('height', '600px'); // 適切な高さを指定

        $('.previewarea').html(embed);
        $('.previewarea').addClass("previewopen");
      }

  
    }




});
$('.deletebutton').on('click',function(){
  $id = $("#id").val();
  if (confirm("本当に削除しますか?")){
    window.location.href = '/delete/'+$id;
  }
  

});

$('.important_title').on('click',function(){
  $('.important_title').toggleClass('close')
  $('.importantelement').toggleClass('open')
});



});

