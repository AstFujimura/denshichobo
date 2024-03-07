// script.jsファイルに記述するJavaScriptコード
$(document).ready(function() {
    $('table tr').click(function() {
      // クリックした列の内容を取得
      var data = $(this).children('td').map(function() {
        return $(this).text();
      }).get();
  
      // POSTするデータを作成
      var postData = {
        data: data
      };
  
      // POSTリクエストを送信
      $.ajax({
        url: '/',
        type: 'POST',
        data: postData,
        success: function(response) {
          // POSTが成功したときに実行される処理
          console.log(response);
        },
        error: function(xhr) {
          // POSTが失敗したときに実行される処理
          console.log(xhr.responseText);
        }
      });
    });
  });
  