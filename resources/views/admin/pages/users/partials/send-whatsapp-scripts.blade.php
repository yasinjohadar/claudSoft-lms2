<script>
(function () {
    'use strict';

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var token = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function showSendWhatsAppAlert(message, type) {
        var el = document.getElementById('sendWhatsAppAlert');
        if (!el) return;
        el.textContent = message;
        el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        el.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    }

    function hideSendWhatsAppAlert() {
        var el = document.getElementById('sendWhatsAppAlert');
        if (!el) return;
        el.classList.add('d-none');
        el.textContent = '';
    }

    function setSendWhatsAppButtonLoading(btn, loading) {
        if (!btn) return;
        var label = btn.querySelector('.send-whatsapp-btn__label');
        var spinner = btn.querySelector('.send-whatsapp-btn__spinner');
        btn.disabled = loading;
        if (label) label.classList.toggle('d-none', loading);
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function getSendWhatsAppPayload() {
        var templateEl = document.getElementById('sendWhatsAppTemplateId');
        var instanceEl = document.getElementById('sendWhatsAppInstanceName');
        var formData = new FormData();
        if (templateEl && templateEl.value) {
            formData.append('whatsapp_template_id', templateEl.value);
        }
        if (instanceEl && instanceEl.value) {
            formData.append('evolution_instance_name', instanceEl.value);
        }
        return formData;
    }

    function renderSendWhatsAppPreview(data) {
        var wrap = document.getElementById('sendWhatsAppPreviewWrap');
        var bodyEl = document.getElementById('sendWhatsAppPreviewBody');
        if (!wrap || !bodyEl) return;
        bodyEl.textContent = data.body || '';
        wrap.classList.remove('d-none');
    }

    function resetSendWhatsAppModal() {
        hideSendWhatsAppAlert();
        var templateEl = document.getElementById('sendWhatsAppTemplateId');
        var wrap = document.getElementById('sendWhatsAppPreviewWrap');
        if (templateEl) templateEl.value = '';
        if (wrap) wrap.classList.add('d-none');
        setSendWhatsAppButtonLoading(document.getElementById('sendWhatsAppPreviewBtn'), false);
        setSendWhatsAppButtonLoading(document.getElementById('sendWhatsAppSubmitBtn'), false);
    }

    window.openSendWhatsAppModal = function (btn) {
        var modalEl = document.getElementById('sendWhatsAppModal');
        if (!modalEl || !btn) return;

        modalEl.dataset.previewUrl = btn.getAttribute('data-preview-url') || '';
        modalEl.dataset.sendUrl = btn.getAttribute('data-send-url') || '';

        var nameEl = document.getElementById('sendWhatsAppUserName');
        var phoneEl = document.getElementById('sendWhatsAppUserPhone');
        if (nameEl) nameEl.textContent = btn.getAttribute('data-user-name') || '—';
        if (phoneEl) phoneEl.textContent = btn.getAttribute('data-user-phone') || '—';

        resetSendWhatsAppModal();

        if (window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    function fetchSendWhatsAppPreview() {
        var modalEl = document.getElementById('sendWhatsAppModal');
        var previewBtn = document.getElementById('sendWhatsAppPreviewBtn');
        var templateEl = document.getElementById('sendWhatsAppTemplateId');
        var previewUrl = modalEl ? modalEl.dataset.previewUrl : '';

        if (!templateEl || !templateEl.value) {
            showSendWhatsAppAlert('يرجى اختيار قالب الواتساب أولاً.', 'error');
            return;
        }
        if (!previewUrl) return;

        hideSendWhatsAppAlert();
        setSendWhatsAppButtonLoading(previewBtn, true);

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getSendWhatsAppPayload(),
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
                    renderSendWhatsAppPreview(result.data);
                    return;
                }
                var msg = result.data.message || (result.data.errors ? Object.values(result.data.errors).flat().join(' ') : 'تعذر تحميل المعاينة.');
                showSendWhatsAppAlert(msg, 'error');
            })
            .catch(function () {
                showSendWhatsAppAlert('تعذر تحميل المعاينة. حاول مرة أخرى.', 'error');
            })
            .finally(function () {
                setSendWhatsAppButtonLoading(previewBtn, false);
            });
    }

    function submitSendWhatsApp() {
        var modalEl = document.getElementById('sendWhatsAppModal');
        var submitBtn = document.getElementById('sendWhatsAppSubmitBtn');
        var templateEl = document.getElementById('sendWhatsAppTemplateId');
        var sendUrl = modalEl ? modalEl.dataset.sendUrl : '';

        if (!templateEl || !templateEl.value) {
            showSendWhatsAppAlert('يرجى اختيار قالب الواتساب.', 'error');
            return;
        }
        if (!sendUrl) return;

        hideSendWhatsAppAlert();
        setSendWhatsAppButtonLoading(submitBtn, true);

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getSendWhatsAppPayload(),
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
                    showSendWhatsAppAlert(result.data.message, 'success');
                    setTimeout(function () {
                        if (modalEl && window.bootstrap) {
                            var instance = window.bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        }
                    }, 1200);
                    return;
                }
                var msg = result.data.message || (result.data.errors ? Object.values(result.data.errors).flat().join(' ') : 'تعذر إرسال رسالة الواتساب.');
                showSendWhatsAppAlert(msg, 'error');
            })
            .catch(function () {
                showSendWhatsAppAlert('تعذر إرسال رسالة الواتساب. حاول مرة أخرى.', 'error');
            })
            .finally(function () {
                setSendWhatsAppButtonLoading(submitBtn, false);
            });
    }

    document.addEventListener('click', function (e) {
        var openBtn = e.target.closest('.js-open-send-whatsapp');
        if (openBtn) {
            e.preventDefault();
            window.openSendWhatsAppModal(openBtn);
            return;
        }

        if (e.target.closest('#sendWhatsAppPreviewBtn')) {
            e.preventDefault();
            fetchSendWhatsAppPreview();
            return;
        }

        if (e.target.closest('#sendWhatsAppSubmitBtn')) {
            e.preventDefault();
            submitSendWhatsApp();
        }
    });

    var templateSelect = document.getElementById('sendWhatsAppTemplateId');
    if (templateSelect) {
        templateSelect.addEventListener('change', function () {
            var wrap = document.getElementById('sendWhatsAppPreviewWrap');
            if (wrap) wrap.classList.add('d-none');
            hideSendWhatsAppAlert();
        });
    }
})();
</script>
