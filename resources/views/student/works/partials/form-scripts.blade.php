@php
    $initialTags = $initialTags ?? [];
@endphp

<script>
(function () {
    var tags = @json($initialTags);
    var tagInput = document.getElementById('tag-input');
    var tagsContainer = document.getElementById('tags-container');
    var tagsHiddenContainer = document.getElementById('tags-hidden-container');
    var imageInput = document.getElementById('image-input');
    var imagePreview = document.getElementById('image-preview');
    var currentImage = document.getElementById('current-image');
    var form = document.getElementById('student-work-form');

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function updateTags() {
        if (!tagsContainer || !tagsHiddenContainer) return;

        tagsContainer.innerHTML = tags.map(function (tag, index) {
            return '<span class="student-work-card__tag student-work-form__tag">' +
                '#' + escapeHtml(tag) +
                '<button type="button" class="student-work-form__tag-remove" data-index="' + index + '" aria-label="إزالة">' +
                '<i class="fe fe-x"></i></button></span>';
        }).join('');

        tagsHiddenContainer.innerHTML = '';
        tags.forEach(function (tag) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'tags[]';
            input.value = tag;
            tagsHiddenContainer.appendChild(input);
        });

        tagsContainer.querySelectorAll('.student-work-form__tag-remove').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = parseInt(btn.dataset.index, 10);
                tags.splice(idx, 1);
                updateTags();
            });
        });
    }

    if (tagInput) {
        tagInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var tag = this.value.trim();
                if (tag && tags.indexOf(tag) === -1) {
                    tags.push(tag);
                    updateTags();
                    this.value = '';
                }
            }
        });
    }

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                imagePreview.querySelector('img').src = ev.target.result;
                imagePreview.classList.remove('d-none');
                if (currentImage) currentImage.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeImage = function () {
        if (imageInput) imageInput.value = '';
        if (imagePreview) {
            imagePreview.classList.add('d-none');
            var img = imagePreview.querySelector('img');
            if (img) img.src = '';
        }
        if (currentImage) currentImage.classList.remove('d-none');
    };

    window.removeCurrentImage = function () {
        if (currentImage) currentImage.classList.add('d-none');
    };

    if (form) {
        form.addEventListener('submit', function (e) {
            var title = form.querySelector('input[name="title"]');
            var category = form.querySelector('select[name="category"]');
            if (!title || !title.value.trim() || !category || !category.value) {
                e.preventDefault();
                alert('الرجاء ملء جميع الحقول المطلوبة');
            }
        });
    }

    updateTags();
})();
</script>
