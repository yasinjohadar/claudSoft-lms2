<script>
    function toggleCourseVisibility(courseId) {
        const item = document.querySelector('.admin-group-course-item[data-course-id="' + courseId + '"]');
        if (!item) {
            return;
        }

        const checkbox = document.getElementById('course_' + courseId);
        const visibilityWrap = item.querySelector('.admin-group-course-item__visibility');

        if (checkbox.checked) {
            item.classList.add('is-selected');
            if (visibilityWrap) {
                visibilityWrap.style.display = '';
                const visibilityCheckbox = visibilityWrap.querySelector('input[type="checkbox"]');
                if (visibilityCheckbox) {
                    visibilityCheckbox.checked = true;
                }
            }
        } else {
            item.classList.remove('is-selected');
            if (visibilityWrap) {
                visibilityWrap.style.display = 'none';
                const visibilityCheckbox = visibilityWrap.querySelector('input[type="checkbox"]');
                if (visibilityCheckbox) {
                    visibilityCheckbox.checked = false;
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('groupCourseSearch');
        const picker = document.getElementById('groupCoursePicker');

        if (searchInput && picker) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                picker.querySelectorAll('.admin-group-course-item').forEach(function (item) {
                    const title = item.querySelector('.admin-group-course-item__title')?.textContent.toLowerCase() || '';
                    const code = item.querySelector('.admin-group-course-item__code')?.textContent.toLowerCase() || '';
                    item.style.display = !query || title.includes(query) || code.includes(query) ? '' : 'none';
                });
            });
        }

        const clearBtn = document.getElementById('clearVisibilityRequirements');
        const selectElement = document.getElementById('visibility_required_groups');

        if (clearBtn && selectElement) {
            clearBtn.addEventListener('click', function () {
                Array.from(selectElement.options).forEach(function (option) {
                    option.selected = false;
                });
            });
        }

        const isCampToggle = document.getElementById('is_camp');
        const campFields = document.getElementById('camp-group-fields');
        const campPrice = document.getElementById('camp_price');

        function syncCampFieldsVisibility() {
            if (!isCampToggle || !campFields) {
                return;
            }
            const enabled = isCampToggle.checked;
            campFields.classList.toggle('d-none', !enabled);
            if (campPrice) {
                campPrice.required = enabled;
            }
        }

        if (isCampToggle) {
            isCampToggle.addEventListener('change', syncCampFieldsVisibility);
            syncCampFieldsVisibility();
        }
    });
</script>
