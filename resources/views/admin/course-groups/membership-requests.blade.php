@extends('admin.layouts.master')

@section('page-title')
    طلبات الانضمام - {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="my-4 page-header-breadcrumb">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.groups.index', $course->id) }}">المجموعات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.groups.show', [$course->id, $group->id]) }}">{{ $group->name }}</a></li>
                        <li class="breadcrumb-item active">طلبات الانضمام</li>
                    </ol>
                </nav>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-user-plus me-1"></i>
                            طلبات الانضمام
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $group->name }}</h2>
                        @if($group->description)
                            <p class="group-show-hero__desc mb-3">{{ Str::limit($group->description, 160) }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-2">
                            @if($group->allow_membership_requests)
                                <span class="group-show-chip">
                                    <i class="fe fe-check-circle me-1"></i>طلب الانضمام مفعّل
                                </span>
                            @else
                                <span class="badge bg-secondary-transparent text-secondary">طلب الانضمام معطّل</span>
                            @endif
                            <span class="badge bg-primary-transparent text-primary">
                                {{ $course->title }}
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('courses.groups.show', [$course->id, $group->id]) }}"
                               class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع لتفاصيل المجموعة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.course-groups.partials.membership-requests-stats', [
                'requests' => $requests,
                'group' => $group,
                'pendingCount' => $pendingCount ?? 0,
            ])

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الطلبات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالاسم أو الإيميل أو الهاتف، أو فلتر حسب الحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form id="membershipRequestsFilterForm" method="GET"
                          action="{{ route('courses.groups.membership-requests', [$course->id, $group->id]) }}"
                          class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">البحث</label>
                                <input type="text"
                                       id="membershipRequestsSearchInput"
                                       name="search"
                                       class="form-control"
                                       placeholder="البحث بالاسم، الإيميل أو الهاتف..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <button type="button" id="membershipRequestsResetBtn" class="btn btn-outline-secondary">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <small id="membershipRequestsFeedback" class="text-muted d-block mt-2"></small>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h6 class="group-show-members-card__title mb-0">
                        طلبات الانضمام
                        <span class="group-show-members-card__count" id="membershipRequestsTotal">{{ $requests->total() }}</span>
                    </h6>
                    <div class="group-show-member-actions">
                        <div id="approve-selected-container" style="display: none;">
                            <form id="approve-selected-form" action="{{ route('courses.groups.membership-requests.approve-multiple', [$course->id, $group->id]) }}" method="POST" class="d-inline">
                                @csrf
                                <div id="selected-request-ids-container"></div>
                                <button type="button" class="btn btn-sm btn-success" id="approve-selected-btn" data-bs-toggle="modal" data-bs-target="#approveSelectedModal">
                                    <i class="fe fe-check-circle me-1"></i>قبول المحدد
                                </button>
                            </form>
                        </div>
                        <div id="delete-selected-container" style="display: none;">
                            <form id="delete-selected-form" action="{{ route('courses.groups.membership-requests.delete-multiple', [$course->id, $group->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <div id="delete-request-ids-container"></div>
                                <button type="button" class="btn btn-sm btn-danger-light" id="delete-selected-btn" data-bs-toggle="modal" data-bs-target="#deleteSelectedModal">
                                    <i class="fe fe-trash-2 me-1"></i>حذف المحدد
                                </button>
                            </form>
                        </div>
                        @if(isset($pendingCount) && $pendingCount > 0)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#approveAllModal">
                                <i class="fe fe-check-square me-1"></i>قبول الكل ({{ $pendingCount }})
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div id="membershipRequestsTableContainer">
                        @include('admin.course-groups.partials.membership-requests-table', [
                            'requests' => $requests,
                            'course' => $course,
                            'group' => $group,
                            'otherGroupsByStudentId' => $otherGroupsByStudentId ?? collect(),
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approveSelectedModal" tabindex="-1" aria-labelledby="approveSelectedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="avatar avatar-xl bg-success-transparent mx-auto mb-3">
                        <i class="fe fe-check-circle text-success fs-24"></i>
                    </div>
                    <h5 class="mb-3" id="approveSelectedModalLabel">قبول الطلبات المحددة</h5>
                    <p class="text-muted mb-4">
                        هل أنت متأكد من قبول <strong id="selected-count">0</strong> طلب انضمام؟
                        <br><small>سيتم إضافة الطلاب للمجموعة تلقائياً.</small>
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-success px-4" id="confirm-approve-selected">
                            <i class="fe fe-check me-1"></i>نعم، قبول
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($pendingCount) && $pendingCount > 0)
    <div class="modal fade" id="approveAllModal" tabindex="-1" aria-labelledby="approveAllModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-3">
                        <i class="fe fe-check-square text-primary fs-24"></i>
                    </div>
                    <h5 class="mb-3" id="approveAllModalLabel">قبول جميع الطلبات المعلقة</h5>
                    <p class="text-muted mb-4">
                        قبول <strong>{{ $pendingCount }}</strong> طلب معلّق دفعة واحدة؟
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                        <form id="approve-all-form" action="{{ route('courses.groups.membership-requests.approve-all', [$course->id, $group->id]) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fe fe-check-square me-1"></i>نعم، قبول الكل
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="modal fade" id="deleteSelectedModal" tabindex="-1" aria-labelledby="deleteSelectedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                        <i class="fe fe-trash-2 text-danger fs-24"></i>
                    </div>
                    <h5 class="mb-3" id="deleteSelectedModalLabel">حذف الطلبات المحددة</h5>
                    <p class="text-muted mb-4">
                        حذف <strong id="delete-selected-count">0</strong> طلب نهائياً؟
                        <br><small>لن يؤثر على انضمام من تم قبولهم.</small>
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" class="btn btn-danger px-4" id="confirm-delete-selected">
                            <i class="fe fe-trash-2 me-1"></i>نعم، حذف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function animateCountup(el, target, duration) {
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('[data-countup]').forEach(function(el) {
        const target = parseFloat(el.dataset.countup || '0');
        if (!isNaN(target)) animateCountup(el, target, 900);
    });

    const tableContainer = document.getElementById('membershipRequestsTableContainer');
    const filterForm = document.getElementById('membershipRequestsFilterForm');
    const searchInput = document.getElementById('membershipRequestsSearchInput');
    const resetBtn = document.getElementById('membershipRequestsResetBtn');
    const totalCounter = document.getElementById('membershipRequestsTotal');
    const feedback = document.getElementById('membershipRequestsFeedback');

    const approveSelectedContainer = document.getElementById('approve-selected-container');
    const approveSelectedForm = document.getElementById('approve-selected-form');
    const selectedRequestIdsContainer = document.getElementById('selected-request-ids-container');
    const deleteSelectedContainer = document.getElementById('delete-selected-container');
    const deleteSelectedForm = document.getElementById('delete-selected-form');
    const deleteRequestIdsContainer = document.getElementById('delete-request-ids-container');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
        const hasPending = Array.from(checkedBoxes).some(cb => cb.getAttribute('data-status') === 'pending');
        if (approveSelectedContainer) {
            approveSelectedContainer.style.display = (checkedBoxes.length > 0 && hasPending) ? 'inline-block' : 'none';
        }
        if (deleteSelectedContainer) {
            deleteSelectedContainer.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
        }
    }

    function updateSelectAll() {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const requestCheckboxes = document.querySelectorAll('.request-checkbox');
        if (!selectAllCheckbox || requestCheckboxes.length === 0) return;
        const allChecked = Array.from(requestCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(requestCheckboxes).some(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }

    function initSelectionHandlers() {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const requestCheckboxes = document.querySelectorAll('.request-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                requestCheckboxes.forEach(checkbox => { checkbox.checked = this.checked; });
                updateBulkActions();
            });
        }

        requestCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkActions();
                updateSelectAll();
            });
        });
    }

    function initCopyEmailButtons() {
        document.querySelectorAll('.copy-email-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const email = btn.getAttribute('data-email');
                if (!email) return;

                const done = function() {
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="fe fe-check text-success"></i>';
                    setTimeout(function() { btn.innerHTML = originalHtml; }, 1200);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(email).then(done).catch(function() { console.error('copy failed'); });
                }
            });
        });
    }

    function debounce(fn, delay) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    let currentController = null;
    let lastRequestUrl = null;

    function setFeedback(message) {
        if (feedback) feedback.textContent = message;
    }

    function fetchAndRender(url) {
        if (!tableContainer) return;
        if (url === lastRequestUrl) return;
        lastRequestUrl = url;

        if (currentController) currentController.abort();
        currentController = new AbortController();
        tableContainer.style.opacity = '0.6';
        setFeedback('جاري تحديث النتائج...');

        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            signal: currentController.signal,
            credentials: 'same-origin',
        })
            .then(response => {
                if (!response.ok) throw new Error('Failed request');
                return response.json();
            })
            .then(data => {
                if (!data || typeof data.table_html !== 'string') throw new Error('Invalid response');
                tableContainer.innerHTML = data.table_html;
                if (totalCounter && data.meta && typeof data.meta.total !== 'undefined') {
                    totalCounter.textContent = data.meta.total;
                }
                initSelectionHandlers();
                initCopyEmailButtons();
                updateBulkActions();
                updateSelectAll();
                setFeedback('تم تحديث النتائج');
            })
            .catch(error => {
                if (error.name === 'AbortError') return;
                setFeedback('تعذر تحديث النتائج. حاول مرة أخرى.');
            })
            .finally(() => { tableContainer.style.opacity = '1'; });
    }

    function requestFromFilterForm() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        formData.set('search', (formData.get('search') || '').toString().trim());
        const queryString = new URLSearchParams(formData).toString();
        const baseUrl = filterForm.getAttribute('action');
        fetchAndRender(queryString ? `${baseUrl}?${queryString}` : baseUrl);
    }

    const debouncedSearch = debounce(requestFromFilterForm, 350);

    if (searchInput) searchInput.addEventListener('input', debouncedSearch);

    if (filterForm) {
        filterForm.querySelectorAll('select[name="status"]').forEach(sel => {
            sel.addEventListener('change', requestFromFilterForm);
        });
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            requestFromFilterForm();
        });
    }

    if (resetBtn && filterForm) {
        resetBtn.addEventListener('click', function() {
            filterForm.reset();
            if (searchInput) searchInput.value = '';
            lastRequestUrl = null;
            requestFromFilterForm();
        });
    }

    if (tableContainer) {
        tableContainer.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('.pagination a');
            if (!paginationLink) return;
            e.preventDefault();
            fetchAndRender(paginationLink.href);
        });
    }

    const approveSelectedModal = document.getElementById('approveSelectedModal');
    const selectedCountSpan = document.getElementById('selected-count');
    const confirmApproveSelectedBtn = document.getElementById('confirm-approve-selected');

    if (approveSelectedModal && selectedCountSpan && confirmApproveSelectedBtn) {
        approveSelectedModal.addEventListener('show.bs.modal', function() {
            selectedCountSpan.textContent = document.querySelectorAll('.request-checkbox:checked').length;
        });
        confirmApproveSelectedBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('يرجى تحديد طلب واحد على الأقل');
                return;
            }
            selectedRequestIdsContainer.innerHTML = '';
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'request_ids[]';
                input.value = checkbox.value;
                selectedRequestIdsContainer.appendChild(input);
            });
            if (approveSelectedForm) approveSelectedForm.submit();
        });
    }

    const deleteSelectedModal = document.getElementById('deleteSelectedModal');
    const deleteSelectedCountSpan = document.getElementById('delete-selected-count');
    const confirmDeleteSelectedBtn = document.getElementById('confirm-delete-selected');

    if (deleteSelectedModal && deleteSelectedCountSpan && confirmDeleteSelectedBtn) {
        deleteSelectedModal.addEventListener('show.bs.modal', function() {
            deleteSelectedCountSpan.textContent = document.querySelectorAll('.request-checkbox:checked').length;
        });
        confirmDeleteSelectedBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('يرجى تحديد طلب واحد على الأقل');
                return;
            }
            deleteRequestIdsContainer.innerHTML = '';
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'request_ids[]';
                input.value = checkbox.value;
                deleteRequestIdsContainer.appendChild(input);
            });
            if (deleteSelectedForm) deleteSelectedForm.submit();
        });
    }

    initSelectionHandlers();
    initCopyEmailButtons();
    updateBulkActions();
    updateSelectAll();
});
</script>
@stop
