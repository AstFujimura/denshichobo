$(document).ready(function () {
    // const canvas = $('#canvas')[0];
    // const ctx = canvas.getContext('2d');
    var cropper;
    var lastCropData

    // 画像が変更されたときにCanvasに描画
    $('#card_file_front').on('change', function () {
        // 前回のトリミング範囲をリセット
        lastCropData = null;
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
                $('.crop_button').addClass('crop_button_open');
                // 画像がロードされたときの処理
                $('.croppable_image').on('load', function () {

                    // 画像が読み込まれた状態を設定
                    $('#card_file_front').data('imageLoaded', true);
                });
            };

            reader.readAsDataURL(file);
        }
    });
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
    $('.crop_complete_button').on('click', function () {
        $('.crop_controller_container').removeClass('controller_open');

        // 現在のトリミング範囲を保存
        lastCropData = cropper.getData();
        // トリミングされた画像を取得
        const croppedImage = cropper.getCroppedCanvas().toDataURL();
        cropper.destroy();

        $('.card_file_front_label .croppable_image').attr('src', croppedImage);

    });
    $('.crop_cancel_button').on('click', function () {
        $('.crop_controller_container').removeClass('controller_open');
        cropper.destroy();
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
        const formData = new FormData();
        formData.append('image', file);

        $.ajax({
            url: '/card/ocr', // Laravelのルートに合わせて変更
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val(),
            },
            success: function (response) {
                if (response.status === 'success') {
                    console.log(response.data);
                } else {
                    console.log("エラーが発生しました。", response.message);
                }
            },
            error: function (xhr) {
                alert('画像の処理に失敗しました。');
                console.error(xhr.responseText);
            }
        });
    }

    // サーバーから受け取った輪郭データを基にCanvasに描画
    function drawBoundingBox(boundingBox) {
        ctx.strokeStyle = 'red';
        ctx.lineWidth = 2;

        // 輪郭の描画
        ctx.beginPath();
        ctx.moveTo(boundingBox[0].x, boundingBox[0].y);

        for (let i = 1; i < boundingBox.length; i++) {
            ctx.lineTo(boundingBox[i].x * canvas.width, boundingBox[i].y * canvas.height);
        }

        ctx.closePath();
        ctx.stroke();
    }
});
