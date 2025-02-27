$(document).ready(function () {
    var prefix = $('#prefix').val();

    $('.dateinputtext').datepicker({
        changeMonth: true,
        changeYear: true,
        duration: 300,
        showAnim: 'show',
        showOn: 'button', // 日付をボタンクリックでのみ表示する
        buttonImage: prefix + '/img/calendar_2_line.svg', // カスタムアイコンのパスを指定
        buttonImageOnly: true, // テキストを非表示にする
    })

    $('.news_delete_button').click(function () {
        $('.news_black').addClass('news_black_none')
    });
    $('.news_black').click(function () {
        $('.news_black').addClass('news_black_none')
    });

    //サイドバーの表示
    $('.hamburger01').click(function () {
        $('.sidebar01').toggleClass('sidebar01open'); // サイドバーの表示/非表示を切り替える
        $('.sidebarbatsu01').toggleClass('sidebarbatsuopen01'); // サイドバーの表示/非表示を切り替える

        $('.hamburger01').toggleClass('hamburger01close');
    });
    $('.accordion1_01').click(function () {
        $('.accordion1content01').toggleClass('accordion1open'); // サイドバーの表示/非表示を切り替える
        $('.allow01').toggleClass('rotate');
    });
    $('.sidebarbatsu01').click(function () {
        $('.sidebar01').toggleClass('sidebar01open'); // サイドバーの表示/非表示を切り替える
        $('.sidebarbatsu01').toggleClass('sidebarbatsuopen01'); // サイドバーの表示/非表示を切り替える
    });


    //パスワードリセット時のアコーディオンメニュー
    $('.title').on('click', function () {
        $('.title').toggleClass('close')
        $('.importantelement').toggleClass('open')
    });



    // 画面をスクロールをしたら動かしたい場合の記述
    $(window).scroll(function () {
        var windowWidth = $(window).width();
        if (windowWidth > 700) {
            FixedAnime();/* スクロール途中からヘッダーを出現させる関数を呼ぶ*/
            const scrollX = $(window).scrollLeft();
            $('.header001').css('left', `${scrollX}px`);
            if (!$('.menu001fixed').length) {
                $('.menu001').css('left', `${scrollX}px`);
            }
            else {
                $('.menu001').css('left', 0);
            }
        }
    });
    //スクロールすると上部に固定させるための設定を関数でまとめる
    function FixedAnime() {
        var headerH = $('.header001').outerHeight(true);
        var pageTitleH = $('.pagetitle').outerHeight(true);
        var serachBoxH = $('.searchBox').outerHeight(true);
        var infoH = $('.info').outerHeight(true);
        var tablecolumnH = $('.top_table_div').outerHeight(true);
        var historytablecolumnH = $('.history_table_div').outerHeight(true);


        var scroll = $(window).scrollTop();
        if (scroll >= headerH) {//headerの高さ以上になったら
            $('.menu001').addClass('menu001fixed');//fixedというクラス名を付与
            $('.pagetitle').addClass('h2_margin');
            $('.sidebar01').addClass('sidebar01top');
        } else {//それ以外は
            $('.menu001').removeClass('menu001fixed');//fixedというクラス名を除去
            $('.pagetitle').removeClass('h2_margin');
            $('.sidebar01').removeClass('sidebar01top');
        }

        if (scroll >= headerH + pageTitleH + serachBoxH + infoH - tablecolumnH) {//headerの高さ以上になったら
            $('.top_table_div').addClass('top_table_column_fixed');//fixedというクラス名を付与
            $('.top_table_element').addClass('top_table_margin');

        } else {//それ以外は
            $('.top_table_div').removeClass('top_table_column_fixed');//fixedというクラス名を除去
            $('.top_table_element').removeClass('top_table_margin');
        }
        if (scroll >= headerH + pageTitleH - historytablecolumnH) {//headerの高さ以上になったら
            $('.history_table_div').addClass('history_table_column_fixed');//fixedというクラス名を付与
            $('.history_table_element').addClass('history_table_margin');

        } else {//それ以外は
            $('.history_table_div').removeClass('history_table_column_fixed');//fixedというクラス名を除去
            $('.history_table_element').removeClass('history_table_margin');
        }



    }





});

