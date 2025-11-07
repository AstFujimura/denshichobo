$(document).ready(function () {


    // ファイル選択時
    let pdfDoc = null;
    let currentPage = 1;
    let canvas = null;
    let ctx = null;

    function renderPage(num) {
        pdfDoc.getPage(num).then(function (page) {
            const viewport = page.getViewport({ scale: 1.5 });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            const renderContext = { canvasContext: ctx, viewport: viewport };
            page.render(renderContext);
            $('#page_info').text(`${num} / ${pdfDoc.numPages}`);
            $('#pdf_controls').show();
        });
    }

    // ファイル処理共通関数
    function handleFile(file) {
        const preview = $('#result_preview');
        preview.empty();

        if (!file) return;
        const fileType = file.type;

        if (fileType.includes('pdf')) {
            preview.html(`
                <div id="pdf_controls">
                    <button id="prev_page">前のページ</button>
                    <span id="page_info"></span>
                    <button id="next_page">次のページ</button>
                </div>
                <canvas id="pdf_canvas"></canvas>
            `);
            canvas = document.getElementById('pdf_canvas');
            ctx = canvas.getContext('2d');

            const fileReader = new FileReader();
            fileReader.onload = function () {
                const typedarray = new Uint8Array(this.result);
                pdfjsLib.getDocument(typedarray).promise.then(function (pdf) {
                    pdfDoc = pdf;
                    currentPage = 1;
                    renderPage(currentPage);
                });
            };
            fileReader.readAsArrayBuffer(file);

            preview.off('click', '#prev_page');
            preview.off('click', '#next_page');

            preview.on('click', '#prev_page', function () {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage(currentPage);
                }
            });

            preview.on('click', '#next_page', function () {
                if (currentPage < pdfDoc.numPages) {
                    currentPage++;
                    renderPage(currentPage);
                }
            });
        } else if (fileType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                preview.html(`<img src="${ev.target.result}" class="image-preview">`);
            };
            reader.readAsDataURL(file);
        } else {
            preview.text("対応していないファイル形式です。");
        }
    }

    // input change イベント
    $('#file_input').on('change', function (e) {
        const file = e.target.files[0];
        handleFile(file);
    });

    // プレビュークリックで input 発火
    $('#result_preview').on('click', function () {
        $('#file_input').click();
    });

    // ドラッグ＆ドロップ対応
    $('#result_preview').on('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    $('#result_preview').on('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    $('#result_preview').on('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        const file = e.originalEvent.dataTransfer.files[0];
        handleFile(file);
    });

    // 送信処理
    $('#transcribe_form').submit(function (event) {
        event.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#gemini_output').html('<pre>' + response.result + '</pre>');
            },
            error: function (xhr) {
                $('#gemini_output').html('<p class="text-danger">エラー: ' + xhr.responseText + '</p>');
            }
        });
    });
});
