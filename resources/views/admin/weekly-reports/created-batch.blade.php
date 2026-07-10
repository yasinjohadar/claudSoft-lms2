@extends('admin.layouts.master')

@section('page-title', 'تفاصيل دفعة التقرير')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.weekly-reports.created') }}">التقارير المنشأة</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($batch['report_title'], 40) }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-users me-1"></i>
                        دفعة تقرير منشأ
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $batch['report_title'] }}</h2>
                    <p class="group-show-hero__desc mb-2">
                        {{ $batch['target_course']?->title ?? '—' }}
                        <span class="text-muted mx-1">•</span>
                        {{ $batch['target_group']?->name ?? '—' }}
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary-transparent text-primary">{{ $batch['students_count'] }} طالب</span>
                        <span class="badge bg-success-transparent text-success">{{ $batch['submitted_count'] }} مسلّم</span>
                        @if($batch['pending_count'] > 0)
                            <span class="badge bg-warning-transparent text-warning">{{ $batch['pending_count'] }} بانتظار</span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.weekly-reports.created.batch.edit', ['batch' => $batchKey]) }}"
                           class="group-show-action group-show-action--warning">
                            <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                            <span class="group-show-action__text">تعديل التقرير</span>
                        </a>
                        <button type="button"
                                class="group-show-action group-show-action--danger border-0 weekly-batch-delete-btn"
                                data-batch-key="{{ $batchKey }}"
                                data-batch-title="{{ $batch['report_title'] }}"
                                data-students-count="{{ $batch['students_count'] }}"
                                data-submitted-count="{{ $batch['submitted_count'] }}">
                            <span class="group-show-action__icon"><i class="fe fe-trash-2"></i></span>
                            <span class="group-show-action__text">حذف الدفعة</span>
                        </button>
                        <a href="{{ route('admin.weekly-reports.created') }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">عودة للتقارير المنشأة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-body">
                        <p class="text-muted fs-12 mb-1">أنشأه</p>
                        <p class="fw-semibold mb-0">{{ $batch['created_by_admin']?->name_ar ?? $batch['created_by_admin']?->name ?? '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-body">
                        <p class="text-muted fs-12 mb-1">تاريخ الإنشاء</p>
                        <p class="fw-semibold mb-0">{{ $batch['created_at']?->format('Y-m-d H:i') ?? '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-body">
                        <p class="text-muted fs-12 mb-1">الموعد النهائي</p>
                        <p class="fw-semibold mb-0">{{ $batch['due_at']?->format('Y-m-d H:i') ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(filled($batch['report_description'] ?? null))
            <div class="card custom-card border-primary-transparent dashboard-fade-in mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="avatar avatar-md bg-primary-transparent text-primary rounded-circle flex-shrink-0">
                            <i class="fe fe-clipboard fs-18"></i>
                        </div>
                        <div class="flex-fill">
                            <h6 class="fw-semibold mb-3">المطلوب من الطلاب</h6>
                            <div class="p-3 rounded border bg-light weekly-report-html-content">
                                {!! $batch['report_description'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h4 class="card-title mb-1">تفاصيل الطلاب</h4>
                        <p class="fs-12 text-muted mb-0">ابحث بالاسم أو البريد وفلتر حسب الحالة — التحديث فوري بدون إعادة تحميل الصفحة.</p>
                    </div>
                    <span id="batchStudentsFilterFeedback" class="fs-12 text-muted"></span>
                </div>
            </div>
            <div class="card-body pt-3">
                <form method="GET"
                      action="{{ route('admin.weekly-reports.created.batch') }}"
                      id="batchStudentsFilterForm"
                      class="group-show-filters mb-4">
                    <input type="hidden" name="batch" value="{{ $batchKey }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label" for="batch_students_search">بحث بالاسم أو البريد</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fe fe-search"></i></span>
                                <input type="search"
                                       class="form-control"
                                       name="search"
                                       id="batch_students_search"
                                       value="{{ $filters['search'] ?? '' }}"
                                       placeholder="مثال: Milad أو @gmail.com"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="batch_students_status">الحالة</label>
                            <select class="form-select" name="status" id="batch_students_status">
                                <option value="">كل الحالات</option>
                                <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="submitted" {{ ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' }}>مرسل</option>
                                <option value="reviewed" {{ ($filters['status'] ?? '') === 'reviewed' ? 'selected' : '' }}>مراجع</option>
                                <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>مغلق</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fe fe-filter me-1"></i>تطبيق
                                </button>
                                <button type="button" class="btn btn-light" id="batchStudentsResetBtn" title="إعادة تعيين">
                                    <i class="fe fe-rotate-ccw"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div id="batchStudentsTableContainer">
                    @include('admin.weekly-reports.partials.created-batch-students-table', ['batch' => $batch])
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.weekly-reports.partials.delete-batch-modal')
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('batchStudentsFilterForm');
        const container = document.getElementById('batchStudentsTableContainer');
        const feedback = document.getElementById('batchStudentsFilterFeedback');
        const searchInput = document.getElementById('batch_students_search');
        const statusSelect = document.getElementById('batch_students_status');
        const resetBtn = document.getElementById('batchStudentsResetBtn');

        if (!form || !container) {
            return;
        }

        let activeController = null;
        let debounceTimer = null;

        const setFeedback = (message) => {
            if (feedback) {
                feedback.textContent = message || '';
            }
        };

        const loadStudents = async () => {
            if (activeController) {
                activeController.abort();
            }

            activeController = new AbortController();
            const params = new URLSearchParams(new FormData(form));
            const url = `${form.action}?${params.toString()}`;

            container.style.opacity = '0.6';
            setFeedback('جاري البحث...');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    signal: activeController.signal,
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Failed request');
                }

                const data = await response.json();
                container.innerHTML = data.table_html || '';
                setFeedback(data.count !== data.total
                    ? `عرض ${data.count} من ${data.total}`
                    : `عرض ${data.total} طالب`);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                setFeedback('تعذر تحديث النتائج');
            } finally {
                container.style.opacity = '1';
            }
        };

        const scheduleSearch = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadStudents, 350);
        };

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            loadStudents();
        });

        if (searchInput) {
            searchInput.addEventListener('input', scheduleSearch);
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', loadStudents);
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                }
                if (statusSelect) {
                    statusSelect.value = '';
                }
                loadStudents();
            });
        }
    })();
</script>
@endpush
