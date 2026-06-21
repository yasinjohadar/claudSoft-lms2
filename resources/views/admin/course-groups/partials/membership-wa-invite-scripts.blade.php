<script>
document.addEventListener('DOMContentLoaded', function() {
    function setMembershipWaBtnLoading(btn, loading) {
        if (!btn) return;
        var label = btn.querySelector('.membership-wa-btn__label');
        var spinner = btn.querySelector('.membership-wa-btn__spinner');
        btn.disabled = loading;
        if (label) label.classList.toggle('d-none', loading);
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function hideMembershipWaAlert() {
        var alertEl = document.getElementById('membershipWaInviteAlert');
        if (!alertEl) return;
        alertEl.classList.add('d-none');
        alertEl.textContent = '';
        alertEl.classList.remove('alert-success', 'alert-danger');
    }

    function showMembershipWaAlert(message, type) {
        var alertEl = document.getElementById('membershipWaInviteAlert');
        if (!alertEl) return;
        alertEl.textContent = message;
        alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertEl.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    }

    function getMembershipWaPayload() {
        var formData = new FormData();
        var studentIdEl = document.getElementById('membershipWaInviteStudentId');
        var templateEl = document.getElementById('membershipWaInviteTemplateId');
        if (studentIdEl && studentIdEl.value) {
            formData.append('student_id', studentIdEl.value);
        }
        if (templateEl && templateEl.value) {
            formData.append('whatsapp_template_id', templateEl.value);
        }
        return formData;
    }

    function resetMembershipWaInviteModal() {
        hideMembershipWaAlert();
        var previewWrap = document.getElementById('membershipWaInvitePreviewWrap');
        if (previewWrap) previewWrap.classList.add('d-none');
        setMembershipWaBtnLoading(document.getElementById('membershipWaInvitePreviewBtn'), false);
        setMembershipWaBtnLoading(document.getElementById('membershipWaInviteSubmitBtn'), false);
    }

    window.initMembershipWaInviteButtons = function() {
        document.querySelectorAll('.js-membership-wa-invite').forEach(function(btn) {
            if (btn.dataset.waInviteBound === '1') return;
            btn.dataset.waInviteBound = '1';
            btn.addEventListener('click', function() {
                var modalEl = document.getElementById('membershipWaInviteModal');
                if (!modalEl) return;

                document.getElementById('membershipWaInviteStudentName').textContent = btn.getAttribute('data-student-name') || '—';
                document.getElementById('membershipWaInviteStudentPhone').textContent = btn.getAttribute('data-student-phone') || '—';
                document.getElementById('membershipWaInviteStudentId').value = btn.getAttribute('data-student-id') || '';

                var templateEl = document.getElementById('membershipWaInviteTemplateId');
                var defaultTemplateId = modalEl.dataset.defaultTemplateId || '';
                if (templateEl && defaultTemplateId) {
                    templateEl.value = defaultTemplateId;
                }

                resetMembershipWaInviteModal();

                if (window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            });
        });
    };

    function fetchMembershipWaPreview() {
        var modalEl = document.getElementById('membershipWaInviteModal');
        var previewBtn = document.getElementById('membershipWaInvitePreviewBtn');
        var templateEl = document.getElementById('membershipWaInviteTemplateId');
        var previewUrl = modalEl ? modalEl.dataset.previewUrl : '';
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        if (!templateEl || !templateEl.value) {
            showMembershipWaAlert('يرجى اختيار قالب الواتساب أولاً.', 'error');
            return;
        }
        if (!previewUrl) return;

        hideMembershipWaAlert();
        setMembershipWaBtnLoading(previewBtn, true);

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getMembershipWaPayload(),
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
                    var previewWrap = document.getElementById('membershipWaInvitePreviewWrap');
                    var previewBody = document.getElementById('membershipWaInvitePreviewBody');
                    if (previewBody) previewBody.textContent = result.data.body || '';
                    if (previewWrap) previewWrap.classList.remove('d-none');
                    return;
                }
                showMembershipWaAlert(result.data.message || 'تعذر تحميل المعاينة.', 'error');
            })
            .catch(function() {
                showMembershipWaAlert('تعذر تحميل المعاينة. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setMembershipWaBtnLoading(previewBtn, false);
            });
    }

    function submitMembershipWaInvite() {
        var modalEl = document.getElementById('membershipWaInviteModal');
        var submitBtn = document.getElementById('membershipWaInviteSubmitBtn');
        var templateEl = document.getElementById('membershipWaInviteTemplateId');
        var sendUrl = modalEl ? modalEl.dataset.sendUrl : '';
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        if (!templateEl || !templateEl.value) {
            showMembershipWaAlert('يرجى اختيار قالب الواتساب.', 'error');
            return;
        }
        if (!sendUrl) return;

        hideMembershipWaAlert();
        setMembershipWaBtnLoading(submitBtn, true);

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getMembershipWaPayload(),
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
                    showMembershipWaAlert(result.data.message || 'تم الإرسال', 'success');
                    setTimeout(function() {
                        if (modalEl && window.bootstrap) {
                            var instance = window.bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        }
                    }, 1200);
                    return;
                }
                showMembershipWaAlert(result.data.message || 'تعذر إرسال الرسالة', 'error');
            })
            .catch(function() {
                showMembershipWaAlert('تعذر إرسال الرسالة. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setMembershipWaBtnLoading(submitBtn, false);
            });
    }

    var membershipWaPreviewBtn = document.getElementById('membershipWaInvitePreviewBtn');
    if (membershipWaPreviewBtn) {
        membershipWaPreviewBtn.addEventListener('click', fetchMembershipWaPreview);
    }

    var membershipWaSubmitBtn = document.getElementById('membershipWaInviteSubmitBtn');
    if (membershipWaSubmitBtn) {
        membershipWaSubmitBtn.addEventListener('click', submitMembershipWaInvite);
    }

    var membershipWaTemplateSelect = document.getElementById('membershipWaInviteTemplateId');
    if (membershipWaTemplateSelect) {
        membershipWaTemplateSelect.addEventListener('change', function() {
            var previewWrap = document.getElementById('membershipWaInvitePreviewWrap');
            if (previewWrap) previewWrap.classList.add('d-none');
            hideMembershipWaAlert();
        });
    }

    if (typeof window.initMembershipWaInviteButtons === 'function') {
        window.initMembershipWaInviteButtons();
    }
});
</script>
