$(document).ready(function () {
    var prefix = $('#prefix').val();

    $('.dateinputtext:not(.search-date-flatpickr):not(.ledger-regist-date)').datepicker({
        changeMonth: true,
        changeYear: true,
        duration: 300,
        showAnim: 'show',
        showOn: 'button', // 日付をボタンクリックでのみ表示する
        buttonImage: prefix + '/img/calendar_2_line.svg', // カスタムアイコンのパスを指定
        buttonImageOnly: true, // テキストを非表示にする
    });

    if (typeof flatpickr !== 'undefined') {
        var flatpickrSelector = '.search-date-flatpickr, .ledger-regist-date';
        if ($(flatpickrSelector).length) {
            flatpickr(flatpickrSelector, {
                dateFormat: 'Y/m/d',
                allowInput: true,
                locale: 'ja',
                onClose: function (selectedDates, dateStr, instance) {
                    $(instance.element).trigger('blur');
                },
            });
        }
    }

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

    // 帳簿一覧：詳細検索の表示切替（狭い画面）
    var searchCompactMq = window.matchMedia('(max-width: 62.5rem)');

    function syncSearchboxDetail() {
        if (!searchCompactMq.matches) {
            $('#searchbox-detail-fields').removeClass('is-open');
            $('.searchbox-detail-toggle').removeClass('is-open').attr('aria-expanded', 'false');
        }
    }

    $('.searchbox-detail-toggle').on('click', function () {
        if (!searchCompactMq.matches) {
            return;
        }
        var $toggle = $(this);
        var $detail = $('#searchbox-detail-fields');
        var isOpen = $detail.toggleClass('is-open').hasClass('is-open');
        $toggle.toggleClass('is-open', isOpen);
        $toggle.attr('aria-expanded', isOpen ? 'true' : 'false');
    });

    $(window).on('resize', syncSearchboxDetail);
    syncSearchboxDetail();

    // 帳簿一覧：ヘッダー固定の閾値（詳細検索開閉・リサイズで再計算）
    var ledgerStickyState = { threshold: null };

    function getLedgerStickyTop() {
        return $('.menu001').outerHeight(true) ;
    }

    function setLedgerHeaderFixed(isFixed) {
        var $header = $('.ledger-table .top_table_div');
        var $body = $('.ledger-table .top_table_element');
        if (!$header.length) {
            return;
        }

        if (isFixed) {
            var $table = $('.ledger-table');
            var tableRect = $table[0].getBoundingClientRect();
            var headerHeight = $header.outerHeight(true);

            $header.addClass('top_table_column_fixed');
            $header.css({
                top: getLedgerStickyTop() + 'px',
                left: tableRect.left + 'px',
                width: tableRect.width + 'px',
            });
            $body.addClass('top_table_margin');
            $body.css('margin-top', headerHeight + 'px');
        } else {
            $header.removeClass('top_table_column_fixed');
            $header.css({ top: '', left: '', width: '' });
            $body.removeClass('top_table_margin');
            $body.css('margin-top', '');
        }
    }

    function updateLedgerHeaderFixedPosition() {
        var $header = $('.ledger-table .top_table_div');
        if (!$header.hasClass('top_table_column_fixed')) {
            return;
        }
        var $table = $('.ledger-table');
        var tableRect = $table[0].getBoundingClientRect();
        $header.css({
            top: getLedgerStickyTop() + 'px',
            left: tableRect.left + 'px',
            width: tableRect.width + 'px',
        });
    }

    function measureLedgerTableSticky() {
        var $header = $('.ledger-table .top_table_div');
        if (!$header.length) {
            ledgerStickyState.threshold = null;
            return;
        }

        var wasFixed = $header.hasClass('top_table_column_fixed');
        if (wasFixed) {
            setLedgerHeaderFixed(false);
        }

        ledgerStickyState.threshold = $header.offset().top - getLedgerStickyTop();

        if (wasFixed && $(window).scrollTop() >= ledgerStickyState.threshold) {
            setLedgerHeaderFixed(true);
        }
    }

    function handleLedgerTableSticky(scroll) {
        if (ledgerStickyState.threshold === null) {
            return;
        }
        var isFixed = scroll >= ledgerStickyState.threshold;
        if (isFixed !== $('.ledger-table .top_table_div').hasClass('top_table_column_fixed')) {
            setLedgerHeaderFixed(isFixed);
        }
        if (isFixed) {
            updateLedgerHeaderFixedPosition();
        }
    }

    measureLedgerTableSticky();
    $(window).on('resize', function () {
        measureLedgerTableSticky();
        if ($(window).width() > 700) {
            FixedAnime();
        }
    });
    $('.searchbox-detail-toggle').on('click', function () {
        setTimeout(measureLedgerTableSticky, 350);
    });

    // 帳簿一覧：行クリックでファイル詳細へ
    function goLedgerRowDetail($row) {
        var href = $row.data('detail-href');
        if (href) {
            window.location.href = href;
        }
    }

    $(document).on('click', '.ledger-table-row[data-detail-href]', function (event) {
        if ($(event.target).closest('[data-row-action="stop"], .download, .downloadbutton, .previewbutton').length) {
            return;
        }
        goLedgerRowDetail($(this));
    });

    $(document).on('keydown', '.ledger-table-row[data-detail-href]', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            goLedgerRowDetail($(this));
        }
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
        var historytablecolumnH = $('.history_table_div').outerHeight(true);

        var scroll = $(window).scrollTop();
        if (scroll >= headerH) {//headerの高さ以上になったら
            $('.menu001').addClass('menu001fixed');//fixedというクラス名を付与
            // $('.pagetitle').addClass('h2_margin');
            $('.sidebar01').addClass('sidebar01top');
        } else {//それ以外は
            $('.menu001').removeClass('menu001fixed');//fixedというクラス名を除去
            // $('.pagetitle').removeClass('h2_margin');
            $('.sidebar01').removeClass('sidebar01top');
        }

        handleLedgerTableSticky(scroll);

        if ($('.history_table_div').length) {
            var historyThreshold = $('.history_table_div').offset().top - getLedgerStickyTop();
            if (scroll >= historyThreshold) {
                $('.history_table_div').addClass('history_table_column_fixed');
                $('.history_table_element').addClass('history_table_margin');
            } else {
                $('.history_table_div').removeClass('history_table_column_fixed');
                $('.history_table_element').removeClass('history_table_margin');
            }
        }
    }

    if ($(window).width() > 700) {
        FixedAnime();
    }

    if ($('.ledger-regist--edit').length) {
        var $previewModal = $('#ledgerRegistPreviewModal');
        var $previewModalBody = $('#ledgerRegistPreviewModalBody');
        var $previewModalTitle = $('#ledgerRegistPreviewModalTitle');

        function closeLedgerRegistPreviewModal() {
            $previewModal.attr('aria-hidden', 'true').removeClass('is-open');
            $('body').removeClass('ledger-regist-modal-open');
            $previewModalBody.empty();
        }

        function openLedgerRegistPreviewModal(title, $source) {
            $previewModalTitle.text(title);
            var $media = $('<div class="ledger-regist-modal__media"></div>');
            $media.append($source.contents().clone(true));
            $previewModalBody.empty().append($media);
            $previewModalBody.find('embed, img, .previewImage').css({
                width: '100%',
                height: '100%',
                maxHeight: 'none',
            });
            $previewModal.attr('aria-hidden', 'false').addClass('is-open');
            $('body').addClass('ledger-regist-modal-open');
        }

        $('.ledger-regist__preview-expand').on('click', function () {
            var target = $(this).data('preview-target');
            var title = $(this).data('preview-title') || 'プレビュー';
            var $source = target === 'past'
                ? $('.ledger-regist--edit .pastpreview').first()
                : $('.ledger-regist--edit .previewarea.editpreviewarea').first();
            openLedgerRegistPreviewModal(title, $source);
        });

        $previewModal.find('.ledger-regist-modal__backdrop, .ledger-regist-modal__close').on('click', closeLedgerRegistPreviewModal);

        $(document).on('keydown.ledgerRegistPreviewModal', function (e) {
            if (e.key === 'Escape' && $previewModal.hasClass('is-open')) {
                closeLedgerRegistPreviewModal();
            }
        });
    }

});

