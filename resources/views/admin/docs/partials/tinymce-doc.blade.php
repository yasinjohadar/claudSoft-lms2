@php
    $tinymceSelector = $tinymceSelector ?? '#doc_content';
@endphp
<!-- Prism.js for code samples in editor preview -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
function initDocTinyMCE() {
    if (typeof tinymce === 'undefined') {
        setTimeout(initDocTinyMCE, 100);
        return;
    }
    tinymce.init({
        selector: @json($tinymceSelector),
        height: 560,
        directionality: 'rtl',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
        promotion: false,
        branding: false,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code codesample fullscreen insertdatetime media table help wordcount emoticons directionality',
        toolbar: 'undo redo | blocks | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image media table | codesample code | fullscreen | help',
        menubar: 'file edit view insert format tools table help',
        content_style: 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; font-size: 14px; direction: rtl; }',
        resize: true,
        relative_urls: false,
        remove_script_host: false,
        image_advtab: true,
        automatic_uploads: true,
        images_upload_url: '/upload',
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Python', value: 'python' },
            { text: 'SQL', value: 'sql' },
            { text: 'JSON', value: 'json' },
            { text: 'Bash/Shell', value: 'bash' }
        ],
        codesample_global_prismjs: true
    }).catch(function (err) {
        console.error('TinyMCE:', err);
    });
}
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(initDocTinyMCE, 200);
});
</script>
