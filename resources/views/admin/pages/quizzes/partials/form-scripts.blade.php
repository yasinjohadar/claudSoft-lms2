<script>
(function () {
    const courseSelect = document.getElementById('course_id');
    const lessonSelect = document.getElementById('lesson_id');
    const currentLessonId = @json($currentLessonId ?? null);

    if (courseSelect && lessonSelect) {
        courseSelect.addEventListener('change', function () {
            const courseId = this.value;
            lessonSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            if (!courseId) {
                lessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
                return;
            }
            const routeUrl = '{{ route("quizzes.get-lessons", ["courseId" => ":courseId"]) }}'.replace(':courseId', courseId);
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
    }
})();
</script>
