<div class="platform-modal" id="storage-prefix-modal" hidden>
    <div class="platform-modal-backdrop" data-close-storage-prefix-modal></div>
    <div class="platform-modal-panel" role="dialog" aria-modal="true" aria-labelledby="storage-prefix-modal-title">
        <h2 id="storage-prefix-modal-title">保存先プレフィックス</h2>
        <p class="platform-modal-warning">このテナントのファイルを置くフォルダの名前です。</p>
        <p>クラウド（S3）の場合は、バケットの中にこの名前のディレクトリを作り、ファイルはその中に保存されます。</p>
        <p>ローカルの場合は、指定したローカル保存先の配下にこの名前のフォルダを作り、ファイルはその中に保存されます。</p>
        <div class="platform-modal-actions">
            <button type="button" class="platform-btn" data-close-storage-prefix-modal>閉じる</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var codePattern = /^[a-z0-9_]+$/;
    var prefixPattern = /^[A-Za-z0-9_-]+$/;
    var dbPattern = /^[A-Za-z0-9_]+$/;

    function toHalfWidth(value) {
        return value.replace(/[！-～]/g, function (char) {
            return String.fromCharCode(char.charCodeAt(0) - 0xFEE0);
        }).replace(/\u3000/g, ' ');
    }

    function bindRule(field, pattern, message, allowEmpty) {
        if (!field) {
            return;
        }
        function check() {
            var value = field.value;
            if (value === '' && allowEmpty) {
                field.setCustomValidity('');
                return;
            }
            if (field.id === 'prefix' && value === 'platform') {
                field.setCustomValidity('URLプレフィックスに platform は使えません。');
                return;
            }
            field.setCustomValidity(pattern.test(value) ? '' : message);
        }
        field.addEventListener('input', check);
        field.addEventListener('blur', check);
        field._checkRule = check;
    }

    var code = document.getElementById('code');
    if (code) {
        var linked = ['prefix', 'storage_prefix', 'db_database_suffix'].map(function (id) {
            return document.getElementById(id);
        }).filter(Boolean);

        linked.forEach(function (field) {
            field.dataset.locked = field.value.trim() === '' ? '0' : '1';
            field.addEventListener('input', function () {
                field.dataset.locked = field.value.trim() === '' ? '0' : '1';
            });
        });

        code.addEventListener('input', function () {
            var next = toHalfWidth(code.value).toLowerCase().replace(/[^a-z0-9_]/g, '');
            if (next !== code.value) {
                code.value = next;
            }
            code.setCustomValidity(next === '' || codePattern.test(next) ? '' : 'テナントコードは半角小文字・数字・アンダースコアのみ使用できます。');
            linked.forEach(function (field) {
                if (field.dataset.locked === '1') {
                    return;
                }
                field.value = next;
                if (typeof field._checkRule === 'function') {
                    field._checkRule();
                }
            });
        });
    }

    bindRule(
        document.getElementById('prefix'),
        prefixPattern,
        'URLプレフィックスは半角英数字・ハイフン・アンダースコアのみ使用できます。',
        false
    );
    bindRule(
        document.getElementById('db_database_suffix'),
        dbPattern,
        'データベース名は半角英数字・アンダースコアのみ使用できます。ハイフンは使えません。',
        false
    );
    bindRule(
        document.getElementById('storage_prefix'),
        prefixPattern,
        '保存先プレフィックスは半角英数字・ハイフン・アンダースコアのみ使用できます。',
        !document.getElementById('storage_prefix') || !document.getElementById('storage_prefix').required
    );

    var storageMode = document.getElementById('storage_mode');
    var pathField = document.querySelector('.onpre-path-field');
    function updateStorageFields() {
        if (!storageMode || !pathField) {
            return;
        }
        pathField.hidden = storageMode.value !== 'onpre';
    }
    if (storageMode) {
        storageMode.addEventListener('change', updateStorageFields);
        updateStorageFields();
    }

    var tameruAi = document.querySelector('input[name="features[ichifuji]"]');
    var aiSection = document.getElementById('ai-settings');
    var aiNav = document.getElementById('ai-settings-nav');
    var provider = document.getElementById('ledger_ocr_provider');
    var modelField = document.getElementById('gemini-model-field');

    function syncAiSettings() {
        if (!aiSection || !tameruAi) {
            return;
        }
        var enabled = tameruAi.checked && !tameruAi.disabled;
        aiSection.hidden = !enabled;
        if (aiNav) {
            aiNav.hidden = !enabled;
        }
        if (modelField && provider) {
            modelField.hidden = provider.value !== 'gemini';
        }
    }

    if (tameruAi) {
        tameruAi.addEventListener('change', syncAiSettings);
    }
    if (provider) {
        provider.addEventListener('change', syncAiSettings);
    }

    var featureGroups = document.querySelectorAll('.feature-group');
    if (featureGroups.length === 0) {
        syncAiSettings();
    }
    featureGroups.forEach(function (group) {
        var parent = group.querySelector('.feature-app input[type="checkbox"]');
        var custom = group.querySelector('.feature-custom');
        if (!parent || !custom) {
            return;
        }
        function syncCustom() {
            var on = parent.checked;
            custom.hidden = !on;
            custom.querySelectorAll('input[type="checkbox"]').forEach(function (input) {
                input.disabled = !on;
            });
            syncAiSettings();
        }
        parent.addEventListener('change', syncCustom);
        syncCustom();
    });

    var infoButton = document.getElementById('open-storage-prefix-info');
    var infoModal = document.getElementById('storage-prefix-modal');
    function setInfoModal(open) {
        if (!infoModal) {
            return;
        }
        infoModal.hidden = !open;
        document.body.classList.toggle('platform-modal-open', open);
    }
    if (infoButton && infoModal) {
        infoButton.addEventListener('click', function () {
            setInfoModal(true);
        });
        infoModal.querySelectorAll('[data-close-storage-prefix-modal]').forEach(function (button) {
            button.addEventListener('click', function () {
                setInfoModal(false);
            });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !infoModal.hidden) {
                setInfoModal(false);
            }
        });
    }
});
</script>
