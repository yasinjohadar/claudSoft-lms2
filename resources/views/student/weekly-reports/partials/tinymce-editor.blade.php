@if(!empty($canEdit))
<style>
    .weekly-report-editor-wrap .tox-tinymce {
        border-radius: 0.375rem;
    }
    .weekly-report-editor-wrap textarea#student-details-editor {
        min-height: 300px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
function initWeeklyReportTinyMCE() {
    const textarea = document.getElementById('student-details-editor');
    if (!textarea) {
        return;
    }

    if (typeof tinymce === 'undefined') {
        setTimeout(initWeeklyReportTinyMCE, 100);
        return;
    }

    const existing = tinymce.get('student-details-editor');
    if (existing) {
        existing.remove();
    }

    tinymce.init({
        selector: '#student-details-editor',
        height: 450,
        directionality: 'rtl',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
        promotion: false,
        branding: false,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount emoticons directionality',
        toolbar: 'undo redo | blocks | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image media table | code | fullscreen | help',
        menubar: 'file edit view insert format tools table help',
        menu: {
            file: { title: 'ملف', items: 'newdocument restoredraft | preview | print' },
            edit: { title: 'تحرير', items: 'undo redo | cut copy paste | selectall | searchreplace' },
            view: { title: 'عرض', items: 'code | visualaid visualchars visualblocks | preview fullscreen' },
            insert: { title: 'إدراج', items: 'image link media | charmap emoticons hr | pagebreak nonbreaking anchor | insertdatetime' },
            format: { title: 'تنسيق', items: 'bold italic underline strikethrough | formats blockformats fontformats fontsizes align | forecolor backcolor | removeformat' },
            tools: { title: 'أدوات', items: 'code wordcount' },
            table: { title: 'جدول', items: 'inserttable | cell row column | tableprops deletetable' },
            help: { title: 'تعليمات', items: 'help' }
        },
        content_style: 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; font-size: 14px; direction: rtl; }',
        resize: true,
        paste_data_images: true,
        relative_urls: false,
        remove_script_host: false,
        image_advtab: true,
        setup: function (editor) {
            editor.on('init', function () {
                const container = editor.getContainer();
                if (container) {
                    container.style.visibility = 'visible';
                }
            });
        },
    }).catch(function (error) {
        console.error('TinyMCE init failed:', error);
        textarea.style.display = 'block';
        textarea.style.visibility = 'visible';
        textarea.style.minHeight = '300px';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(initWeeklyReportTinyMCE, 150);
    });
} else {
    setTimeout(initWeeklyReportTinyMCE, 150);
}
</script>
@endif
