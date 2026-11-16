<div class="platform-modal" id="{{ $modalId }}" @if (empty($reopen)) hidden @endif>
    <div class="platform-modal-backdrop" data-close-otp-modal></div>
    <div class="platform-modal-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
        <h2 id="{{ $modalId }}-title">{{ $title }}</h2>
        <p class="platform-modal-warning">{{ $warning }}</p>
        <label for="{{ $modalId }}-local">メールアドレス</label>
        <div class="otp-email-input">
            <input id="{{ $modalId }}-local" type="text" inputmode="email" autocomplete="username" placeholder="例：taro" maxlength="64">
            <span>@ast-sys.co.jp</span>
        </div>
        <div class="otp-send-row">
            <button type="button" class="platform-btn secondary" id="{{ $modalId }}-send">ワンタイムパスワードを送信</button>
        </div>
        <p class="otp-status" id="{{ $modalId }}-status" @if (empty($otpError)) hidden @endif>{{ $otpError ?? '' }}</p>
        <div id="{{ $modalId }}-code-wrap" @if (empty($reopen)) hidden @endif>
            <label for="{{ $modalId }}-code">ワンタイムパスワード</label>
            <input id="{{ $modalId }}-code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6桁の数字">
        </div>
        <div class="platform-modal-actions">
            <button type="button" class="platform-btn secondary" data-close-otp-modal>キャンセル</button>
            <button type="button" class="platform-btn {{ ! empty($danger) ? 'danger' : '' }}" id="{{ $modalId }}-execute" @disabled(empty($reopen))>{{ $executeLabel }}</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById(@json($modalId));
    var openButton = document.getElementById(@json($openButtonId));
    var targetForm = document.getElementById(@json($targetFormId));
    if (!modal || !targetForm) {
        return;
    }
    var localInput = document.getElementById(@json($modalId.'-local'));
    var sendButton = document.getElementById(@json($modalId.'-send'));
    var status = document.getElementById(@json($modalId.'-status'));
    var codeWrap = document.getElementById(@json($modalId.'-code-wrap'));
    var codeInput = document.getElementById(@json($modalId.'-code'));
    var executeButton = document.getElementById(@json($modalId.'-execute'));
    var endpoint = @json(route('platform.tenants.otp'));
    var csrf = @json(csrf_token());
    var purpose = @json($purpose);
    var tenantId = @json($tenantId ?? null);

    function setStatus(message, kind) {
        status.hidden = false;
        status.textContent = message;
        status.className = 'otp-status ' + (kind || '');
    }

    function setOpen(open) {
        modal.hidden = !open;
        document.body.classList.toggle('platform-modal-open', open);
        if (open && localInput) {
            localInput.focus();
        }
    }

    function readyToExecute() {
        return /^\d{6}$/.test(codeInput.value);
    }

    function syncExecute() {
        executeButton.disabled = !readyToExecute();
    }

    targetForm.addEventListener('submit', function (event) {
        var hidden = targetForm.querySelector('[name="otp_code"]');
        if (!hidden || !/^\d{6}$/.test(hidden.value)) {
            event.preventDefault();
            if (openButton) {
                openButton.click();
            }
        }
    });

    function prepareAndOpen(button) {
        if (typeof targetForm.reportValidity === 'function' && !targetForm.reportValidity()) {
            return;
        }
        var storageMode = document.getElementById('storage_mode');
        var pathInput = document.getElementById('file_upload_path');
        if (purpose === 'create' && storageMode && pathInput && storageMode.value === 'onpre' && pathInput.value.trim() === '') {
            pathInput.setCustomValidity('ローカル保存先を入力してください。');
            pathInput.reportValidity();
            pathInput.addEventListener('input', function () {
                pathInput.setCustomValidity('');
            }, { once: true });
            return;
        }
        if (button && button.hasAttribute('data-user-id')) {
            var userField = targetForm.querySelector('[name="user_id"]');
            if (userField) {
                userField.value = button.getAttribute('data-user-id') || '';
            }
        }
        setOpen(true);
    }

    if (openButton) {
        openButton.addEventListener('click', function () {
            prepareAndOpen(openButton);
        });
    }
    document.querySelectorAll('[data-open-otp="' + modal.id + '"]').forEach(function (button) {
        if (button === openButton) {
            return;
        }
        button.addEventListener('click', function () {
            prepareAndOpen(button);
        });
    });

    modal.querySelectorAll('[data-close-otp-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            setOpen(false);
        });
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            setOpen(false);
        }
    });

    sendButton.addEventListener('click', async function () {
        var local = localInput.value.trim();
        if (!/^[A-Za-z0-9._+-]+$/.test(local)) {
            setStatus('メールアドレスは @ より前の半角英数字を入力してください。', 'error');
            return;
        }
        sendButton.disabled = true;
        setStatus('送信しています...', '');
        try {
            var response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    local: local,
                    purpose: purpose,
                    tenant_id: tenantId
                })
            });
            var data = await response.json();
            if (!response.ok) {
                var detail = data.message || '確認コードを送信できませんでした。';
                if (data.errors) {
                    var first = Object.values(data.errors)[0];
                    if (first && first[0]) {
                        detail = first[0];
                    }
                }
                throw new Error(detail);
            }
            codeWrap.hidden = false;
            setStatus(data.message, 'success');
            codeInput.focus();
        } catch (error) {
            setStatus(error.message, 'error');
        } finally {
            sendButton.disabled = false;
        }
    });

    codeInput.addEventListener('input', function () {
        codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
        syncExecute();
    });

    executeButton.addEventListener('click', function () {
        if (!readyToExecute()) {
            setStatus('6桁の確認コードを入力してください。', 'error');
            return;
        }
        var hidden = targetForm.querySelector('[name="otp_code"]');
        if (!hidden) {
            return;
        }
        hidden.value = codeInput.value;
        executeButton.disabled = true;
        targetForm.submit();
    });

    if (@json(! empty($reopen))) {
        document.body.classList.add('platform-modal-open');
        if (status.textContent) {
            status.className = 'otp-status error';
        }
    }
});
</script>
