// 確認ボックス
function confirm_box(title, message, callback) {
    var confirm_box_container = $('<div class="confirm_box_container"></div>');
    var confirm_box = $('<div class="confirm_box"></div>');
    confirm_box.html(`
            <h2 class="confirm_box_title">`
        + title +
        `</h2>
            <p class="confirm_box_message">`
        + message +
        `</p>

              <div class="confirm_button_container">
              <div class="cancel_button">いいえ</div>
              <div class="confirm_button">はい</div>
              </div>`);


    confirm_box_container.append(confirm_box);
    confirm_box_container.appendTo('body');
    confirm_box_container.on('click', '.confirm_button', function () {
        confirm_box_container.remove();
        callback('aaa', 'bbb');
    });
    confirm_box_container.on('click', '.cancel_button', function () {
        confirm_box_container.remove();
    });
}
// 選択肢ボックス
function select_box(title, message, select_array, callback) {
    var select_box_container = $('<div class="select_box_container"></div>');
    var select_box = $('<div class="select_box"></div>');
    select_box.html(`
        <h2 class="select_box_title">`
        + title +
        `</h2>
        <p class="select_box_message">`
        + message +
        `</p>
        <div class="select_box_button_container">
        `
        + select_array.map(function (item, index) {
            return `<div class="select_box_button" data-value="${index}">${item}</div>`;
        }).join('') +
        `
        </div>`);

    select_box_container.append(select_box);
    select_box_container.appendTo('body');
    select_box_container.on('click', '.select_box_button', function () {
        select_box_container.remove();
        callback($(this).data('value'));
    });
}

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
                                // var Url = URL.createObjectURL(response);
                                img.attr('src', response.path);
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
                                    // var Url = URL.createObjectURL(response);
                                    img.attr('src', response.path);
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
            if ($('#edit').val() == 'add' || $('#edit').val() == 'edit') {
                formData.append('existing_search', 'false');
            }
            else {
                formData.append('existing_search', 'true');
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
                        console.log(response);
                        try {
                            if (response.existing_card) {

                                let select_array = [];
                                let message = '';

                                // 自分がすでに登録済みである可能性のある場合
                                if (response.mycard) {
                                    message = `「${response.data.名前}」さんはすでに登録済みです。次のアクションを選択してください。`;
                                    select_array = [
                                        { key: 'register_other', label: '別人物として名刺を登録する' },
                                        { key: 'add_card', label: `「${response.data.名前}」さんの名刺を追加する` },
                                        { key: 'edit_card', label: '登録済みの名刺を編集する' },
                                        { key: 'cancel', label: '登録をキャンセルする' }
                                    ];
                                }
                                // 他のユーザーがすでに登録済みである可能性のある場合
                                else if (response.otheruser) {
                                    message = `「${response.data.名前}」さんはすでに${response.otheruser}さんが登録済みです。次のアクションを選択してください。`;
                                    select_array = [
                                        { key: 'register_other', label: '別人物として名刺を登録する' },
                                        { key: 'mycard', label: 'マイ名刺登録する' },
                                        { key: 'cancel', label: '登録をキャンセルする' }
                                    ];
                                }

                                // select_box用にラベルだけの配列を渡す
                                select_box('確認', message,
                                    select_array.map(item => item.label),
                                    function (result) {
                                        // 選択キーを取得
                                        const selectedKey = select_array[result].key;

                                        switch (selectedKey) {
                                            // 別人物として名刺を登録する
                                            case 'register_other':
                                                autoFillForm(response.data);
                                                getCompanyCandidate(response.data.会社名, true);
                                                console.log(response.data);
                                                console.log(response.existing_card);
                                                break;

                                            // 自分でマイ名刺登録しておりそのユーザーの名刺を追加する時
                                            case 'add_card':
                                                autoFillForm(response.data);
                                                getCompanyCandidate(response.data.会社名, true);
                                                $('#edit').val('add');
                                                $('#carduser').val(response.existing_card.名刺ユーザーID);
                                                $('#card_regist_title span').text(response.data.名前 + 'さん 名刺追加');
                                                break;
                                            case 'mycard':
                                                window.location.href = prefix + '/card/mycard/' + response.existing_card.id;
                                                break;

                                            // 登録済みの名刺を編集する
                                            case 'edit_card':
                                                window.location.href = prefix + '/card/edit/' + response.existing_card.id;
                                                break;

                                            // 登録をキャンセルする
                                            case 'cancel':
                                                location.reload();
                                                break;
                                        }
                                    }
                                );
                            }

                            else {
                                // 別人物として名刺を登録する
                                autoFillForm(response.data);
                                getCompanyCandidate(response.data.会社名, true);
                                console.log(response.data);
                                console.log(response.token);
                            }
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
                        // $('#branch_address').attr('disabled', true);
                        $('#branch_phone_number').val(response.branches[0].電話番号);
                        // $('#branch_phone_number').attr('disabled', true);
                        $('#branch_fax_number').val(response.branches[0].FAX番号);
                        // $('#branch_fax_number').attr('disabled', true);
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

        function required_check() {
            var required_error = false;
            $('.required_error').removeClass('required_error');
            $('[data-required="true"]').each(function () {
                if ($(this).val() == '') {
                    $(this).addClass('required_error');
                    required_error = true;
                }
            });
            if (required_error) {
                alert('必須項目を入力してください。');
            }
            return required_error;
        }

        //登録ボタンが押されたとき
        $('.submit_button').on('click', function () {
            if (required_check()) {
                return;
            }
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

        // Excel出力ボタンを押したとき
        $('.card_view_excel_button').on('click', function () {
            var form = $('#card_view_excel_form');

            // card_view_card のdata-showがtrueのものを配列で取得して送信
            var card_view_card = $('.card_view_card[data-show="true"]');
            var card_view_card_array = [];
            console.log(card_view_card);
            card_view_card.each(function () {
                card_view_card_array.push($(this).find('img[data-front="front"]').attr('data-card_id'));
            });
            console.log(card_view_card_array);
            form.find('input[name="card_view_card_array"]').val(JSON.stringify(card_view_card_array));

            form.submit();
        });


        var prefix = $('#prefix').val();
        lazyload('imgset');

        var user_id = $('#user_id').val();
        other_user_card_check(user_id);

        var start_date_input = flatpickr('#start_date', {
            dateFormat: 'Y/m/d',
            allowInput: true,
            locale: 'ja'
        })
        var end_date_input = flatpickr('#end_date', {
            dateFormat: 'Y/m/d',
            allowInput: true,
            locale: 'ja'
        })

        // 並び替えを押したとき
        $('#sort_select').on('change', function () {
            const sortType = $(this).val();
            const $container = $('.card_view_container');

            // aタグの配列を取得
            let cards = $container.find('.card_view_card').get();

            cards.sort(function (a, b) {
                let valA, valB;
                if (sortType === '1') {
                    valA = $(a).data('name_kana');
                    valB = $(b).data('name_kana');
                } else if (sortType === '2') {
                    valA = $(a).data('company_name');
                    valB = $(b).data('company_name');
                } else if (sortType === '3') {
                    valA = $(a).data('card_created_at');
                    valB = $(b).data('card_created_at');
                } else if (sortType === '4') {
                    valA = $(a).data('card_updated_at');
                    valB = $(b).data('card_updated_at');
                } else {
                    return 0; // 並び替えなし
                }
                // localeCompareで日本語にも対応
                return valA.localeCompare(valB, 'ja');
            });

            // 並び替えた要素を再配置
            $container.append(cards);
            search_card();
        });
        // 表示タイプを押したとき
        $('input[name="view_type"]').on('change', function () {
            const viewType = $(this).val();
            $('.card_view_card').removeClass('large_view small_view');
            $('.card_view_card').addClass(viewType);
            $('.card_view_card_header').removeClass('large_view small_view');
            $('.card_view_card_header').addClass(viewType);
        });

        // 他のユーザーの登録情報を見るボタンを押したときはaタグの遷移を行わない
        $(document).on('click', '.other_user_card_check', function (e) {
            e.preventDefault();    // aタグのデフォルト動作（遷移）を止める
            e.stopPropagation();   // 親要素へのイベント伝播を止める
        });

        // 会社名を押したとき
        $(document).on('click', '.card_view_card_company', function (e) {
            e.preventDefault();    // aタグのデフォルト動作（遷移）を止める
            e.stopPropagation();   // 親要素へのイベント伝播を止める
            var company_id = $(this).data('company_id');
            window.location.href = prefix + '/card/company/edit/' + company_id;
        });


        // 検索のフォーカス時にエンターを押したとき
        $('.search_input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                $('.search_button').click();
            }
        });


        // 検索を押した時
        $(document).on('click', '.search_button', function () {
            search_card();
        });
        // 登録年月日の値が変更した時
        $('#start_date,#end_date').on('change', function () {
            search_card();
        });

        // 検索文字と登録年月日で名刺を絞り込む
        function search_card() {
            var search_text = $('.search_input').val();
            $('.search_card').removeClass('search_card');
            $('.none_search_card').removeClass('none_search_card');
            $('.card_view_card').each(function () {
                if ($(this).text().includes(search_text)) {
                    $(this).addClass('search_card');

                    var start_date_str = $('#start_date').val() || '1900/01/01';
                    var end_date_str = $('#end_date').val() || '2100/12/31';

                    // 日付文字列を Date オブジェクトに変換（スラッシュとハイフンの違いを統一）
                    var start_date = new Date(start_date_str.replace(/\//g, '-'));
                    var end_date = new Date(end_date_str.replace(/\//g, '-'));

                    var card_created_at_str = $(this).data('card_created_at'); // 例: "2025-08-25 10:25:52"
                    // 時間部分を切り離して日付だけをDateに変換する場合
                    var card_date = new Date(card_created_at_str.split(' ')[0]);

                    if (card_date >= start_date && card_date <= end_date) {
                        $(this).addClass('search_card');
                        $(this).removeClass('none_search_card');
                    } else {
                        $(this).addClass('none_search_card');
                        $(this).removeClass('search_card');
                    }

                }
                else {
                    $(this).addClass('none_search_card');
                }


            });
            card_view_header_count_text_update();
        }


        if ($('.card_view_header_count_text').length > 0) {
            card_view_header_count_text_update();
        }
        function card_view_header_count_text_update() {
            $('.card_view_header_count_text').text($('.card_view_card[data-show="true"]:not(.none_search_card)').length);
        }

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
            card_view_header_count_text_update();
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

    function company_valid_check() {
        var company_name = $('#card_company_edit_name').val();
        var company_name_kana = $('#card_company_edit_name_kana').val();
        if (company_name == '') {
            alert('会社名を入力してください');
            return false;
        }
        if (company_name_kana == '') {
            alert('会社名カナを入力してください');
            return false;
        }
        // 会社名カナが全てカタカナ
        if (!/^[ァ-ヶー]+$/.test(company_name_kana)) {
            alert('会社名カナは全てカタカナで入力してください');
            return false;
        }

        return true;
    }

    // 会社編集画面

    if ($('#card_company_edit_title').length > 0) {
        $('.card_company_edit_button').on('click', function () {
            if (company_valid_check()) {
                if (confirm('会社情報を更新しますか？')) {
                $('#card_company_edit_form').submit();
                }
            }
        });

        $('.card_company_edit_button_cancel').on('click', function () {
            // リロード
            location.reload();
        });

        // 部署削除ボタンを押した時
        $(document).on('click', '.card_company_edit_department_item_delete', function () {
            $(this).closest('.card_company_edit_department_item').remove();
        });
        // 拠点削除ボタンを押した時
        $(document).on('click', '.card_company_edit_branch_item_delete', function () {
            $(this).closest('.card_company_edit_branch_item').remove();
        });

        // 部署追加ボタンを押した時
        $('.department_add_button').on('click', function () {

            var prefix = $('#prefix').val();
            $('.department_add_button').before(`
                <div class="card_company_edit_department_item">
                    <input type="text" name="new_department_name[]" value="">
                    <div class="card_company_edit_department_item_delete">
                        <img src="${prefix}/img/card/delete.svg" alt="">
                    </div>
                </div>
            `);
        });
        var new_branch_id = 0;
        // 拠点追加ボタンを押した時
        $('.branch_add_button').on('click', function () {
            var prefix = $('#prefix').val();
            new_branch_id++;
            $('.branch_add_button').before(`
                <div class="card_company_edit_branch_item">
                    <input type="text" name="new_branch[${new_branch_id}][branch_name]" value="">
                    <div class="card_company_edit_branch_item_delete">
                        <img src="${prefix}/img/card/delete.svg" alt="">
                    </div>
                    <div class="card_company_edit_branch_item_detail">
                        <div class="card_company_edit_branch_item_detail_content">
                            <div class="card_company_edit_branch_item_detail_title">
                                住所
                            </div>
                            <div class="card_company_edit_branch_item_detail_content">
                                <input type="text" name="new_branch[${new_branch_id}][branch_address]" value="">
                            </div>
                        </div>
                        <div class="card_company_edit_branch_item_detail_content">
                            <div class="card_company_edit_branch_item_detail_title">
                                電話番号
                            </div>
                            <div class="card_company_edit_branch_item_detail_content">
                                <input type="text" name="new_branch[${new_branch_id}][branch_tel]" value="">
                            </div>
                        </div>
                        <div class="card_company_edit_branch_item_detail_content">
                            <div class="card_company_edit_branch_item_detail_title">
                                FAX番号
                            </div>
                            <div class="card_company_edit_branch_item_detail_content">
                                <input type="text" name="new_branch[${new_branch_id}][branch_fax]" value="">
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });


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
    // 一覧画面において他のユーザーの名刺があるかどうかをチェック
    function other_user_card_check(user_id) {
        var prefix = $('#prefix').val();
        $.ajax({
            url: prefix + '/card/other_user_card_check/' + user_id,
            method: 'GET',
            success: function (response) {
                console.log(response);
                response.forEach(function (card) {
                    $('.other_user_card_check[data-carduser_id="' + card.名刺ユーザーID + '"]').removeClass('display_none');
                    $('.other_user_card_check[data-carduser_id="' + card.名刺ユーザーID + '"] .other_user_list').append(
                        `<span class="other_user_list_item">${card.name}</span>`);
                });
            },
            error: function (xhr, status, error) {
                console.error(error); // エラー処理
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

    // $('#folder_upload').on('change', function () {
    //     const files = this.files;
    //     if (files.length > 0) {
    //         // 最初のファイルのフォルダ名を取得
    //         const folderName = files[0].webkitRelativePath.split('/')[0];
    //         $('.folder_upload_label_text').text('選択中:フォルダ名「 ' + folderName + '」');
    //         $('.upload_button').addClass('enabled');
    //     } else {
    //         $('.folder_upload_label_text').text('タップしてフォルダを選択');
    //         $('.upload_button').removeClass('enabled');
    //     }
    // });
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
        $('.analyzing_text').addClass('loading');
        $('.folder_upload_label').addClass('loading');
        let uploadId = generateUUID(); // ここで一度作成！
        const allowedExtensions = ['jpg', 'jpeg', 'png'];
        const files = Array.from(event.target.files);

        if (files.length === 0) {
            $('.analyzing_text').removeClass('loading');
            $('.folder_upload_label').removeClass('loading');
            return;
        }
        // 拡張子フィルタ
        selectedFiles = files.filter(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            return allowedExtensions.includes(ext);
        });
        if (selectedFiles.length === 0) {
            alert('アップロードできる画像ファイルがありません。');
            $('.analyzing_text').removeClass('loading');
            $('.folder_upload_label').removeClass('loading');
            return;
        }

        selectedFiles = Array.from(event.target.files);
        let fileMap = {}; // cleanedName ごとに front/back を管理
        let payload = []; // サーバーに送る配列

        selectedFiles.forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png'].includes(ext)) return;

            // 個別IDを生成して file オブジェクトに保持
            file.core_id = generateUUID();

            const basename = file.name.replace(/\.\w+$/, ''); // 拡張子除去
            let front_back = 'front';
            let cleanedName = basename;

            const match = basename.match(/_(\d+)$/);
            if (match) {
                const number = parseInt(match[1], 10);
                cleanedName = basename.replace(/_\d+$/, '');

                if (number === 1) {
                    front_back = 'back';
                } else {
                    // _002 以降はスキップ
                    return;
                }
            }

            // 同じ cleanedName で front/back が重複していないか確認
            if (!fileMap[cleanedName]) {
                fileMap[cleanedName] = {};
            }
            if (fileMap[cleanedName][front_back]) {
                return; // 重複はスキップ
            }

            fileMap[cleanedName][front_back] = true;

            // payload に格納（file.id を含める）
            payload.push({
                core_id: file.core_id,
                front_back: front_back,
                filename: cleanedName
            });
        });


        // ここで payload を一括送信
        $.ajax({
            url: prefix + '/card/multiple/past',
            method: 'POST',  // ← GET から POST に変更
            data: JSON.stringify({
                files: payload,
                upload_id: uploadId
            }),
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            contentType: 'application/json', // JSON で送る宣言
            success: function (response) {
                $('.upload_list_container').addClass('upload_list_container_open');
                $('.upload_list_item_container').empty();
                response.forEach(function (item) {
                    // 元のFileオブジェクト取得
                    let originalFile = selectedFiles.find(f => f.core_id === item.core_id);

                    // 見つかったら status を追加
                    if (originalFile) {
                        originalFile.uploaded_card_id = item.uploaded_card_id;
                        originalFile.status = item.status; // ← ここで status を保持
                        originalFile.front_back = item.front_back;
                        originalFile.card_id = item.card_id;

                    }

                    let imgUrl = originalFile ? URL.createObjectURL(originalFile) : '';

                    // data属性用フラグ初期化
                    let isMyCard = (item.status === 'mycard');
                    let isOtherCard = (item.status === 'othercard');
                    let isNewCard = (item.status === 'newcard');

                    // 既存アイテムチェック
                    let list_item = $('.upload_list_item[data-uploaded_card_id="' + item.uploaded_card_id + '"]');

                    if (item.front_back === 'front') {
                        if (list_item.length > 0) {
                            list_item.find('.upload_list_item_front').append(`
                                <img class="front_img" src="${imgUrl}" alt="${item.filename}" data-core_id="${item.core_id}">
                            `);
                        } else {
                            $('.upload_list_item_container').append(`
                                <label for="${item.core_id}" class="upload_list_item"
                                     data-uploaded_card_id="${item.uploaded_card_id}"
                                     data-mycard="${isMyCard}"
                                     data-othercard="${isOtherCard}"
                                     data-newcard="${isNewCard}">
                                    <div class="checkbox_list_item">
                                        <input type="checkbox"
                                               class="checkbox_list_item_checkbox"
                                               data-uploaded_card_id="${item.uploaded_card_id}"
                                               id="${item.core_id}"
                                               >
                                    </div>
                                    <div class="upload_list_item_front">
                                        <img class="front_img" src="${imgUrl}" alt="${item.filename}" data-core_id="${item.core_id}">
                                    </div>
                                    <div class="upload_list_item_back"></div>
                                    <div class="upload_list_name">
                                        ${item.filename}
                                    </div>
                                </label>
                            `);
                        }
                    } else if (item.front_back === 'back') {
                        if (list_item.length > 0) {
                            list_item.find('.upload_list_item_back').append(`
                                <img class="back_img" src="${imgUrl}" alt="${item.filename}" data-core_id="${item.core_id}">
                            `);
                        } else {
                            $('.upload_list_item_container').append(`
                                <label for="${item.core_id}" class="upload_list_item"
                                     data-uploaded_card_id="${item.uploaded_card_id}"
                                     data-mycard="${isMyCard}"
                                     data-othercard="${isOtherCard}"
                                     data-newcard="${isNewCard}">
                                    <div class="checkbox_list_item">
                                        <input type="checkbox"
                                               class="checkbox_list_item_checkbox"
                                               data-uploaded_card_id="${item.uploaded_card_id}"
                                               id="${item.core_id}"
                                               >
                                    </div>
                                    <div class="upload_list_item_front"></div>
                                    <div class="upload_list_item_back">
                                        <img class="back_img" src="${imgUrl}" alt="${item.filename}" data-core_id="${item.core_id}">
                                    </div>
                                    <div class="upload_list_name">
                                        ${item.filename}
                                    </div>
                                </label>
                            `);
                        }
                    }
                });
                $('.upload_button').addClass('enabled');
                $('.analyzing_text').removeClass('loading');
            },


            error: function () {
                console.error('送信失敗');
            }
        });



    });
    $(document).on('change', '.checkbox_controller_item_all', function () {
        if (this.checked) {
            $('.upload_list_item:not([data-mycard="true"]) .checkbox_list_item_checkbox').prop('checked', true);

            $('#checkbox_controller_item_new').prop('checked', false);
        }
        else {
            $('.upload_list_item:not([data-mycard="true"]) .checkbox_list_item_checkbox').prop('checked', false);
        }
        checkbox_reload();
    });
    $(document).on('click', '.checkbox_controller_item_new', function () {
        if (this.checked) {
            $('.upload_list_item[data-newcard="true"] .checkbox_list_item_checkbox').prop('checked', true);
            $('.upload_list_item[data-othercard="true"] .checkbox_list_item_checkbox').prop('checked', false);
            $('.upload_list_item[data-mycard="true"] .checkbox_list_item_checkbox').prop('checked', false);

        }
        else {
            $('.upload_list_item[data-newcard="true"] .checkbox_list_item_checkbox').prop('checked', false);
        }
        checkbox_reload();
    });
    // チェックボックスの変更時に selectedFiles に反映
    $(document).on('change', '.checkbox_list_item_checkbox', function () {
        checkbox_reload();
    });
    function checkbox_reload() {
        $('.checkbox_list_item_checkbox').each(function () {
            if ($(this).closest('.upload_list_item').attr('data-mycard') == 'true' || $(this).closest('.upload_list_item').attr('data-success') == 'true') {
                $(this).remove();
            }
            const uploaded_card_id = $(this).data('uploaded_card_id');
            var front_core_id = $(this).closest('.upload_list_item').find('.front_img').data('core_id');
            var back_core_id = $(this).closest('.upload_list_item').find('.back_img').data('core_id');
            if ($(this).is(':checked')) {
                // 元のFileオブジェクト取得
                let front_originalFile = selectedFiles.find(f => f.core_id === front_core_id);
                let back_originalFile = selectedFiles.find(f => f.core_id === back_core_id);
                // 見つかったら status を追加
                if (front_originalFile) {
                    front_originalFile.check = true;
                }
                if (back_originalFile) {
                    back_originalFile.check = true;
                }
            }
            else {
                let front_originalFile = selectedFiles.find(f => f.core_id === front_core_id);
                let back_originalFile = selectedFiles.find(f => f.core_id === back_core_id);
                if (front_originalFile) {
                    front_originalFile.check = false;
                }
            }
        });
    }
    $(document).on('click', '.upload_button_cancel', function (event) {
        $('.upload_list_container').removeClass('upload_list_container_open');
        $('.upload_list_item_container').empty();
        $('.upload_button').removeClass('enabled');
        $('.analyzing_text').removeClass('loading');
        $('.folder_upload_label').removeClass('loading');

        selectedFiles = [];
        $('#folder_upload').val('');
    })

    $(document).on('click', '.upload_button.enabled', function (event) {
        if (confirm('名刺を一括アップロードしますか？')) {
            checkbox_reload();
            $('#multiple_upload_form').submit();
        }
    })

    $('#multiple_upload_form').on('submit', function (event) {
        event.preventDefault();
        const filesToSend = selectedFiles.filter(f => f.check);

        let index = 0;
        $('.progress_container_wrapper').addClass('progress_container_wrapper_open');
        $('#uploadedfiles_count').val(0);
        $('#total_files_count').val(filesToSend.length);


        selectedFiles.forEach(f => f.check = false);
        // チェックボックスが外れているものを削除
        $('.checkbox_list_item_checkbox').each(function () {
            if (!$(this).prop('checked') && $(this).closest('.upload_list_item').attr('data-failed') != 'true') {
                $(this).closest('.upload_list_item').remove();
            }
        });
        // マイ名刺候補を削除
        $('.upload_list_item[data-mycard="true"]').remove();

        $('.checkbox_description_container[data-status="new"]').addClass('close');
        $('.checkbox_description_container[data-status="again"]').removeClass('close');
        $('.checkbox_controller_item_container').addClass('close');
        $('.upload_button').text('再送開始');

        const sendNext = () => {
            if (index >= filesToSend.length) {
                return; // 全部送信完了
            }


            const fileObj = filesToSend[index];
            index++;

            const formData = new FormData();
            if (fileObj.status == 'mycard' || fileObj.status == 'othercard') {
                formData.append('file', null);
            } else {
                formData.append('file', fileObj);
            }
            formData.append('uploaded_card_id', fileObj.uploaded_card_id);
            formData.append('status', fileObj.status);
            formData.append('front_back', fileObj.front_back);
            formData.append('card_id', fileObj.card_id);

            // 応答を待たずに送信だけする
            $.ajax({
                url: prefix + '/card/multiple/upload',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function (response) {
                    if (response.status === 'success') {
                        console.log('送信成功', fileObj.uploaded_card_id, response);
                        if (response.front_back === 'front') {
                            $('.upload_list_item[data-uploaded_card_id="' + fileObj.uploaded_card_id + '"]').attr('data-success', 'true');
                            $('.upload_list_item[data-uploaded_card_id="' + fileObj.uploaded_card_id + '"]').attr('data-failed', 'false');
                            $('.upload_list_item[data-uploaded_card_id="' + fileObj.uploaded_card_id + '"]').find('.checkbox_list_item_checkbox').remove();
                        }
                        $('#uploadedfiles_count').val(parseInt($('#uploadedfiles_count').val()) + 1);
                        var progress = (Math.floor(((parseInt($('#uploadedfiles_count').val()) + 1) / parseInt($('#total_files_count').val())) * 1000) / 10) + '%';
                        $('.progress_message').text('AI解析中 :' + progress);
                        $('.progress_bar').css('width', progress);

                        if (parseInt($('#uploadedfiles_count').val()) >= parseInt($('#total_files_count').val())) {
                            $('.progress_message').text('AI解析完了');
                            $('.progress_bar').css('width', '100%');
                            $('.progress_container_wrapper').removeClass('progress_container_wrapper_open');
                        }
                    }
                    else if (response.status === 'error') {
                        console.error('送信失敗', fileObj.uploaded_card_id);
                        if (response.front_back === 'front') {
                            $('.upload_list_item[data-uploaded_card_id="' + fileObj.uploaded_card_id + '"]').attr('data-failed', 'true');
                        }
                        $('#total_files_count').val(parseInt($('#total_files_count').val()) - 1);
                        var progress = (Math.floor(((parseInt($('#uploadedfiles_count').val()) + 1) / parseInt($('#total_files_count').val())) * 1000) / 10) + '%';
                        $('.progress_message').text('AI解析中 :' + progress);
                        $('.progress_bar').css('width', progress);
                        if (parseInt($('#uploadedfiles_count').val()) >= parseInt($('#total_files_count').val())) {
                            $('.progress_message').text('AI解析完了');
                            $('.progress_bar').css('width', '100%');
                            $('.progress_container_wrapper').removeClass('progress_container_wrapper_open');
                        }
                    }
                    // 取込件数の再読み込み
                    count_reload()
                },
                error: function () {
                    console.error('送信失敗', fileObj.uploaded_card_id);
                    if (response.front_back === 'front') {
                        $('.upload_list_item[data-uploaded_card_id="' + fileObj.uploaded_card_id + '"]').attr('data-failed', 'true');
                    }
                    $('#total_files_count').val(parseInt($('#total_files_count').val()) - 1);
                    var progress = (Math.floor(((parseInt($('#uploadedfiles_count').val()) + 1) / parseInt($('#total_files_count').val())) * 1000) / 10) + '%';
                    $('.progress_message').text('AI解析中 :' + progress);
                    $('.progress_bar').css('width', progress);
                    if (parseInt($('#uploadedfiles_count').val()) >= parseInt($('#total_files_count').val())) {
                        $('.progress_message').text('AI解析完了');
                        $('.progress_bar').css('width', '100%');
                        $('.progress_container_wrapper').removeClass('progress_container_wrapper_open');
                    }
                }
            });


            // 0.3秒後に次を送信
            setTimeout(sendNext, 300);
        };

        sendNext(); // 最初の送信開始
    });

    function count_reload() {
        var failed_count = $('.upload_list_item[data-failed="true"]').length;
        var success_count = $('.upload_list_item[data-success="true"]').length;
        $('.failed_card_count').text(failed_count + '件');
        $('.success_card_count').text(success_count + '件');
    }


    function processing_check(uploadId) {
        var prefix = $('#prefix').val();
        var intervalId = setInterval(function () {
            // 小数第一位の位で切り捨てを行います
            var progress = (Math.floor(((parseInt($('#uploadedfiles_count').val()) + 1) / parseInt($('#total_files_count').val())) * 1000) / 10) + '%';
            $('.progress_message').text('AI解析中 :' + progress);
            $('.progress_bar').css('width', progress);
            if ($('#uploadedfiles_count').val() === $('#total_files_count').val()) {
                $('.folder_upload_label_text').text('タップしてフォルダを選択');
                $('#upload_complete_flag').val('true');
                clearInterval(intervalId); // 通信を止める
                $('.progress_container_wrapper').removeClass('progress_container_wrapper_open');
                alert($('#frontfiles_count').val() + '件の名刺を新規登録しました。');
                if ($('.error_wrapper').hasClass('error_wrapper_open')) {
                    alert('名刺一括アップロードに失敗した名刺があります。');
                }
                return;
            }

        }, 300);
    }

    // $(document).on('click', '.error_button_resend', function (event) {
    //     var form = $(this).closest('.error_content').find('form');
    //     form.submit();
    // })
    // $(document).on('click', '.error_button_delete', function (event) {
    //     var error_content = $(this).closest('.error_content');
    //     error_content.remove();
    //     if ($('.error_content:not(.error_content_clone)').length === 0) {
    //         $('.error_wrapper').removeClass('error_wrapper_open');
    //     }
    // })
    // $(document).on('submit', '.resend_form', function (event) {
    //     event.preventDefault();
    //     var form = $(this);
    //     var formData = new FormData(form[0]);
    //     var prefix = $('#prefix').val();
    //     var failedIndex = form.attr('data-failed_index');
    //     var data = failedUploads[failedIndex];
    //     formData.append('cards', data);
    //     $.ajax({
    //         url: prefix + '/card/multiple/upload',
    //         type: 'POST',
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         success: function (response) {
    //             if (response.status === 'success') {
    //                 form.closest('.error_content').remove();
    //                 if ($('.error_content:not(.error_content_clone)').length === 0) {
    //                     $('.error_wrapper').removeClass('error_wrapper_open');
    //                 }
    //             }
    //         },
    //         error: function (xhr, status, error) {
    //             alert('アップロードに失敗しました');
    //         }
    //     })
    // })

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

