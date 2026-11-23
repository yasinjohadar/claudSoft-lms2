<script>
document.addEventListener('DOMContentLoaded', function() {
    function setMembershipEmailBtnLoading(btn, loading) {
        if (!btn) return;
        var label = btn.querySelector('.membership-email-btn__label');
        var spinner = btn.querySelector('.membership-email-btn__spinner');
        btn.disabled = loading;
        if (label) label.classList.toggle('d-none', loading);
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function hideMembershipEmailAlert() {
        var alertEl = document.getElementById('membershipEmailInviteAlert');
        if (!alertEl) return;
        alertEl.classList.add('d-none');
        alertEl.textContent = '';
        alertEl.classList.remove('alert-success', 'alert-danger');
    }

    function showMembershipEmailAlert(message, type) {
        var alertEl = document.getElementById('membershipEmailInviteAlert');
        if (!alertEl) return;
        alertEl.textContent = message;
        alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertEl.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    }

    function getMembershipEmailPayload() {
        var formData = new FormData();
        var studentIdEl = document.getElementById('membershipEmailInviteStudentId');
        var templateEl = document.getElementById('membershipEmailInviteTemplateId');
        var settingEl = document.getElementById('membershipEmailInviteSettingId');
        if (studentIdEl && studentIdEl.value) {
            formData.append('student_id', studentIdEl.value);
        }
        if (templateEl && templateEl.value) {
            formData.append('email_template_id', templateEl.value);
        }
        if (settingEl && settingEl.value) {
            formData.append('email_setting_id', settingEl.value);
        }
        return formData;
    }

    function resetMembershipEmailInviteModal() {
        hideMembershipEmailAlert();
        var previewWrap = document.getElementById('membershipEmailInvitePreviewWrap');
        if (previewWrap) previewWrap.classList.add('d-none');
        setMembershipEmailBtnLoading(document.getElementById('membershipEmailInvitePreviewBtn'), false);
        setMembershipEmailBtnLoading(document.getElementById('membershipEmailInviteSubmitBtn'), false);
    }

    window.initMembershipEmailInviteButtons = function() {
        document.querySelectorAll('.js-membership-email-invite').forEach(function(btn) {
            if (btn.dataset.emailInviteBound === '1') return;
            btn.dataset.emailInviteBound = '1';
            btn.addEventListener('click', function() {
                var modalEl = document.getElementById('membershipEmailInviteModal');
                if (!modalEl) return;

                document.getElementById('membershipEmailInviteStudentName').textContent = btn.getAttribute('data-student-name') || '—';
                document.getElementById('membershipEmailInviteStudentEmail').textContent = btn.getAttribute('data-student-email') || '—';
                document.getElementById('membershipEmailInviteStudentId').value = btn.getAttribute('data-student-id') || '';

                var templateEl = document.getElementById('membershipEmailInviteTemplateId');
                var defaultTemplateId = modalEl.dataset.defaultTemplateId || '';
                if (templateEl && defaultTemplateId) {
                    templateEl.value = defaultTemplateId;
                }

                resetMembershipEmailInviteModal();

                if (window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            });
        });
    };

    function fetchMembershipEmailPreview() {
        var modalEl = document.getElementById('membershipEmailInviteModal');
        var previewBtn = document.getElementById('membershipEmailInvitePreviewBtn');
        var templateEl = document.getElementById('membershipEmailInviteTemplateId');
        var previewUrl = modalEl ? modalEl.dataset.previewUrl : '';
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        if (!templateEl || !templateEl.value) {
            showMembershipEmailAlert('يرجى اختيار قالب البريد أولاً.', 'error');
            return;
        }
        if (!previewUrl) return;

        hideMembershipEmailAlert();
        setMembershipEmailBtnLoading(previewBtn, true);

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getMembershipEmailPayload(),
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
                    var previewWrap = document.getElementById('membershipEmailInvitePreviewWrap');
                    var previewSubject = document.getElementById('membershipEmailInvitePreviewSubject');
                    var previewBody = document.getElementById('membershipEmailInvitePreviewBody');
                    if (previewSubject) previewSubject.textContent = result.data.subject || '—';
                    if (previewBody) previewBody.innerHTML = result.data.body || '';
                    if (previewWrap) previewWrap.classList.remove('d-none');
                    return;
                }
                showMembershipEmailAlert(result.data.message || 'تعذر تحميل المعاينة.', 'error');
            })
            .catch(function() {
                showMembershipEmailAlert('تعذر تحميل المعاينة. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setMembershipEmailBtnLoading(previewBtn, false);
            });
    }

    // تحديث خلية البريد للطالب في مكانها بدل إعادة تحميل الجدول (للحفاظ على الصفحة والتمرير)
    function markMembershipEmailRowInvited(studentId, sentAt) {
        if (!studentId) return;
        if (!sentAt) {
            var d = new Date();
            var p = function(n) { return String(n).padStart(2, '0'); };
            sentAt = d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
        }
        document.querySelectorAll('.js-membership-email-invite[data-student-id="' + studentId + '"]').forEach(function(btn) {
            var cell = btn.closest('td');
            if (!cell) return;

            Array.from(cell.children).forEach(function(child) {
                if (child !== btn) child.remove();
            });

            var badge = document.createElement('span');
            badge.className = 'badge bg-warning-transparent text-warning d-block mb-1';
            badge.innerHTML = '<i class="fe fe-mail me-1"></i>دُعي بالبريد';

            var sentEl = document.createElement('small');
            sentEl.className = 'text-muted d-block mb-1';
            sentEl.textContent = 'آخر دعوة: ' + sentAt;

            cell.insertBefore(badge, btn);
            cell.insertBefore(sentEl, btn);

            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
            btn.innerHTML = '<i class="fe fe-send me-1"></i>إعادة الدعوة';

            var row = btn.closest('tr');
            if (row) {
                row.classList.add('membership-invite-flash');
                setTimeout(function() { row.classList.remove('membership-invite-flash'); }, 2000);
            }
        });
    }

    function submitMembershipEmailInvite() {
        var modalEl = document.getElementById('membershipEmailInviteModal');
        var submitBtn = document.getElementById('membershipEmailInviteSubmitBtn');
        var templateEl = document.getElementById('membershipEmailInviteTemplateId');
        var sendUrl = modalEl ? modalEl.dataset.sendUrl : '';
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        if (!templateEl || !templateEl.value) {
            showMembershipEmailAlert('يرجى اختيار قالب البريد.', 'error');
            return;
        }
        if (!sendUrl) return;

        hideMembershipEmailAlert();
        setMembershipEmailBtnLoading(submitBtn, true);

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            credentials: 'same-origin',
            body: getMembershipEmailPayload(),
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
                    showMembershipEmailAlert(result.data.message || 'تم الإرسال', 'success');
                    markMembershipEmailRowInvited(
                        result.data.student_id || document.getElementById('membershipEmailInviteStudentId').value,
                        result.data.invite_sent_at
                    );
                    setTimeout(function() {
                        if (modalEl && window.bootstrap) {
                            var instance = window.bootstrap.Modal.getInstance(modalEl);
                            if (instance) instance.hide();
                        }
                    }, 900);
                    return;
                }
                showMembershipEmailAlert(result.data.message || 'تعذر إرسال البريد', 'error');
            })
            .catch(function() {
                showMembershipEmailAlert('تعذر إرسال البريد. حاول مرة أخرى.', 'error');
            })
            .finally(function() {
                setMembershipEmailBtnLoading(submitBtn, false);
            });
    }

    var membershipEmailPreviewBtn = document.getElementById('membershipEmailInvitePreviewBtn');
    if (membershipEmailPreviewBtn) {
        membershipEmailPreviewBtn.addEventListener('click', fetchMembershipEmailPreview);
    }

    var membershipEmailSubmitBtn = document.getElementById('membershipEmailInviteSubmitBtn');
    if (membershipEmailSubmitBtn) {
        membershipEmailSubmitBtn.addEventListener('click', submitMembershipEmailInvite);
    }

    var membershipEmailTemplateSelect = document.getElementById('membershipEmailInviteTemplateId');
    if (membershipEmailTemplateSelect) {
        membershipEmailTemplateSelect.addEventListener('change', function() {
            var previewWrap = document.getElementById('membershipEmailInvitePreviewWrap');
            if (previewWrap) previewWrap.classList.add('d-none');
            hideMembershipEmailAlert();
        });
    }

    if (typeof window.initMembershipEmailInviteButtons === 'function') {
        window.initMembershipEmailInviteButtons();
    }
});
</script>
