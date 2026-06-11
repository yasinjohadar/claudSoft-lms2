@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            const form = document.getElementById('gift-form');
            if (!form) return;

            const searchUrl = @json(route('admin.gifts.search-students'));
            const previewUrl = @json(route('admin.gifts.preview-recipients'));
            const giftId = @json($gift->id ?? null);
            const oldUserId = @json(old('user_id', ($gift->target_payload ?? [])['user_id'] ?? null));
            const oldUserIds = @json(old('user_ids', ($gift->target_payload ?? [])['user_ids'] ?? []));

            const getTargetType = () => form.querySelector('input[name="target_type"]:checked')?.value || 'single';

            const disableInactiveFields = (activeType) => {
                const fieldMap = {
                    single: ['user_id'],
                    multiple: ['user_ids'],
                    group: ['group_id_only'],
                    course: ['course_id_only'],
                    course_group: ['course_id_grouped', 'group_id_course', 'course_id_hidden'],
                };

                form.querySelectorAll('.target-panel input, .target-panel select, .target-panel textarea').forEach((el) => {
                    el.disabled = true;
                });

                (fieldMap[activeType] || []).forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.disabled = false;
                    }
                });

                if (activeType === 'group') {
                    document.getElementById('group_id_only')?.removeAttribute('disabled');
                }
                if (activeType === 'course') {
                    document.getElementById('course_id_only')?.removeAttribute('disabled');
                }
            };

            const togglePanels = () => {
                const activeType = getTargetType();
                form.querySelectorAll('.target-panel').forEach((panel) => {
                    panel.classList.toggle('d-none', panel.dataset.target !== activeType);
                });
                disableInactiveFields(activeType);
            };

            const getContentMode = () => form.querySelector('input[name="content_mode"]:checked')?.value;

            const toggleContentPanels = () => {
                const mode = getContentMode();
                form.querySelectorAll('.content-panel').forEach((panel) => {
                    panel.classList.toggle('d-none', panel.dataset.content !== mode);
                });
            };

            form.querySelectorAll('.target-type-radio').forEach((radio) => {
                radio.addEventListener('change', () => {
                    togglePanels();
                    if (window.giftInitActiveStudentSelect) {
                        window.giftInitActiveStudentSelect();
                    }
                });
            });

            form.querySelectorAll('.content-mode-radio').forEach((radio) => {
                radio.addEventListener('change', toggleContentPanels);
            });

            const courseSelect = document.getElementById('course_id_grouped');
            const groupSelect = document.getElementById('group_id_course');
            const courseHidden = document.getElementById('course_id_hidden');
            const groupEmptyHint = document.getElementById('group-empty-hint');

            const applyGroupFilter = () => {
                if (!courseSelect || !groupSelect) return;
                const selectedCourseId = courseSelect.value;
                if (courseHidden) courseHidden.value = selectedCourseId;
                let hasMatching = false;

                Array.from(groupSelect.options).forEach((option, index) => {
                    if (index === 0) return;
                    const courseIds = (option.dataset.courseIds || '').split(',').map((id) => id.trim()).filter(Boolean);
                    const isMatch = selectedCourseId && courseIds.includes(selectedCourseId);
                    option.disabled = !isMatch;
                    option.hidden = !isMatch;
                    if (isMatch) hasMatching = true;
                    else if (option.selected) option.selected = false;
                });

                if (!selectedCourseId) {
                    groupSelect.value = '';
                    groupEmptyHint?.classList.add('d-none');
                } else if (!hasMatching) {
                    groupSelect.value = '';
                    groupEmptyHint?.classList.remove('d-none');
                } else {
                    groupEmptyHint?.classList.add('d-none');
                }
            };

            courseSelect?.addEventListener('change', applyGroupFilter);

            const buildPreviewParams = () => {
                const params = new URLSearchParams();
                const targetType = getTargetType();
                params.set('target_type', targetType || '');
                if (giftId) params.set('gift_id', giftId);

                if (targetType === 'single') {
                    params.set('user_id', document.getElementById('user_id')?.value || '');
                }
                if (targetType === 'multiple' && window.jQuery) {
                    (window.jQuery('#user_ids').val() || []).forEach((id) => params.append('user_ids[]', id));
                }
                if (targetType === 'group') {
                    params.set('group_id', document.getElementById('group_id_only')?.value || '');
                }
                if (targetType === 'course') {
                    params.set('course_id', document.getElementById('course_id_only')?.value || '');
                }
                if (targetType === 'course_group') {
                    params.set('course_id', courseSelect?.value || '');
                    params.set('group_id', groupSelect?.value || '');
                }

                return params;
            };

            const previewBox = document.getElementById('preview-recipients-box');
            const previewText = document.getElementById('preview-recipients-text');

            document.getElementById('preview-recipients-btn')?.addEventListener('click', async () => {
                try {
                    const response = await fetch(previewUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || '',
                        },
                        body: buildPreviewParams().toString(),
                    });
                    const data = await response.json();
                    previewBox?.classList.remove('d-none');

                    if (!response.ok) {
                        previewText.textContent = 'يرجى إكمال الحقول المطلوبة أولاً.';
                        return;
                    }

                    previewText.textContent = `إجمالي المستهدفين: ${data.total} — يمتلكونها مسبقاً: ${data.already_have} — سيتم المنح لـ: ${data.will_grant}`;
                } catch (e) {
                    previewBox?.classList.remove('d-none');
                    previewText.textContent = 'تعذرت معاينة المستلمين.';
                }
            });

            jQuery(function ($) {
                const dropdownParent = $('#gift-form').closest('.card-body');
                const select2Ajax = {
                    url: searchUrl,
                    dataType: 'json',
                    delay: 300,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    data: (params) => ({ search: params.term || '' }),
                    processResults: (data) => data,
                    cache: true,
                };

                const destroySelect2 = (selector) => {
                    const $el = $(selector);
                    if ($el.length && $el.data('select2')) {
                        $el.select2('destroy');
                    }
                };

                const initSingleSelect = () => {
                    destroySelect2('#user_ids');
                    destroySelect2('#user_id');

                    $('#user_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dir: 'rtl',
                        placeholder: 'ابحث عن طالب…',
                        allowClear: true,
                        minimumInputLength: 2,
                        dropdownParent,
                        ajax: select2Ajax,
                    });
                };

                const initMultipleSelect = () => {
                    destroySelect2('#user_id');
                    destroySelect2('#user_ids');

                    $('#user_ids').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dir: 'rtl',
                        placeholder: 'ابحث واختر عدة طلاب…',
                        allowClear: true,
                        minimumInputLength: 2,
                        dropdownParent,
                        ajax: select2Ajax,
                    });
                };

                const initActiveStudentSelect = () => {
                    const activeType = getTargetType();
                    if (activeType === 'single') {
                        initSingleSelect();
                    } else if (activeType === 'multiple') {
                        initMultipleSelect();
                    } else {
                        destroySelect2('#user_id');
                        destroySelect2('#user_ids');
                    }
                };

                window.giftInitActiveStudentSelect = initActiveStudentSelect;

                const loadOldValues = () => {
                    const activeType = getTargetType();

                    if (activeType === 'single' && oldUserId) {
                        fetch(searchUrl + '?ids=' + oldUserId, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        })
                            .then((r) => r.json())
                            .then((data) => {
                                (data.results || []).forEach((item) => {
                                    const option = new Option(item.text, item.id, true, true);
                                    $('#user_id').append(option).trigger('change');
                                });
                            });
                    }

                    if (activeType === 'multiple' && oldUserIds.length) {
                        fetch(searchUrl + '?ids=' + oldUserIds.join(','), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        })
                            .then((r) => r.json())
                            .then((data) => {
                                (data.results || []).forEach((item) => {
                                    const option = new Option(item.text, item.id, true, true);
                                    $('#user_ids').append(option).trigger('change');
                                });
                            });
                    }
                };

                togglePanels();
                applyGroupFilter();
                toggleContentPanels();
                initActiveStudentSelect();
                loadOldValues();
            });
        })();
    </script>
@endpush
