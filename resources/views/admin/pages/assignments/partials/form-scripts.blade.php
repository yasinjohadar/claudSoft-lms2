<script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.9/tinymce.min.js"></script>
<script>
(function () {
    tinymce.init({
        selector: '#description, #instructions',
        directionality: 'rtl',
        height: 300,
        menubar: false,
        plugins: [
            'advlist autolink lists link charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime table paste code help wordcount codesample'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table | codesample code | fullscreen',
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'C', value: 'c' },
            { text: 'C++', value: 'cpp' },
            { text: 'SQL', value: 'sql' }
        ],
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial; font-size: 14px; direction: rtl; }'
    });

    const courseSelect = document.getElementById('course_id');
    const lessonSelect = document.getElementById('lesson_id');
    const groupSelect = document.getElementById('target_group_id');
    const currentLessonId = @json($currentLessonId ?? null);
    const currentGroupId = @json($currentGroupId ?? null);

    function loadGroups(courseId, selectedGroupId) {
        if (!groupSelect) {
            return;
        }

        if (!courseId) {
            groupSelect.innerHTML = '<option value="">كل طلاب الكورس</option>';
            return;
        }

        const groupsUrl = '{{ route("assignments.get-groups", ["courseId" => ":courseId"]) }}'.replace(':courseId', courseId);
        groupSelect.innerHTML = '<option value="">جاري التحميل...</option>';

        fetch(groupsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) {
                return response.ok ? response.json() : Promise.reject();
            })
            .then(function (groups) {
                groupSelect.innerHTML = '<option value="">كل طلاب الكورس</option>';
                (groups || []).forEach(function (group) {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    if (selectedGroupId && String(group.id) === String(selectedGroupId)) {
                        option.selected = true;
                    }
                    groupSelect.appendChild(option);
                });
            })
            .catch(function () {
                groupSelect.innerHTML = '<option value="">كل طلاب الكورس</option>';
            });
    }

    if (courseSelect && lessonSelect) {
        courseSelect.addEventListener('change', function () {
            const courseId = this.value;
            loadGroups(courseId, null);
            lessonSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            if (!courseId) {
                lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
                return;
            }
            const routeUrl = '{{ route("assignments.get-lessons", ["courseId" => ":courseId"]) }}'.replace(':courseId', courseId);
            fetch(routeUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(data => {
                lessonSelect.innerHTML = '<option value="">لا يوجد دروس مرتبطة</option>';
                (data || []).forEach(function (lesson) {
                    const option = document.createElement('option');
                    option.value = lesson.id;
                    option.textContent = lesson.title;
                    if (currentLessonId && String(lesson.id) === String(currentLessonId)) {
                        option.selected = true;
                    }
                    lessonSelect.appendChild(option);
                });
            })
            .catch(function () {
                lessonSelect.innerHTML = '<option value="">خطأ في تحميل الدروس</option>';
            });
        });

        if (courseSelect.value) {
            loadGroups(courseSelect.value, currentGroupId);
        }
    } else if (courseSelect && groupSelect) {
        if (courseSelect.value) {
            loadGroups(courseSelect.value, currentGroupId);
        }
        courseSelect.addEventListener('change', function () {
            loadGroups(this.value, null);
        });
    }

    const submissionType = document.getElementById('submission_type');
    if (submissionType) {
        submissionType.addEventListener('change', function () {
            const type = this.value;
            const linkSettings = document.getElementById('link_settings');
            const fileSettingsCount = document.getElementById('file_settings_count');
            const fileSettingsSize = document.getElementById('file_settings_size');
            const linkInput = linkSettings ? linkSettings.querySelector('input') : null;
            const fileCountInput = fileSettingsCount ? fileSettingsCount.querySelector('input') : null;
            const fileSizeInput = fileSettingsSize ? fileSettingsSize.querySelector('input') : null;

            if (type === 'link') {
                if (linkSettings) linkSettings.style.display = 'block';
                if (fileSettingsCount) fileSettingsCount.style.display = 'none';
                if (fileSettingsSize) fileSettingsSize.style.display = 'none';
                if (linkInput) linkInput.disabled = false;
                if (fileCountInput) fileCountInput.disabled = true;
                if (fileSizeInput) fileSizeInput.disabled = true;
            } else if (type === 'file') {
                if (linkSettings) linkSettings.style.display = 'none';
                if (fileSettingsCount) fileSettingsCount.style.display = 'block';
                if (fileSettingsSize) fileSettingsSize.style.display = 'block';
                if (linkInput) linkInput.disabled = true;
                if (fileCountInput) fileCountInput.disabled = false;
                if (fileSizeInput) fileSizeInput.disabled = false;
            } else {
                if (linkSettings) linkSettings.style.display = 'block';
                if (fileSettingsCount) fileSettingsCount.style.display = 'block';
                if (fileSettingsSize) fileSettingsSize.style.display = 'block';
                if (linkInput) linkInput.disabled = false;
                if (fileCountInput) fileCountInput.disabled = false;
                if (fileSizeInput) fileSizeInput.disabled = false;
            }
        });
        submissionType.dispatchEvent(new Event('change'));
    }

    const allowResubmission = document.getElementById('allow_resubmission');
    if (allowResubmission) {
        allowResubmission.addEventListener('change', function () {
            const show = this.checked;
            const resubmissionSettings = document.getElementById('resubmission_settings');
            const resubmissionGrading = document.getElementById('resubmission_grading');
            if (resubmissionSettings) resubmissionSettings.style.display = show ? 'block' : 'none';
            if (resubmissionGrading) resubmissionGrading.style.display = show ? 'block' : 'none';
        });
        if (allowResubmission.checked) {
            const resubmissionSettings = document.getElementById('resubmission_settings');
            const resubmissionGrading = document.getElementById('resubmission_grading');
            if (resubmissionSettings) resubmissionSettings.style.display = 'block';
            if (resubmissionGrading) resubmissionGrading.style.display = 'block';
        }
    }

    const form = document.querySelector('form[enctype="multipart/form-data"]');
    if (form) {
        form.addEventListener('submit', function () {
            if (window.tinymce) tinymce.triggerSave();
            ['link_settings', 'file_settings_count', 'file_settings_size'].forEach(function (id) {
                const div = document.getElementById(id);
                const input = div ? div.querySelector('input') : null;
                if (input) input.disabled = false;
            });
        });
    }
})();
</script>
