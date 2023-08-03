$(document).ready(function() {

  var selectedOption = $('#select').val();
//ページが表示されたとき
deleteselect(selectedOption);
//一覧と検索画面において選択肢に応じて件数表示を変える




$('#select').on('change', function() {
  // 選択されたオプションの値を取得

  var selectedOption = $(this).val();
  deleteselect(selectedOption);

});
function deleteselect(selectedOption){
  if (selectedOption == "有効データ"){
    $(".notdeletecount").addClass("selected");
    $(".count").removeClass("selected");
    $(".deletecount").removeClass("selected");

    $(".delete_table").removeClass("table_selected")
    $(".top_table_body").addClass("table_selected")
    $('#deleteOrzenken').val("yukou")
  }
  else if (selectedOption == "削除データ"){
    $(".notdeletecount").removeClass("selected");
    $(".count").removeClass("selected");
    $(".deletecount").addClass("selected");

    $(".delete_table").addClass("table_selected")
    $(".top_table_body").removeClass("table_selected")
    $('#deleteOrzenken').val("delete")
  }
  else if (selectedOption == "全件データ"){
    $(".notdeletecount").removeClass("selected");
    $(".count").addClass("selected");
    $(".deletecount").removeClass("selected");

    $(".delete_table").addClass("table_selected")
    $(".top_table_body").addClass("table_selected")
    $('#deleteOrzenken').val("zenken")
  }

};
});
