<script>
(function () {
    const modal = document.getElementById('membershipTgInviteModal');
    if (!modal) return;
    const previewUrl = modal.dataset.previewUrl;
    const sendUrl = modal.dataset.sendUrl;
    const bsModal = bootstrap.Modal.getOrCreateInstance(modal);

    function showAlert(msg, type) {
        const el = document.getElementById('membershipTgInviteAlert');
        el.className = 'alert alert-' + (type || 'info');
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    document.querySelectorAll('.js-membership-tg-invite').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('membershipTgInviteStudentId').value = btn.dataset.studentId;
            document.getElementById('membershipTgInviteStudentName').textContent = btn.dataset.studentName || '—';
            document.getElementById('membershipTgInviteAlert').classList.add('d-none');
            document.getElementById('membershipTgInvitePreviewWrap').classList.add('d-none');
            if (modal.dataset.defaultTemplateId) {
                document.getElementById('membershipTgInviteTemplateId').value = modal.dataset.defaultTemplateId;
            }
            bsModal.show();
        });
    });

    document.getElementById('membershipTgInvitePreviewBtn')?.addEventListener('click', async function () {
        const studentId = document.getElementById('membershipTgInviteStudentId').value;
        const templateId = document.getElementById('membershipTgInviteTemplateId').value;
        const r = await fetch(previewUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
            body: JSON.stringify({ student_id: studentId, telegram_template_id: templateId }),
        });
        const j = await r.json();
        if (j.success) {
            document.getElementById('membershipTgInvitePreviewBody').textContent = j.body;
            document.getElementById('membershipTgInvitePreviewWrap').classList.remove('d-none');
        } else {
            showAlert(j.message || 'فشل المعاينة', 'danger');
        }
    });

    document.getElementById('membershipTgInviteSubmitBtn')?.addEventListener('click', async function () {
        const studentId = document.getElementById('membershipTgInviteStudentId').value;
        const templateId = document.getElementById('membershipTgInviteTemplateId').value;
        const r = await fetch(sendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
            body: JSON.stringify({ student_id: studentId, telegram_template_id: templateId }),
        });
        const j = await r.json();
        if (j.success) {
            showAlert(j.message, 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showAlert(j.message || 'فشل الإرسال', 'danger');
        }
    });
})();
</script>
