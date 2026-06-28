@php
    use App\Support\MembershipRequestFormColumns;

    $mrColumnDefaults = MembershipRequestFormColumns::defaultVisibility();
@endphp
<script>
(function () {
    const DEFAULT_VISIBLE = @json($mrColumnDefaults);

    function storageKey() {
        const table = document.getElementById('membershipRequestsTable');
        return table ? (table.getAttribute('data-columns-storage-key') || 'membership_requests_columns') : 'membership_requests_columns';
    }

    function allColumnKeys() {
        return Object.keys(DEFAULT_VISIBLE);
    }

    function loadPrefs() {
        try {
            const raw = localStorage.getItem(storageKey());
            if (!raw) return { ...DEFAULT_VISIBLE };
            const parsed = JSON.parse(raw);
            return { ...DEFAULT_VISIBLE, ...parsed };
        } catch (e) {
            return { ...DEFAULT_VISIBLE };
        }
    }

    function savePrefs(prefs) {
        try {
            localStorage.setItem(storageKey(), JSON.stringify(prefs));
        } catch (e) { /* ignore */ }
    }

    function applyColumnVisibility(prefs) {
        const table = document.getElementById('membershipRequestsTable');
        if (!table) return;

        allColumnKeys().forEach(function (col) {
            const visible = prefs[col] !== false;
            table.querySelectorAll('[data-mr-col="' + col + '"]').forEach(function (el) {
                el.classList.toggle('d-none', !visible);
            });
        });

        document.querySelectorAll('.js-mr-col-toggle').forEach(function (input) {
            const col = input.value;
            if (prefs[col] === undefined) {
                input.checked = DEFAULT_VISIBLE[col] !== false;
            } else {
                input.checked = prefs[col] !== false;
            }
        });
    }

    function syncFromCheckboxes() {
        const prefs = loadPrefs();
        document.querySelectorAll('.js-mr-col-toggle').forEach(function (input) {
            prefs[input.value] = input.checked;
        });

        const visibleDataCols = Object.keys(prefs).filter(function (k) {
            return prefs[k] !== false && document.querySelector('[data-mr-col="' + k + '"]');
        });
        if (visibleDataCols.length === 0) {
            alert('يجب إظهار عمود واحد على الأقل.');
            const first = document.querySelector('.js-mr-col-toggle');
            if (first) {
                first.checked = true;
                prefs[first.value] = true;
            }
        }

        savePrefs(prefs);
        applyColumnVisibility(prefs);
    }

    function initColumnPicker() {
        const prefs = loadPrefs();
        applyColumnVisibility(prefs);

        document.querySelectorAll('.js-mr-col-toggle').forEach(function (input) {
            input.removeEventListener('change', syncFromCheckboxes);
            input.addEventListener('change', syncFromCheckboxes);
        });

        const selectAllBtn = document.getElementById('membershipColumnsSelectAll');
        if (selectAllBtn) {
            selectAllBtn.onclick = function () {
                document.querySelectorAll('.js-mr-col-toggle').forEach(function (input) {
                    input.checked = true;
                });
                syncFromCheckboxes();
            };
        }

        const resetBtn = document.getElementById('membershipColumnsReset');
        if (resetBtn) {
            resetBtn.onclick = function () {
                savePrefs({ ...DEFAULT_VISIBLE });
                applyColumnVisibility({ ...DEFAULT_VISIBLE });
            };
        }
    }

    window.initMembershipRequestColumns = initColumnPicker;

    document.addEventListener('DOMContentLoaded', initColumnPicker);
})();
</script>
