$(document).ready(function () {
    // 名刺登録画面
    if ($('#card_regist_title').length > 0) {

        var prefix = $('#prefix').val();
        if ($('#edit').val() == 'edit') {
            const $img = $('<img>', {
                class: 'croppable_image',
            });
            $('.card_file_front_label').html($img);
            $('.croppable_image').each(function () {
                var img = $(this);
                if ($('#server').val() == "cloud") {
                    $.ajax({
                        url: prefix + '/img/' + ID, // データを取得するURLを指定
                        method: 'GET',
                        dataType: "json",
                        success: function (response) {
                            if (response.Type === 'application/pdf') {
                                var embed = $('<embed>');
                                embed.attr('src', response.path);
                                embed.attr('width', '100%');
                                embed.attr('height', '600px');
                                embed.attr('type', 'application/pdf');
                                embed.addClass('imgset');

                                $('.pastpreview').html(embed);
                            }
                            else if (response.Type.startsWith('image/')) {
                                var img = $('<img>');
                                img.attr('src', response.path);
                                img.attr('width', '100%');
                                img.attr('height', '600px');
                                img.addClass('imgset');

                                $('.pastpreview').html(img);
                            }
                        }
                    });
                }
                else {
                    $.ajax({
                        url: prefix + '/card/img/' + $('#card_id').val() + '/front', // データを取得するURLを指定
                        method: 'GET',
                        xhrFields: {
                            responseType: 'blob' // ファイルをBlobとして受け取る
                        },
                        success: function (response) {
                            var Url = URL.createObjectURL(response);
                            if (response.type.startsWith('image/')) {
                                img.attr('src', Url);
                            }


                        },
                        error: function (xhr, status, error) {
                            console.error(error); // エラー処理
                        }
                    });

                }
            });

            $('.button_container').addClass('button_container_open');
        }

        // const canvas = $('#canvas')[0];
        // const ctx = canvas.getContext('2d');
        var cropper;
        var lastCropData
        let croppedBlob;  // トリミング済み画像のBlobを保持

        // 画像が変更されたときにCanvasに描画
        $('#card_file_front').on('change', function () {
            // 前回のトリミング範囲をリセット
            lastCropData = null;
            croppedBlob = null; // Blobをリセット
            const file = this.files[0]; // 変更されたファイルを取得
            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    // 古い画像とCropperを削除
                    if (cropper) {
                        cropper.destroy();
                    }
                    const $img = $('<img>', {
                        class: 'croppable_image',
                    });
                    $('.card_file_front_label').html($img);
                    $('.croppable_image').attr('src', e.target.result);
                    $('.button_container').addClass('button_container_open');
                    // 画像がロードされたときの処理
                    $('.croppable_image').on('load', function () {

                        // 画像が読み込まれた状態を設定
                        $('#card_file_front').data('imageLoaded', true);
                    });
                };

                reader.readAsDataURL(file);
            }
        });


        //切り取りボタンが押されたとき
        $('.crop_button').on('click', function () {
            $('.crop_controller_container').addClass('controller_open');
            // Cropper.js の初期化
            cropper = new Cropper($('.crop_controller_content .croppable_image')[0], {
                aspectRatio: NaN, // 縦横比を固定しない
                viewMode: 1, // クロップ領域が画像内に収まるよう制限
                autoCropArea: 0.9, // 初期表示時のトリミング範囲
                responsive: true, // ウィンドウサイズに応じてレスポンシブに対応
                background: true, // 背景を非表示
                minContainerWidth: 300, // コンテナの最小幅
                minContainerHeight: 300, // コンテナの最小高さ
                minCropBoxWidth: 100, // トリミングボックスの最小幅
                minCropBoxHeight: 100, // トリミングボックスの最小高さ
                movable: true, // トリミング範囲の移動を許可
                zoomable: true, // ズームを許可
                rotatable: true, // 回転を許可
                scalable: true, // 拡大・縮小を許可
                cropBoxResizable: true, // トリミングボックスのサイズ変更を許可
                cropBoxMovable: true // トリミングボックスの移動を許可
            });

        });

        //回転ボタンが押されたとき
        $('.crop_rotate_button').on('click', function () {
            cropper.rotate(90);
            const imageData = cropper.getImageData(); // 画像データを取得
            const containerData = cropper.getContainerData(); // コンテナデータを取得
            // 回転後に画像がコンテナからはみ出しているかチェック
            if (imageData.width > containerData.width || imageData.height > containerData.height) {
                const scale = Math.min(
                    containerData.width / imageData.width,
                    containerData.height / imageData.height
                );
                cropper.scale(scale, scale); // スケール調整
            }
        });


        //切り取り完了
        $('.crop_complete_button').on('click', function () {
            $('.crop_controller_container').removeClass('controller_open');

            // 現在のトリミング範囲を保存
            lastCropData = cropper.getData();
            // トリミングされた画像をBlob形式で取得
            cropper.getCroppedCanvas().toBlob(function (blob) {
                croppedBlob = blob; // Blobを保存
                const croppedImageURL = URL.createObjectURL(blob); // BlobをURLに変換してプレビュー表示用に利用
                $('.card_file_front_label .croppable_image').attr('src', croppedImageURL);
            });
            cropper.destroy();

        });



        //切り取りキャンセル
        $('.crop_cancel_button').on('click', function () {
            $('.crop_controller_container').removeClass('controller_open');
            cropper.destroy();
        });

        // 会社候補検索ボタンが押されたとき
        $('.company_search_button').on('click', function () {
            $('.company_candidate_container').addClass('company_candidate_container_open');
            getCompanyCandidate($('.company_search_button').closest('tr').find('input[name="company_name"]').val());
        });
        // 会社候補が選択されたとき
        $(document).on('change', '.company_candidate_container input[type="radio"]', function () {
            $('.company_candidate_container').removeClass('company_candidate_container_open');
            if ($(this).val() == 'new') {
                $('#company_id').val(0);
                new_company();
            }
            else {
                $('#company_id').val($(this).val());
                company_info_get($(this).val());
            }
        });
        // すでに選択された会社名をクリックして編集し直すとき
        $(document).on('click', '.company_choiced', function () {
            $('#company_id').val(0);
            new_company();
        });
        // 会社名を直に入力したときに会社を「新規」として登録表示する
        $('#company_name').on('change', function () {
            $('#company_id').val(0);
            new_company();
        });


        // 送信ボタンが押されたときに画像が読み込まれているか確認し、送信
        $('#send_button').on('click', function () {
            // 画像が読み込まれているか確認
            if ($('#card_file_front').data('imageLoaded')) {
                const file = $('#card_file_front')[0].files[0];  // 送信する画像ファイル
                if (file) {
                    sendImageToServer(file);  // サーバーに画像を送信
                }
            } else {
                alert('画像を選択してください');
            }
        });

        // サーバーに画像データを送信
        function sendImageToServer(file) {
            var prefix = $('#prefix').val();
            const formData = new FormData();
            if (croppedBlob) {
                formData.append('blob-image', croppedBlob, 'cropped-image.png'); // Blobをフォームデータに追加
            }
            else {
                formData.append('image', file);
            }
            $('.loading_container').addClass('loading_container_open');
            $.ajax({
                url: prefix + '/card/ocr', // Laravelのルートに合わせて変更
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                },
                success: function (response) {
                    if (response.status === 'success') {
                        try{
                        autoFillForm(response.data);
                        getCompanyCandidate(response.data.会社名, true);
                        }
                        catch(e){
                            console.log(e);
                            $('.loading_container').removeClass('loading_container_open');
                        }
                    } else {
                        console.log("エラーが発生しました。", response.message);
                    }
                    $('.loading_container').removeClass('loading_container_open');
                },
                error: function (xhr) {
                    alert('画像の処理に失敗しました。');
                    console.error(xhr.responseText);
                    $('.loading_container').removeClass('loading_container_open');
                }
            });
        }
        // フォームにデータを自動入力
        function autoFillForm(data) {
            $('#name').val(data.名前);
            $('#name_kana').val(data.名前カナ);
            $('#department_name').val(data.部署名);
            $('#position').val(data.役職);
            $('#phone_number').val(data.携帯電話番号);
            $('#email').val(data.メールアドレス);
            department_reset();
            var i = 1;
            while (data['部署' + i]) {
                if ($('#department' + i).length === 0) {
                    $('#add_department').click();
                }
                $('#department' + i).val(data['部署' + i]);
                i++;
            }

            $('#company_name').val(data.会社名);
            $('#company_name_kana').val(data.会社名カナ);
            $('#company_phone_number').val(data.電話番号);
            $('#company_fax_number').val(data.FAX番号);
            $('#company_address').val(data.住所);
            $('#company_postal_code').val(data.郵便番号);
        }
        // 会社候補を取得して表示
        // AI読み取りで候補がない場合は候補を表示せずに「新規」表示だけをおこなう
        function getCompanyCandidate(company_name, auto = false) {
            var prefix = $('#prefix').val();
            $.ajax({
                url: prefix + '/card/company/candidate',
                type: 'GET',
                data: { company_name: company_name },
                success: function (response) {
                    $('.company_candidate_container').empty();
                    if (auto && response.length == 0) {
                        new_company();
                    }
                    else {
                        $('.company_candidate_container').addClass('company_candidate_container_open');
                        $('.company_candidate_container').append(`
                        <input type="radio" name="company_candidate" value="new" id="company_candidate_new">
                        <label class="company_candidate_content company_candidate_new" for="company_candidate_new">
                            <div class="compapany_card_img_container">
                                <img alt="">
                            </div>
                            <div class="company_candidate_item">
                                新規登録
                            </div>
                        </label>
                        `);
                        response.forEach(function (company) {
                            $('.company_candidate_container').append(`
                        <input type="radio" name="company_candidate" value="${company.card.id}" id="company_candidate_${company.card.id}">
                        <label class="company_candidate_content" for="company_candidate_${company.card.id}">
                            <div class="compapany_card_img_container">
                                <img data-card_id="${company.card.id}" alt="">
                            </div>
                            <div class="company_candidate_item">
                                ${company.会社名}
                            </div>
                        </label>
                        `);
                            getCompanyCardImage($('.company_candidate_content:last-child .compapany_card_img_container img'), company.card.id);
                        });
                    }
                }
            });
        }
        // 会社情報を取得して表示
        function company_info_get(id) {
            var prefix = $('#prefix').val();
            $.ajax({
                url: prefix + '/card/company/info/' + id,
                type: 'GET',
                success: function (response) {
                    $('#company_name').val(response.会社名);
                    $('#company_name').attr('readonly', true);
                    $('#company_name').addClass('company_choiced');
                    $('#company_name_kana').val(response.会社名カナ);
                    $('#company_name_kana').attr('disabled', true);
                    $('#company_address').val(response.住所);
                    $('#company_address').attr('disabled', true);
                    $('#company_phone_number').val(response.電話番号);
                    $('#company_phone_number').attr('disabled', true);
                    $('#company_fax_number').val(response.FAX番号);
                    $('#company_fax_number').attr('disabled', true);
                    $('.company_td').find('.new_company_tag').remove();
                }
            });
        }
        // 新規会社を追加
        function new_company() {
            $('.company_choiced').removeClass('company_choiced');
            $('#company_name').attr('readonly', false);
            $('#company_name_kana').attr('disabled', false);
            $('#company_address').attr('disabled', false);
            $('#company_phone_number').attr('disabled', false);
            $('#company_fax_number').attr('disabled', false);
            if ($('.company_td').find('.new_company_tag').length == 0) {
                $('.company_td').append(`
                <div class="new_company_tag">
                    新規
                </div>
            `);
            }
        }


        // 部署追加ボタンが押されたときに部署を追加
        $('#add_department').on('click', function () {
            var departmentNumber = $('#add_department').data('now_department_number');
            var next_department_number = departmentNumber + 1;
            var next_department = `
        <tr>
            <td>部署${next_department_number}</td>
            <td><input type="text" name="department${next_department_number}" class="department" id="department${next_department_number}" data-department_number="${next_department_number}"></td>
            <td><div class="delete_department_button">×</div></td>
        </tr>
        `;
            $('#add_department').closest('tr').before(next_department);
            $('#add_department').data('now_department_number', next_department_number);

        });

        // 部署をリセット
        function department_reset() {
            $('.department:not([name="department1"])').closest('tr').remove();
            $('#add_department').data('now_department_number', 1);
        }

        // 部署削除ボタンが押されたときに部署を削除
        $(document).on('click', '.delete_department_button', function () {
            var departmentNumber = $(this).data('department_number');
            $(this).closest('tr').remove();
            $('#add_department').data('now_department_number', departmentNumber - 1);
            department_rename();
        });
        // 部署番号を振り直す
        function department_rename() {
            var departmentNumber = 1;
            $('.department').each(function () {
                $(this).closest('tr').find('td:first-child').text('部署' + departmentNumber);
                $(this).attr('name', 'department' + departmentNumber);
                $(this).attr('id', 'department' + departmentNumber);
                $(this).data('department_number', departmentNumber);
                $('#add_department').data('now_department_number', departmentNumber);
                departmentNumber++;
            });
        }

        //登録ボタンが押されたとき
        $('.submit_button').on('click', function () {
            const form = $('#card_regist_form');
            // 既存のBlob用のinputがあれば削除（重複防止のため）
            form.find('input[name="blob-image"]').remove();

            if (croppedBlob) {
                // 新しいinput要素を作成してBlobデータを設定
                const blobInput = $('<input>', {
                    type: 'file',
                    name: 'blob-image',
                    css: {
                        display: 'none'
                    }
                });

                // BlobデータをFileオブジェクトに変換
                const mimeType = croppedBlob.type;
                const extension = mimeType.split('/')[1];
                const file = new File([croppedBlob], 'cropped-image.' + extension, { type: mimeType });

                // input要素にFileオブジェクトを設定
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                blobInput[0].files = dataTransfer.files;

                // フォームにinput要素を追加
                form.append(blobInput);

            }

            // フォームを送信
            form.submit();
        });
        $('#card_regist_form').on('submit', function (e) {
            e.preventDefault();
            this.submit();
        });
    }
    // 名刺一覧画面
    if ($('#card_view_title').length > 0) {
        var prefix = $('#prefix').val();
        lazyload('imgset');

    }

    // 名刺詳細画面
    if ($('#card_detail_title').length > 0) {
        var prefix = $('#prefix').val();
        lazyload('imgset');
        $(document).on('change', 'input[name="card_history"]', function () {
            var card_edit_button = $('#card_edit_button');
            card_edit_button.attr('href', prefix + '/card/edit/' + $(this).val());
            $('.imgset').data('card_id',$(this).val())
            $('.card_history_container').toggleClass('card_history_container_open');
            $.ajax({
                url: prefix + '/card/history/' + $(this).val(),
                method: 'GET',
                success: function (response) {
                    console.log(response)
                    card_detail_renew(response)
                    designateload($('.card_detail_card .imgset'))
                }
            });

        });

        
        $('.card_history_button,.card_history_close_button').on('click', function () {
            $('.card_history_container').toggleClass('card_history_container_open');
        })

        function card_detail_renew(response){
            $('#name').text(response.名前);
            $('#name_kana').text(response.名前カナ);
            $('#phone_number').text(response.携帯電話番号);
            $('#email').text(response.メールアドレス);
            $('#company_name').text(response.会社名);
            $('#company_name_kana').text(response.会社名カナ);
            $('#company_address').text(response.会社所在地);
            $('#company_phone_number').text(response.電話番号);
            $('#company_fax_number').text(response.FAX番号);
            $('#position').text(response.役職);
            $('#department_name').text(response.部署名);
        }
    }
    // data-card_idから画像を読み込んで出力
    // addclassにはその要素に対してクラスを追加
    function lazyload(addclass) {
        var prefix = $('#prefix').val();
        $('img.lazyload').each(function () {
            var img = $(this);
            if ($('#server').val() == "cloud") {
                $.ajax({
                    url: prefix + '/card/img/' + img.data('card_id') + '/' + img.data('front'), // データを取得するURLを指定
                    method: 'GET',
                    dataType: "json",
                    success: function (response) {
                        if (response.Type === 'application/pdf') {
                            // var embed = $('<embed>');
                            // embed.attr('src', response.path);
                            // embed.attr('width', '100%');
                            // embed.attr('height', '600px');
                            // embed.attr('type', 'application/pdf');
                            // embed.addClass('imgset');

                            // $('.pastpreview').html(embed);
                        }
                        else if (response.Type.startsWith('image/')) {
                            img.attr('src', response.path);
                            img.addClass(addclass);
                        }
                    }
                });
            }
            else {
                $.ajax({
                    url: prefix + '/card/img/' + img.data('card_id') + '/' + img.data('front'), // データを取得するURLを指定
                    method: 'GET',
                    xhrFields: {
                        responseType: 'blob' // ファイルをBlobとして受け取る
                    },
                    success: function (response) {
                        var Url = URL.createObjectURL(response);
                        if (response.type.startsWith('image/')) {
                            img.attr('src', Url);
                            img.addClass(addclass);
                        }


                    },
                    error: function (xhr, status, error) {
                        console.error(error); // エラー処理
                    }
                });

            }
        });
    }
    // 指定したimgの画像を再読み込み
    function designateload(img){
        if ($('#server').val() == "cloud") {
            $.ajax({
                url: prefix + '/card/img/' + img.data('card_id') + '/' + img.data('front'), // データを取得するURLを指定
                method: 'GET',
                dataType: "json",
                success: function (response) {
                    var Url = URL.createObjectURL(response);
                    if (response.type.startsWith('image/')) {
                        img.attr('src', Url);
                    }


                },
                error: function (xhr, status, error) {
                    console.error(error); // エラー処理
                }
            });
        }
        else {
            $.ajax({
                url: prefix + '/card/img/' + img.data('card_id') + '/' + img.data('front'), // データを取得するURLを指定
                method: 'GET',
                xhrFields: {
                    responseType: 'blob' // ファイルをBlobとして受け取る
                },
                success: function (response) {
                    var Url = URL.createObjectURL(response);
                    if (response.type.startsWith('image/')) {
                        img.attr('src', Url);
                    }


                },
                error: function (xhr, status, error) {
                    console.error(error); // エラー処理
                }
            });

        }
    }
    // 会社カード画像の表(その会社に属する名刺の一つ)を取得して表示
    function getCompanyCardImage(img, card_id) {
        var prefix = $('#prefix').val();
        $.ajax({
            url: prefix + '/card/img/' + card_id + '/front',
            method: 'GET',
            xhrFields: {
                responseType: 'blob' // ファイルをBlobとして受け取る
            },
            success: function (response) {
                var Url = URL.createObjectURL(response);
                if (response.type.startsWith('image/')) {
                    img.attr('src', Url);
                    img.css('height', '100%');
                }
            }
        });
    }

});

