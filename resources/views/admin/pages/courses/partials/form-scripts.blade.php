<script>
    (function () {
        function initCourseThumbnail(inputId, previewWrapId) {
            const input = document.getElementById(inputId);
            const wrap = document.getElementById(previewWrapId);
            if (!input || !wrap) return;

            const openPicker = function () {
                input.click();
            };

            wrap.addEventListener('click', openPicker);
            wrap.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openPicker();
                }
            });

            ['dragenter', 'dragover'].forEach(function (eventName) {
                wrap.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    wrap.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                wrap.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    wrap.classList.remove('is-dragover');
                });
            });

            wrap.addEventListener('drop', function (e) {
                const file = e.dataTransfer?.files?.[0];
                if (!file || !file.type.startsWith('image/')) return;

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            input.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (ev) {
                    const placeholder = wrap.querySelector('.admin-course-form-page__thumbnail-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }

                    let img = wrap.querySelector('.admin-course-form-page__thumbnail-preview');
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'admin-course-form-page__thumbnail-preview';
                        img.alt = 'معاينة';
                        wrap.appendChild(img);
                    }

                    img.src = ev.target.result;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }

        window.initCourseThumbnail = initCourseThumbnail;
    })();
</script>
