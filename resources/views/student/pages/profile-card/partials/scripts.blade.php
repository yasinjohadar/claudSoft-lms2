<script>
document.addEventListener('DOMContentLoaded', function () {
    var platforms = @json($socialPlatforms ?? config('profile-card.social_platforms', []));
    var container = document.getElementById('socialLinksContainer');
    var addBtn = document.getElementById('addSocialLink');
    var linkIndex = {{ count(old('social_links', $card->social_links ?? [])) }};

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function platformOptions(selected) {
        return Object.keys(platforms).map(function (key) {
            var label = escapeHtml(platforms[key].default_label || key);
            var sel = key === selected ? ' selected' : '';
            return '<option value="' + escapeHtml(key) + '"' + sel + '>' + label + '</option>';
        }).join('');
    }

    function buildRow(index, link) {
        link = link || {};
        var platform = link.platform || 'custom';
        var preset = platforms[platform] || platforms.custom || {};
        return '<div class="profile-card-social-row social-link-row" data-index="' + index + '">' +
            '<div class="row g-2 align-items-end">' +
            '<div class="col-md-4"><label class="form-label fs-12">المنصة</label>' +
            '<select name="social_links[' + index + '][platform]" class="form-select form-select-sm js-platform-select">' +
            platformOptions(platform) + '</select></div>' +
            '<div class="col-md-8"><label class="form-label fs-12">الرابط</label>' +
            '<input type="url" name="social_links[' + index + '][url]" class="form-control form-control-sm" value="' + escapeHtml(link.url || '') + '" placeholder="' + escapeHtml(preset.url_hint || 'https://') + '"></div>' +
            '<div class="col-md-4"><label class="form-label fs-12">التسمية</label>' +
            '<input type="text" name="social_links[' + index + '][label]" class="form-control form-control-sm js-link-label" value="' + escapeHtml(link.label || preset.default_label || '') + '"></div>' +
            '<div class="col-md-4"><label class="form-label fs-12">الأيقونة</label>' +
            '<input type="text" name="social_links[' + index + '][icon]" class="form-control form-control-sm js-link-icon" dir="ltr" value="' + escapeHtml(link.icon || preset.default_icon || 'fas fa-link') + '"></div>' +
            '<div class="col-md-2"><label class="form-label fs-12">ترتيب</label>' +
            '<input type="number" name="social_links[' + index + '][sort_order]" class="form-control form-control-sm" min="0" value="' + (link.sort_order != null ? link.sort_order : index) + '"></div>' +
            '<div class="col-md-2"><input type="hidden" name="social_links[' + index + '][enabled]" value="0">' +
            '<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="social_links[' + index + '][enabled]" value="1" ' + ((link.enabled !== false) ? 'checked' : '') + '><label class="form-check-label fs-12">مفعّل</label></div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill w-100 js-remove-social"><i class="fe fe-trash-2 me-1"></i>حذف</button></div>' +
            '</div></div>';
    }

    function bindRow(row) {
        if (!row) return;
        var platformSelect = row.querySelector('.js-platform-select');
        if (platformSelect) {
            platformSelect.addEventListener('change', function () {
                var preset = platforms[this.value] || platforms.custom || {};
                var labelInput = row.querySelector('.js-link-label');
                var iconInput = row.querySelector('.js-link-icon');
                if (labelInput && !labelInput.dataset.touched) labelInput.value = preset.default_label || '';
                if (iconInput && !iconInput.dataset.touched) iconInput.value = preset.default_icon || 'fas fa-link';
            });
        }
        row.querySelectorAll('.js-link-label, .js-link-icon').forEach(function (input) {
            input.addEventListener('input', function () { this.dataset.touched = '1'; });
        });
        var removeBtn = row.querySelector('.js-remove-social');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () { row.remove(); });
        }
    }

    document.querySelectorAll('.social-link-row').forEach(bindRow);

    if (addBtn && container) {
        addBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var msg = document.getElementById('noSocialLinksMsg');
            if (msg) msg.remove();
            container.insertAdjacentHTML('beforeend', buildRow(linkIndex, {}));
            bindRow(container.lastElementChild);
            linkIndex++;
        });
    }

    var copyBtn = document.getElementById('copyPublicUrlBtn');
    var urlInput = document.getElementById('publicUrlInput');
    if (copyBtn && urlInput) {
        copyBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(urlInput.value).then(function () {
                var original = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fe fe-check"></i>';
                setTimeout(function () { copyBtn.innerHTML = original; }, 1200);
            });
        });
    }

    var regenBtn = document.getElementById('regenerateQrBtn');
    if (regenBtn) {
        regenBtn.addEventListener('click', function () {
            regenBtn.disabled = true;
            fetch(@json(route('student.profile-card.regenerate-qr')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success && data.qr_url) {
                        var img = document.getElementById('qrPreviewImg');
                        if (img) img.src = data.qr_url + '?t=' + Date.now();
                    }
                })
                .finally(function () { regenBtn.disabled = false; });
        });
    }
});
</script>
