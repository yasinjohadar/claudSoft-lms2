<div class="modal fade" id="attachSimulatorModal" tabindex="-1" aria-labelledby="attachSimulatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachSimulatorModalLabel">
                    <i class="fe fe-cpu me-2"></i>إضافة محاكاة إلى القسم
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('courses.simulator-links.store', $course->id) }}" id="attach-simulator-form">
                @csrf
                <input type="hidden" name="section_id" id="sim-section-id" value="">
                <div class="modal-body">
                    <div id="sim-form-errors" class="alert alert-danger d-none"></div>

                    <div class="alert alert-light border small mb-3">
                        تظهر المحاكيات <strong>المنشورة</strong> فقط. بعد الإضافة يمكنك إظهارها/إخفاءها وترتيبها مثل الدروس والفيديو.
                        <a href="{{ route('admin.lesson-simulators.create') }}" target="_blank" class="ms-1">إنشاء محاكاة جديدة</a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">بحث</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="sim-search-input" placeholder="ابحث بالعنوان أو slug...">
                            <button type="button" class="btn btn-primary" id="sim-search-btn">بحث</button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">المحاكيات <span class="text-danger">*</span></label>
                        <div id="sim-list" class="border rounded p-2 bg-white" style="max-height:280px;overflow-y:auto;">
                            <p class="text-muted small mb-0 p-2">اضغط «بحث» لعرض المحاكيات أو اترك الحقل فارغاً لعرض الكل.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="sim-submit-btn">
                        <i class="fe fe-plus me-1"></i>إضافة للقسم
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('attachSimulatorModal');
    const sectionInput = document.getElementById('sim-section-id');
    const searchInput = document.getElementById('sim-search-input');
    const searchBtn = document.getElementById('sim-search-btn');
    const listEl = document.getElementById('sim-list');
    const form = document.getElementById('attach-simulator-form');
    const errorsEl = document.getElementById('sim-form-errors');
    const submitBtn = document.getElementById('sim-submit-btn');
    const searchUrl = @json(route('courses.simulator-links.search', $course->id));

    function renderList(items) {
        if (!items.length) {
            listEl.innerHTML = '<p class="text-muted small mb-0 p-2">لا توجد محاكيات منشورة.</p>';
            return;
        }
        listEl.innerHTML = items.map(function (item) {
            return '<label class="d-flex align-items-start gap-2 border-bottom py-2 mb-0 sim-item-label" style="cursor:pointer;">'
                + '<input type="checkbox" class="form-check-input mt-1" name="lesson_simulator_ids[]" value="' + item.id + '">'
                + '<span><strong>' + escapeHtml(item.text) + '</strong>'
                + (item.description ? '<br><span class="text-muted small">' + escapeHtml(item.description.substring(0, 120)) + '</span>' : '')
                + '<br><code class="fs-11">' + escapeHtml(item.slug) + '</code></span></label>';
        }).join('');
    }

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text || '';
        return d.innerHTML;
    }

    function loadSimulators() {
        const q = searchInput.value.trim();
        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) { renderList(data.results || []); })
            .catch(function () {
                listEl.innerHTML = '<p class="text-danger small mb-0 p-2">فشل تحميل المحاكيات.</p>';
            });
    }

    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            sectionInput.value = button.getAttribute('data-section-id') || '';
            searchInput.value = '';
            errorsEl.classList.add('d-none');
            errorsEl.textContent = '';
            loadSimulators();
        });
    }

    searchBtn.addEventListener('click', loadSimulators);
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); loadSimulators(); }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errorsEl.classList.add('d-none');
        submitBtn.disabled = true;
        const original = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الإضافة...';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (res.ok && res.data.success) {
                    window.location.href = res.data.redirect || window.location.href;
                    return;
                }
                errorsEl.textContent = res.data.message || 'تعذّر الإضافة.';
                if (res.data.errors) {
                    errorsEl.textContent += ' ' + Object.values(res.data.errors).flat().join(' ');
                }
                errorsEl.classList.remove('d-none');
            })
            .catch(function () {
                errorsEl.textContent = 'حدث خطأ أثناء الإضافة.';
                errorsEl.classList.remove('d-none');
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = original;
            });
    });
});
</script>
@endpush
