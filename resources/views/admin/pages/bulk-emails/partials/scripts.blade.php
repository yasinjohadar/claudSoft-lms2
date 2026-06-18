<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('#bulkEmailForm input[name="_token"]')?.value;

    const previewCountUrl = @json(route('admin.bulk-emails.preview-count'));
    const previewContentUrl = @json(route('admin.bulk-emails.preview-content'));
    const previewRecipientsUrl = @json(route('admin.bulk-emails.preview-recipients'));
    const studentsApiUrl = @json(route('admin.notifications.api.students'));

    let tinyEditor = null;
    let individualSelectInitialized = false;
    let selectedSelectInitialized = false;

    function getAudienceType() {
        return document.querySelector('input[name="audience_type"]:checked')?.value || 'individual';
    }

    function getContentMode() {
        return document.querySelector('input[name="content_mode"]:checked')?.value || 'template';
    }

    function getActiveAudiencePanel() {
        return document.querySelector(`[data-audience-panel="${getAudienceType()}"]`);
    }

    function setAudiencePanelInputsState() {
        document.querySelectorAll('[data-audience-panel]').forEach(panel => {
            const isActive = panel === getActiveAudiencePanel();
            panel.classList.toggle('d-none', !isActive);
            panel.querySelectorAll('input, select, textarea, button').forEach(input => {
                input.disabled = !isActive;
            });
        });
    }

    function getStudentIdsForType(type) {
        if (type === 'individual') {
            const value = document.getElementById('student_id_individual')?.value;
            return value ? [value] : [];
        }
        if (type === 'selected') {
            return $('#student_ids_selected').val() || [];
        }
        return [];
    }

    function getCourseIdForType(type) {
        if (type === 'course') {
            return document.getElementById('course_id')?.value || '';
        }
        if (type === 'course_group') {
            return document.getElementById('course_id_course_group')?.value || '';
        }
        return '';
    }

    function getGroupIdForType(type) {
        if (type === 'group') {
            return document.getElementById('group_id')?.value || '';
        }
        if (type === 'course_group') {
            return document.getElementById('group_id_course_group')?.value || '';
        }
        return '';
    }

    function hasValidAudienceSelection(type) {
        switch (type) {
            case 'individual':
                return getStudentIdsForType(type).length === 1;
            case 'selected':
                return getStudentIdsForType(type).length > 0;
            case 'group':
                return Boolean(getGroupIdForType(type));
            case 'course':
                return Boolean(getCourseIdForType(type));
            case 'course_group':
                return Boolean(getCourseIdForType(type)) && Boolean(getGroupIdForType(type));
            default:
                return false;
        }
    }

    function initStudentSelect2(selectId, maxSelection) {
        const $select = $(selectId);
        if ($select.data('select2')) {
            $select.select2('destroy');
        }

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dir: 'rtl',
            placeholder: maxSelection === 1 ? 'ابحث عن طالب...' : 'ابحث واختر الطلاب...',
            allowClear: true,
            maximumSelectionLength: maxSelection || undefined,
            ajax: {
                url: studentsApiUrl,
                dataType: 'json',
                delay: 250,
                data: params => ({ search: params.term || '' }),
                processResults: data => ({
                    results: (data || []).map(student => ({
                        id: student.id,
                        text: `${student.name}${student.email ? ' — ' + student.email : ''}`,
                    })),
                }),
            },
        });
    }

    function toggleAudienceFields() {
        const type = getAudienceType();
        setAudiencePanelInputsState();

        if (type === 'individual' && !individualSelectInitialized) {
            initStudentSelect2('#student_id_individual', 1);
            individualSelectInitialized = true;
        }

        if (type === 'selected' && !selectedSelectInitialized) {
            initStudentSelect2('#student_ids_selected', null);
            selectedSelectInitialized = true;
        }

        document.getElementById('recipientsPreviewCard')?.classList.add('d-none');
        updateRecipientCountDisplay();
    }

    function toggleContentFields() {
        const mode = getContentMode();
        document.querySelectorAll('.content-template').forEach(el => el.classList.toggle('d-none', mode !== 'template'));
        document.querySelectorAll('.content-custom').forEach(el => el.classList.toggle('d-none', mode !== 'custom'));
    }

    function collectAudiencePayload() {
        const type = getAudienceType();
        const payload = { audience_type: type, _token: csrfToken };

        if (type === 'individual' || type === 'selected') {
            payload.student_ids = getStudentIdsForType(type);
        }
        if (type === 'course' || type === 'course_group') {
            payload.course_id = getCourseIdForType(type);
        }
        if (type === 'group' || type === 'course_group') {
            payload.group_id = getGroupIdForType(type);
        }

        return payload;
    }

    function updateRecipientCountDisplay() {
        const badge = document.getElementById('recipientCountBadge');
        const durationBadge = document.getElementById('estimatedDurationBadge');
        if (!badge) return;

        if (!hasValidAudienceSelection(getAudienceType())) {
            badge.textContent = '0';
            if (durationBadge) durationBadge.textContent = '';
            return;
        }

        refreshRecipientCount();
    }

    async function refreshRecipientCount() {
        const badge = document.getElementById('recipientCountBadge');
        const durationBadge = document.getElementById('estimatedDurationBadge');
        if (!badge) return;

        const type = getAudienceType();
        if (!hasValidAudienceSelection(type)) {
            badge.textContent = '0';
            if (durationBadge) durationBadge.textContent = '';
            return;
        }

        badge.textContent = '...';
        if (durationBadge) durationBadge.textContent = '';

        try {
            const response = await fetch(previewCountUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(collectAudiencePayload()),
            });

            const data = await response.json();
            if (data.success) {
                badge.textContent = data.count;
                if (durationBadge && data.estimated_duration_label) {
                    durationBadge.innerHTML = '<i class="fe fe-clock me-1"></i>المدة المتوقعة: <strong>' + data.estimated_duration_label + '</strong>';
                }
            } else {
                badge.textContent = '—';
            }
        } catch (e) {
            badge.textContent = '—';
        }
    }

    async function previewRecipients() {
        const card = document.getElementById('recipientsPreviewCard');
        const tbody = document.getElementById('recipientsPreviewBody');
        const meta = document.getElementById('recipientsPreviewMeta');
        if (!card || !tbody) return;

        const type = getAudienceType();
        if (!hasValidAudienceSelection(type)) {
            alert('يرجى اختيار الجمهور أولاً.');
            return;
        }

        tbody.innerHTML = '<tr><td colspan="3" class="text-muted text-center">جاري التحميل...</td></tr>';
        card.classList.remove('d-none');
        if (meta) meta.textContent = '';

        try {
            const response = await fetch(previewRecipientsUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(collectAudiencePayload()),
            });

            const data = await response.json();
            if (!data.success) {
                alert(data.message || 'تعذر تحميل قائمة المستلمين.');
                card.classList.add('d-none');
                return;
            }

            if (meta) {
                const total = data.total_count ?? data.recipients.length;
                meta.textContent = data.truncated
                    ? `عرض ${data.recipients.length} من ${total} مستلم`
                    : `${total} مستلم`;
            }

            if (!data.recipients.length) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-muted text-center">لا يوجد مستلمون.</td></tr>';
                return;
            }

            tbody.innerHTML = data.recipients.map((recipient, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(recipient.name_ar || recipient.name || '—')}</td>
                    <td>${escapeHtml(recipient.email || '—')}</td>
                </tr>
            `).join('');
        } catch (e) {
            alert('حدث خطأ أثناء تحميل قائمة المستلمين.');
            card.classList.add('d-none');
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function previewContent() {
        const mode = getContentMode();
        const studentIds = getStudentIdsForType(getAudienceType());
        const payload = {
            content_mode: mode,
            email_template_id: document.getElementById('email_template_id')?.value || null,
            subject: document.getElementById('custom_subject')?.value || '',
            body: tinyEditor ? tinyEditor.getContent() : (document.getElementById('custom_body')?.value || ''),
            student_id: studentIds.length ? studentIds[0] : null,
            _token: csrfToken,
        };

        const card = document.getElementById('contentPreviewCard');
        if (!card) return;

        try {
            const response = await fetch(previewContentUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();
            if (!data.success) {
                alert(data.message || 'تعذر معاينة المحتوى.');
                return;
            }

            document.getElementById('previewSampleUser').textContent = data.sample_user || '—';
            document.getElementById('previewSubject').textContent = data.subject || '—';
            document.getElementById('previewBody').innerHTML = data.body || '';
            card.classList.remove('d-none');
        } catch (e) {
            alert('حدث خطأ أثناء معاينة المحتوى.');
        }
    }

    function initTinyMce() {
        if (typeof tinymce === 'undefined') return;

        tinymce.init({
            selector: '#custom_body',
            height: 360,
            directionality: 'rtl',
            menubar: false,
            plugins: 'lists link code table',
            toolbar: 'undo redo | bold italic underline | alignright aligncenter alignleft | bullist numlist | link | code',
            language: 'ar',
            language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
            setup(editor) {
                tinyEditor = editor;
            },
        });
    }

    document.querySelectorAll('input[name="audience_type"]').forEach(radio => {
        radio.addEventListener('change', toggleAudienceFields);
    });

    document.querySelectorAll('input[name="content_mode"]').forEach(radio => {
        radio.addEventListener('change', toggleContentFields);
    });

    ['course_id', 'course_id_course_group', 'group_id', 'group_id_course_group'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', () => {
            document.getElementById('recipientsPreviewCard')?.classList.add('d-none');
            updateRecipientCountDisplay();
        });
    });

    document.getElementById('refreshCountBtn')?.addEventListener('click', refreshRecipientCount);
    document.getElementById('previewRecipientsBtn')?.addEventListener('click', previewRecipients);
    document.getElementById('previewContentBtn')?.addEventListener('click', previewContent);

    $('#student_id_individual').on('change', () => {
        document.getElementById('recipientsPreviewCard')?.classList.add('d-none');
        updateRecipientCountDisplay();
    });

    $('#student_ids_selected').on('change', () => {
        document.getElementById('recipientsPreviewCard')?.classList.add('d-none');
        updateRecipientCountDisplay();
    });

    document.getElementById('bulkEmailForm')?.addEventListener('submit', function () {
        setAudiencePanelInputsState();

        if (tinyEditor) {
            tinyEditor.save();
        }
        const btn = document.getElementById('submitCampaignBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...';
        }
    });

    toggleContentFields();
    toggleAudienceFields();
    initTinyMce();
})();
</script>
