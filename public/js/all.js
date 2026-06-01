$(document).ready(function () {
  if ($('#error_message').length != 0) {
    alert($('#error_message').val())
  }
  if ($('#success_message').length != 0) {
    alert($('#success_message').val())
  }
  var prefix = $('#prefix').val();


  //登録画面、変更画面以外は登録画面に遷移。(登録ボタンなどと間違う可能性が高いため)
  $('#registpagebutton').on('click', function (event) {
    $pagetitle = $('.pagetitle').text();
    if ($pagetitle != "帳簿変更" && $pagetitle != "帳簿保存" && $pagetitle != "変更履歴") {
      window.location.href = prefix + "/regist"
    }
  });


  $('.wholecontainer').on('click', function () {
    $(this).fadeOut();
    $('.previewcontainer').fadeOut();
  });

  //現在のページ番号をクリックしても遷移しない
  $('.nowpagebutton').on('click', function (event) {
    event.preventDefault()
  });
  //ドットをクリックしても遷移しない
  $('.dotpagebutton').on('click', function (event) {
    event.preventDefault()
  });


  function applyLedgerEditPreviewMedia($media) {
    $media.addClass('imgset');
    if ($('.ledger-regist--edit').length) {
      $media.css({ width: '100%', height: '100%' });
      return;
    }
    $media.attr('width', '100%');
    $media.attr('height', '600px');
  }

  function renderLedgerPreviewBlob(blob, $container, options) {
    options = options || {};
    var Url = URL.createObjectURL(blob);
    var $media;

    if (blob.type === 'application/pdf') {
      $media = $('<embed>');
      $media.attr('src', Url);
      $media.attr('type', 'application/pdf');
    } else if (blob.type.startsWith('image/')) {
      $media = $('<img>');
      $media.attr('src', Url);
    } else {
      if (options.unsupportedText) {
        $container.text(options.unsupportedText);
      }
      return;
    }

    if (options.useEditPreviewMedia) {
      applyLedgerEditPreviewMedia($media);
    } else {
      $media.attr('width', '100%');
      $media.attr('height', '100%');
      $media.addClass('imgset');
    }

    if (options.append) {
      $container.append($media);
    } else {
      $container.html($media);
    }
  }

  function loadLedgerPreview(fileId, $container, options) {
    $.ajax({
      url: prefix + '/img/' + fileId,
      method: 'GET',
      xhrFields: {
        responseType: 'blob'
      },
      success: function (response) {
        renderLedgerPreviewBlob(response, $container, options);
      },
      error: function (xhr, status, error) {
        console.error('Preview load failed:', error);
      }
    });
  }

  if ($(".pagetitle").text() == "帳簿変更") {
    var ID = $(".pagetitle").attr("id");
    loadLedgerPreview(ID, $('.pastpreview'), { useEditPreviewMedia: true });
  }

  //ダウンロードボタンを押したとき
  $('.downloadbutton').on('click', function () {
    if ($('#server').val() == "cloud") {
      window.location.href = $(this).attr("id")
    }
    else {
      window.location.href = $(this).attr("id")
    }
  });



  //プレビューボタンを押したとき
  $('.previewbutton').on('click', function () {
    $('.wholecontainer').fadeIn();
    $('.previewcontainer').fadeIn();
    // containerクラス内の要素を削除
    $(".previewcontainer").empty();
    var ID = $(this).attr("id");
    loadLedgerPreview(ID, $('.previewcontainer'), {
      append: true,
      unsupportedText: 'ファイルが変更されました'
    });
  });








  $('.input-field').keydown(function (event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化


      var currentIndex = $('.input-field').index(this);
      var nextInput = $('.input-field').eq(currentIndex + 1);

      if (nextInput.length === 0) {
        $('.form').submit(); // 最後の入力欄でエンターキーを押すとフォームが送信される
        $('#admin-myForm').submit();
        $('#myForm').submit();
      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });

  $('.searchinputtext').keydown(function (event) {
    if (event.keyCode === 13) { // エンターキーのキーコードは 13
      event.preventDefault(); // デフォルトのエンターキーの動作を無効化



      var currentIndex = $('.searchinputtext').index(this);
      var nextInput = $('.searchinputtext').eq(currentIndex + 1);

      if (nextInput.length === 0) {
        $('.searchbutton').focus(); // 最後の入力欄でエンターキーを押すとフォームが送信される

      } else {
        nextInput.focus(); // 次の入力欄にフォーカスを移動
      }
    }
  });







  $('.ledger-regist .droparea').on('click', function () {
    $('#file').trigger('click');
  });

  function isBanbanEnabled() {
    return $('#banbanEnabled').val() === '1';
  }

  function isIchifujiEnabled() {
    return $('#ichifujiEnabled').val() === '1';
  }

  function isAiOcrTraceEnabled() {
    return $('#aiOcrTraceEnabled').val() === '1';
  }

  function logAiOcrResponse(resp, label) {
    label = label || 'AI OCR';
    if (!resp) {
      console.warn(label + ': empty response');
      return;
    }
    if (resp.prompt) {
      console.log(label + ' prompt (最終):', resp.prompt);
    }
    console.log(label + ':', {
      ok: resp.ok,
      step: resp.step,
      error: resp.error,
      trace_id: resp.trace_id,
      teisyutu: resp.teisyutu,
      provider: resp.provider,
      data: resp.data,
      raw: resp.raw,
    });
  }

  function showAiOcrFailure(resp, xhr) {
    var msg = 'AI OCRに失敗しました';
    if (resp && (resp.step || resp.error || resp.trace_id)) {
      msg += '\n\nstep: ' + (resp.step || '(なし)');
      if (resp.error) msg += '\n' + resp.error;
      if (resp.trace_id) msg += '\ntrace_id: ' + resp.trace_id;
      if (resp.provider) msg += '\nprovider: ' + resp.provider;
    } else if (xhr) {
      msg += '\n\nHTTP ' + xhr.status;
      try {
        var parsed = JSON.parse(xhr.responseText);
        if (parsed.step) msg += '\nstep: ' + parsed.step;
        if (parsed.error) msg += '\n' + parsed.error;
        if (parsed.trace_id) msg += '\ntrace_id: ' + parsed.trace_id;
      } catch (e) { /* ignore */ }
    }
    console.warn('AI OCR failure:', resp || xhr);
    if (isAiOcrTraceEnabled()) {
      alert(msg);
    }
  }

  function sleepMs(ms) {
    return new Promise(function (resolve) { setTimeout(resolve, ms); });
  }

  function normalizeDateString(value) {
    if (!value) return '';
    var str = String(value).trim();
    // 例: 20260528 / 2026-05-28 / 2026.05.28 / 2026年5月28日 -> 2026/05/28
    var m = str.match(/^(\d{4})[\/\-.年]?\s*(\d{1,2})[\/\-.月]?\s*(\d{1,2})日?$/);
    if (m) {
      var y = m[1];
      var mm = String(m[2]).padStart(2, '0');
      var dd = String(m[3]).padStart(2, '0');
      return y + '/' + mm + '/' + dd;
    }
    var m2 = str.match(/^(\d{4})(\d{2})(\d{2})$/);
    if (m2) {
      return m2[1] + '/' + m2[2] + '/' + m2[3];
    }
    return str;
  }

  function normalizeAmount(value) {
    if (value === null || value === undefined) return '';
    var str = String(value).replace(/[,\s￥¥]/g, '').trim();
    str = str.replace(/[^\d.-]/g, '');
    return str;
  }

  function normalizeCompanyNameForMatch(value) {
    if (!value) return '';
    var s = String(value);
    s = s.replace(/\s+/g, '').toLowerCase();
    s = s.replace(/[（）\(\)\[\]【】]/g, '');
    s = s.replace(/[・,，\.。\/／\-ー—_]/g, '');
    s = s.replace(/株式会社|（株）|\(株\)|㈱/g, '');
    s = s.replace(/有限会社|（有）|\(有\)|㈲/g, '');
    s = s.replace(/合同会社|（同）|\(同\)/g, '');
    return s;
  }

  function fetchTorihikisakiCandidates(searchText) {
    return new Promise(function (resolve) {
      $.ajax({
        url: prefix + '/torihikisaki/',
        method: 'GET',
        data: { search: searchText },
        success: function (response) {
          if (response === "該当なし") {
            resolve([]);
            return;
          }
          if (Array.isArray(response)) {
            resolve(response.map(function (r) { return r.取引先; }).filter(Boolean));
            return;
          }
          resolve([]);
        },
        error: function () { resolve([]); },
      });
    });
  }

  function pickBestTorihikisakiCandidate(ocrValue, candidates) {
    var raw = (ocrValue || '').trim();
    if (!raw || !candidates || !candidates.length) return raw;

    var norm = normalizeCompanyNameForMatch(raw);
    if (!norm) return raw;

    var best = { score: 0, value: raw };

    candidates.forEach(function (c) {
      var cRaw = String(c || '').trim();
      if (!cRaw) return;
      var cNorm = normalizeCompanyNameForMatch(cRaw);
      if (!cNorm) return;

      var score = 0;
      if (cNorm === norm) {
        score = 1.0;
      } else if (cNorm.includes(norm) || norm.includes(cNorm)) {
        var shortLen = Math.min(cNorm.length, norm.length);
        var longLen = Math.max(cNorm.length, norm.length);
        score = 0.85 * (shortLen / Math.max(longLen, 1));
      }

      if (score > best.score) {
        best = { score: score, value: cRaw };
      }
    });

    return best.score >= 0.80 ? best.value : raw;
  }

  async function resolveTorihikisakiByExistingCandidates(ocrValue) {
    var raw = (ocrValue || '').trim();
    if (!raw) return '';
    var candidates = await fetchTorihikisakiCandidates(raw);
    return pickBestTorihikisakiCandidate(raw, candidates);
  }

  function renderPreviewInto($target, file) {
    if (!$target || !$target.length || !file) return;
    var fileType = file.type || '';
    if (fileType.startsWith("image/")) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $target.html('<img src="' + e.target.result + '" class="previewImage">');
      };
      reader.readAsDataURL(file);
      return;
    }
    if (fileType === "application/pdf") {
      var pdfUrl = URL.createObjectURL(file);
      var embed = $('<embed>');
      embed.attr('src', pdfUrl);
      embed.attr('type', 'application/pdf');
      applyLedgerEditPreviewMedia(embed);
      $target.html(embed);
      return;
    }
    $target.text("対応していないファイル形式です");
  }

  function applyOcrDataToRow($row, data) {
    if (!$row || !$row.length || !data) return;
    var hiduke = normalizeDateString(data.hiduke);
    var kinngaku = normalizeAmount(data.kinngaku);
    var torihikisaki = data.torihikisaki ? String(data.torihikisaki).trim() : '';

    if (hiduke) $row.find('[data-bulk-field="hiduke"]').val(hiduke).trigger('change').trigger('blur');
    if (kinngaku) $row.find('[data-bulk-field="kinngaku"]').val(kinngaku).trigger('change').trigger('blur');
    if (torihikisaki) {
      resolveTorihikisakiByExistingCandidates(torihikisaki).then(function (resolved) {
        if (resolved) {
          $row.find('[data-bulk-field="torihikisaki"]').val(resolved).trigger('change').trigger('blur');
        }
      });
    }
  }

  function getLedgerTeisyutuForOcr($row) {
    if ($row && $row.length) {
      var rowVal = $row.find('[data-bulk-field="teisyutu"]').val();
      if (rowVal) return rowVal;
    }
    if ($('#ledgerRegistTabBulk').hasClass('is-active')) {
      var bulkVal = $('#bulkCommonTeisyutu').val();
      if (bulkVal) return bulkVal;
    }
    var normalVal = $('#teisyutu').val();
    return normalVal || '受領';
  }

  function runSingleOcr(file, teisyutu) {
    var formData = new FormData();
    formData.append('_token', $('input[name="_token"]').val());
    formData.append('file', file);
    formData.append('teisyutu', teisyutu || '受領');

    return new Promise(function (resolve, reject) {
      $.ajax({
        url: prefix + '/ai/ocr/ledger',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (resp) { resolve(resp); },
        error: function (xhr) { reject({ status: xhr.status, body: xhr.responseText }); },
      });
    });
  }

  function ensureAiOcrButtonVisible(file) {
    if (!isIchifujiEnabled()) {
      return;
    }
    if (!file) {
      return;
    }
    if ($('#aiOcrLedgerButton').length) {
      return;
    }

    var $container = $('#aiOcrButtonSlot').first();
    if (!$container.length) {
      $container = $('.ledger-regist .torihikisakiinput').first();
    }
    if (!$container.length) {
      return;
    }

    var $btn = $('<button type="button" id="aiOcrLedgerButton" class="aiocrbutton">AI OCRで読み込む</button>');
    $btn.on('click', function () {
      var input = document.getElementById('file');
      var selected = input && input.files && input.files[0] ? input.files[0] : null;
      if (!selected) {
        alert('先にファイルを選択してください');
        return;
      }

      $btn.prop('disabled', true).text('AI OCR 実行中...');

      var teisyutu = getLedgerTeisyutuForOcr();

      runSingleOcr(selected, teisyutu).then(function (resp) {
          logAiOcrResponse(resp, 'AI OCR');
          if (resp && resp.ok && resp.data) {
            var hiduke = normalizeDateString(resp.data.hiduke);
            var kinngaku = normalizeAmount(resp.data.kinngaku);
            var torihikisaki = resp.data.torihikisaki ? String(resp.data.torihikisaki).trim() : '';

            if (hiduke) {
              $('#hiduke').val(hiduke).trigger('change').trigger('blur');
            }
            if (kinngaku) {
              $('#kinngaku').val(kinngaku).trigger('change').trigger('blur');
            }
            if (torihikisaki) {
              resolveTorihikisakiByExistingCandidates(torihikisaki).then(function (resolved) {
                if (resolved) {
                  $('#torihikisaki').val(resolved).trigger('change').trigger('blur');
                }
              });
            }
          } else {
            showAiOcrFailure(resp);
          }
      }).catch(function (xhr) {
          var resp = null;
          try { resp = JSON.parse(xhr.body); } catch (e) { /* ignore */ }
          logAiOcrResponse(resp, 'AI OCR error');
          showAiOcrFailure(resp, xhr);
      }).finally(function () {
          $btn.prop('disabled', false).text('AI OCRで読み込む');
      });
    });

    $container.append($btn);
  }

  $('.droparea').on('dragover', function (event) {
    event.preventDefault();
    $(this).addClass("dragover");
  });
  $('.droparea').on('dragleave', function (event) {
    event.preventDefault();
    $(this).removeClass("dragover");
  });

  $('.droparea').on('drop', function (event) {
    event.preventDefault();
    $(this).removeClass("dragover");
    var File = event.originalEvent.dataTransfer.files[0];
    $('#file').prop("files", event.originalEvent.dataTransfer.files);
    // ファイルのタイプを取得
    var fileType = File.type;

    // 画像をプレビューとして表示する
    if (fileType.startsWith("image/")) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('.previewarea').html('<img src="' + e.target.result + '" class="previewImage">');
        // $('.previewarea').addClass("previewopen");
        ensureAiOcrButtonVisible(File);
      };
      reader.readAsDataURL(File);
    }
    // PDFをプレビューとして表示する
    else if (fileType === "application/pdf") {
      var pdfUrl = URL.createObjectURL(File);
      var embed = $('<embed>');
      embed.attr('src', pdfUrl);
      embed.attr('type', 'application/pdf');
      applyLedgerEditPreviewMedia(embed);

      $('.previewarea').html(embed);
      // $('.previewarea').addClass("previewopen");
      ensureAiOcrButtonVisible(File);
    }
    else {
      if ($(".pagetitle").text() == "帳簿変更") {
        $('.previewarea').html("ファイルが変更されました")
      }
      else if ($(".pagetitle").text() == "帳簿保存") {
        $('.previewarea').html("ファイルが登録されました")
      }

    }
  });

  $('#file').change(function () {
    var input = this;

    if (input.files && input.files[0]) {
      if (this.files[0].type.startsWith("image/")) {
        var reader = new FileReader();

        reader.onload = function (e) {
          $('.previewarea').html('<img src="' + e.target.result + '" class="previewImage">');
          // $('.previewarea').addClass("previewopen");
          ensureAiOcrButtonVisible(input.files[0]);
        };


        reader.readAsDataURL(this.files[0]);

      }
      // PDFをプレビューとして表示する
      else if (this.files[0].type === "application/pdf") {
        var pdfUrl = URL.createObjectURL(this.files[0]);
        var embed = $('<embed>');
        embed.attr('src', pdfUrl);
        embed.attr('type', 'application/pdf');
        applyLedgerEditPreviewMedia(embed);

        $('.previewarea').html(embed);
        // $('.previewarea').addClass("previewopen");
        ensureAiOcrButtonVisible(input.files[0]);
      }

      else {
        if ($(".pagetitle").text() == "帳簿変更") {
          $('.previewarea').html("ファイルが変更されました")
        }
        else if ($(".pagetitle").text() == "帳簿保存") {
          $('.previewarea').html("ファイルが登録されました")
        }

      }


    }




  });

  // -------------------------------
  // 帳簿保存（BANBAN）: 通常/一括タブ + 一括取込
  // -------------------------------
  function initLedgerBulkUI() {
    if (!isBanbanEnabled()) return;
    if (!$('.ledger-regist-tabs').length) return;
    var aiEnabled = isIchifujiEnabled();
    $('#bulkAiOcrAll').toggle(aiEnabled);

    function switchTab(target) {
      $('.ledger-regist-tabs__tab').removeClass('is-active').attr('aria-selected', 'false');
      $('.ledger-regist-tabpanel').removeClass('is-active');
      if (target === 'bulk') {
        $('[data-ledger-tab="bulk"]').addClass('is-active').attr('aria-selected', 'true');
        $('#ledgerRegistTabBulk').addClass('is-active');
      } else {
        $('[data-ledger-tab="normal"]').addClass('is-active').attr('aria-selected', 'true');
        $('#ledgerRegistTabNormal').addClass('is-active');
      }
    }

    $(document).on('click', '.ledger-regist-tabs__tab', function () {
      var tab = $(this).data('ledger-tab');
      switchTab(tab);
    });

    function makeBulkRow(index, file) {
      var safeName = file && file.name ? file.name : ('file_' + index);
      var html = ''
        + '<div class="ledger-bulk-row" data-bulk-index="' + index + '">'
        + '  <div class="ledger-bulk-row__header">'
        + '    <div class="ledger-bulk-row__meta">'
        + '      <span class="ledger-bulk-row__name">' + safeName + '</span>'
        + '    </div>'
        + '    <div class="ledger-bulk-row__header-actions">'
        + '      <button type="button" class="ledger-bulk-row__toggle" data-bulk-action="toggle">フォームを表示</button>'
        + (aiEnabled ? '      <button type="button" class="aiocrbutton" data-bulk-action="ocrOne">AI OCR</button>' : '')
        + '    </div>'
        + '  </div>'
        + '  <div class="ledger-bulk-row__body" style="display:none;">'
        + '    <div class="ledger-bulk-row__layout">'
        + '      <div class="ledger-bulk-row__fields">'
        + '        <div class="ledger-regist__ocr-grid">'
        + '          <div class="ledger-regist__field">'
        + '            <label class="ledger-regist__label">取引日<span class="requirered">*</span></label>'
        + '            <div class="ledger-regist__control dateform">'
        + '              <input type="text" name="hiduke[]" class="input-field dateinputtext ledger-regist-date ledger-regist__input" data-bulk-field="hiduke" autocomplete="off" required>'
        + '              <span class="errorelement ledger-regist__error bulk-required-msg" data-bulk-error="hiduke">必須項目です</span>'
        + '            </div>'
        + '          </div>'
        + '          <div class="ledger-regist__field">'
        + '            <label class="ledger-regist__label">金額<span class="requirered">*</span></label>'
        + '            <div class="ledger-regist__control">'
        + '              <input type="text" name="kinngaku[]" class="input-field kinngakuinput-field ledger-regist__input ledger-regist__input--amount" data-bulk-field="kinngaku" required>'
        + '              <span class="errorelement ledger-regist__error bulk-required-msg" data-bulk-error="kinngaku">必須項目です</span>'
        + '            </div>'
        + '          </div>'
        + '          <div class="ledger-regist__field">'
        + '            <label class="ledger-regist__label">取引先<span class="requirered">*</span></label>'
        + '            <div class="ledger-regist__control torihikisakiinput">'
        + '              <input type="text" name="torihikisaki[]" class="input-field ledger-regist__input" data-bulk-field="torihikisaki" autocomplete="off" required>'
        + '              <span class="errorelement ledger-regist__error bulk-required-msg" data-bulk-error="torihikisaki">必須項目です</span>'
        + '            </div>'
        + '          </div>'
        + '        </div>'
        + '        <div class="ledger-regist__detail-grid">'
        + '          <div class="ledger-regist__field">'
        + '            <label class="ledger-regist__label">書類区分</label>'
        + '            <div class="ledger-regist__control">'
        + '              <select name="syorui[]" class="input-field ledger-regist__input ledger-regist__select" data-bulk-field="syorui"></select>'
        + '            </div>'
        + '          </div>'
        + '          <div class="ledger-regist__field">'
        + '            <label class="ledger-regist__label">受領・提出<span class="requirered">*</span></label>'
        + '            <div class="ledger-regist__control">'
        + '              <select name="teisyutu[]" class="input-field ledger-regist__input ledger-regist__select" data-bulk-field="teisyutu" required><option>受領</option><option>提出</option></select>'
        + '              <span class="errorelement ledger-regist__error bulk-required-msg" data-bulk-error="teisyutu">必須項目です</span>'
        + '            </div>'
        + '          </div>'
        + '          <div class="ledger-regist__field">'
        + '            <label class="ledger-regist__label">保存方法</label>'
        + '            <div class="ledger-regist__control">'
        + '              <select name="hozonn[]" class="input-field ledger-regist__input ledger-regist__select" data-bulk-field="hozonn"><option>電子保存</option><option>スキャナ保存</option></select>'
        + '            </div>'
        + '          </div>'
        + '          <div class="ledger-regist__field">'
        + '            <label class="ledger-regist__label">グループ</label>'
        + '            <div class="ledger-regist__control">'
        + '              <select name="group[]" class="input-field ledger-regist__input ledger-regist__select" data-bulk-field="group"></select>'
        + '            </div>'
        + '          </div>'
        + '          <div class="ledger-regist__field ledger-regist__field--span2">'
        + '            <label class="ledger-regist__label">検索ワード</label>'
        + '            <div class="ledger-regist__control">'
        + '              <input type="text" name="kennsakuword[]" class="input-field ledger-regist__input" data-bulk-field="kennsakuword">'
        + '            </div>'
        + '          </div>'
        + '        </div>'
        + '      </div>'
        + '      <div class="ledger-bulk-row__preview previewarea"></div>'
        + '    </div>'
        + '  </div>'
        + '</div>';

      var $row = $(html);
      $row.data('file', file);
      $row.find('[data-bulk-field="syorui"]').html($('#bulkCommonSyorui').html());
      $row.find('[data-bulk-field="group"]').html($('#bulkCommonGroup').html());
      renderPreviewInto($row.find('.ledger-bulk-row__preview'), file);
      return $row;
    }

    $('#bulkFiles').on('change', function (e) {
      var files = (e.target && e.target.files) ? Array.from(e.target.files) : [];
      var $list = $('#bulkFileList');
      $list.empty();
      files.forEach(function (file, idx) {
        $list.append(makeBulkRow(idx, file));
      });

      // ファイル選択後に初めて共通操作・登録ボタンを表示/有効化
      var hasFiles = files.length > 0;
      $('#bulkCommonActions').toggleClass('is-hidden', !hasFiles);
      $('#bulkRegistButton').prop('disabled', !hasFiles);
      $('#bulkAiOcrAll').toggle(aiEnabled);
    });

    function validateBulkRequiredFields() {
      var hasError = false;
      var $rows = $('#bulkFileList .ledger-bulk-row');

      $rows.each(function () {
        var $row = $(this);
        var $hiduke = $row.find('[data-bulk-field="hiduke"]');
        var $kinngaku = $row.find('[data-bulk-field="kinngaku"]');
        var $torihikisaki = $row.find('[data-bulk-field="torihikisaki"]');
        var $teisyutu = $row.find('[data-bulk-field="teisyutu"]');
        var $hidukeMsg = $row.find('[data-bulk-error="hiduke"]');
        var $kinngakuMsg = $row.find('[data-bulk-error="kinngaku"]');
        var $torihikiMsg = $row.find('[data-bulk-error="torihikisaki"]');
        var $teisyutuMsg = $row.find('[data-bulk-error="teisyutu"]');

        var hidukeVal = String($hiduke.val() || '').trim();
        var kinngakuVal = String($kinngaku.val() || '').trim();
        var torihikiVal = String($torihikisaki.val() || '').trim();
        var teisyutuVal = String($teisyutu.val() || '').trim();

        $hiduke.toggleClass('invalid', !hidukeVal);
        $kinngaku.toggleClass('invalid', !kinngakuVal);
        $torihikisaki.toggleClass('invalid', !torihikiVal);
        $teisyutu.toggleClass('invalid', !teisyutuVal);
        $hidukeMsg.toggleClass('errorsentence', !hidukeVal);
        $kinngakuMsg.toggleClass('errorsentence', !kinngakuVal);
        $torihikiMsg.toggleClass('errorsentence', !torihikiVal);
        $teisyutuMsg.toggleClass('errorsentence', !teisyutuVal);

        if (!hidukeVal || !kinngakuVal || !torihikiVal || !teisyutuVal) {
          hasError = true;
          // 入力不足がある行は自動で開く
          $row.find('.ledger-bulk-row__body').show();
          $row.find('[data-bulk-action="toggle"]').text('フォームを隠す');
        }
      });

      return !hasError;
    }

    $('#bulkRegistForm').on('submit', function (e) {
      // HTMLのrequiredだけだと折りたたみ状態で気づきにくいので、明示チェックする
      if (!validateBulkRequiredFields()) {
        e.preventDefault();
        alert('一括取込の必須項目（取引日・受領/提出・取引先）を入力してください。');
      }
    });

    function applyCommonToAllRows() {
      var common = {
        syorui: $('#bulkCommonSyorui').val(),
        teisyutu: $('#bulkCommonTeisyutu').val(),
        hozonn: $('#bulkCommonHozonn').val(),
        group: $('#bulkCommonGroup').val(),
        kennsakuword: $('#bulkCommonKensaku').val(),
      };

      $('#bulkFileList .ledger-bulk-row').each(function () {
        var $row = $(this);
        $row.find('[data-bulk-field="syorui"]').val(common.syorui).trigger('change');
        $row.find('[data-bulk-field="teisyutu"]').val(common.teisyutu).trigger('change');
        $row.find('[data-bulk-field="hozonn"]').val(common.hozonn).trigger('change');
        $row.find('[data-bulk-field="group"]').val(common.group).trigger('change');
        $row.find('[data-bulk-field="kennsakuword"]').val(common.kennsakuword).trigger('change');
      });
    }

    $('#bulkApplyCommon').on('click', function () {
      applyCommonToAllRows();
    });

    $(document).on('click', '[data-bulk-action="toggle"]', function () {
      var $row = $(this).closest('.ledger-bulk-row');
      var $body = $row.find('.ledger-bulk-row__body');
      var isOpen = $body.is(':visible');
      $body.toggle(!isOpen);
      $(this).text(isOpen ? 'フォームを表示' : 'フォームを隠す');
    });

    $(document).on('click', '[data-bulk-action="ocrOne"]', async function () {
      if (!aiEnabled) {
        return;
      }
      var $btn = $(this);
      var $row = $btn.closest('.ledger-bulk-row');
      var file = $row.data('file');
      if (!file) return;

      if ($btn.hasClass('is-done')) {
        return;
      }

      $row.removeClass('is-ocr-error');
      $row.addClass('is-ocr-running');
      $btn.addClass('is-loading').prop('disabled', true).text('OCR中...');
      try {
        var teisyutu = getLedgerTeisyutuForOcr($row);
        var resp = await runSingleOcr(file, teisyutu);
        logAiOcrResponse(resp, 'AI OCR bulk');
        if (resp && resp.ok && resp.data) {
          applyOcrDataToRow($row, resp.data);
        } else {
          $row.addClass('is-ocr-error');
          showAiOcrFailure(resp);
          throw new Error(resp && resp.step ? resp.step : 'ocr_failed');
        }
      } catch (e) {
        console.warn('AI OCR error (bulk):', e);
        $row.addClass('is-ocr-error');
        if (!e || e.message !== 'ocr_failed') {
          showAiOcrFailure(null, e && e.status ? e : null);
        }
      } finally {
        $row.removeClass('is-ocr-running');
        $btn.removeClass('is-loading');
        if (!$row.hasClass('is-ocr-error')) {
          $btn.addClass('is-done').prop('disabled', true).html('✓');
        } else {
          $btn.prop('disabled', false).text('AI OCR');
        }
      }
    });

    $('#bulkAiOcrAll').on('click', async function () {
      if (!aiEnabled) {
        return;
      }
      var $btn = $(this);
      var $rows = $('#bulkFileList .ledger-bulk-row');
      if (!$rows.length) {
        alert('先にファイルを選択してください');
        return;
      }

      $btn.prop('disabled', true).text('一括OCR実行中...');
      try {
        // すでに完了している行はスキップ
        var tasks = [];
        $rows.each(function (i) {
          var $row = $(this);
          var file = $row.data('file');
          if (!file) return;

          var $rowBtn = $row.find('[data-bulk-action="ocrOne"]');
          if ($rowBtn.hasClass('is-done')) return;

          $row.removeClass('is-ocr-error');
          $row.addClass('is-ocr-running');
          $rowBtn.addClass('is-loading').prop('disabled', true).text('OCR中...');

          var task = (async function () {
            // 0.5秒間隔で「送信開始」だけずらす（完了待ちはしない）
            if (i !== 0) {
              await sleepMs(500 * i);
            }
            try {
              var teisyutu = getLedgerTeisyutuForOcr($row);
              var resp = await runSingleOcr(file, teisyutu);
              logAiOcrResponse(resp, 'AI OCR bulk all');
              if (resp && resp.ok && resp.data) {
                applyOcrDataToRow($row, resp.data);
                $rowBtn.addClass('is-done').prop('disabled', true).html('✓');
              } else {
                $row.addClass('is-ocr-error');
                showAiOcrFailure(resp);
                $rowBtn.prop('disabled', false).text('AI OCR');
              }
            } catch (e) {
              console.warn('AI OCR error (bulk all):', e);
              $row.addClass('is-ocr-error');
              $rowBtn.prop('disabled', false).text('AI OCR');
              showAiOcrFailure(null, e && e.status ? e : null);
            } finally {
              $row.removeClass('is-ocr-running');
              $rowBtn.removeClass('is-loading');
            }
          })();

          tasks.push(task);
        });

        await Promise.allSettled(tasks);
      } finally {
        $btn.prop('disabled', false).text('一括でAI OCRで読み込む');
      }
    });
  }

  initLedgerBulkUI();
  $('.deletebutton').on('click', function () {
    $id = $("#id").val();
    if (confirm("本当に削除しますか?")) {
      window.location.href = prefix + '/delete/' + $id;
    }


  });

  $('.important_title').on('click', function () {
    $('.important_title').toggleClass('close')
    $('.importantelement').toggleClass('open')
  });


  $('.excelbutton').on('click', function () {
    $showcount1 = $('#showcount1').text().trim();
    $showcount2 = $('#showcount2').text().trim().replace('件', '');
    $showcount = $showcount2 - $showcount1 + 1;
    if ($showcount <= 500) {
      $(".excelerror").css("display", "none")
      if (confirm("現在表示中の" + $showcount + "件を出力します。よろしいですか。")) {
        // 1. 現在のURLを取得
        var currentURL = window.location.href;

        // 2. 新しいクエリパラメータを追加
        var newParameter = "excel=true";

        // URLが既にクエリパラメータを持っているか確認し、適切な区切り記号を選択します
        var separator = currentURL.includes("?") ? "&" : "?";

        // 新しいクエリパラメータを現在のURLに追加
        var newURL = currentURL + separator + newParameter;
        // 3. 更新されたURLでページを再ロードまたはリダイレクト
        window.location.href = newURL;
      }

    }
    else {
      $(".excelerror").css("display", "block")
    }


  });




});

