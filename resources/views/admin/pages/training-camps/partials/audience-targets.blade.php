{{-- جمهور المعسكر: كورس + مجموعات (مجموعة واحدة على الأقل لكل كورس) --}}
@php
    $initialRows = old('targets');
    if ($initialRows === null) {
        $initialRows = isset($camp) ? $camp->audienceRowsForForm() : [];
    }
@endphp

<div class="card custom-card group-show-members-card mt-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">
            <i class="fe fe-users me-1"></i>الجمهور المستهدف
            <span class="text-danger">*</span>
        </h6>
        <p class="fs-12 text-muted mb-0">
            يظهر المعسكر فقط لطلاب المجموعات المختارة. بدون مجموعات لا يظهر لأحد.
        </p>
    </div>
    <div class="card-body pt-3">
        <div class="alert alert-info py-2 mb-3 fs-12">
            أضف صفاً لكل كورس، واختر مجموعة واحدة على الأقل من مجموعات ذلك الكورس.
            يمكن إضافة أكثر من كورس.
        </div>

        @error('targets')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        <div id="camp-audience-empty" class="camp-audience__empty">
            لا جمهور محدد — المعسكر لن يظهر لأي طالب حتى تضيف كورساً ومجموعة.
        </div>

        <div id="camp-audience-rows" class="camp-audience"></div>

        <button type="button" class="camp-audience__add" id="camp-audience-add">
            <i class="fe fe-plus"></i> إضافة كورس / مجموعات
        </button>
    </div>
</div>

<style>
    .camp-audience { display: grid; gap: 0.75rem; margin-bottom: 0.85rem; }
    .camp-audience__row {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #f8fafc;
        padding: 0.9rem;
    }
    .camp-audience__row-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.65rem;
    }
    .camp-audience__row-title {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 800;
        color: #0f172a;
    }
    .camp-audience__remove {
        border: 0;
        background: transparent;
        color: #b91c1c;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
    }
    .camp-audience__groups {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.4rem 0.75rem;
        margin-top: 0.55rem;
        max-height: 10rem;
        overflow: auto;
        padding: 0.55rem 0.65rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
    }
    @media (max-width: 575.98px) {
        .camp-audience__groups { grid-template-columns: 1fr; }
    }
    .camp-audience__group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }
    .camp-audience__hint {
        margin: 0.45rem 0 0;
        font-size: 0.75rem;
        color: #64748b;
    }
    .camp-audience__add {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        border-radius: 0.55rem;
        border: 1px dashed #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.8rem;
        font-weight: 800;
        cursor: pointer;
    }
    .camp-audience__add:hover { background: #dbeafe; }
    .camp-audience__empty {
        text-align: center;
        padding: 1rem;
        border: 1px dashed #e2e8f0;
        border-radius: 12px;
        color: #64748b;
        font-size: 0.82rem;
        margin-bottom: 0.75rem;
    }
</style>

<script>
window.CampAudience = (function () {
    const courses = @json(($courses ?? collect())->map(fn ($c) => ['id' => $c->id, 'title' => $c->title])->values());
    const groupsUrlTpl = @json(route('assignments.get-groups', ['courseId' => '__ID__']));
    let initialRows = @json($initialRows);
    let rowIndex = 0;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function courseOptionsHtml(selectedId) {
        let html = '<option value="">اختر كورساً...</option>';
        courses.forEach(function (c) {
            html += '<option value="' + c.id + '"' +
                (String(c.id) === String(selectedId || '') ? ' selected' : '') + '>' +
                escapeHtml(c.title) + '</option>';
        });
        return html;
    }

    function rowHtml(i, data) {
        data = data || {};
        return '<div class="camp-audience__row" data-row="' + i + '">' +
            '<div class="camp-audience__row-head">' +
            '<h6 class="camp-audience__row-title">جمهور #' + (i + 1) + '</h6>' +
            '<button type="button" class="camp-audience__remove" data-remove-row>حذف</button>' +
            '</div>' +
            '<label class="form-label fw-semibold">الكورس <span class="text-danger">*</span></label>' +
            '<select name="targets[' + i + '][course_id]" class="form-select camp-audience__course" data-row="' + i + '" required>' +
            courseOptionsHtml(data.course_id) +
            '</select>' +
            '<p class="camp-audience__hint">المجموعات <span class="text-danger">*</span> — اختر مجموعة واحدة على الأقل</p>' +
            '<div class="camp-audience__groups" data-groups-for="' + i + '">' +
            '<span class="text-muted small">اختر كورساً أولاً</span>' +
            '</div>' +
            '</div>';
    }

    function refreshTitles() {
        document.querySelectorAll('#camp-audience-rows .camp-audience__row').forEach(function (row, idx) {
            const title = row.querySelector('.camp-audience__row-title');
            if (title) title.textContent = 'جمهور #' + (idx + 1);
        });
        const empty = document.getElementById('camp-audience-empty');
        const hasRows = document.querySelectorAll('#camp-audience-rows .camp-audience__row').length > 0;
        if (empty) empty.style.display = hasRows ? 'none' : '';
    }

    function loadGroups(rowEl, courseId, selectedIds) {
        const i = rowEl.getAttribute('data-row');
        const box = rowEl.querySelector('[data-groups-for="' + i + '"]');
        if (!box) return;
        selectedIds = (selectedIds || []).map(String);

        if (!courseId) {
            box.innerHTML = '<span class="text-muted small">اختر كورساً أولاً</span>';
            return;
        }

        box.innerHTML = '<span class="text-muted small">جاري التحميل...</span>';
        const url = groupsUrlTpl.replace('__ID__', courseId);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function (groups) {
                if (!groups || !groups.length) {
                    box.innerHTML = '<span class="text-danger small">لا مجموعات مرتبطة بهذا الكورس</span>';
                    return;
                }
                box.innerHTML = groups.map(function (g) {
                    const checked = selectedIds.indexOf(String(g.id)) !== -1 ? ' checked' : '';
                    return '<label class="camp-audience__group">' +
                        '<input type="checkbox" name="targets[' + i + '][group_ids][]" value="' + g.id + '"' + checked + '>' +
                        escapeHtml(g.name) +
                        '</label>';
                }).join('');
            })
            .catch(function () {
                box.innerHTML = '<span class="text-danger small">تعذر تحميل المجموعات</span>';
            });
    }

    function addRow(data) {
        const container = document.getElementById('camp-audience-rows');
        if (!container) return;
        const i = rowIndex++;
        container.insertAdjacentHTML('beforeend', rowHtml(i, data));
        const rowEl = container.querySelector('[data-row="' + i + '"]');
        const select = rowEl.querySelector('.camp-audience__course');
        select.addEventListener('change', function () {
            loadGroups(rowEl, select.value, []);
        });
        if (data && data.course_id) {
            loadGroups(rowEl, data.course_id, data.group_ids || []);
        }
        refreshTitles();
    }

    function bind() {
        const container = document.getElementById('camp-audience-rows');
        if (!container) return;

        document.getElementById('camp-audience-add')?.addEventListener('click', function () {
            addRow({});
        });

        container.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-remove-row]');
            if (!btn) return;
            btn.closest('.camp-audience__row')?.remove();
            refreshTitles();
        });

        if (Array.isArray(initialRows) && initialRows.length) {
            initialRows.forEach(function (row) { addRow(row); });
        } else {
            addRow({});
            refreshTitles();
        }
    }

    return { bind: bind };
})();
</script>
