@extends('admin.layouts.master')

@section('page-title')
    تفاصيل المعسكر: {{ $camp->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                        <li class="breadcrumb-item active">{{ $camp->name }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-flag me-1"></i>
                            تفاصيل المعسكر
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $camp->name }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            @if($camp->category)
                                <span class="group-show-chip group-show-chip--sm me-2"
                                      @if($camp->category->color) style="background: {{ $camp->category->color }}18; color: {{ $camp->category->color }}; border-color: {{ $camp->category->color }}30;" @endif>
                                    {{ $camp->category->name }}
                                </span>
                            @endif
                            @if($camp->location)
                                <i class="fe fe-map-pin me-1"></i>{{ $camp->location }}
                            @endif
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('training-camps.edit', $camp->id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل المعسكر</span>
                            </a>
                            <a href="{{ route('training-camps.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للقائمة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-users', 'label' => 'المشاركين الحاليين', 'value' => $camp->current_participants, 'sub' => 'مسجّلون في المعسكر', 'id' => 'camp-stat-participants'],
                    ['variant' => 'green', 'icon' => 'fe-user-check', 'label' => 'الحد الأقصى', 'value' => $camp->max_participants ?? 0, 'sub' => $camp->max_participants ? 'إجمالي المقاعد' : 'غير محدد', 'text' => empty($camp->max_participants), 'textValue' => $camp->max_participants ? (string) $camp->max_participants : 'غير محدد'],
                    ['variant' => 'cyan', 'icon' => 'fe-calendar', 'label' => 'المدة', 'value' => $camp->duration_days, 'sub' => 'يوم تدريبي'],
                    ['variant' => 'orange', 'icon' => 'fe-dollar-sign', 'label' => 'السعر', 'sub' => 'تكلفة الاشتراك', 'text' => true, 'textValue' => '$' . number_format($camp->price, 2)],
                ];
            @endphp

            <div class="row g-3 dashboard-fade-in mb-4">
                @foreach ($kpiCards as $index => $card)
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                        <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                                </div>
                                <div class="admin-stats-card__content flex-fill min-w-0">
                                    <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                    @if(!empty($card['text']))
                                        <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1" id="{{ $card['id'] ?? '' }}">{{ $card['textValue'] }}</h3>
                                    @else
                                        <h3 class="admin-stats-card__value mb-1" id="{{ $card['id'] ?? '' }}" data-countup="{{ $card['value'] }}">0</h3>
                                    @endif
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4">
                <div class="col-xl-9">
                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                إدارة الأعضاء
                                <span class="group-show-members-card__count" id="enrollments-count">{{ $camp->enrollments_count }}</span>
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('training-camps.enrollments.create-individual', $camp->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fe fe-user-plus me-1"></i>إضافة فردي
                                </a>
                                <a href="{{ route('training-camps.enrollments.create-bulk', $camp->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fe fe-users me-1"></i>إضافة من الكروبات
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <form id="enrollments-filter-form" class="group-show-filters mb-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label" for="filter-search">البحث</label>
                                        <input type="text" name="search" id="filter-search" class="form-control"
                                               placeholder="بحث بالاسم أو البريد...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="filter-status">الحالة</label>
                                        <select name="status" id="filter-status" class="form-select">
                                            <option value="">جميع الحالات</option>
                                            <option value="pending">قيد الانتظار</option>
                                            <option value="approved">مقبول</option>
                                            <option value="rejected">مرفوض</option>
                                            <option value="cancelled">ملغي</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="filter-payment-status">حالة الدفع</label>
                                        <select name="payment_status" id="filter-payment-status" class="form-select">
                                            <option value="">حالة الدفع</option>
                                            <option value="unpaid">غير مدفوع</option>
                                            <option value="paid">مدفوع</option>
                                            <option value="refunded">مسترد</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <small id="campEnrollmentsFeedback" class="text-muted d-block"></small>
                                    </div>
                                </div>
                            </form>

                            <div id="enrollments-table-container">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">جاري التحميل...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in mt-4">
                        <div class="card-header border-0 pb-0">
                            <h6 class="card-title mb-0">معلومات المعسكر</h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3 admin-camp-info-strip">
                                <div class="col-md-6 col-lg-3">
                                    <small class="text-muted d-block">المدرب</small>
                                    <strong>{{ $camp->instructor_name ?? '—' }}</strong>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <small class="text-muted d-block">تاريخ البداية</small>
                                    <strong>{{ $camp->start_date->format('Y-m-d') }}</strong>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <small class="text-muted d-block">تاريخ النهاية</small>
                                    <strong>{{ $camp->end_date->format('Y-m-d') }}</strong>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <small class="text-muted d-block">حالة المعسكر</small>
                                    @if($camp->isOngoing())
                                        <span class="admin-camp-status admin-camp-status--ongoing">جاري الآن</span>
                                    @elseif($camp->hasEnded())
                                        <span class="admin-camp-status admin-camp-status--completed">منتهي</span>
                                    @else
                                        <span class="admin-camp-status admin-camp-status--upcoming">قادم</span>
                                    @endif
                                </div>
                            </div>
                            @if($camp->description)
                                <hr class="my-3">
                                <p class="text-muted mb-0 small">{{ Str::limit(strip_tags($camp->description), 300) }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    @if($camp->image)
                        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 overflow-hidden">
                            <img src="{{ asset('storage/' . $camp->image) }}" alt="{{ $camp->name }}"
                                 class="admin-camp-show-image w-100">
                        </div>
                    @endif

                    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                        <div class="card-header border-0 pb-0">
                            <h6 class="card-title mb-0">معلومات إضافية</h6>
                        </div>
                        <div class="card-body pt-3">
                            <div class="admin-profile-detail-field mb-3">
                                <small class="text-muted d-block">المعرف (Slug)</small>
                                <code class="small">{{ $camp->slug }}</code>
                            </div>
                            <div class="admin-profile-detail-field mb-3">
                                <small class="text-muted d-block">الترتيب</small>
                                <strong>{{ $camp->order }}</strong>
                            </div>
                            @if($camp->max_participants)
                                <div class="admin-profile-detail-field mb-3">
                                    <small class="text-muted d-block">المقاعد المتبقية</small>
                                    <strong>{{ $camp->availableSeats() }}</strong> من {{ $camp->max_participants }}
                                    @if($camp->isFull())
                                        <span class="admin-camp-status admin-camp-status--inactive d-inline-flex mt-1">ممتلئ</span>
                                    @endif
                                </div>
                            @endif
                            <div class="admin-profile-detail-field mb-3">
                                <small class="text-muted d-block">تاريخ الإنشاء</small>
                                <strong class="small">{{ $camp->created_at->format('Y-m-d H:i') }}</strong>
                            </div>
                            <div class="admin-profile-detail-field">
                                <small class="text-muted d-block">آخر تحديث</small>
                                <strong class="small">{{ $camp->updated_at->format('Y-m-d H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('training-camps.enrollments.create-individual', $camp->id) }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-user-plus"></i></span>
                            <span class="group-show-action__text">إضافة عضو</span>
                        </a>
                        <button type="button" class="group-show-action group-show-action--danger border-0 bg-transparent w-100 text-start"
                                onclick="if(confirm('هل أنت متأكد من حذف هذا المعسكر؟')) document.getElementById('deleteCampForm').submit();">
                            <span class="group-show-action__icon"><i class="fe fe-trash-2"></i></span>
                            <span class="group-show-action__text">حذف المعسكر</span>
                        </button>
                    </div>
                    <form id="deleteCampForm" action="{{ route('training-camps.destroy', $camp->id) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="enrollmentDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">تفاصيل العضو</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3"><small class="text-muted d-block">الاسم</small><strong id="enrollment-details-name">—</strong></div>
                    <div class="mb-3"><small class="text-muted d-block">البريد</small><span id="enrollment-details-email">—</span></div>
                    <div class="mb-3"><small class="text-muted d-block">الحالة</small><span id="enrollment-details-status">—</span></div>
                    <div class="mb-3"><small class="text-muted d-block">حالة الدفع</small><span id="enrollment-details-payment-status">—</span></div>
                    <div class="mb-3"><small class="text-muted d-block">تاريخ التسجيل</small><span id="enrollment-details-date">—</span></div>
                    <div class="mb-0"><small class="text-muted d-block">ملاحظات</small><span id="enrollment-details-notes">—</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteEnrollmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger"><i class="fe fe-trash-2 me-2"></i>حذف العضو</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="text-muted mb-0">هل أنت متأكد من حذف هذا العضو من المعسكر؟ لا يمكن التراجع عن هذا الإجراء.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteEnrollmentBtn">حذف</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    'use strict';

    const campId = {{ $camp->id }};
    const baseUrl = @json(route('training-camps.enrollments.index', $camp->id));
    const showUrlTemplate = @json(route('training-camps.enrollments.show', [$camp->id, ':id']));
    const updateStatusUrlTemplate = @json(route('training-camps.enrollments.update-status', [$camp->id, ':id']));
    const destroyUrlTemplate = @json(route('training-camps.enrollments.destroy', [$camp->id, ':id']));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let currentPage = 1;
    let filterController = null;
    let currentDeleteEnrollmentId = null;

    const statusLabels = { pending: 'قيد الانتظار', approved: 'مقبول', rejected: 'مرفوض', cancelled: 'ملغي' };
    const paymentLabels = { unpaid: 'غير مدفوع', paid: 'مدفوع', refunded: 'مسترد' };

    function debounce(fn, delay) {
        let timer = null;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function initCountup() {
        document.querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseInt(el.dataset.countup || '0', 10);
            if (!target) { el.textContent = '0'; return; }
            let current = 0;
            const step = Math.max(1, Math.ceil(target / 20));
            const timer = setInterval(function () {
                current += step;
                if (current >= target) { current = target; clearInterval(timer); }
                el.textContent = current.toLocaleString('ar-EG');
            }, 30);
        });
    }

    function updateCampStats(camp) {
        const countEl = document.getElementById('enrollments-count');
        const participantsEl = document.getElementById('camp-stat-participants');
        if (countEl && camp.enrollments_count !== undefined) countEl.textContent = camp.enrollments_count;
        if (participantsEl && camp.current_participants !== undefined) {
            participantsEl.textContent = camp.current_participants.toLocaleString('ar-EG');
        }
    }

    function loadEnrollments(page) {
        currentPage = page || 1;
        const form = document.getElementById('enrollments-filter-form');
        const container = document.getElementById('enrollments-table-container');
        const feedback = document.getElementById('campEnrollmentsFeedback');
        if (!form || !container) return;

        const fd = new FormData(form);
        fd.set('page', currentPage);
        const url = new URL(baseUrl);
        fd.forEach(function (value, key) { if (value) url.searchParams.set(key, value); });

        if (filterController) filterController.abort();
        filterController = new AbortController();
        if (feedback) feedback.textContent = 'جاري التحديث...';
        container.style.opacity = '0.6';

        fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: filterController.signal,
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'error');
                if (typeof data.table_html === 'string') container.innerHTML = data.table_html;
                if (data.camp) updateCampStats(data.camp);
                if (feedback) feedback.textContent = 'تم التحديث';
            })
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                if (feedback) feedback.textContent = 'تعذر التحديث';
                if (typeof toastr !== 'undefined') toastr.error('تعذر تحميل الأعضاء');
            })
            .finally(function () { container.style.opacity = '1'; });
    }

    function viewEnrollmentDetails(enrollmentId) {
        fetch(showUrlTemplate.replace(':id', enrollmentId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error();
                const e = data.enrollment;
                document.getElementById('enrollment-details-name').textContent = e.student?.name || '—';
                document.getElementById('enrollment-details-email').textContent = e.student?.email || '—';
                document.getElementById('enrollment-details-status').textContent = statusLabels[e.status] || e.status;
                document.getElementById('enrollment-details-payment-status').textContent = paymentLabels[e.payment_status] || e.payment_status;
                document.getElementById('enrollment-details-date').textContent = e.enrollment_date ? new Date(e.enrollment_date).toLocaleDateString('ar-SA') : '—';
                document.getElementById('enrollment-details-notes').textContent = e.notes || '—';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('enrollmentDetailsModal')).show();
            })
            .catch(function () {
                if (typeof toastr !== 'undefined') toastr.error('تعذر تحميل التفاصيل');
            });
    }

    function updateEnrollmentStatus(enrollmentId, newStatus) {
        const label = statusLabels[newStatus] || newStatus;
        if (!confirm('هل أنت متأكد من تغيير الحالة إلى: ' + label + '؟')) return;

        fetch(updateStatusUrlTemplate.replace(':id', enrollmentId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status: newStatus }),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message);
                if (typeof toastr !== 'undefined') toastr.success(data.message);
                loadEnrollments(currentPage);
                if (data.camp) updateCampStats(data.camp);
            })
            .catch(function (err) {
                if (typeof toastr !== 'undefined') toastr.error(err.message || 'تعذر التحديث');
            });
    }

    function confirmDeleteEnrollment() {
        if (!currentDeleteEnrollmentId) return;
        const id = currentDeleteEnrollmentId;
        currentDeleteEnrollmentId = null;

        fetch(destroyUrlTemplate.replace(':id', id), {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                bootstrap.Modal.getInstance(document.getElementById('deleteEnrollmentModal'))?.hide();
                if (!data.success) throw new Error(data.message);
                if (typeof toastr !== 'undefined') toastr.success(data.message);
                loadEnrollments(currentPage);
                if (data.camp) updateCampStats(data.camp);
            })
            .catch(function (err) {
                if (typeof toastr !== 'undefined') toastr.error(err.message || 'تعذر الحذف');
            });
    }

    document.getElementById('confirmDeleteEnrollmentBtn')?.addEventListener('click', confirmDeleteEnrollment);

    const container = document.getElementById('enrollments-table-container');
    if (container) {
        container.addEventListener('click', function (e) {
            const pageLink = e.target.closest('.pagination a');
            if (pageLink) {
                e.preventDefault();
                const match = pageLink.href.match(/[?&]page=(\d+)/);
                loadEnrollments(match ? parseInt(match[1], 10) : 1);
                return;
            }
            const viewBtn = e.target.closest('.js-view-camp-enrollment');
            if (viewBtn) {
                viewEnrollmentDetails(viewBtn.getAttribute('data-enrollment-id'));
                return;
            }
            const statusBtn = e.target.closest('.js-update-camp-enrollment-status');
            if (statusBtn) {
                updateEnrollmentStatus(statusBtn.getAttribute('data-enrollment-id'), statusBtn.getAttribute('data-new-status'));
                return;
            }
            const deleteBtn = e.target.closest('.js-delete-camp-enrollment');
            if (deleteBtn) {
                currentDeleteEnrollmentId = deleteBtn.getAttribute('data-enrollment-id');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteEnrollmentModal')).show();
            }
        });
    }

    const debouncedLoad = debounce(function () { loadEnrollments(1); }, 350);
    document.getElementById('filter-search')?.addEventListener('input', debouncedLoad);
    document.getElementById('filter-status')?.addEventListener('change', function () { loadEnrollments(1); });
    document.getElementById('filter-payment-status')?.addEventListener('change', function () { loadEnrollments(1); });

    initCountup();
    loadEnrollments(1);
})();
</script>
@stop
