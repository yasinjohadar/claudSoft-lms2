<script>
function appendImportDefaults(formData) {
    const course = document.getElementById('default_course_id');
    const lang = document.getElementById('default_programming_language_id');
    if (course && course.value) {
        formData.append('default_course_id', course.value);
    }
    if (lang && lang.value) {
        formData.append('default_programming_language_id', lang.value);
    }
}

function formatImportDefaultCell(value, fromDefault, emptyLabel) {
    if (!value) {
        return emptyLabel || '<span class="text-danger">مطلوب</span>';
    }
    const badge = fromDefault
        ? ' <span class="badge bg-primary-subtle text-primary" style="font-size:0.65rem">من الواجهة</span>'
        : '';
    return value + badge;
}
</script>
