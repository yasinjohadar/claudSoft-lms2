<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    const form = document.getElementById('points-grant-form');
    if (!form) return;

    const searchUrl = @json(route('admin.gamification.points.search-students'));
    const previewUrl = @json(route('admin.gamification.points.preview-recipients'));
    const oldUserId = @json(old('user_id'));
    const oldUserIds = @json(old('user_ids', []));

    const getTargetType = () => form.querySelector('input[name="target_type"]:checked')?.value || 'single';
    const getOperation = () => form.querySelector('input[name="operation"]:checked')?.value || 'bonus';

    const disableInactiveFields = (activeType) => {
        const fieldMap = {
            single: ['user_id'],
            multiple: ['user_ids'],
            group: ['group_id_only'],
            multiple_groups: ['group_ids'],
            course: ['course_id_only'],
            course_group: ['course_id_grouped', 'group_id_course'],
        };

        form.querySelectorAll('.target-panel input, .target-panel select, .target-panel textarea').forEach((el) => {
            el.disabled = true;
        });

        (fieldMap[activeType] || []).forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.disabled = false;
        });
    };

    const togglePanels = () => {
        const activeType = getTargetType();
        form.querySelectorAll('.target-panel').forEach((panel) => {
            panel.classList.toggle('d-none', panel.dataset.target !== activeType);
        });
        disableInactiveFields(activeType);
        if (window.pointsInitActiveStudentSelect) {
            window.pointsInitActiveStudentSelect();
        }
    };

    const toggleOperation = () => {
        const op = getOperation();
        const wrap = document.getElementById('points-input-wrap');
        const pointsInput = document.getElementById('points');
        if (!wrap || !pointsInput) return;

        if (op === 'backfill') {
            wrap.classList.add('d-none');
            pointsInput.removeAttribute('required');
            pointsInput.value = '';
        } else {
            wrap.classList.remove('d-none');
            pointsInput.setAttribute('required', 'required');
        }
    };

    form.querySelectorAll('.target-type-radio').forEach((radio) => {
        radio.addEventListener('change', togglePanels);
    });

    form.querySelectorAll('.operation-radio').forEach((radio) => {
        radio.addEventListener('change', toggleOperation);
    });

    const initStudentSelect = (selector, multiple = false) => {
        const el = $(selector);
        if (!el.length) return;

        if (el.hasClass('select2-hidden-accessible')) {
            el.select2('destroy');
        }

        el.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dir: 'rtl',
            placeholder: multiple ? 'ابحث واختر الطلاب' : 'ابحث عن طالب',
            allowClear: !multiple,
            minimumInputLength: 2,
            ajax: {
                url: searchUrl,
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term || '' }),
                processResults: (data) => ({ results: data.results || [] }),
            },
        });
    };

    window.pointsInitActiveStudentSelect = function () {
        const type = getTargetType();
        if (type === 'single') initStudentSelect('#user_id', false);
        if (type === 'multiple') initStudentSelect('#user_ids', true);
    };

    if ($('#group_ids').length && !$('#group_ids').hasClass('select2-hidden-accessible')) {
        $('#group_ids').select2({ theme: 'bootstrap-5', width: '100%', dir: 'rtl', placeholder: 'اختر مجموعة أو أكثر' });
    }

    const courseSelect = document.getElementById('course_id_grouped');
    const groupSelect = document.getElementById('group_id_course');

    const applyGroupFilter = () => {
        if (!courseSelect || !groupSelect) return;
        const courseId = courseSelect.value;
        Array.from(groupSelect.options).forEach((opt) => {
            if (!opt.value) return;
            const ids = (opt.dataset.courseIds || '').split(',').filter(Boolean);
            opt.hidden = courseId ? !ids.includes(courseId) : false;
        });
    };

    courseSelect?.addEventListener('change', applyGroupFilter);

    document.getElementById('preview-recipients-btn')?.addEventListener('click', async function () {
        const box = document.getElementById('points-preview-box');
        const formData = new FormData(form);
        formData.append('_token', @json(csrf_token()));

        try {
            const response = await fetch(previewUrl, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();

            if (!response.ok) {
                const errors = data.errors ? Object.values(data.errors).flat().join(' — ') : 'تعذرت المعاينة';
                box.className = 'alert alert-danger';
                box.textContent = errors;
                box.classList.remove('d-none');
                return;
            }

            if (data.operation === 'backfill') {
                box.className = 'alert alert-info';
                box.innerHTML = `<strong>${data.total_students}</strong> طالب مستهدف.<br>${data.message}`;
            } else {
                box.className = 'alert alert-info';
                box.innerHTML = `<strong>${data.total_students}</strong> طالب × <strong>${data.points_per_student}</strong> نقطة = <strong>${data.total_points}</strong> نقطة إجمالاً`;
            }

            box.classList.remove('d-none');
        } catch (e) {
            box.className = 'alert alert-danger';
            box.textContent = 'حدث خطأ أثناء المعاينة';
            box.classList.remove('d-none');
        }
    });

    togglePanels();
    toggleOperation();
    applyGroupFilter();

    if (oldUserId) {
        fetch(`${searchUrl}?ids[]=${oldUserId}`)
            .then((r) => r.json())
            .then((data) => {
                const item = (data.results || [])[0];
                if (item) {
                    const opt = new Option(item.text, item.id, true, true);
                    $('#user_id').append(opt).trigger('change');
                }
            });
    }

    if (oldUserIds.length) {
        const params = oldUserIds.map((id) => `ids[]=${id}`).join('&');
        fetch(`${searchUrl}?${params}`)
            .then((r) => r.json())
            .then((data) => {
                (data.results || []).forEach((item) => {
                    const opt = new Option(item.text, item.id, true, true);
                    $('#user_ids').append(opt);
                });
                $('#user_ids').trigger('change');
            });
    }
})();
</script>
