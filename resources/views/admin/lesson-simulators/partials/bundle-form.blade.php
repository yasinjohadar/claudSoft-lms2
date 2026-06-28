@php
    $selectedCourses = old('course_ids', isset($simulator) ? $simulator->courses->pluck('id')->all() : []);
    $bundleHtml = old('bundle_html', $bundle['html'] ?? '');
    $bundleCss = old('bundle_css', $bundle['css'] ?? '');
    $bundleJs = old('bundle_js', $bundle['js'] ?? '');
    $hasCustomAssets = trim($bundleCss) !== '' || trim($bundleJs) !== '';
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" id="simulator-bundle-form">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">العنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $simulator->title ?? '') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $simulator->slug ?? '') }}" placeholder="يُولَّد تلقائياً من العنوان" dir="ltr">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التصنيف</label>
                        <select name="simulator_category_id" class="form-select">
                            <option value="">— بدون تصنيف —</option>
                            @foreach($categoryOptions ?? [] as $id => $label)
                                <option value="{{ $id }}" @selected((string) old('simulator_category_id', $simulator->simulator_category_id ?? '') === (string) $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <a href="{{ route('admin.lesson-simulators.categories.index') }}" target="_blank">إدارة التصنيفات</a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select" required>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(old('status', $simulator->status ?? 'published') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $simulator->description ?? '') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ربط بالكورسات</label>
                        <select name="course_ids[]" class="form-select" multiple size="4">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(in_array($course->id, $selectedCourses))>{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>HTML</strong>
                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-update-preview">تحديث المعاينة</button>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border small mb-3">
                        <strong>الربط التلقائي:</strong> CSS/JS يُحمَّلان من
                        <a href="{{ route('admin.lesson-simulators.global-assets') }}" target="_blank">الملفات المركزية</a>
                        — الصق HTML فقط.
                        <ul class="mb-0 mt-2 ps-3">
                            <li>روابط shared: <code>../../shared/css/tokens.css</code></li>
                            <li>أو placeholders: <code>__SIMULATOR_KIT__</code> و <code>__GLOBAL_ASSETS__</code></li>
                            <li>بدون أي روابط → يُحقَن CSS/JS المركزي تلقائياً</li>
                        </ul>
                    </div>
                    <textarea name="bundle_html" id="bundle_html" class="form-control font-monospace" rows="22" dir="ltr" style="text-align:left;font-size:12px;" required>{{ $bundleHtml }}</textarea>

                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#custom-assets-panel">
                            تخصيص CSS/JS لهذه المحاكاة فقط (اختياري)
                        </button>
                        <div class="collapse mt-3 {{ $hasCustomAssets ? 'show' : '' }}" id="custom-assets-panel">
                            <p class="text-muted small">إذا ملأت الحقول أدناه، تُستخدم بدل الملفات المركزية لهذه المحاكاة.</p>
                            <label class="form-label">CSS مخصص</label>
                            <textarea name="bundle_css" id="bundle_css" class="form-control font-monospace mb-3" rows="8" dir="ltr" style="text-align:left;font-size:12px;">{{ $bundleCss }}</textarea>
                            <label class="form-label">JS مخصص</label>
                            <textarea name="bundle_js" id="bundle_js" class="form-control font-monospace" rows="8" dir="ltr" style="text-align:left;font-size:12px;">{{ $bundleJs }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4 flex-wrap">
                <button type="submit" class="btn btn-primary">حفظ</button>
                @if(isset($simulator))
                    <a href="{{ route('admin.lesson-simulators.preview', $simulator) }}" class="btn btn-outline-info" target="_blank">معاينة كاملة</a>
                @endif
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-3 sticky-top" style="top:80px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>معاينة حية</span>
                    <span class="text-muted small" id="preview-status"></span>
                </div>
                <div class="card-body p-0 position-relative">
                    <div id="preview-loading" class="position-absolute top-50 start-50 translate-middle text-muted d-none" style="z-index:2;">
                        <div class="spinner-border spinner-border-sm"></div>
                    </div>
                    <div id="preview-placeholder" class="d-flex flex-column align-items-center justify-content-center text-muted text-center px-4" style="min-height:780px;background:#111;">
                        <i class="fe fe-monitor mb-2 fs-24 opacity-50"></i>
                        <span>الصق HTML ثم اضغط «تحديث المعاينة»</span>
                        <span class="small opacity-75 mt-1">تُحدَّث تلقائياً عند الكتابة بعد إدخال المحتوى</span>
                    </div>
                    <iframe id="bundle-preview-frame" title="معاينة" sandbox="allow-scripts" class="d-none" style="width:100%;min-height:780px;border:0;background:#000;display:block;"></iframe>
                </div>
            </div>
        </div>
    </div>
</form>

@if(isset($simulator))
    <form action="{{ route('admin.lesson-simulators.destroy', $simulator) }}" method="POST" class="mt-2" onsubmit="return confirm('حذف هذه المحاكاة؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm">حذف المحاكاة</button>
    </form>
@endif

<script>
(function () {
    const previewUrl = @json(route('admin.lesson-simulators.preview-bundle'));
    const csrf = @json(csrf_token());
    const frame = document.getElementById('bundle-preview-frame');
    const htmlEl = document.getElementById('bundle_html');
    const cssEl = document.getElementById('bundle_css');
    const jsEl = document.getElementById('bundle_js');
    const btn = document.getElementById('btn-update-preview');
    const statusEl = document.getElementById('preview-status');
    const loadingEl = document.getElementById('preview-loading');
    const placeholderEl = document.getElementById('preview-placeholder');
    let previewTimer = null;

    function hasPreviewContent() {
        return htmlEl && htmlEl.value.trim() !== '';
    }

    function showPreviewPlaceholder() {
        if (placeholderEl) placeholderEl.classList.remove('d-none');
        if (frame) {
            frame.classList.add('d-none');
            frame.removeAttribute('srcdoc');
        }
        if (statusEl) statusEl.textContent = '';
    }

    function showPreviewFrame(html) {
        if (!frame) return;
        if (placeholderEl) placeholderEl.classList.add('d-none');
        frame.classList.remove('d-none');
        frame.srcdoc = html;
    }

    function isAdminLayoutHtml(html) {
        return /app-header|admin-portal|bundle-preview-frame|simulator-bundle-form/i.test(html || '');
    }

    function updatePreview(force) {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(function () {
            if (!htmlEl || !frame) return;

            if (!hasPreviewContent() && !force) {
                showPreviewPlaceholder();
                return;
            }

            loadingEl.classList.remove('d-none');
            statusEl.textContent = 'جاري التحديث...';

            fetch(previewUrl, {
                method: 'POST',
                redirect: 'manual',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    bundle_html: htmlEl.value,
                    bundle_css: cssEl ? cssEl.value : '',
                    bundle_js: jsEl ? jsEl.value : '',
                }),
            })
                .then(function (r) {
                    if (r.type === 'opaqueredirect' || (r.status >= 300 && r.status < 400)) {
                        throw new Error('redirect');
                    }
                    if (!r.ok) {
                        return r.json().catch(function () { return {}; }).then(function (data) {
                            throw new Error(data.message || 'preview failed');
                        });
                    }
                    return r.json();
                })
                .then(function (data) {
                    var html = (data && data.html) ? data.html : '';
                    if (!html || isAdminLayoutHtml(html)) {
                        throw new Error('invalid preview');
                    }
                    showPreviewFrame(html);
                    statusEl.textContent = 'محدّث';
                })
                .catch(function () {
                    showPreviewPlaceholder();
                    statusEl.textContent = 'فشل المعاينة';
                })
                .finally(function () {
                    loadingEl.classList.add('d-none');
                });
        }, force ? 0 : 500);
    }

    if (btn) {
        btn.addEventListener('click', function () {
            clearTimeout(previewTimer);
            updatePreview(true);
        });
    }

    [htmlEl, cssEl, jsEl].forEach(function (el) {
        if (el) el.addEventListener('input', function () { updatePreview(false); });
    });

    window.simulatorRefreshPreview = function () {
        clearTimeout(previewTimer);
        updatePreview(true);
    };

    document.addEventListener('simulator-ai-generated', function (e) {
        const data = e.detail || {};
        const titleEl = document.querySelector('#simulator-bundle-form [name="title"]');
        const descEl = document.querySelector('#simulator-bundle-form [name="description"]');
        const customPanel = document.getElementById('custom-assets-panel');

        if (titleEl && data.title) titleEl.value = data.title;
        if (descEl && data.description) descEl.value = data.description;
        if (htmlEl) htmlEl.value = data.html || '';
        if (cssEl) cssEl.value = data.css || '';
        if (jsEl) jsEl.value = data.js || '';
        if (customPanel && ((data.css && data.css.trim()) || (data.js && data.js.trim()))) {
            customPanel.classList.add('show');
        }
        window.simulatorRefreshPreview();
    });

    if (htmlEl && hasPreviewContent()) {
        updatePreview(false);
    } else {
        showPreviewPlaceholder();
    }
})();
</script>
