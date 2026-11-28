<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('membershipWaMessageModal');
    if (!modalEl) return;

    var alertEl = document.getElementById('membershipWaMessageAlert');
    var studentIdEl = document.getElementById('membershipWaMessageStudentId');
    var templateEl = document.getElementById('membershipWaMessageTemplateId');
    var previewWrap = document.getElementById('membershipWaMessagePreviewWrap');
    var previewBody = document.getElementById('membershipWaMessagePreviewBody');
    var previewBtn = document.getElementById('membershipWaMessagePreviewBtn');
    var submitBtn = document.getElementById('membershipWaMessageSubmitBtn');

    function setLoading(btn, loading) {
        if (!btn) return;
        var label = btn.querySelector('.membership-wa-btn__label');
        var spinner = btn.querySelector('.membership-wa-btn__spinner');
        btn.disabled = loading;
        if (label) label.classList.toggle('d-none', loading);
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function hideAlert() {
        alertEl.classList.add('d-none', 'alert');
        alertEl.classList.remove('alert-success', 'alert-danger');
        alertEl.textContent = '';
    }

    function showAlert(message, type) {
        alertEl.textContent = message;
        alertEl.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertEl.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
    }

    function payload() {
        var fd = new FormData();
        if (studentIdEl.value) fd.append('student_id', studentIdEl.value);
        if (templateEl.value) fd.append('whatsapp_template_id', templateEl.value);
        return fd;
    }

    function post(url) {
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenMeta ? tokenMeta.getAttribute('content') : '',
            },
            credentials: 'same-origin',
            body: payload(),
        }).then(function(response) {
            return response.json()
                .then(function(data) { return { ok: response.ok, data: data }; })
                .catch(function() { return { ok: response.ok, data: {} }; });
        });
    }

    // تفويض الحدث على مستوى المستند ليعمل بعد إعادة تحميل الجدول عبر AJAX
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-membership-wa-message');
        if (!btn) return;

        document.getElementById('membershipWaMessageStudentName').textContent = btn.getAttribute('data-student-name') || '—';
        document.getElementById('membershipWaMessageStudentPhone').textContent = btn.getAttribute('data-student-phone') || '—';
        studentIdEl.value = btn.getAttribute('data-student-id') || '';

        hideAlert();
        previewWrap.classList.add('d-none');
        setLoading(previewBtn, false);
        setLoading(submitBtn, false);

        if (window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    templateEl.addEventListener('change', function() {
        previewWrap.classList.add('d-none');
        hideAlert();
    });

    previewBtn.addEventListener('click', function() {
        if (!templateEl.value) {
            showAlert('يرجى اختيار قالب الواتساب أولاً.', 'error');
            return;
        }
        hideAlert();
        setLoading(previewBtn, true);
        post(modalEl.dataset.previewUrl)
            .then(function(result) {
                if (result.ok && result.data.success) {
                    previewBody.textContent = result.data.body || '';
                    previewWrap.classList.remove('d-none');
                    return;
                }
                showAlert(result.data.message || 'تعذر تحميل المعاينة.', 'error');
            })
            .catch(function() { showAlert('تعذر تحميل المعاينة. حاول مرة أخرى.', 'error'); })
            .finally(function() { setLoading(previewBtn, false); });
    });

    submitBtn.addEventListener('click', function() {
        if (!templateEl.value) {
            showAlert('يرجى اختيار قالب الواتساب.', 'error');
            return;
        }
        hideAlert();
        setLoading(submitBtn, true);
        post(modalEl.dataset.sendUrl)
            .then(function(result) {
                if (result.ok && result.data.success) {
                    showAlert(result.data.message || 'تم الإرسال', 'success');
                    setTimeout(function() {
                        var instance = window.bootstrap && window.bootstrap.Modal.getInstance(modalEl);
                        if (instance) instance.hide();
                    }, 1200);
                    return;
                }
                showAlert(result.data.message || 'تعذر إرسال الرسالة', 'error');
            })
            .catch(function() { showAlert('تعذر إرسال الرسالة. حاول مرة أخرى.', 'error'); })
            .finally(function() { setLoading(submitBtn, false); });
    });
});
</script>
