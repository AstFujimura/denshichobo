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
    if (!$(this).closest('#ledgerRegistTabNormal').length) {
      return;
    }
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

  var ledgerOcrKinngakuModalItems = [];
  var ledgerOcrKinngakuModalTargetInput = null;
  var ocrKinngakuBreakdownByInput = new WeakMap();

  function parseBreakdownAmount(value) {
    if (value === null || value === undefined) return null;
    if (typeof value === 'number' && !isNaN(value)) {
      return Math.round(value);
    }
    var normalized = normalizeAmount(value);
    if (normalized === '' || normalized === '-') return null;
    var n = parseFloat(normalized);
    if (isNaN(n)) return null;
    return Math.round(n);
  }

  function getKinngakuBreakdownForInput($input) {
    if (!$input || !$input.length) return null;
    return ocrKinngakuBreakdownByInput.get($input[0]) || null;
  }

  function setKinngakuBreakdownForInput($input, items) {
    if (!$input || !$input.length) return;
    if (!items || !items.length) {
      ocrKinngakuBreakdownByInput.delete($input[0]);
      return;
    }
    ocrKinngakuBreakdownByInput.set($input[0], items);
  }

  function labelsSuggestMissingFirstBreakdownPage(items) {
    var hasFirst = false;
    var hasSecondOrLater = false;
    (items || []).forEach(function (it) {
      var label = String(it.label || '');
      if (/1\s*[枚页ページ]/.test(label)) hasFirst = true;
      if (/[2-9]\s*[枚页ページ]/.test(label)) hasSecondOrLater = true;
    });
    return hasSecondOrLater && !hasFirst;
  }

  function reconcileBreakdownWithKinngakuTotal(items, kinngakuTotal) {
    var list = cloneBreakdownItems(items);
    if (!list.length) return list;

    var expected = parseBreakdownAmount(kinngakuTotal);
    if (expected === null) return list;

    var sum = 0;
    list.forEach(function (it) {
      var part = parseBreakdownAmount(it.amount);
      if (part !== null) sum += part;
    });

    if (sum === expected) {
      return list;
    }

    var diff = expected - sum;

    var missingLabel = labelsSuggestMissingFirstBreakdownPage(list)
      ? '1枚目'
      : '1枚目（OCR内訳に未記載の金額）';

    list.unshift({
      id: 'kb_missing_' + Date.now(),
      amount: String(diff),
      label: missingLabel,
      included: true,
    });

    return list;
  }

  function formatYenDisplay(amountStr) {
    var n = parseBreakdownAmount(amountStr);
    if (n === null) return '0円';
    var prefix = n < 0 ? '-' : '';
    return prefix + Math.abs(n).toLocaleString('ja-JP') + '円';
  }

  function cloneBreakdownItems(items) {
    return (items || []).map(function (it, idx) {
      var amountNum = parseBreakdownAmount(it.amount);
      return {
        id: it.id || ('kb_' + idx + '_' + Date.now()),
        amount: amountNum !== null && amountNum !== 0 ? String(amountNum) : '',
        label: (it.label && String(it.label).trim()) ? String(it.label).trim() : ('項目' + (idx + 1)),
        included: it.included !== false,
      };
    }).filter(function (it) { return it.amount !== ''; });
  }

  function normalizeKinngakuBreakdownFromApi(raw) {
    if (!raw || !raw.length) return [];
    return cloneBreakdownItems(raw.map(function (item, idx) {
      return {
        id: 'kb_' + idx + '_' + Date.now(),
        amount: item.amount != null ? item.amount : item,
        label: item.label,
        included: true,
      };
    }));
  }

  function sumBreakdownItems(items) {
    var total = 0;
    (items || []).forEach(function (it) {
      if (!it.included) return;
      var n = parseBreakdownAmount(it.amount);
      if (n !== null) total += n;
    });
    return String(total);
  }

  function removeKinngakuBreakdownUi($input) {
    if (!$input || !$input.length) return;
    $input.closest('.ledger-regist__control').find('.ledger-ocr-kinngaku-breakdown').remove();
    setKinngakuBreakdownForInput($input, null);
  }

  function renderKinngakuBreakdownSummary($input, items) {
    var $wrap = $input.closest('.ledger-regist__control');
    $wrap.find('.ledger-ocr-kinngaku-breakdown').remove();
    if (!items || items.length < 2) return;

    var includedCount = items.filter(function (it) { return it.included; }).length;
    var total = sumBreakdownItems(items);
    var $bar = $('<div class="ledger-ocr-kinngaku-breakdown"></div>');
    var $text = $('<span class="ledger-ocr-kinngaku-breakdown__text"></span>');
    $text.text('OCR合算: ' + formatYenDisplay(total) + '（全' + items.length + '件・採用' + includedCount + '件）');
    var $btn = $('<button type="button" class="ledger-ocr-kinngaku-breakdown__detail">詳細</button>');
    $btn.on('click', function (e) {
      e.preventDefault();
      openLedgerOcrKinngakuBreakdownModal($input);
    });
    $bar.append($text, $btn);
    $wrap.append($bar);
  }

  function applyKinngakuBreakdownItems($input, items) {
    if (!$input || !$input.length) return;
    var cloned = cloneBreakdownItems(items);
    if (cloned.length < 2) {
      removeKinngakuBreakdownUi($input);
      return;
    }
    setKinngakuBreakdownForInput($input, cloned);
    $input.val(sumBreakdownItems(cloned)).trigger('change').trigger('blur');
    renderKinngakuBreakdownSummary($input, cloned);
  }

  function attachKinngakuBreakdownFromOcrData($input, data) {
    if (!$input || !$input.length || !data) return;
    var items = normalizeKinngakuBreakdownFromApi(data.kinngaku_breakdown);
    items = reconcileBreakdownWithKinngakuTotal(items, data.kinngaku);
    if (items.length < 2) {
      removeKinngakuBreakdownUi($input);
      return;
    }
    applyKinngakuBreakdownItems($input, items);
  }

  function refreshLedgerOcrKinngakuBreakdownModalList() {
    var $list = $('#ledgerOcrKinngakuBreakdownList');
    var $total = $('#ledgerOcrKinngakuBreakdownTotal');
    if (!$list.length) return;
    $list.empty();

    ledgerOcrKinngakuModalItems.forEach(function (item) {
      var $row = $('<div class="ledger-ocr-kinngaku-breakdown-modal__row"></div>');
      if (!item.included) {
        $row.addClass('is-excluded');
      }
      var $label = $('<span class="ledger-ocr-kinngaku-breakdown-modal__label"></span>').text(item.label);
      var $amount = $('<span class="ledger-ocr-kinngaku-breakdown-modal__amount"></span>').text(formatYenDisplay(item.amount));
      var $toggle = $('<button type="button" class="ledger-ocr-kinngaku-breakdown-modal__exclude" title="合算から除外／再び含める" aria-label="合算から除外"></button>');
      $toggle.text('×');
      $toggle.on('click', function (e) {
        e.preventDefault();
        item.included = !item.included;
        refreshLedgerOcrKinngakuBreakdownModalList();
      });
      $row.append($label, $amount, $toggle);
      $list.append($row);
    });

    if ($total.length) {
      $total.text(formatYenDisplay(sumBreakdownItems(ledgerOcrKinngakuModalItems)));
    }
  }

  function closeLedgerOcrKinngakuBreakdownModal() {
    $('#ledgerOcrKinngakuBreakdownModal').removeClass('is-open');
    $('body').removeClass('ledger-regist-modal-open');
    ledgerOcrKinngakuModalTargetInput = null;
    ledgerOcrKinngakuModalItems = [];
  }

  function openLedgerOcrKinngakuBreakdownModal($input) {
    var stored = getKinngakuBreakdownForInput($input);
    if (!stored || stored.length < 2) return;
    ledgerOcrKinngakuModalTargetInput = $input;
    ledgerOcrKinngakuModalItems = cloneBreakdownItems(stored);
    refreshLedgerOcrKinngakuBreakdownModalList();
    $('#ledgerOcrKinngakuBreakdownModal').addClass('is-open');
    $('body').addClass('ledger-regist-modal-open');
  }

  function bindLedgerOcrKinngakuBreakdownModal() {
    var $modal = $('#ledgerOcrKinngakuBreakdownModal');
    if (!$modal.length || $modal.data('bound')) return;
    $modal.data('bound', true);

    $modal.on('click', '[data-kinngaku-breakdown-close]', function (e) {
      e.preventDefault();
      closeLedgerOcrKinngakuBreakdownModal();
    });

    $('#ledgerOcrKinngakuBreakdownApply').on('click', function (e) {
      e.preventDefault();
      if (ledgerOcrKinngakuModalTargetInput && ledgerOcrKinngakuModalTargetInput.length) {
        applyKinngakuBreakdownItems(
          ledgerOcrKinngakuModalTargetInput,
          cloneBreakdownItems(ledgerOcrKinngakuModalItems)
        );
      }
      closeLedgerOcrKinngakuBreakdownModal();
    });
  }

  bindLedgerOcrKinngakuBreakdownModal();

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
      if ($target.is('#bulkSharedPreview') || $target.closest('#bulkSharedPreview').length) {
        embed.addClass('ledger-bulk__pdf');
      } else {
        applyLedgerEditPreviewMedia(embed);
      }
      $target.html(embed);
      return;
    }
    $target.text("対応していないファイル形式です");
  }

  function setLedgerRegistDateInputValue($input, value) {
    if (!$input || !$input.length) return;
    var el = $input[0];
    var dateStr = value ? String(value).trim() : '';
    $input.val(dateStr).trigger('change').trigger('blur');
    if (el && el._flatpickr && dateStr) {
      el._flatpickr.setDate(dateStr, true);
    }
  }

  function applyOcrDataToRow($row, data, onSettled) {
    if (!$row || !$row.length || !data) {
      if (typeof onSettled === 'function') onSettled();
      return;
    }
    var hiduke = normalizeDateString(data.hiduke);
    var kinngaku = normalizeAmount(data.kinngaku);
    var torihikisaki = data.torihikisaki ? String(data.torihikisaki).trim() : '';

    if (hiduke) setLedgerRegistDateInputValue($row.find('[data-bulk-field="hiduke"]'), hiduke);
    var $kinInput = $row.find('[data-bulk-field="kinngaku"]');
    if (kinngaku) {
      $kinInput.val(kinngaku).trigger('change').trigger('blur');
      attachKinngakuBreakdownFromOcrData($kinInput, data);
    } else {
      removeKinngakuBreakdownUi($kinInput);
    }
    if (torihikisaki) {
      resolveTorihikisakiByExistingCandidates(torihikisaki).then(function (resolved) {
        if (resolved) {
          $row.find('[data-bulk-field="torihikisaki"]').val(resolved).trigger('change').trigger('blur');
        }
      }).finally(function () {
        if (typeof onSettled === 'function') onSettled();
      });
    } else if (typeof onSettled === 'function') {
      onSettled();
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

  function getLedgerSyoruiIdForOcr($row) {
    if ($row && $row.length) {
      var rowVal = $row.find('[data-bulk-field="syorui"]').val();
      if (rowVal) return rowVal;
    }
    if ($('#ledgerRegistTabBulk').hasClass('is-active')) {
      var bulkVal = $('#bulkCommonSyorui').val();
      if (bulkVal) return bulkVal;
    }
    var normalVal = $('#syorui').val();
    return normalVal || '';
  }

  var ledgerOcrSettingsSyoruiSource = null;

  function getLedgerSyoruiSelectForOcrSettings() {
    if (ledgerOcrSettingsSyoruiSource === 'bulk') {
      return $('#bulkCommonSyorui');
    }
    if (ledgerOcrSettingsSyoruiSource === 'normal') {
      return $('#syorui');
    }
    return getLedgerSyoruiSelectForOcr();
  }

  function openLedgerOcrSettingsModal(source) {
    ledgerOcrSettingsSyoruiSource = source || null;
    syncLedgerOcrOptionsFromMaster();
    $('#ledgerOcrSettingsModal').addClass('is-open').attr('aria-hidden', 'false');
    $('body').addClass('ledger-regist-modal-open');
    $('#ledgerOcrSettingsModal .ledger-ocr-settings-modal__close-btn').focus();
  }

  function closeLedgerOcrSettingsModal() {
    $('#ledgerOcrSettingsModal').removeClass('is-open').attr('aria-hidden', 'true');
    $('body').removeClass('ledger-regist-modal-open');
    refreshLedgerOcrSettingsChips();
  }

  function bindLedgerOcrSettingsModal() {
    var $modal = $('#ledgerOcrSettingsModal');
    if (!$modal.length || $modal.data('bound')) {
      return;
    }
    $modal.data('bound', true);

    $(document).on('click', '[data-ledger-ocr-settings-open]', function (e) {
      e.preventDefault();
      var source = $(this).attr('data-ocr-syorui-source') || 'normal';
      openLedgerOcrSettingsModal(source);
    });

    $modal.on('click', '[data-ledger-ocr-settings-close]', function (e) {
      e.preventDefault();
      closeLedgerOcrSettingsModal();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && $modal.hasClass('is-open')) {
        closeLedgerOcrSettingsModal();
      }
    });
  }

  function getLedgerSyoruiSelectForOcr($row) {
    if ($row && $row.length) {
      var $rowSelect = $row.find('[data-bulk-field="syorui"]');
      if ($rowSelect.length) return $rowSelect;
    }
    if ($('#ledgerRegistTabBulk').hasClass('is-active')) {
      return $('#bulkCommonSyorui');
    }
    return $('#syorui');
  }

  function readDocumentOcrMasterFromSelect($select) {
    if (!$select || !$select.length) {
      return { sumAmounts: false, taxIncluded: false };
    }
    var $opt = $select.find('option:selected');
    if (!$opt.length) {
      return { sumAmounts: false, taxIncluded: false };
    }
    return {
      sumAmounts: String($opt.attr('data-ocr-sum-amounts') || '') === '1',
      taxIncluded: String($opt.attr('data-ocr-tax-included') || '') === '1',
    };
  }

  function syncLedgerOcrOptionsFromMaster() {
    if (!isIchifujiEnabled() || !$('#ledgerOcrSettingsModal').length) {
      return;
    }
    var $select = getLedgerSyoruiSelectForOcrSettings();
    var master = readDocumentOcrMasterFromSelect($select);
    var $sumWrap = $('#ledgerOcrSumAmountsWrap');
    var $sumCheck = $('#ledgerOcrUseSumAmounts');

    if (master.sumAmounts) {
      $sumWrap.removeClass('is-hidden');
      if (!$sumCheck.data('userTouched')) {
        $sumCheck.prop('checked', true);
      }
    } else {
      $sumWrap.addClass('is-hidden');
      $sumCheck.prop('checked', false).data('userTouched', false);
    }

    if (!$('input[name="ledger_ocr_tax_mode"]').data('userTouched')) {
      $('input[name="ledger_ocr_tax_mode"][value="' + (master.taxIncluded ? 'included' : 'excluded') + '"]')
        .prop('checked', true);
    }

    var hints = [];
    if (master.sumAmounts) hints.push('複数合算');
    if (master.taxIncluded) hints.push('税込');
    if (!hints.length) hints.push('標準（税抜・単一金額）');
    $('#ledgerOcrOptionsMasterHint').text('書類区分の初期設定: ' + hints.join('・') + '（必要なら変更できます）');
    refreshLedgerOcrSettingsChips();
  }

  function getLedgerOcrRequestOptionsForSelect($select) {
    var master = readDocumentOcrMasterFromSelect($select);
    var sumAmounts = false;
    if (master.sumAmounts && $('#ledgerOcrUseSumAmounts').prop('checked')) {
      sumAmounts = true;
    }
    var taxIncluded = $('input[name="ledger_ocr_tax_mode"]:checked').val() === 'included';
    return {
      sumAmounts: sumAmounts,
      taxIncluded: taxIncluded,
    };
  }

  function renderLedgerOcrSettingsChips($container, opts) {
    if (!$container || !$container.length) {
      return;
    }
    $container.empty();
    var labels = [opts.taxIncluded ? '税込' : '税抜'];
    if (opts.sumAmounts) {
      labels.push('合算');
    }
    labels.forEach(function (label) {
      $container.append($('<span class="ledger-ocr-settings-chip"></span>').text(label));
    });
    $container.attr('title', 'AI OCR: ' + labels.join('・'));
  }

  function refreshLedgerOcrSettingsChips() {
    if (!isIchifujiEnabled()) {
      return;
    }
    renderLedgerOcrSettingsChips($('#ledgerOcrSettingsChipNormal'), getLedgerOcrRequestOptionsForSelect($('#syorui')));
    renderLedgerOcrSettingsChips($('#ledgerOcrSettingsChipBulk'), getLedgerOcrRequestOptionsForSelect($('#bulkCommonSyorui')));
  }

  function getLedgerOcrRequestOptions($row) {
    return getLedgerOcrRequestOptionsForSelect(getLedgerSyoruiSelectForOcr($row));
  }

  function runSingleOcr(file, teisyutu, syoruiId, ocrOptions, $row) {
    var formData = new FormData();
    formData.append('_token', $('input[name="_token"]').val());
    formData.append('file', file);
    formData.append('teisyutu', teisyutu || '受領');
    if (syoruiId) {
      formData.append('syorui', syoruiId);
    }

    if (isIchifujiEnabled()) {
      var opts = ocrOptions || getLedgerOcrRequestOptions($row);
      formData.append('ocr_sum_amounts', opts.sumAmounts ? '1' : '0');
      formData.append('ocr_tax_included', opts.taxIncluded ? '1' : '0');
    }

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

    var $btn = $('<button type="button" id="aiOcrLedgerButton" class="ledger-regist__action ledger-regist__action--ocr">AI OCRで読み込む</button>');
    $btn.on('click', function () {
      var input = document.getElementById('file');
      var selected = input && input.files && input.files[0] ? input.files[0] : null;
      if (!selected) {
        alert('先にファイルを選択してください');
        return;
      }

      $btn.prop('disabled', true).text('AI OCR 実行中...');

      var teisyutu = getLedgerTeisyutuForOcr();

      runSingleOcr(selected, teisyutu, getLedgerSyoruiIdForOcr(), null, null).then(function (resp) {
          logAiOcrResponse(resp, 'AI OCR');
          if (resp && resp.ok && resp.data) {
            var hiduke = normalizeDateString(resp.data.hiduke);
            var kinngaku = normalizeAmount(resp.data.kinngaku);
            var torihikisaki = resp.data.torihikisaki ? String(resp.data.torihikisaki).trim() : '';

            if (hiduke) {
              $('#hiduke').val(hiduke).trigger('change').trigger('blur');
            }
            if (kinngaku) {
              var $kinInput = $('#kinngaku');
              $kinInput.val(kinngaku).trigger('change').trigger('blur');
              attachKinngakuBreakdownFromOcrData($kinInput, resp.data);
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
    if (!aiEnabled) {
      $('#bulkAiOcrAll').addClass('is-hidden');
    }

    function bulkHasFiles() {
      return $('#ledgerBulkLayout').hasClass('has-files');
    }

    function setBulkHasFiles(hasFiles) {
      $('#ledgerBulkLayout').toggleClass('has-files', hasFiles);
      $('#bulkRegistForm').toggleClass('has-files', hasFiles);
    }

    function syncLedgerRegistModeInUrl(mode) {
      try {
        var url = new URL(window.location.href);
        if (mode === 'bulk') {
          url.searchParams.set('mode', 'bulk');
        } else {
          url.searchParams.delete('mode');
        }
        var qs = url.searchParams.toString();
        window.history.replaceState({}, '', url.pathname + (qs ? '?' + qs : '') + url.hash);
      } catch (e) { /* noop */ }
    }

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
      syncLedgerRegistModeInUrl(target === 'bulk' ? 'bulk' : 'normal');
      if (typeof syncLedgerOcrOptionsFromMaster === 'function') {
        ledgerOcrSettingsSyoruiSource = null;
        $('#ledgerOcrUseSumAmounts').data('userTouched', false);
        $('input[name="ledger_ocr_tax_mode"]').data('userTouched', false);
        syncLedgerOcrOptionsFromMaster();
      }
    }

    var initialMode = $('#ledgerRegistInitialMode').val() === 'bulk' ? 'bulk' : 'normal';
    switchTab(initialMode);

    $(document).on('click', '.ledger-regist-tabs__tab', function () {
      var tab = $(this).data('ledger-tab');
      switchTab(tab);
    });

    function updateBulkRowStatus($header, $body) {
      if (!$body || !$body.length) {
        $body = $header && $header.data('bulkBody');
      }
      if (!$header || !$header.length) {
        $header = $body && $body.data('bulkHeader');
      }
      if (!$header || !$header.length || !$body || !$body.length) return;
      var fields = ['hiduke', 'kinngaku', 'torihikisaki'];
      fields.forEach(function (key) {
        var val = String($body.find('[data-bulk-field="' + key + '"]').val() || '').trim();
        $header.find('[data-bulk-chip="' + key + '"]').toggleClass('is-filled', !!val);
      });
      var complete = fields.every(function (key) {
        return !!String($body.find('[data-bulk-field="' + key + '"]').val() || '').trim()
          && !!String($body.find('[data-bulk-field="teisyutu"]').val() || '').trim();
      });
      $header.toggleClass('is-complete', complete);
    }

    var bulkFilesCache = [];
    var selectedBulkIndex = -1;
    var bulkAddMorePending = false;
    var bulkRowStateByFile = new WeakMap();
    var bulkRowStateFieldKeys = ['hiduke', 'kinngaku', 'torihikisaki', 'syorui', 'teisyutu', 'hozonn', 'group', 'kennsakuword'];

    function captureBulkRowState($header, $body) {
      if (!$body || !$body.length) return null;
      var state = {};
      bulkRowStateFieldKeys.forEach(function (key) {
        state[key] = String($body.find('[data-bulk-field="' + key + '"]').val() || '');
      });
      if ($header && $header.length) {
        var $ocrBtn = $header.find('[data-bulk-action="ocrOne"]');
        state.ocrDone = $ocrBtn.hasClass('is-done');
        state.ocrError = $header.hasClass('is-ocr-error');
      }
      return state;
    }

    function saveBulkRowState(file, $header, $body) {
      if (!file || !$body || !$body.length) return;
      var state = captureBulkRowState($header, $body);
      if (state) {
        bulkRowStateByFile.set(file, state);
      }
    }

    function snapshotAllBulkRowStates() {
      $('#bulkFileList .ledger-bulk-file').each(function () {
        var $header = $(this);
        var $body = $header.data('bulkBody');
        var file = $header.data('file');
        if (file && $body && $body.length) {
          saveBulkRowState(file, $header, $body);
        }
      });
    }

    function restoreBulkRowState(file, $header, $body) {
      var state = bulkRowStateByFile.get(file);
      if (!state || !$body || !$body.length) return;

      bulkRowStateFieldKeys.forEach(function (key) {
        var $el = $body.find('[data-bulk-field="' + key + '"]');
        if (!$el.length) return;
        var val = state[key] != null ? state[key] : '';
        if (key === 'hiduke') {
          setLedgerRegistDateInputValue($el, val);
        } else {
          $el.val(val).trigger('change');
        }
      });

      if ($header && $header.length) {
        var $ocrBtn = $header.find('[data-bulk-action="ocrOne"]');
        $header.removeClass('is-ocr-error');
        if (state.ocrError) {
          $header.addClass('is-ocr-error');
        }
        if (state.ocrDone && $ocrBtn.length) {
          $ocrBtn.addClass('is-done').prop('disabled', true).html('✓');
        } else if ($ocrBtn.length) {
          $ocrBtn.removeClass('is-done is-loading').prop('disabled', false).text('AI OCR');
        }
      }
      updateBulkRowStatus($header, $body);
    }

    function syncBulkRowDatesAfterFlatpickr() {
      $('#bulkFileList .ledger-bulk-file').each(function () {
        var $header = $(this);
        var $body = $header.data('bulkBody');
        var file = $header.data('file');
        if (!file || !$body || !$body.length) return;
        var state = bulkRowStateByFile.get(file);
        if (!state || !state.hiduke) return;
        setLedgerRegistDateInputValue($body.find('[data-bulk-field="hiduke"]'), state.hiduke);
      });
    }

    function updateBulkUiForFileCount(files, hasFiles) {
      bulkFilesCache = files.slice();
      setBulkHasFiles(hasFiles);
      $('#bulkRegistButton').prop('disabled', !hasFiles);
      if (aiEnabled) {
        $('#bulkAiOcrAll').prop('disabled', !hasFiles);
      }
      $('#bulkListCount').text(hasFiles ? (files.length + '件') : '');
      $('#bulkFilesLoaded').toggleClass('is-hidden', !hasFiles);
      $('#bulkFilePickRow').toggleClass('is-hidden', hasFiles);
    }

    function selectBulkRow(index) {
      var $headers = $('#bulkFileList .ledger-bulk-file');
      if (!$headers.length) {
        selectedBulkIndex = -1;
        $('#bulkSharedPreview').text('プレビュー');
        return;
      }
      if (index < 0 || index >= $headers.length) {
        index = 0;
      }
      $headers.removeClass('is-selected');
      $('#bulkDetailPanel .ledger-bulk-row__body').removeClass('is-active').hide();
      var $header = $headers.filter('[data-bulk-index="' + index + '"]');
      if (!$header.length) {
        $header = $headers.first();
        index = parseInt($header.attr('data-bulk-index'), 10) || 0;
      }
      var $body = $header.data('bulkBody');
      $header.addClass('is-selected');
      if ($body && $body.length) {
        $body.addClass('is-active').show();
      }
      renderPreviewInto($('#bulkSharedPreview'), $header.data('file'));
      selectedBulkIndex = index;
      var el = $header.get(0);
      if (el && el.scrollIntoView) {
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
      }
    }

    function applyCommonToAllRows() {
      var common = {
        syorui: $('#bulkCommonSyorui').val(),
        teisyutu: $('#bulkCommonTeisyutu').val(),
        hozonn: $('#bulkCommonHozonn').val(),
        group: $('#bulkCommonGroup').val(),
        kennsakuword: $('#bulkCommonKensaku').val(),
      };

      $('#bulkFileList .ledger-bulk-file').each(function () {
        var $header = $(this);
        var $body = $header.data('bulkBody');
        if (!$body || !$body.length) return;
        $body.find('[data-bulk-field="syorui"]').val(common.syorui).trigger('change');
        $body.find('[data-bulk-field="teisyutu"]').val(common.teisyutu).trigger('change');
        $body.find('[data-bulk-field="hozonn"]').val(common.hozonn).trigger('change');
        $body.find('[data-bulk-field="group"]').val(common.group).trigger('change');
        $body.find('[data-bulk-field="kennsakuword"]').val(common.kennsakuword).trigger('change');
        updateBulkRowStatus($header, $body);
        saveBulkRowState($header.data('file'), $header, $body);
      });
    }

    function setBulkInputFiles(fileList) {
      var input = document.getElementById('bulkFiles');
      if (!input || typeof DataTransfer === 'undefined') return;
      var dt = new DataTransfer();
      fileList.forEach(function (file) {
        dt.items.add(file);
      });
      input.files = dt.files;
    }

    function refreshBulkFileList(files, options) {
      options = options || {};
      var $list = $('#bulkFileList');
      var $detail = $('#bulkDetailPanel');
      var keepIndex = selectedBulkIndex;
      if (!options.skipSnapshot) {
        snapshotAllBulkRowStates();
      }
      $list.empty();
      $detail.empty();
      files.forEach(function (file, idx) {
        var parts = makeBulkRow(idx, file);
        $list.append(parts.$header);
        $detail.append(parts.$body);
        applyCommonValuesToRow(parts.$body);
        restoreBulkRowState(file, parts.$header, parts.$body);
        updateBulkRowStatus(parts.$header, parts.$body);
      });

      var hasFiles = files.length > 0;
      updateBulkUiForFileCount(files, hasFiles);

      if (hasFiles) {
        var selectIndex = typeof options.selectIndex === 'number'
          ? options.selectIndex
          : (keepIndex >= 0 ? Math.min(keepIndex, files.length - 1) : 0);
        selectBulkRow(selectIndex);
      } else {
        selectedBulkIndex = -1;
        $('#bulkSharedPreview').text('プレビュー');
        $('#bulkDetailPanel').empty();
        bulkRowStateByFile = new WeakMap();
      }

      if (typeof window.initLedgerRegistDateFlatpickr === 'function') {
        window.initLedgerRegistDateFlatpickr('#bulkDetailPanel');
      }
      syncBulkRowDatesAfterFlatpickr();
    }

    function appendBulkFileRows(newFiles, mergedFiles, selectIndex) {
      if (!newFiles.length) return;
      var $list = $('#bulkFileList');
      var $detail = $('#bulkDetailPanel');
      var startIndex = mergedFiles.length - newFiles.length;
      newFiles.forEach(function (file, i) {
        var idx = startIndex + i;
        var parts = makeBulkRow(idx, file);
        $list.append(parts.$header);
        $detail.append(parts.$body);
        applyCommonValuesToRow(parts.$body);
        updateBulkRowStatus(parts.$header, parts.$body);
      });

      updateBulkUiForFileCount(mergedFiles, mergedFiles.length > 0);

      if (typeof window.initLedgerRegistDateFlatpickr === 'function') {
        window.initLedgerRegistDateFlatpickr('#bulkDetailPanel');
      }

      selectBulkRow(typeof selectIndex === 'number' ? selectIndex : startIndex);
    }

    function applyCommonValuesToRow($body) {
      $body.find('[data-bulk-field="syorui"]').val($('#bulkCommonSyorui').val());
      $body.find('[data-bulk-field="teisyutu"]').val($('#bulkCommonTeisyutu').val());
      $body.find('[data-bulk-field="hozonn"]').val($('#bulkCommonHozonn').val());
      $body.find('[data-bulk-field="group"]').val($('#bulkCommonGroup').val());
      $body.find('[data-bulk-field="kennsakuword"]').val($('#bulkCommonKensaku').val());
    }

    function makeBulkRow(index, file) {
      var safeName = file && file.name ? file.name : ('file_' + index);
      var fieldsHtml = ''
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
        + '              <div class="registtorihikisakiselect"></div>'
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
        + '        </div>';

      var $header = $('<div class="ledger-bulk-file" data-bulk-index="' + index + '" role="button" tabindex="0"></div>');
      $header.append(
        '<div class="ledger-bulk-file__inner">'
        + '  <span class="ledger-bulk-file__name">' + safeName + '</span>'
        + '  <span class="ledger-bulk-file__status" aria-hidden="true">'
        + '    <span class="ledger-bulk-row__chip" data-bulk-chip="hiduke"></span>'
        + '    <span class="ledger-bulk-row__chip" data-bulk-chip="kinngaku"></span>'
        + '    <span class="ledger-bulk-row__chip" data-bulk-chip="torihikisaki"></span>'
        + '  </span>'
        + '</div>'
        + (aiEnabled ? '<button type="button" class="aiocrbutton ledger-bulk-file__ocr" data-bulk-action="ocrOne">AI OCR</button>' : '')
      );
      $header.data('file', file);

      var $body = $('<div class="ledger-bulk-row__body" data-bulk-index="' + index + '"></div>');
      $body.html(fieldsHtml);
      $body.find('[data-bulk-field="syorui"]').html($('#bulkCommonSyorui').html());
      $body.find('[data-bulk-field="group"]').html($('#bulkCommonGroup').html());

      $header.data('bulkBody', $body);
      $body.data('bulkHeader', $header);
      return { $header: $header, $body: $body };
    }

    function ingestBulkFiles(rawFiles, mode) {
      var incoming = Array.from(rawFiles || []).filter(Boolean);
      if (!incoming.length) {
        bulkAddMorePending = false;
        return;
      }
      if (mode === 'append' && bulkFilesCache.length) {
        snapshotAllBulkRowStates();
        var merged = bulkFilesCache.concat(incoming);
        setBulkInputFiles(merged);
        appendBulkFileRows(incoming, merged, merged.length - incoming.length);
        bulkAddMorePending = false;
        var input = document.getElementById('bulkFiles');
        if (input) {
          input.value = '';
        }
        return;
      }
      var next = incoming;
      setBulkInputFiles(next);
      refreshBulkFileList(next);
      bulkAddMorePending = false;
    }

    $('#bulkFiles').on('change', function (e) {
      var files = (e.target && e.target.files) ? Array.from(e.target.files) : [];
      if (bulkAddMorePending) {
        ingestBulkFiles(files, 'append');
        bulkAddMorePending = false;
      } else {
        ingestBulkFiles(files, 'replace');
      }
    });

    function openBulkFilePicker(appendMode) {
      bulkAddMorePending = !!appendMode;
      $('#bulkFiles').trigger('click');
    }

    $('#bulkPickZone').on('click', function () {
      openBulkFilePicker(false);
    }).on('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openBulkFilePicker(false);
      }
    });

    $('#bulkAddMoreFiles').on('click', function (e) {
      e.preventDefault();
      openBulkFilePicker(true);
    });

    $(document).on('click', '.ledger-bulk-file', function (e) {
      if ($(e.target).closest('[data-bulk-action="ocrOne"]').length) {
        return;
      }
      var index = parseInt($(this).attr('data-bulk-index'), 10);
      if (!isNaN(index)) {
        selectBulkRow(index);
      }
    });

    $(document).on('keydown', '.ledger-bulk-file', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).trigger('click');
      }
    });

    function bindBulkDropTarget($target) {
      $target.on('dragover', function (event) {
        event.preventDefault();
        $(this).addClass('dragover');
      }).on('dragleave', function (event) {
        event.preventDefault();
        $(this).removeClass('dragover');
      }).on('drop', function (event) {
        event.preventDefault();
        $(this).removeClass('dragover');
        var dropped = event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.files;
        if (!dropped || !dropped.length) return;
        var mode = bulkHasFiles() ? 'append' : 'replace';
        ingestBulkFiles(dropped, mode);
      });
    }

    bindBulkDropTarget($('#bulkFilesBar'));
    bindBulkDropTarget($('#bulkSharedPreview'));

    $('#bulkCommonSyorui, #bulkCommonTeisyutu, #bulkCommonHozonn, #bulkCommonGroup, #bulkCommonKensaku').on('change input', function () {
      if (!bulkHasFiles()) return;
      applyCommonToAllRows();
    });

    $(document).on('input change', '#bulkDetailPanel [data-bulk-field]', function () {
      var $body = $(this).closest('.ledger-bulk-row__body');
      var $header = $body.data('bulkHeader');
      var file = $header && $header.data('file');
      if (file) {
        saveBulkRowState(file, $header, $body);
      }
      updateBulkRowStatus($header, $body);
    });

    function validateBulkRequiredFields() {
      var hasError = false;

      $('#bulkFileList .ledger-bulk-file').each(function () {
        var $header = $(this);
        var $body = $header.data('bulkBody');
        if (!$body || !$body.length) return;

        var $hiduke = $body.find('[data-bulk-field="hiduke"]');
        var $kinngaku = $body.find('[data-bulk-field="kinngaku"]');
        var $torihikisaki = $body.find('[data-bulk-field="torihikisaki"]');
        var $teisyutu = $body.find('[data-bulk-field="teisyutu"]');
        var $hidukeMsg = $body.find('[data-bulk-error="hiduke"]');
        var $kinngakuMsg = $body.find('[data-bulk-error="kinngaku"]');
        var $torihikiMsg = $body.find('[data-bulk-error="torihikisaki"]');
        var $teisyutuMsg = $body.find('[data-bulk-error="teisyutu"]');

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
          var index = parseInt($header.attr('data-bulk-index'), 10);
          if (!isNaN(index)) {
            selectBulkRow(index);
          }
        }
        updateBulkRowStatus($header, $body);
      });

      return !hasError;
    }

    $('#bulkRegistForm').on('submit', function (e) {
      if (!validateBulkRequiredFields()) {
        e.preventDefault();
        alert('一括取込の必須項目（取引日・金額・受領/提出・取引先）を入力してください。');
      }
    });

    $(document).on('click', '[data-bulk-action="ocrOne"]', async function (e) {
      e.stopPropagation();
      if (!aiEnabled) {
        return;
      }
      var $btn = $(this);
      var $header = $btn.closest('.ledger-bulk-file');
      var $body = $header.data('bulkBody');
      var file = $header.data('file');
      if (!$body || !$body.length || !file) return;

      if ($btn.hasClass('is-done')) {
        return;
      }

      $header.removeClass('is-ocr-error');
      $header.addClass('is-ocr-running');
      $btn.addClass('is-loading').prop('disabled', true).text('OCR中...');
      try {
        var teisyutu = getLedgerTeisyutuForOcr($body);
        var syoruiId = getLedgerSyoruiIdForOcr($body);
        var resp = await runSingleOcr(file, teisyutu, syoruiId, null, $body);
        logAiOcrResponse(resp, 'AI OCR bulk');
        if (resp && resp.ok && resp.data) {
          applyOcrDataToRow($body, resp.data, function () {
            updateBulkRowStatus($header, $body);
            saveBulkRowState(file, $header, $body);
          });
        } else {
          $header.addClass('is-ocr-error');
          showAiOcrFailure(resp);
          throw new Error(resp && resp.step ? resp.step : 'ocr_failed');
        }
      } catch (e) {
        console.warn('AI OCR error (bulk):', e);
        $header.addClass('is-ocr-error');
        if (!e || e.message !== 'ocr_failed') {
          showAiOcrFailure(null, e && e.status ? e : null);
        }
      } finally {
        $header.removeClass('is-ocr-running');
        $btn.removeClass('is-loading');
        if (!$header.hasClass('is-ocr-error')) {
          $btn.addClass('is-done').prop('disabled', true).html('✓');
          saveBulkRowState(file, $header, $body);
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
      var $headers = $('#bulkFileList .ledger-bulk-file');
      if (!$headers.length) {
        return;
      }

      $btn.prop('disabled', true).text('一括OCR実行中...');
      try {
        var tasks = [];
        $headers.each(function (i) {
          var $header = $(this);
          var $body = $header.data('bulkBody');
          var file = $header.data('file');
          if (!$body || !$body.length || !file) return;

          var $rowBtn = $header.find('[data-bulk-action="ocrOne"]');
          if ($rowBtn.hasClass('is-done')) return;

          $header.removeClass('is-ocr-error');
          $header.addClass('is-ocr-running');
          $rowBtn.addClass('is-loading').prop('disabled', true).text('OCR中...');

          var task = (async function () {
            if (i !== 0) {
              await sleepMs(500 * i);
            }
            try {
              var teisyutu = getLedgerTeisyutuForOcr($body);
              var syoruiId = getLedgerSyoruiIdForOcr($body);
              var resp = await runSingleOcr(file, teisyutu, syoruiId, null, $body);
              logAiOcrResponse(resp, 'AI OCR bulk all');
              if (resp && resp.ok && resp.data) {
                applyOcrDataToRow($body, resp.data, function () {
                  updateBulkRowStatus($header, $body);
                  saveBulkRowState(file, $header, $body);
                  $rowBtn.addClass('is-done').prop('disabled', true).html('✓');
                });
              } else {
                $header.addClass('is-ocr-error');
                showAiOcrFailure(resp);
                $rowBtn.prop('disabled', false).text('AI OCR');
              }
            } catch (e) {
              console.warn('AI OCR error (bulk all):', e);
              $header.addClass('is-ocr-error');
              $rowBtn.prop('disabled', false).text('AI OCR');
              showAiOcrFailure(null, e && e.status ? e : null);
            } finally {
              $header.removeClass('is-ocr-running');
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




  if (isIchifujiEnabled()) {
    bindLedgerOcrSettingsModal();
    syncLedgerOcrOptionsFromMaster();
    $(document).on('change', '#syorui, #bulkCommonSyorui', function () {
      $('#ledgerOcrUseSumAmounts').data('userTouched', false);
      $('input[name="ledger_ocr_tax_mode"]').data('userTouched', false);
      syncLedgerOcrOptionsFromMaster();
    });
    $('#ledgerOcrUseSumAmounts').on('change', function () {
      $(this).data('userTouched', true);
      refreshLedgerOcrSettingsChips();
    });
    $(document).on('change', 'input[name="ledger_ocr_tax_mode"]', function () {
      $('input[name="ledger_ocr_tax_mode"]').data('userTouched', true);
      refreshLedgerOcrSettingsChips();
    });
    refreshLedgerOcrSettingsChips();
  }

});

