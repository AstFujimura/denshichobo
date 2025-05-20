$(document).ready(function () {
    // 名刺登録画面
    if ($('#card_regist_title').length > 0) {

        var prefix = $('#prefix').val();
        if ($('#edit').val() == 'edit') {
            const $front_img = $('<img>')
                .addClass('croppable_image')
                .attr('data-card_type', 'front');
            $('#card_file_front_label .cropped_image_container').html($front_img);
            $('.croppable_image[data-card_type="front"]').each(function () {
                var img = $(this);
                if ($('#server').val() == "cloud") {
                    $.ajax({
                        url: prefix + '/card/img/' + $('#card_id').val() + '/front', // データを取得するURLを指定
                        method: 'GET',
                        dataType: "json",
                        success: function (response) {
                            if (response.Type === 'application/pdf') {
                            }
                            else if (response.Type.startsWith('image/')) {
                                var Url = URL.createObjectURL(response);
                                img.attr('src', Url);
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
            if ($('#back_image').val() != '') {
                const $back_img = $('<img>')
                    .addClass('croppable_image')
                    .attr('data-card_type', 'back');
                $('#card_file_back_label .cropped_image_container').html($back_img);
                $('.croppable_image[data-card_type="back"]').each(function () {
                    var img = $(this);
                    if ($('#server').val() == "cloud") {
                        $.ajax({
                            url: prefix + '/card/img/' + $('#card_id').val() + '/back', // データを取得するURLを指定
                            method: 'GET',
                            dataType: "json",
                            success: function (response) {
                                if (response.Type === 'application/pdf') {
                                }
                                else if (response.Type.startsWith('image/')) {
                                    var Url = URL.createObjectURL(response);
                                    img.attr('src', Url);
                                }
                            }
                        });
                    }
                    else {
                        $.ajax({
                            url: prefix + '/card/img/' + $('#card_id').val() + '/back', // データを取得するURLを指定
                            method: 'GET',
                            xhrFields: {
                                responseType: 'blob' // ファイルをBlobとして受け取る
                            },
                            success: function (response) {
                                var Url = URL.createObjectURL(response);
                                if (response.type.startsWith('image/')) {
                                    img.attr('src', Url);
                                }
                            }
                        });
                    }
                });
            }

            $('.button_container').addClass('button_container_open');
        }

        // 裏表切り替えボタンが押されたとき
        $('#card_switch_button').on('click', function () {
            const card_status = $('#card_status').data('card_type');
            $('.card_switch_button').removeClass('card_switch_button_active');
            $(this).addClass('card_switch_button_active');
            if (card_status == 'front') {
                $('#card_status').data('card_type', 'back');
                $('#card_status').attr('data-card_type', 'back');
                $('#card_status').text('裏面');
                $('#card_file_front_label').addClass('display_none');
                $('#card_file_back_label').removeClass('display_none');
            }
            else {
                $('#card_status').data('card_type', 'front');
                $('#card_status').attr('data-card_type', 'front');
                $('#card_status').text('表面');
                $('#card_file_front_label').removeClass('display_none');
                $('#card_file_back_label').addClass('display_none');
            }
            button_container_open()
        });

        // const canvas = $('#canvas')[0];
        // const ctx = canvas.getContext('2d');
        var cropper;
        var lastCropData
        let front_croppedBlob;  // トリミング済み画像のBlobを保持
        let back_croppedBlob;  // トリミング済み画像のBlobを保持

        // 画像が変更されたときにCanvasに描画
        $('.card_file_input').on('change', function () {
            const card_status = $(this).data('card_type');
            // 前回のトリミング範囲をリセット
            lastCropData = null;
            if (card_status == 'front') {
                front_croppedBlob = null; // Blobをリセット
            }
            else {
                back_croppedBlob = null; // Blobをリセット
            }
            const file = this.files[0]; // 変更されたファイルを取得
            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    // 古い画像とCropperを削除
                    if (cropper) {
                        cropper.destroy();
                    }
                    const $img = $('<img>')
                        .addClass('croppable_image')
                        .attr('data-card_type', card_status);

                    $('#card_file_' + card_status + '_label .cropped_image_container').html($img);
                    $('.croppable_image[data-card_type="' + card_status + '"]').attr('src', e.target.result);

                    button_container_open(card_status);
                    // 画像がロードされたときの処理
                    $('.croppable_image').on('load', function () {

                        // 画像が読み込まれた状態を設定
                        $('#card_file_front').data('imageLoaded', true);
                    });
                };

                reader.readAsDataURL(file);
            }
        });
        // ボタンコンテナを開くか否かの関数
        function button_container_open() {
            const card_status = $("#card_status").data('card_type');
            $('.button_container').addClass('button_container_open');
            if (card_status == 'front') {
                $('#send_button').removeClass('display_none');
                $('#remove_button').addClass('display_none');
            }
            else {
                if ($('#card_file_back').val() != '') {
                    $('#send_button').addClass('display_none');
                    $('#remove_button').removeClass('display_none');
                }
                else {
                    $('.button_container').removeClass('button_container_open');
                }
            }
        }


        //切り取りボタンが押されたとき
        $('.crop_button').on('click', function () {
            const card_status = $("#card_status").data('card_type');
            $('.crop_controller_container').removeClass('display_none');
            $('.crop_controller_content').addClass('display_none');
            $('.crop_controller_content[data-card_type="' + card_status + '"]').removeClass('display_none');
            // Cropper.js の初期化
            cropper = new Cropper($('.crop_controller_content .croppable_image[data-card_type="' + card_status + '"]')[0], {
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
        // 解除ボタンが押されたとき
        $('#remove_button').on('click', function () {
            $('#card_file_back_label').html(
                `<div class="cropped_image_container">
                    <div class="cropped_image_container_text">
                        裏 タップして名刺を読みこんでください
                    </div>
                </div>
                `
            );
            $('#card_file_back').val('');
            $('.croppable_image[data-card_type="back"]').attr('src', '');
            button_container_open();
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
            const card_status = $("#card_status").data('card_type');
            $('.crop_controller_container').addClass('display_none');

            // 現在のトリミング範囲を保存
            lastCropData = cropper.getData();
            // トリミングされた画像をBlob形式で取得
            cropper.getCroppedCanvas().toBlob(function (blob) {
                if (card_status == 'front') {
                    front_croppedBlob = blob; // Blobを保存
                }
                else {
                    back_croppedBlob = blob; // Blobを保存
                }
                const croppedImageURL = URL.createObjectURL(blob); // BlobをURLに変換してプレビュー表示用に利用
                $('#card_file_' + card_status + '_label .croppable_image').attr('src', croppedImageURL);
            });
            cropper.destroy();

        });



        //切り取りキャンセル
        $('.crop_cancel_button').on('click', function () {
            $('.crop_controller_container').addClass('display_none');
            cropper.destroy();
        });

        // 会社候補検索ボタンが押されたとき
        $('.company_search_button').on('click', function () {
            $('.company_candidate_container').addClass('company_candidate_container_open');
            $('.company_candidate_container_background').addClass('company_candidate_container_background_open');
            getCompanyCandidate($('.company_search_button').closest('tr').find('input[name="company_name"]').val());
        });
        // 会社候補背景をクリックしたとき
        $('.company_candidate_container_background').on('click', function () {
            $('.company_candidate_container').removeClass('company_candidate_container_open');
            $('.company_candidate_container_background').removeClass('company_candidate_container_background_open');
        });
        // 会社候補が選択されたとき
        $(document).on('change', '.company_candidate_container input[type="radio"]', function () {
            $('.company_candidate_container').removeClass('company_candidate_container_open');
            $('.company_candidate_container_background').removeClass('company_candidate_container_background_open');
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

        // 拠点追加ボタンが押されたとき
        $(document).on('click', '.add_branch_button', function () {
            $('#branch_name_container').html(
                `<input type="text" name="branch_name" id="branch_name" autocomplete="off">`
            );
            $('#branch_name').focus();
            $('#branch_address').attr('disabled', false);
            $('#branch_phone_number').attr('disabled', false);
            $('#branch_fax_number').attr('disabled', false);
        });

        // 拠点を選択した時
        $(document).on('change', '.branch_name_select', function () {
            var branch_data = $(this).find('option:selected').data();
            $('#branch_address').val(branch_data.address);
            $('#branch_phone_number').val(branch_data.phone_number);
            $('#branch_fax_number').val(branch_data.fax_number);
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
            if (front_croppedBlob) {
                formData.append('blob-image', front_croppedBlob, 'cropped-image.png'); // Blobをフォームデータに追加
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
                        try {
                            autoFillForm(response.data);
                            getCompanyCandidate(response.data.会社名, true);
                            console.log(response.data);
                            console.log(response.token);
                        }
                        catch (e) {
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
            $('#branch_name').val(data.拠点名);
            $('#branch_address').val(data.住所);
            $('#branch_phone_number').val(data.電話番号);
            $('#branch_fax_number').val(data.FAX番号);
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
                        $('.company_candidate_container_background').addClass('company_candidate_container_background_open');
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
                            console.log(company);
                            $('.company_candidate_container').append(`
                        <input type="radio" name="company_candidate" value="${company.id}" id="company_candidate_${company.id}">
                        <label class="company_candidate_content" for="company_candidate_${company.id}">
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
            var candidate_branch = $('#branch_name').val();
            $.ajax({
                url: prefix + '/card/company/info/' + id,
                type: 'GET',
                data: { candidate_branch: candidate_branch },
                success: function (response) {
                    console.log(response);
                    $('#company_name').val(response.company.会社名);
                    $('#company_name').attr('readonly', true);
                    $('#company_name').addClass('company_choiced');
                    $('#company_name_kana').val(response.company.会社名カナ);
                    $('#company_name_kana').attr('disabled', true);

                    // 拠点指定がある場合は拠点のセレクトボックスを置く
                    if (response.designate_branch) {
                        $('#branch_name_container').html(`
                        <select class="branch_name_select" name="branch_id" id="branch_id">
                            ${response.branches.map(branch => `<option value="${branch.id}" data-address="${branch.拠点所在地}" data-phone_number="${branch.電話番号}" data-fax_number="${branch.FAX番号}">${branch.拠点名}</option>`).join('')}
                          </select>
                          <span class="add_branch_button">
                            拠点追加
                          </span>
                        `);

                    }
                    if (response.candidate_branch) {
                        $('#branch_address').val(response.candidate_branch.拠点所在地);
                        $('#branch_address').attr('disabled', true);
                        $('#branch_phone_number').val(response.candidate_branch.電話番号);
                        $('#branch_phone_number').attr('disabled', true);
                        $('#branch_fax_number').val(response.candidate_branch.FAX番号);
                        $('#branch_fax_number').attr('disabled', true);
                        $('#branch_id option[value="' + response.candidate_branch.id + '"]').prop('selected', true);

                    }
                    else {
                        $('#branch_address').val(response.branches[0].拠点所在地);
                        $('#branch_address').attr('disabled', true);
                        $('#branch_phone_number').val(response.branches[0].電話番号);
                        $('#branch_phone_number').attr('disabled', true);
                        $('#branch_fax_number').val(response.branches[0].FAX番号);
                        $('#branch_fax_number').attr('disabled', true);
                    }

                    $('.company_td').find('.new_company_tag').remove();
                }
            });
        }
        // 新規会社を追加
        function new_company() {
            $('.company_choiced').removeClass('company_choiced');
            $('#company_name').attr('readonly', false);
            $('#company_name_kana').attr('disabled', false);
            if ($('#branch_id').length > 0) {
                $('#branch_name_container').html(
                    `<input type="text" name="branch_name" id="branch_name" autocomplete="off">`
                );
            }
            $('#branch_address').attr('disabled', false);
            $('#branch_phone_number').attr('disabled', false);
            $('#branch_fax_number').attr('disabled', false);
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
            $('.department').val('');
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

            if (front_croppedBlob) {
                // 新しいinput要素を作成してBlobデータを設定
                const blobInput = $('<input>', {
                    type: 'file',
                    name: 'front_blob-image',
                    css: {
                        display: 'none'
                    }
                });

                // BlobデータをFileオブジェクトに変換
                const mimeType = front_croppedBlob.type;
                const extension = mimeType.split('/')[1];
                const file = new File([front_croppedBlob], 'front_cropped-image.' + extension, { type: mimeType });

                // input要素にFileオブジェクトを設定
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                blobInput[0].files = dataTransfer.files;

                // フォームにinput要素を追加
                form.append(blobInput);

            }
            if (back_croppedBlob) {
                // 新しいinput要素を作成してBlobデータを設定
                const blobInput = $('<input>', {
                    type: 'file',
                    name: 'back_blob-image',
                    css: {
                        display: 'none'
                    }
                });

                // BlobデータをFileオブジェクトに変換
                const mimeType = back_croppedBlob.type;
                const extension = mimeType.split('/')[1];
                const file = new File([back_croppedBlob], 'back_cropped-image.' + extension, { type: mimeType });

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

        // 検索のフォーカス時にエンターを押したとき
        $('.search_input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                $('.search_button').click();
            }
        });


        // 検索を押した時
        $(document).on('click', '.search_button', function () {

            var search_text = $('.search_input').val();
            $('.search_card').removeClass('search_card');
            $('.none_search_card').removeClass('none_search_card');
            $('.card_view_card').each(function () {
                if ($(this).text().includes(search_text)) {
                    $(this).addClass('search_card');
                }
                // else if ($(this).find('.card_view_card_company').text().includes(search_text)) {
                //     $(this).addClass('search_card');
                // }
                else {
                    $(this).addClass('none_search_card');
                }
            });
        });

        // 名刺の種類のタブを切り替えた時
        $(document).on('click', '.tab_item:not(.tab_item_active)', function () {
            $('.tab_item').removeClass('tab_item_active');
            $(this).addClass('tab_item_active');
            if ($(this).data('tab') == 'my_card_user') {
                $('.card_view_card').each(function () {
                    if ($(this).attr('data-my_card_user') == "true") {
                        $(this).data('show', "true");
                        $(this).attr('data-show', "true");
                    }
                    else {
                        $(this).data('show', "false");
                        $(this).attr('data-show', "false");
                    }
                });
            }
            else if ($(this).data('tab') == 'favorite_user') {
                $('.card_view_card').each(function () {
                    if ($(this).attr('data-favorite_user') == "true") {
                        $(this).data('show', "true");
                        $(this).attr('data-show', "true");
                    }
                    else {
                        $(this).data('show', "false");
                        $(this).attr('data-show', "false");
                    }
                });
            }
            else {
                $('.card_view_card').data('show', "true");
                $('.card_view_card').attr('data-show', "true");

            }
            lazyload('imgset');
        });


    }

    // 名刺詳細画面
    if ($('#card_detail_title').length > 0) {
        var prefix = $('#prefix').val();
        lazyload('imgset');

        // マイ名刺・お気に入りチェックを押した時
        $('.favorite_check').on('change', function () {
            var checkbox = $(this); // ← ここで this を保存
            var card_user_id = $(this).data('card_user_id');
            var check = $(this).is(':checked');
            var type = $(this).attr('id');
            $.ajax({
                url: prefix + '/card/favorite',
                method: 'POST',
                data: {
                    card_user_id: card_user_id,
                    check: check,
                    type: type
                },
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function (response) {
                    if (!response.success) {
                        alert("エラーが発生しました。");
                        checkbox.prop('checked', !check);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error); // エラー処理
                    alert("エラーが発生しました。");
                    checkbox.prop('checked', !check);
                }
            });
        });

        $('.card_latest_button').on('click', function () {
            var card_id = $(this).data('card_id');
            
            $.ajax({
                url: prefix + '/card/latest',
                method: 'POST',
                data: {
                    card_id: card_id
                },
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function (response) {
                    if (!response.success) {
                        alert("エラーが発生しました。");
                    }
                    else {
                        alert("名刺を最新にしました。");
                        $('.new_card_check').addClass('display_none');
                        $('.new_card_check[data-card_id="' + card_id + '"]').removeClass('display_none');
                        $('.card_latest_button').addClass('display_none');
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error); // エラー処理
                    alert("エラーが発生しました。");
                }
            });
        });



        // 歴代の名刺の選択が変わった時
        $(document).on('change', 'input[name="card_history"]', function () {
            var card_edit_button = $('.card_edit_button');
            var card_delete_button = $('.card_delete_button');
            var card_latest_button = $('.card_latest_button');
            card_edit_button.attr('href', prefix + '/card/edit/' + $(this).val());
            card_edit_button.data('card_id', $(this).val());
            card_edit_button.attr('data-card_id', $(this).val());
            card_delete_button.data('card_id', $(this).val());
            card_delete_button.attr('data-card_id', $(this).val());
            card_latest_button.data('card_id', $(this).val());
            card_latest_button.attr('data-card_id', $(this).val());


            // 最新フラグが経っている場合は「この名刺を最新にするボタンを非表示」
            if ($(this).closest('.card_history_content').find('.new_card_check:not(.display_none)').length != 0) {
                $('.card_latest_button').addClass('display_none');
            }
            else {
                $('.card_latest_button').removeClass('display_none');
            }


            $('.imgset').data('card_id', $(this).val())
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
        // 設定ボタンを押したとき
        $('.card_setting_button').on('click', function () {
            $('.gray_area').addClass('gray_area_open');
        });
        // グレーエリアをクリックした時
        $('.gray_area').on('click', function (e) {
            // 編集ボタンや名刺削除ボタンでなければグレーエリアを非表示
            if ($(e.target).closest('.card_setting_button,.card_delete_button').length == 0) {
                $('.gray_area').removeClass('gray_area_open');
            }
        });
        // 削除ボタンを押したとき
        $('.card_delete_button').on('click', function () {
            var prefix = $('#prefix').val();

            if (confirm('本当に名刺を削除しますか')) {
                let cardId = $(this).data('card_id'); // ボタンにdata-id属性があると仮定
                let actionUrl = prefix + '/card/delete'; // 削除用のエンドポイント（適宜変更）

                let form = $('<form>', {
                    'method': 'POST',
                    'action': actionUrl
                }).append(
                    $('<input>', {
                        'type': 'hidden',
                        'name': 'card_id',
                        'value': cardId
                    }),
                    $('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': $('input[name="_token"]').val() // CSRF対策（Laravelの場合）
                    })
                );

                $('body').append(form);
                form.submit();
            }
        })

        $('.card_history_button,.card_history_close_button').on('click', function () {
            $('.card_history_container').toggleClass('card_history_container_open');


        })

        function card_detail_renew(response) {
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



            $('.department_content').each(function () {
                $(this).remove();
            })
            response.department.forEach(function (department, index) {
                $('.position_info').append(`
                    <div class="position_info_content department_content">
                        <div class="position_info_content_title">
                            部署${index + 1}
                        </div>
                        <div class="position_info_content_text" id="department{{$index+1}}">
                            ${department.部署名}
                        </div>
                    </div>
                `)
            })
        }
    }
    // data-card_idから画像を読み込んで出力
    // addclassにはその要素に対してクラスを追加
    function lazyload(addclass) {
        var prefix = $('#prefix').val();
        // 名刺一覧画面の画像読み込み(addclassがあれば読み込まない)
        $('img.lazyload:not(.' + addclass + ')').each(function () {
            // 親要素のdata-showがfalseなら読み込まない
            if ($(this).closest('.card_view_card').attr('data-show') == "false") {
                return
            }
            else {
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
            }
        });
    }
    // 指定したimgの画像を再読み込み
    function designateload(img) {
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

    $('#folder_upload').on('change', function () {
        const files = this.files;
        if (files.length > 0) {
            // 最初のファイルのフォルダ名を取得
            const folderName = files[0].webkitRelativePath.split('/')[0];
            $('.folder_upload_label_text').text('選択中:フォルダ名「 ' + folderName + '」');
            $('.upload_button').addClass('enabled');
        } else {
            $('.folder_upload_label_text').text('タップしてフォルダを選択');
            $('.upload_button').removeClass('enabled');
        }
    });
    // uuid生成
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // 名刺一括アップロード
    let selectedFiles = [];

    $('#folder_upload').on('change', function (event) {
        selectedFiles = Array.from(event.target.files);
    });
    $('.upload_button').on('click', function (event) {
        if (confirm('名刺を一括アップロードしますか？')) {
            $('.progress_container_wrapper').addClass('progress_container_wrapper_open');
            $('.progress_bar').css('width', '0%');
            $('.progress_message').text('アップロード中');
            $('#multiple_upload_form').submit();
        }
    })
    $('#multiple_upload_form').on('submit', function (event) {
        event.preventDefault(); // 通常のフォーム送信は止める
        let uploadId = generateUUID(); // ここで一度作成！
        var prefix = $('#prefix').val();
        const allowedExtensions = ['jpg', 'jpeg', 'png'];

        let validFiles = selectedFiles.filter(function (file) {
            const extension = file.name.split('.').pop().toLowerCase();
            return allowedExtensions.includes(extension);
        });

        if (validFiles.length === 0) {
            alert('アップロードできる画像ファイルがありません。');
            return;
        }

        var totalFiles = validFiles.length;
        $('#total_files_count').val(totalFiles);
        let uploadedFiles = 0;
        $('#uploadedfiles_count').val(uploadedFiles);
        processing_check(uploadId)

        var upload_index = 0;
        var send_index = 0;
        validFiles.forEach(function (file) {
            // 過去データ参照 選択肢として新規登録
            setTimeout(function () {
                $.ajax({
                    url: prefix + '/card/multiple/past',
                    method: 'GET',
                    data: {
                        filename: file.name,
                        upload_id: uploadId
                    },
                    success: function (response) {
                        if (response.status === 'new' || response.status === 'add_front' || response.status === 'new_back' || response.status === 'back') {
                            // 新規登録
                            const formData = new FormData();
                            formData.append('cards', file);
                            formData.append('upload_id', uploadId);
                            formData.append('status', response.status);
                            formData.append('uploaded_card_id', response.uploaded_card_id);
                            formData.append('filename', response.filename);
                            $.ajax({
                                url: prefix + '/card/multiple/upload',
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                                },
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function (response) {
                                    if (response.status === 'front_success') {
                                        setTimeout(function () {
                                            $.ajax({
                                                url: prefix + '/card/openai/process',
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                                                },
                                                data: {
                                                    uploaded_card_id: response.uploaded_card_id,
                                                    type: response.type
                                                },
                                                success: function (response) {
                                                    console.log('OpenAI process' + uploadedFiles);

                                                    uploadedFiles++;
                                                    $('#uploadedfiles_count').val(uploadedFiles);
                                                },
                                                error: function (response) {
                                                    console.error('OpenAI process failed.' + uploadedFiles);

                                                    totalFiles--;
                                                    $('#total_files_count').val(totalFiles);
                                                }
                                            });

                                        }, send_index * 3000); // 3秒ごとにずらす（3000ms）    

                                        send_index++;
                                    }
                                    else if (response.status === 'back_success') {
                                        uploadedFiles++;
                                        $('#uploadedfiles_count').val(uploadedFiles);
                                    }
                                    else {
                                        totalFiles--;
                                        $('#total_files_count').val(totalFiles);
                                    }
                                },
                                error: function (xhr, status, error) {
                                }
                            });
                        }
                        else if (response.status === 'skip') {
                            // スキップ
                            totalFiles--;
                            $('#total_files_count').val(totalFiles);
                        }
                    }
                })
            }, upload_index * 500); // 0.5秒ごとにずらす（500ms）    

            upload_index++;
        });
    });

    function processing_check(uploadId) {
        var prefix = $('#prefix').val();
        var intervalId = setInterval(function () {
            var progress = parseInt(parseInt($('#uploadedfiles_count').val()) / parseInt($('#total_files_count').val()) * 100) + '%';
            $('.progress_message').text('AI解析中 :' + progress);
            $('.progress_bar').css('width', progress);
            if ($('#uploadedfiles_count').val() === $('#total_files_count').val()) {
                $('#upload_complete_flag').val('true');
                clearInterval(intervalId); // 通信を止める
                $('.progress_message').text('ai処理完了');
                $('.progress_container_wrapper').removeClass('progress_container_wrapper_open');
                return;
            }

        }, 300);
    }

    // function processing_check(uploadId) {
    //     var prefix = $('#prefix').val();
    //     var intervalId = setInterval(function () {
    //         $.ajax({
    //             url: prefix + '/card/multiple/progress',
    //             method: 'GET',
    //             data: {
    //                 upload_id: uploadId
    //             },
    //             success: function (response) {
    //                 var progress = parseInt(parseInt(response.done) / parseInt(response.total) * 80 + 20) + '%';
    //                 $('.progress_message').text('AI解析中 :' + progress);
    //                 $('.progress_bar').css('width', progress);
    //                 if (response.notdone === 0) {
    //                     $('#upload_complete_flag').val('true');
    //                     clearInterval(intervalId); // 通信を止める
    //                     $('.progress_message').text('ai処理完了');
    //                     $('.progress_container_wrapper').removeClass('progress_container_wrapper_open');
    //                     return;
    //                 }
    //             }
    //         });
    //     }, 300);
    // }
});

