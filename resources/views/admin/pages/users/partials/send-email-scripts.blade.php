<script>
(function () {
    'use strict';

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var token = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function showSendEmailAlert(message, type) {
        var el = document.getElementById('sendEmailAlert');
        if (!el) return;
        el.textContent = message;
        el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        el.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    }

    function hideSendEmailAlert() {
        var el = document.getElementById('sendEmailAlert');
        if (!el) return;
        el.classList.add('d-none');
        el.textContent = '';
    }

    function setSendEmailButtonLoading(btn, loading) {
        if (!btn) return;
        var label = btn.querySelector('.send-email-btn__label');
        var spinner = btn.querySelector('.send-email-btn__spinner');
        btn.disabled = loading;
        if (label) label.classList.toggle('d-none', loading);
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function getSendEmailPayload() {
        var templateEl = document.getElementById('sendEmailTemplateId');
        var settingEl = document.getElementById('sendEmailSettingId');
        var formData = new FormData();
        if (templateEl && templateEl.value) {
            formData.append('email_template_id', templateEl.value);
        }
        if (settingEl && settingEl.value) {
            formData.append('email_setting_id', settingEl.value);
        }
        return formData;
    }

    function renderSendEmailPreview(data) {
        var wrap = document.getElementById('sendEmailPreviewWrap');
        var subjectEl = document.getElementById('sendEmailPreviewSubject');
        var bodyEl = document.getElementById('sendEmailPreviewBody');
        if (!wrap || !subjectEl || !bodyEl) return;
        subjectEl.textContent = data.subject || '—';
        bodyEl.innerHTML = data.body || '';
        wrap.classList.remove('d-none');
    }

    function resetSendEmailModal() {
        hideSendEmailAlert();
        var templateEl = document.getElementById('sendEmailTemplateId');
        var settingEl = document.getElementById('sendEmailSettingId');
        var wrap = document.getElementById('sendEmailPreviewWrap');
        if (templateEl) templateEl.value = '';
        if (wrap) wrap.classList.add('d-none');
        var previewBtn = document.getElementById('sendEmailPreviewBtn');
        var submitBtn = document.getElementById('sendEmailSubmitBtn');
        setSendEmailButtonLoading(previewBtn, false);
        setSendEmailButtonLoading(submitBtn, false);
    }

    window.openSendEmailModal = function (btn) {
        var modalEl = document.getElementById('sendEmailModal');
        if (!modalEl || !btn) return;

        modalEl.dataset.previewUrl = btn.getAttribute('data-preview-url') || '';
        modalEl.dataset.sendUrl = btn.getAttribute('data-send-url') || '';

        var nameEl = document.getElementById('sendEmailUserName');
        var emailEl = document.getElementById('sendEmailUserEmail');
        if (nameEl) nameEl.textContent = btn.getAttribute('data-user-name') || '—';
        if (emailEl) emailEl.textContent = btn.getAttribute('data-user-email') || '—';

        resetSendEmailModal();

        if (window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    function fetchSendEmailPreview() {
        var modalEl = document.getElementById('sendEmailModal');
        var previewBtn = document.getElementById('sendEmailPreviewBtn');
        var templateEl = document.getElementById('sendEmailTemplateId');
        var previewUrl = modalEl ? modalEl.dataset.previewUrl : '';

        if (!templateEl || !templateEl.value) {
            showSendEmailAlert('يرجى اختيار قالب البريد أولاً.', 'error');
            return;
        }
        if (!previewUrl) return;

        hideSendEmailAlert();
        setSendEmailButtonLoading(previewBtn, true);

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getSendEmailPayload(),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: {} };
                });
            })
            .then(function (result) {
                if (result.ok && result.data.success) {
                    renderSendEmailPreview(result.data);
                    return;
                }
                var msg = result.data.message || (result.data.errors ? Object.values(result.data.errors).flat().join(' ') : 'تعذر تحميل المعاينة.');
                showSendEmailAlert(msg, 'error');
            })
            .catch(function () {
                showSendEmailAlert('تعذر تحميل المعاينة. حاول مرة أخرى.', 'error');
            })
            .finally(function () {
                setSendEmailButtonLoading(previewBtn, false);
            });
    }

    function submitSendEmail() {
        var modalEl = document.getElementById('sendEmailModal');
        var submitBtn = document.getElementById('sendEmailSubmitBtn');
        var templateEl = document.getElementById('sendEmailTemplateId');
        var sendUrl = modalEl ? modalEl.dataset.sendUrl : '';

        if (!templateEl || !templateEl.value) {
            showSendEmailAlert('يرجى اختيار قالب البريد.', 'error');
            return;
        }
        if (!sendUrl) return;

        hideSendEmailAlert();
        setSendEmailButtonLoading(submitBtn, true);

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getSendEmailPayload(),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                }).catch(function () {
                    return { ok: response.ok, data: {} };
                });
            })
            .then(function (result) {
                if (result.ok && result.data.success) {
                    showSendEmailAlert(result.data.message, 'success');
                    setTimeout(function () {
                        if (modalEl && window.bootstrap) {
                            var instance = window.bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        }
                    }, 1200);
                    return;
                }
                var msg = result.data.message || (result.data.errors ? Object.values(result.data.errors).flat().join(' ') : 'تعذر إرسال البريد.');
                showSendEmailAlert(msg, 'error');
            })
            .catch(function () {
                showSendEmailAlert('تعذر إرسال البريد. حاول مرة أخرى.', 'error');
            })
            .finally(function () {
                setSendEmailButtonLoading(submitBtn, false);
            });
    }

    document.addEventListener('click', function (e) {
        var openBtn = e.target.closest('.js-open-send-email');
        if (openBtn) {
            e.preventDefault();
            window.openSendEmailModal(openBtn);
            return;
        }

        if (e.target.closest('#sendEmailPreviewBtn')) {
            e.preventDefault();
            fetchSendEmailPreview();
            return;
        }

        if (e.target.closest('#sendEmailSubmitBtn')) {
            e.preventDefault();
            submitSendEmail();
        }
    });

    var templateSelect = document.getElementById('sendEmailTemplateId');
    if (templateSelect) {
        templateSelect.addEventListener('change', function () {
            var wrap = document.getElementById('sendEmailPreviewWrap');
            if (wrap) wrap.classList.add('d-none');
            hideSendEmailAlert();
        });
    }
})();
</script>
