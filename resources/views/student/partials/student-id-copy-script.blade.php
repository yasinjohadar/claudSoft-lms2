<script>
(function () {
    'use strict';

    function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        textarea.remove();

        return Promise.resolve();
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-copy-student-id]');
        if (!button) {
            return;
        }

        const studentId = button.dataset.copyStudentId;
        if (!studentId) {
            return;
        }

        copyText(studentId).then(function () {
            const originalHtml = button.innerHTML;
            const originalTitle = button.title;
            button.innerHTML = '<i class="fe fe-check text-success"></i>';
            button.title = 'تم نسخ رقم الطالب';

            window.setTimeout(function () {
                button.innerHTML = originalHtml;
                button.title = originalTitle;
            }, 1500);
        });
    });
})();
</script>
