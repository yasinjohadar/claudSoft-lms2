@php
    $modalMode = $modalMode ?? 'course';
    $course = $course ?? null;
    $allCourses = $allCourses ?? collect();
@endphp

<style>
    #attachDocumentationModal .modal-dialog {
        max-height: calc(100vh - 2rem);
        margin: 1rem auto;
    }

    #attachDocumentationModal .modal-content {
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #attachDocumentationModal .modal-header,
    #attachDocumentationModal .modal-footer {
        flex-shrink: 0;
    }

    #attachDocumentationModal .modal-body {
        overflow-y: auto !important;
        overflow-x: hidden;
        flex: 1 1 auto;
        min-height: 0;
        max-height: none;
    }

    #attachDocumentationModal .doc-link-step {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    #attachDocumentationModal .doc-link-step__title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.75rem;
    }

    #attachDocumentationModal #doc-pages-list {
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    #attachDocumentationModal .doc-advanced-toggle {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        user-select: none;
    }

    #attachDocumentationModal .doc-advanced-toggle:hover {
        color: #334155;
    }

    #attachDocumentationModal .doc-fixed-page-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }
</style>

<div class="modal fade" id="attachDocumentationModal" tabindex="-1" aria-labelledby="attachDocumentationModalLabel" aria-hidden="true"
     data-modal-mode="{{ $modalMode }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachDocumentationModalLabel">
                    <i class="fe fe-book me-2"></i>
                    <span id="doc-modal-title-text">{{ $modalMode === 'docs' ? 'ربط التوثيق بكورس' : 'ربط صفحات توثيق' }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <form method="POST"
                  action="{{ $modalMode === 'course' && $course ? route('courses.documentation-links.store', $course->id) : '#' }}"
                  id="attach-documentation-form">
                @csrf
                <div class="modal-body">
                    <div id="doc-form-errors" class="alert alert-danger d-none" role="alert"></div>

                    {{-- Docs mode: fixed page --}}
                    <div id="doc-mode-docs-page" class="doc-link-step {{ $modalMode === 'docs' ? '' : 'd-none' }}">
                        <div class="doc-link-step__title">1 — صفحة التوثيق</div>
                        <div class="doc-fixed-page-card">
                            <strong id="doc-fixed-page-title">—</strong>
                            <div class="text-muted fs-12 mt-1">
                                <span id="doc-fixed-page-category"></span>
                                <code id="doc-fixed-page-slug" class="fs-11 ms-1"></code>
                            </div>
                        </div>
                    </div>

                    {{-- Docs mode: course selection --}}
                    <div id="doc-mode-docs-course" class="doc-link-step {{ $modalMode === 'docs' ? '' : 'd-none' }}">
                        <div class="doc-link-step__title">2 — اختيار الكورس</div>
                        <label class="form-label">الكورس <span class="text-danger">*</span></label>
                        <select name="course_id" id="doc-primary-course-select" class="form-select form-select-sm">
                            <option value="">اختر الكورس...</option>
                            @foreach($allCourses as $courseOption)
                                <option value="{{ $courseOption->id }}">{{ $courseOption->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Course mode: category filter --}}
                    <div id="doc-mode-course-filter" class="doc-link-step {{ $modalMode === 'course' ? '' : 'd-none' }}">
                        <div class="doc-link-step__title">1 — فلترة التصنيف</div>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fs-12 mb-1">نوع التصنيف</label>
                                <select id="doc-kind-filter" class="form-select form-select-sm">
                                    <option value="">كل الأنواع</option>
                                    <option value="section">قسم</option>
                                    <option value="technology">تقنية</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fs-12 mb-1">التصنيف</label>
                                <select id="doc-category-filter" class="form-select form-select-sm">
                                    <option value="">اختر التصنيف...</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Course mode: page selection --}}
                    <div id="doc-mode-course-pages" class="doc-link-step {{ $modalMode === 'course' ? '' : 'd-none' }}">
                        <div class="doc-link-step__title">2 — اختيار صفحة التوثيق</div>
                        <div class="mb-2">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="doc-search-input"
                                       placeholder="بحث داخل التصنيف (اختياري)..." disabled>
                                <button type="button" class="btn btn-primary" id="doc-search-btn" disabled>بحث</button>
                            </div>
                        </div>
                        <label class="form-label mb-1">الصفحات <span class="text-danger">*</span></label>
                        <div id="doc-pages-list" class="border rounded p-2 bg-white">
                            <p class="text-muted mb-0 fs-12 text-center py-3">
                                اختر التصنيف أو النوع أولاً لعرض الصفحات
                            </p>
                        </div>
                    </div>

                    {{-- Shared: placement --}}
                    <div class="doc-link-step mb-0">
                        <div class="doc-link-step__title" id="doc-placement-step-title">
                            {{ $modalMode === 'docs' ? '3 — نوع الربط' : '3 — نوع الربط' }}
                        </div>
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="placement" id="placement_reference" value="reference" checked>
                                <label class="form-check-label" for="placement_reference">مرجع عام (لوحة التوثيقات)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="placement" id="placement_curriculum" value="curriculum">
                                <label class="form-check-label" for="placement_curriculum">إضافة للمنهج (عنصر في القسم)</label>
                            </div>
                        </div>

                        <div class="mb-0" id="doc-section-wrap" style="display: none;">
                            <label class="form-label">القسم <span class="text-danger">*</span></label>
                            <select name="section_id" id="doc-section-select" class="form-select form-select-sm">
                                <option value="">اختر القسم...</option>
                                @if($modalMode === 'course' && $course)
                                    @foreach($course->sections()->orderBy('order_index')->get() as $section)
                                        <option value="{{ $section->id }}">{{ $section->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="doc-advanced-toggle mb-2" data-bs-toggle="collapse" data-bs-target="#docAdvancedOptions" aria-expanded="false">
                            <i class="fe fe-chevron-down me-1"></i> خيارات متقدمة (كورسات / دروس)
                        </div>
                        <div class="collapse" id="docAdvancedOptions">
                            <div class="mb-3" id="doc-additional-courses-wrap">
                                <label class="form-label fs-12">كورسات إضافية (مرجع فقط)</label>
                                <select name="additional_course_ids[]" id="doc-additional-courses" class="form-select form-select-sm" multiple size="3">
                                    @foreach($allCourses as $otherCourse)
                                        @if($modalMode !== 'course' || !$course || $otherCourse->id != $course->id)
                                            <option value="{{ $otherCourse->id }}">{{ $otherCourse->title }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="text-muted">Ctrl+Click لتحديد عدة كورسات</small>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fs-12">دروس محددة (مرجع على الدرس)</label>
                                <select name="lesson_module_ids[]" id="doc-lesson-modules" class="form-select form-select-sm" multiple size="3">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="doc-submit-btn">
                        <i class="fe fe-link me-1"></i>ربط
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('attachDocumentationModal');
    if (!modal) return;

    var modalMode = modal.getAttribute('data-modal-mode') || 'course';
    var config = {
        mode: modalMode,
        @if($modalMode === 'course' && $course)
        categoriesUrl: @json(route('courses.documentation-links.categories', $course->id)),
        searchUrl: @json(route('courses.documentation-links.search', $course->id)),
        lessonModulesUrl: @json(route('courses.documentation-links.lesson-modules', $course->id)),
        sectionsUrl: @json(route('courses.documentation-links.sections', $course->id)),
        storeUrl: @json(route('courses.documentation-links.store', $course->id)),
        redirectUrl: @json(route('courses.show', $course->id)),
        @else
        storeUrlTemplate: @json(route('admin.docs.pages.documentation-links.store', ['documentation_page' => '__PAGE_ID__'])),
        sectionsUrlTemplate: @json(route('courses.documentation-links.sections', ['course' => '__COURSE_ID__'])),
        lessonModulesUrlTemplate: @json(route('courses.documentation-links.lesson-modules', ['course' => '__COURSE_ID__'])),
        redirectUrl: @json(route('admin.docs.pages.index')),
        @endif
    };

    var currentPageId = null;
    var kindFilter = document.getElementById('doc-kind-filter');
    var categoryFilter = document.getElementById('doc-category-filter');
    var pagesList = document.getElementById('doc-pages-list');
    var searchInput = document.getElementById('doc-search-input');
    var searchBtn = document.getElementById('doc-search-btn');
    var sectionWrap = document.getElementById('doc-section-wrap');
    var sectionSelect = document.getElementById('doc-section-select');
    var placementReference = document.getElementById('placement_reference');
    var placementCurriculum = document.getElementById('placement_curriculum');
    var lessonSelect = document.getElementById('doc-lesson-modules');
    var attachForm = document.getElementById('attach-documentation-form');
    var submitBtn = document.getElementById('doc-submit-btn');
    var formErrors = document.getElementById('doc-form-errors');
    var primaryCourseSelect = document.getElementById('doc-primary-course-select');
    var additionalCoursesSelect = document.getElementById('doc-additional-courses');
    var selectedPageIds = {};

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function showFormErrors(messages) {
        if (!formErrors) return;
        var list = Array.isArray(messages) ? messages : [messages];
        formErrors.innerHTML = list.map(function (m) { return '<div>' + escapeHtml(m) + '</div>'; }).join('');
        formErrors.classList.remove('d-none');
    }

    function hideFormErrors() {
        if (!formErrors) return;
        formErrors.classList.add('d-none');
        formErrors.innerHTML = '';
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function toggleSectionField() {
        if (!sectionWrap) return;
        sectionWrap.style.display = placementCurriculum.checked ? '' : 'none';
    }

    if (placementReference) placementReference.addEventListener('change', toggleSectionField);
    if (placementCurriculum) placementCurriculum.addEventListener('change', toggleSectionField);
    toggleSectionField();

    function urlWithCourseId(template, courseId) {
        return template.replace('__COURSE_ID__', courseId);
    }

    function getActiveCourseId() {
        if (config.mode === 'docs') {
            return primaryCourseSelect ? primaryCourseSelect.value : '';
        }
        return null;
    }

    function loadSectionsForCourse(courseId, preselectSectionId) {
        if (!sectionSelect || !courseId) {
            if (sectionSelect) {
                sectionSelect.innerHTML = '<option value="">اختر القسم...</option>';
            }
            return;
        }

        var sectionsUrl = config.mode === 'course'
            ? config.sectionsUrl
            : urlWithCourseId(config.sectionsUrlTemplate, courseId);

        sectionSelect.innerHTML = '<option value="">جاري التحميل...</option>';

        fetch(sectionsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var results = data.results || [];
                var html = '<option value="">اختر القسم...</option>';
                results.forEach(function (item) {
                    html += '<option value="' + item.id + '">' + escapeHtml(item.text) + '</option>';
                });
                sectionSelect.innerHTML = html;
                if (preselectSectionId) {
                    sectionSelect.value = preselectSectionId;
                }
            })
            .catch(function () {
                sectionSelect.innerHTML = '<option value="">تعذّر تحميل الأقسام</option>';
            });
    }

    function loadLessonModules(courseId) {
        if (!lessonSelect) return;

        var url = config.mode === 'course'
            ? config.lessonModulesUrl
            : (courseId ? urlWithCourseId(config.lessonModulesUrlTemplate, courseId) : null);

        if (!url) {
            lessonSelect.innerHTML = '';
            return;
        }

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var results = data.results || [];
                lessonSelect.innerHTML = results.map(function (item) {
                    return '<option value="' + item.id + '">' + escapeHtml(item.text) + '</option>';
                }).join('');
            })
            .catch(function () {
                lessonSelect.innerHTML = '';
            });
    }

    function syncAdditionalCoursesOptions() {
        if (config.mode !== 'docs' || !additionalCoursesSelect || !primaryCourseSelect) return;

        var primaryId = primaryCourseSelect.value;
        Array.prototype.forEach.call(additionalCoursesSelect.options, function (opt) {
            opt.disabled = primaryId !== '' && opt.value === primaryId;
            if (opt.disabled) opt.selected = false;
        });
    }

    if (primaryCourseSelect) {
        primaryCourseSelect.addEventListener('change', function () {
            var courseId = primaryCourseSelect.value;
            loadSectionsForCourse(courseId);
            loadLessonModules(courseId);
            syncAdditionalCoursesOptions();
        });
    }

    // --- Course mode: page search ---
    function canSearchPages() {
        return kindFilter && categoryFilter && (kindFilter.value !== '' || categoryFilter.value !== '');
    }

    function updateSearchControls() {
        if (!searchInput || !searchBtn || !pagesList) return;
        var enabled = canSearchPages();
        searchInput.disabled = !enabled;
        searchBtn.disabled = !enabled;
        if (!enabled) {
            pagesList.innerHTML = '<p class="text-muted mb-0 fs-12 text-center py-3">اختر التصنيف أو النوع أولاً لعرض الصفحات</p>';
        }
    }

    function loadCategories() {
        if (config.mode !== 'course' || !categoryFilter) return;

        var params = new URLSearchParams();
        if (kindFilter && kindFilter.value) {
            params.set('kind', kindFilter.value);
        }

        categoryFilter.innerHTML = '<option value="">جاري التحميل...</option>';

        fetch(config.categoriesUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var results = data.results || [];
                var html = '<option value="">اختر التصنيف...</option>';
                results.forEach(function (item) {
                    html += '<option value="' + item.id + '">' + escapeHtml(item.text) + ' (' + escapeHtml(item.kind_label) + ')</option>';
                });
                categoryFilter.innerHTML = html;
                updateSearchControls();
            })
            .catch(function () {
                categoryFilter.innerHTML = '<option value="">تعذّر تحميل التصنيفات</option>';
            });
    }

    function rememberCheckedPages() {
        if (!pagesList) return;
        pagesList.querySelectorAll('input[name="documentation_page_ids[]"]:checked').forEach(function (input) {
            selectedPageIds[input.value] = true;
        });
    }

    function renderPages(results) {
        if (!pagesList) return;
        rememberCheckedPages();

        if (!results.length) {
            pagesList.innerHTML = '<p class="text-muted mb-0 fs-12 text-center py-3">لا توجد صفحات في هذا التصنيف</p>';
            return;
        }

        pagesList.innerHTML = results.map(function (item) {
            var checked = selectedPageIds[item.id] ? ' checked' : '';
            return '<div class="form-check mb-2">' +
                '<input class="form-check-input" type="checkbox" name="documentation_page_ids[]" value="' + item.id + '" id="doc-page-' + item.id + '"' + checked + '>' +
                '<label class="form-check-label" for="doc-page-' + item.id + '">' +
                '<strong>' + escapeHtml(item.text) + '</strong>' +
                (item.category ? ' <span class="text-muted fs-11">· ' + escapeHtml(item.category) + '</span>' : '') +
                (item.slug ? ' <code class="fs-11">' + escapeHtml(item.slug) + '</code>' : '') +
                '</label></div>';
        }).join('');
    }

    function searchPages() {
        if (config.mode !== 'course' || !canSearchPages()) {
            updateSearchControls();
            return;
        }

        var params = new URLSearchParams();
        if (categoryFilter.value) {
            params.set('category_id', categoryFilter.value);
        } else if (kindFilter.value) {
            params.set('kind', kindFilter.value);
        }
        if (searchInput.value.trim()) {
            params.set('q', searchInput.value.trim());
        }

        pagesList.innerHTML = '<p class="text-muted mb-0 fs-12 text-center py-3">جاري التحميل...</p>';

        fetch(config.searchUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) { renderPages(data.results || []); })
            .catch(function () {
                pagesList.innerHTML = '<p class="text-danger mb-0 fs-12 text-center py-3">تعذّر تحميل النتائج</p>';
            });
    }

    if (kindFilter) {
        kindFilter.addEventListener('change', function () {
            selectedPageIds = {};
            loadCategories();
            searchPages();
        });
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', function () {
            selectedPageIds = {};
            updateSearchControls();
            searchPages();
        });
    }

    if (searchBtn) searchBtn.addEventListener('click', searchPages);
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPages();
            }
        });
    }

    // --- Form submit ---
    if (attachForm) {
        attachForm.addEventListener('submit', function (e) {
            e.preventDefault();
            hideFormErrors();

            if (config.mode === 'course') {
                var checked = attachForm.querySelectorAll('input[name="documentation_page_ids[]"]:checked');
                if (!checked.length) {
                    showFormErrors('اختر صفحة توثيق واحدة على الأقل.');
                    return;
                }
            } else {
                if (!primaryCourseSelect || !primaryCourseSelect.value) {
                    showFormErrors('اختر الكورس.');
                    return;
                }
                if (!currentPageId) {
                    showFormErrors('صفحة التوثيق غير محددة.');
                    return;
                }
                attachForm.action = config.storeUrlTemplate.replace('__PAGE_ID__', currentPageId);
            }

            if (placementCurriculum.checked && sectionSelect && !sectionSelect.value) {
                showFormErrors('اختر القسم عند الإضافة للمنهج.');
                return;
            }

            var token = getCsrfToken();
            var tokenInput = attachForm.querySelector('input[name="_token"]');
            if (tokenInput && token) {
                tokenInput.value = token;
            }

            var formData = new FormData(attachForm);
            var originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fe fe-loader me-1"></i> جاري الربط...';

            fetch(attachForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    }).catch(function () {
                        if (response.status === 419) {
                            return { ok: false, status: 419, data: { message: 'انتهت الجلسة. حدّث الصفحة وحاول مجدداً.' } };
                        }
                        return { ok: false, status: response.status, data: { message: 'تعذّر إتمام الربط.' } };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.data.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(result.data.message || 'تم الربط بنجاح');
                        }
                        var redirect = result.data.redirect || config.redirectUrl || window.location.href;
                        if (config.mode === 'docs') {
                            var bsModal = bootstrap.Modal.getInstance(modal);
                            if (bsModal) bsModal.hide();
                            window.location.reload();
                        } else {
                            window.location.href = redirect;
                        }
                        return;
                    }

                    var messages = [];
                    if (result.data.errors) {
                        Object.keys(result.data.errors).forEach(function (key) {
                            messages = messages.concat(result.data.errors[key]);
                        });
                    } else if (result.data.message) {
                        messages.push(result.data.message);
                    } else {
                        messages.push('تعذّر ربط التوثيق.');
                    }
                    showFormErrors(messages);
                })
                .catch(function () {
                    showFormErrors('تعذّر الاتصال بالخادم.');
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                });
        });
    }

    function resetModalState() {
        selectedPageIds = {};
        hideFormErrors();
        if (placementReference) placementReference.checked = true;
        toggleSectionField();
        if (kindFilter) kindFilter.value = '';
        if (categoryFilter) categoryFilter.innerHTML = '<option value="">اختر التصنيف...</option>';
        if (searchInput) searchInput.value = '';
        updateSearchControls();
        if (primaryCourseSelect) primaryCourseSelect.value = '';
        if (sectionSelect) sectionSelect.innerHTML = '<option value="">اختر القسم...</option>';
        if (lessonSelect) lessonSelect.innerHTML = '';
        currentPageId = null;
        syncAdditionalCoursesOptions();
    }

    modal.addEventListener('shown.bs.modal', function () {
        if (config.mode === 'course') {
            loadCategories();
            loadLessonModules();
            updateSearchControls();
        }
    });

    modal.addEventListener('hidden.bs.modal', resetModalState);

    modal.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) return;

        if (config.mode === 'course') {
            var sectionId = trigger.getAttribute('data-section-id');
            if (sectionId && placementCurriculum && sectionSelect) {
                placementCurriculum.checked = true;
                toggleSectionField();
                sectionSelect.value = sectionId;
            }
        } else if (config.mode === 'docs') {
            currentPageId = trigger.getAttribute('data-page-id');
            var titleEl = document.getElementById('doc-fixed-page-title');
            var categoryEl = document.getElementById('doc-fixed-page-category');
            var slugEl = document.getElementById('doc-fixed-page-slug');

            if (titleEl) titleEl.textContent = trigger.getAttribute('data-page-title') || '—';
            if (categoryEl) {
                var cat = trigger.getAttribute('data-page-category') || '';
                categoryEl.textContent = cat ? cat : '';
            }
            if (slugEl) {
                var slug = trigger.getAttribute('data-page-slug') || '';
                slugEl.textContent = slug ? slug : '';
            }

            var sectionId = trigger.getAttribute('data-section-id');
            if (sectionId && placementCurriculum) {
                placementCurriculum.checked = true;
                toggleSectionField();
            }
        }
    });
})();
</script>
