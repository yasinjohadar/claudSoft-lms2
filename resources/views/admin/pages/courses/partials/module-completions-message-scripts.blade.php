<script>
document.addEventListener('DOMContentLoaded', function() {
    function csrfToken() {
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        return tokenMeta ? tokenMeta.getAttribute('content') : '';
    }

    function currentGroupId() {
        var filterForm = document.getElementById('js-module-completions-filter-form');
        if (!filterForm) return '';
        var groupSelect = filterForm.querySelector('select[name="group_id"]');
        return groupSelect && groupSelect.value ? groupSelect.value : '';
    }

    function setBtnLoading(btn, labelClass, loading) {
        if (!btn) return;
        var label = btn.querySelector('.' + labelClass + '__label');
        var spinner = btn.querySelector('.' + labelClass + '__spinner');
        btn.disabled = loading;
        if (label) label.classList.toggle('d-none', loading);
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function hideAlert(alertId) {
        var alertEl = document.getElementById(alertId);
        if (!alertEl) return;
        alertEl.classList.add('d-none');
        alertEl.textContent = '';
        alertEl.classList.remove('alert-success', 'alert-danger');
    }

    function showAlert(alertId, message, type) {
        var alertEl = document.getElementById(alertId);
        if (!alertEl) return;
        alertEl.textContent = message;
        alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertEl.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    }

    function appendMessagingContext(formData) {
        var groupId = currentGroupId();
        if (groupId) {
            formData.append('group_id', groupId);
        }
        return formData;
    }

    /* WhatsApp */
    function getModuleWaPayload() {
        var formData = new FormData();
        var studentIdEl = document.getElementById('moduleCompletionWaStudentId');
        var completionIdEl = document.getElementById('moduleCompletionWaCompletionId');
        var templateEl = document.getElementById('moduleCompletionWaTemplateId');
        if (studentIdEl && studentIdEl.value) formData.append('student_id', studentIdEl.value);
        if (completionIdEl && completionIdEl.value) formData.append('completion_id', completionIdEl.value);
        if (templateEl && templateEl.value) formData.append('whatsapp_template_id', templateEl.value);
        return appendMessagingContext(formData);
    }

    function resetModuleWaModal() {
        hideAlert('moduleCompletionWaAlert');
        var previewWrap = document.getElementById('moduleCompletionWaPreviewWrap');
        if (previewWrap) previewWrap.classList.add('d-none');
        setBtnLoading(document.getElementById('moduleCompletionWaPreviewBtn'), 'module-wa-btn', false);
        setBtnLoading(document.getElementById('moduleCompletionWaSubmitBtn'), 'module-wa-btn', false);
    }

    window.initModuleCompletionMessageButtons = function() {
        document.querySelectorAll('.js-module-wa-message').forEach(function(btn) {
            if (btn.dataset.moduleWaBound === '1') return;
            btn.dataset.moduleWaBound = '1';
            btn.addEventListener('click', function() {
                var modalEl = document.getElementById('moduleCompletionWaModal');
                if (!modalEl) return;

                document.getElementById('moduleCompletionWaStudentName').textContent = btn.getAttribute('data-student-name') || '—';
                document.getElementById('moduleCompletionWaStudentPhone').textContent = btn.getAttribute('data-student-phone') || '—';
                document.getElementById('moduleCompletionWaStudentId').value = btn.getAttribute('data-student-id') || '';
                document.getElementById('moduleCompletionWaCompletionId').value = btn.getAttribute('data-completion-id') || '';

                resetModuleWaModal();

                if (window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            });
        });

        document.querySelectorAll('.js-module-email-message').forEach(function(btn) {
            if (btn.dataset.moduleEmailBound === '1') return;
            btn.dataset.moduleEmailBound = '1';
            btn.addEventListener('click', function() {
                var modalEl = document.getElementById('moduleCompletionEmailModal');
                if (!modalEl) return;

                document.getElementById('moduleCompletionEmailStudentName').textContent = btn.getAttribute('data-student-name') || '—';
                document.getElementById('moduleCompletionEmailStudentEmail').textContent = btn.getAttribute('data-student-email') || '—';
                document.getElementById('moduleCompletionEmailStudentId').value = btn.getAttribute('data-student-id') || '';
                document.getElementById('moduleCompletionEmailCompletionId').value = btn.getAttribute('data-completion-id') || '';

                hideAlert('moduleCompletionEmailAlert');
                var emailPreviewWrap = document.getElementById('moduleCompletionEmailPreviewWrap');
                if (emailPreviewWrap) emailPreviewWrap.classList.add('d-none');
                setBtnLoading(document.getElementById('moduleCompletionEmailPreviewBtn'), 'module-email-btn', false);
                setBtnLoading(document.getElementById('moduleCompletionEmailSubmitBtn'), 'module-email-btn', false);

                if (window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            });
        });
    };

    function fetchModuleWaPreview() {
        var modalEl = document.getElementById('moduleCompletionWaModal');
        var previewBtn = document.getElementById('moduleCompletionWaPreviewBtn');
        var templateEl = document.getElementById('moduleCompletionWaTemplateId');
        var previewUrl = modalEl ? modalEl.dataset.previewUrl : '';

        if (!templateEl || !templateEl.value) {
            showAlert('moduleCompletionWaAlert', 'يرجى اختيار قالب الواتساب أولاً.', 'error');
            return;
        }
        if (!previewUrl) return;

        hideAlert('moduleCompletionWaAlert');
        setBtnLoading(previewBtn, 'module-wa-btn', true);

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: getModuleWaPayload(),
        })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { ok: response.ok, data: data };
                }).catch(function() {
                    return { ok: response.ok, data: {} };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    var previewBody = document.getElementById('moduleCompletionWaPreviewBody');
                    var previewWrap = document.getElementById('moduleCompletionWaPreviewWrap');
                    if (previewBody) previewBody.textContent = result.data.body || '';
                    if (previewWrap) previewWrap.classList.remove('d-none');
                    return;
                }
                showAlert('moduleCompletionWaAlert', result.data.message || 'تعذر تحميل المعاينة.', 'error');
            })
            .catch(function() {
                showAlert('moduleCompletionWaAlert', 'تعذر تحميل المعاينة. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setBtnLoading(previewBtn, 'module-wa-btn', false);
            });
    }

    function submitModuleWaMessage() {
        var modalEl = document.getElementById('moduleCompletionWaModal');
        var submitBtn = document.getElementById('moduleCompletionWaSubmitBtn');
        var templateEl = document.getElementById('moduleCompletionWaTemplateId');
        var sendUrl = modalEl ? modalEl.dataset.sendUrl : '';

        if (!templateEl || !templateEl.value) {
            showAlert('moduleCompletionWaAlert', 'يرجى اختيار قالب الواتساب.', 'error');
            return;
        }
        if (!sendUrl) return;

        hideAlert('moduleCompletionWaAlert');
        setBtnLoading(submitBtn, 'module-wa-btn', true);

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: getModuleWaPayload(),
        })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { ok: response.ok, data: data };
                }).catch(function() {
                    return { ok: response.ok, data: {} };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    var msg = result.data.message || 'تم الإرسال';
                    if (result.data.instance_name) {
                        msg += ' (الجلسة: ' + result.data.instance_name + ')';
                    }
                    showAlert('moduleCompletionWaAlert', msg, 'success');
                    setTimeout(function() {
                        if (modalEl && window.bootstrap) {
                            var instance = window.bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        }
                    }, 1200);
                    return;
                }
                showAlert('moduleCompletionWaAlert', result.data.message || 'تعذر إرسال الرسالة', 'error');
            })
            .catch(function() {
                showAlert('moduleCompletionWaAlert', 'تعذر إرسال الرسالة. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setBtnLoading(submitBtn, 'module-wa-btn', false);
            });
    }

    /* Email */
    function getModuleEmailPayload() {
        var formData = new FormData();
        var studentIdEl = document.getElementById('moduleCompletionEmailStudentId');
        var completionIdEl = document.getElementById('moduleCompletionEmailCompletionId');
        var templateEl = document.getElementById('moduleCompletionEmailTemplateId');
        var settingEl = document.getElementById('moduleCompletionEmailSettingId');
        if (studentIdEl && studentIdEl.value) formData.append('student_id', studentIdEl.value);
        if (completionIdEl && completionIdEl.value) formData.append('completion_id', completionIdEl.value);
        if (templateEl && templateEl.value) formData.append('email_template_id', templateEl.value);
        if (settingEl && settingEl.value) formData.append('email_setting_id', settingEl.value);
        return appendMessagingContext(formData);
    }

    function fetchModuleEmailPreview() {
        var modalEl = document.getElementById('moduleCompletionEmailModal');
        var previewBtn = document.getElementById('moduleCompletionEmailPreviewBtn');
        var templateEl = document.getElementById('moduleCompletionEmailTemplateId');
        var previewUrl = modalEl ? modalEl.dataset.previewUrl : '';

        if (!templateEl || !templateEl.value) {
            showAlert('moduleCompletionEmailAlert', 'يرجى اختيار قالب البريد أولاً.', 'error');
            return;
        }
        if (!previewUrl) return;

        hideAlert('moduleCompletionEmailAlert');
        setBtnLoading(previewBtn, 'module-email-btn', true);

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: getModuleEmailPayload(),
        })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { ok: response.ok, data: data };
                }).catch(function() {
                    return { ok: response.ok, data: {} };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    var previewWrap = document.getElementById('moduleCompletionEmailPreviewWrap');
                    var previewSubject = document.getElementById('moduleCompletionEmailPreviewSubject');
                    var previewBody = document.getElementById('moduleCompletionEmailPreviewBody');
                    if (previewSubject) previewSubject.textContent = result.data.subject || '—';
                    if (previewBody) previewBody.innerHTML = result.data.body || '';
                    if (previewWrap) previewWrap.classList.remove('d-none');
                    return;
                }
                showAlert('moduleCompletionEmailAlert', result.data.message || 'تعذر تحميل المعاينة.', 'error');
            })
            .catch(function() {
                showAlert('moduleCompletionEmailAlert', 'تعذر تحميل المعاينة. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setBtnLoading(previewBtn, 'module-email-btn', false);
            });
    }

    function submitModuleEmailMessage() {
        var modalEl = document.getElementById('moduleCompletionEmailModal');
        var submitBtn = document.getElementById('moduleCompletionEmailSubmitBtn');
        var templateEl = document.getElementById('moduleCompletionEmailTemplateId');
        var sendUrl = modalEl ? modalEl.dataset.sendUrl : '';

        if (!templateEl || !templateEl.value) {
            showAlert('moduleCompletionEmailAlert', 'يرجى اختيار قالب البريد.', 'error');
            return;
        }
        if (!sendUrl) return;

        hideAlert('moduleCompletionEmailAlert');
        setBtnLoading(submitBtn, 'module-email-btn', true);

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: getModuleEmailPayload(),
        })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { ok: response.ok, data: data };
                }).catch(function() {
                    return { ok: response.ok, data: {} };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    showAlert('moduleCompletionEmailAlert', result.data.message || 'تم الإرسال', 'success');
                    setTimeout(function() {
                        if (modalEl && window.bootstrap) {
                            var instance = window.bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        }
                    }, 900);
                    return;
                }
                showAlert('moduleCompletionEmailAlert', result.data.message || 'تعذر إرسال البريد', 'error');
            })
            .catch(function() {
                showAlert('moduleCompletionEmailAlert', 'تعذر إرسال البريد. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setBtnLoading(submitBtn, 'module-email-btn', false);
            });
    }

    var waPreviewBtn = document.getElementById('moduleCompletionWaPreviewBtn');
    if (waPreviewBtn) waPreviewBtn.addEventListener('click', fetchModuleWaPreview);

    var waSubmitBtn = document.getElementById('moduleCompletionWaSubmitBtn');
    if (waSubmitBtn) waSubmitBtn.addEventListener('click', submitModuleWaMessage);

    var waTemplateSelect = document.getElementById('moduleCompletionWaTemplateId');
    if (waTemplateSelect) {
        waTemplateSelect.addEventListener('change', function() {
            var previewWrap = document.getElementById('moduleCompletionWaPreviewWrap');
            if (previewWrap) previewWrap.classList.add('d-none');
            hideAlert('moduleCompletionWaAlert');
        });
    }

    var emailPreviewBtn = document.getElementById('moduleCompletionEmailPreviewBtn');
    if (emailPreviewBtn) emailPreviewBtn.addEventListener('click', fetchModuleEmailPreview);

    var emailSubmitBtn = document.getElementById('moduleCompletionEmailSubmitBtn');
    if (emailSubmitBtn) emailSubmitBtn.addEventListener('click', submitModuleEmailMessage);

    var emailTemplateSelect = document.getElementById('moduleCompletionEmailTemplateId');
    if (emailTemplateSelect) {
        emailTemplateSelect.addEventListener('change', function() {
            var previewWrap = document.getElementById('moduleCompletionEmailPreviewWrap');
            if (previewWrap) previewWrap.classList.add('d-none');
            hideAlert('moduleCompletionEmailAlert');
        });
    }

    if (typeof window.initModuleCompletionMessageButtons === 'function') {
        window.initModuleCompletionMessageButtons();
    }
});
</script>
