$(document).ready(function() {
// 画面をスクロールをしたら動かしたい場合の記述
$(window).scroll(function () {
	FixedAnime();/* スクロール途中からヘッダーを出現させる関数を呼ぶ*/
});
//スクロールすると上部に固定させるための設定を関数でまとめる
function FixedAnime() {
	var headerH = $('.header001').outerHeight(true);
  var menubarH = $('.menu001').outerHeight(true);
  var pageTitleH = $('.pagetitle').outerHeight(true);
  var serachBoxH = $('.searchBox').outerHeight(true);
  var infoH = $('.info').outerHeight(true);
  var tablecolumnH = $('.top_table_div').outerHeight(true);


	var scroll = $(window).scrollTop();
	if (scroll >= headerH){//headerの高さ以上になったら
			$('.menu001').addClass('menu001fixed');//fixedというクラス名を付与
      $('.pagetitle').addClass('h2_margin');
      $('.sidebar01').addClass('sidebar01top');
		}else{//それ以外は
			$('.menu001').removeClass('menu001fixed');//fixedというクラス名を除去
      $('.pagetitle').removeClass('h2_margin');
      $('.sidebar01').removeClass('sidebar01top');
		}

  if (scroll >= headerH+pageTitleH+serachBoxH+infoH-tablecolumnH){//headerの高さ以上になったら
    $('.top_table_div').addClass('top_table_column_fixed');//fixedというクラス名を付与
    $('.top_table_element').addClass('top_table_margin');

  }else{//それ以外は
    $('.top_table_div').removeClass('top_table_column_fixed');//fixedというクラス名を除去
    $('.top_table_element').removeClass('top_table_margin');
  }
}






//一覧と検索画面において選択肢に応じて件数表示を変える
$('#select').on('change', function() {
  // 選択されたオプションの値を取得
  var selectedOption = $(this).val();

  if (selectedOption == "削除データを除く"){
    $(".notdeletecount").addClass("selected");
    $(".count").removeClass("selected");
    $(".deletecount").removeClass("selected");

    $(".delete_table").removeClass("table_selected")
    $(".top_table_body").addClass("table_selected")
  }
  else if (selectedOption == "削除データのみ表示"){
    $(".notdeletecount").removeClass("selected");
    $(".count").removeClass("selected");
    $(".deletecount").addClass("selected");

    $(".delete_table").addClass("table_selected")
    $(".top_table_body").removeClass("table_selected")
  }
  else if (selectedOption == "全データ表示"){
    $(".notdeletecount").removeClass("selected");
    $(".count").addClass("selected");
    $(".deletecount").removeClass("selected");

    $(".delete_table").addClass("table_selected")
    $(".top_table_body").addClass("table_selected")
  }
  console.log('選択されたオプション:', selectedOption);
});
});
