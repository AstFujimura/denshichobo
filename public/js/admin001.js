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
  var tablecolumnH = $('.top_table_column').outerHeight(true);


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

  if (scroll >= headerH+pageTitleH+serachBoxH-tablecolumnH){//headerの高さ以上になったら
    $('.top_table_column').addClass('top_table_column_fixed');//fixedというクラス名を付与
    $('.top_table').addClass('top_table_margin');

  }else{//それ以外は
    $('.top_table_column').removeClass('top_table_column_fixed');//fixedというクラス名を除去
    $('.top_table').removeClass('top_table_margin');
  }
}
});
