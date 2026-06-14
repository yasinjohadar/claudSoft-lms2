<script>
function initImportDropzone(inputId, dropzoneId, filenameId) {
    const input = document.getElementById(inputId);
    const dropzone = document.getElementById(dropzoneId);
    const filenameEl = document.getElementById(filenameId);

    if (!input || !dropzone) return;

    const defaultHint = filenameEl ? filenameEl.dataset.defaultHint || filenameEl.textContent : '';

    function setFilename(name) {
        if (!filenameEl) return;
        if (name) {
            filenameEl.innerHTML = '<span class="qb-import-dropzone__filename"><i class="fe fe-file me-1"></i>' + name + '</span>';
        } else {
            filenameEl.textContent = defaultHint;
        }
    }

    input.addEventListener('change', function() {
        setFilename(input.files[0] ? input.files[0].name : '');
    });

    ['dragenter', 'dragover'].forEach(function(evt) {
        dropzone.addEventListener(evt, function(e) {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function(evt) {
        dropzone.addEventListener(evt, function(e) {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', function(e) {
        const files = e.dataTransfer.files;
        if (!files.length) return;
        input.files = files;
        setFilename(files[0].name);
    });
}
</script>
