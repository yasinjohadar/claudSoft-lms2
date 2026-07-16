{{-- Multi course/group audience targets for programming challenges --}}
@php
    $sectionCourseId = isset($section) ? $section->course_id : null;
    $initialRows = old('targets');
    if ($initialRows === null) {
        if (isset($challenge)) {
            $initialRows = $challenge->audienceRowsForForm();
        } elseif ($sectionCourseId) {
            $initialRows = [['course_id' => $sectionCourseId, 'group_ids' => []]];
        } else {
            $initialRows = [];
        }
    }
@endphp

<div class="pch-form__panel">
    <div class="pch-form__panel-head">
        <span class="pch-form__panel-icon pch-form__panel-icon--amber"><i class="fe fe-users"></i></span>
        <div>
            <h6 class="pch-form__panel-title">الجمهور المستهدف</h6>
            <p class="pch-form__panel-sub">كورسات ومجموعات متعددة لنفس التحدي — بدون استنساخ</p>
        </div>
    </div>
    <div class="pch-form__panel-body">
        <div class="pch-form__access-note">
            بدون جمهور = مكتبة عامة لكل الطلاب. أضف صفاً لكل كورس، ويمكن اختيار مجموعة أو أكثر داخل الصف.
            إن تُركت المجموعات فارغة يظهر التحدي لكل المسجّلين في ذلك الكورس.
        </div>

        @error('targets')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        <div id="pch-audience-rows" class="pch-audience"></div>

        <button type="button" class="pch-audience__add" id="pch-audience-add">
            <i class="fe fe-plus"></i> إضافة كورس / دفعة
        </button>
    </div>
</div>

<style>
    .pch-audience { display: grid; gap: 0.75rem; margin-bottom: 0.85rem; }
    .pch-audience__row {
        border: 1px solid var(--pf-border, #e2e8f0);
        border-radius: 14px;
        background: var(--pf-soft, #f8fafc);
        padding: 0.9rem;
    }
    .pch-audience__row-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.65rem;
    }
    .pch-audience__row-title {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--pf-ink, #0f172a);
    }
    .pch-audience__remove {
        border: 0;
        background: transparent;
        color: #b91c1c;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
    }
    .pch-audience__groups {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.4rem 0.75rem;
        margin-top: 0.55rem;
        max-height: 10rem;
        overflow: auto;
        padding: 0.55rem 0.65rem;
        border: 1px solid var(--pf-border, #e2e8f0);
        border-radius: 10px;
        background: #fff;
    }
    @media (max-width: 575.98px) {
        .pch-audience__groups { grid-template-columns: 1fr; }
    }
    .pch-audience__group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }
    .pch-audience__hint {
        margin: 0.45rem 0 0;
        font-size: 0.75rem;
        color: var(--pf-muted, #64748b);
    }
    .pch-audience__add {
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
    .pch-audience__add:hover { background: #dbeafe; }
    .pch-audience__empty {
        text-align: center;
        padding: 1rem;
        border: 1px dashed var(--pf-border, #e2e8f0);
        border-radius: 12px;
        color: var(--pf-muted, #64748b);
        font-size: 0.82rem;
        margin-bottom: 0.75rem;
    }
    [data-theme-mode="dark"] .pch-audience__row,
    [data-theme-mode="dark"] .pch-audience__groups {
        background: rgba(15, 23, 42, 0.45);
    }
</style>

<script>
window.PchAudience = (function () {
    const courses = @json($courses->map(fn ($c) => ['id' => $c->id, 'title' => $c->title])->values());
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
        return '<div class="pch-audience__row" data-row="' + i + '">' +
            '<div class="pch-audience__row-head">' +
            '<h6 class="pch-audience__row-title">جمهور #' + (i + 1) + '</h6>' +
            '<button type="button" class="pch-audience__remove" data-remove-row>حذف</button>' +
            '</div>' +
            '<label class="pch-form__label">الكورس</label>' +
            '<select name="targets[' + i + '][course_id]" class="form-select pch-audience__course" data-row="' + i + '">' +
            courseOptionsHtml(data.course_id) +
            '</select>' +
            '<p class="pch-audience__hint">المجموعات (اختياري — متعدد)</p>' +
            '<div class="pch-audience__groups" data-groups-for="' + i + '">' +
            '<span class="text-muted small">اختر كورساً أولاً</span>' +
            '</div>' +
            '</div>';
    }

    function refreshTitles() {
        document.querySelectorAll('#pch-audience-rows .pch-audience__row').forEach(function (row, idx) {
            const title = row.querySelector('.pch-audience__row-title');
            if (title) title.textContent = 'جمهور #' + (idx + 1);
        });
        const empty = document.getElementById('pch-audience-empty');
        const hasRows = document.querySelectorAll('#pch-audience-rows .pch-audience__row').length > 0;
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
                    box.innerHTML = '<span class="text-muted small">لا مجموعات مرتبطة — سيظهر لكل طلاب الكورس</span>';
                    return;
                }
                box.innerHTML = groups.map(function (g) {
                    const checked = selectedIds.indexOf(String(g.id)) !== -1 ? ' checked' : '';
                    return '<label class="pch-audience__group">' +
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
        const container = document.getElementById('pch-audience-rows');
        if (!container) return;
        const i = rowIndex++;
        container.insertAdjacentHTML('beforeend', rowHtml(i, data));
        const rowEl = container.querySelector('[data-row="' + i + '"]');
        const select = rowEl.querySelector('.pch-audience__course');
        select.addEventListener('change', function () {
            loadGroups(rowEl, select.value, []);
        });
        if (data && data.course_id) {
            loadGroups(rowEl, data.course_id, data.group_ids || []);
        }
        refreshTitles();
    }

    function bind() {
        const container = document.getElementById('pch-audience-rows');
        if (!container) return;

        container.insertAdjacentHTML('beforebegin',
            '<div class="pch-audience__empty" id="pch-audience-empty">لا جمهور مقيّد — التحدي عام في المكتبة (إن كان مستقلاً).</div>'
        );

        document.getElementById('pch-audience-add')?.addEventListener('click', function () {
            addRow({});
        });

        container.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-remove-row]');
            if (!btn) return;
            btn.closest('.pch-audience__row')?.remove();
            refreshTitles();
        });

        if (Array.isArray(initialRows) && initialRows.length) {
            initialRows.forEach(function (row) { addRow(row); });
        } else {
            refreshTitles();
        }
    }

    return { bind: bind };
})();
</script>
