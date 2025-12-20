@extends('admin.layouts.master')

@section('page-title')
    إضافة أعضاء جماعياً - {{ $group->name }}
@stop

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .filter-card {
        position: sticky;
        top: 20px;
    }
    .stats-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 300px;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #667eea;
        border-color: #667eea;
        color: white;
        padding: 0.35rem 0.65rem;
        font-size: 0.9rem;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-left: 0.5rem;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة أعضاء جماعياً</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.groups.show', [$group->courses->first()->id ?? 1, $group->id]) }}">{{ $group->name }}</a></li>
                            <li class="breadcrumb-item active">إضافة جماعية</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('courses.groups.show', [$group->courses->first()->id ?? 1, $group->id]) }}" class="btn btn-light">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['current_members'] }}</h3>
                            <p class="text-muted mb-0">الأعضاء الحاليين</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-user-plus fa-2x"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['total_available'] }}</h3>
                            <p class="text-muted mb-0">الطلاب المتاحين</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['available_slots'] ?? 'غير محدود' }}</h3>
                            <p class="text-muted mb-0">الأماكن المتاحة</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3">
                    <div class="card custom-card filter-card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-filter me-2"></i>فلترة الطلاب
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('groups.bulk-enroll-page', $group->id) }}">

                                <!-- Search -->
                                <div class="mb-3">
                                    <label class="form-label">بحث</label>
                                    <input type="text" name="search" class="form-control"
                                           placeholder="الاسم أو البريد الإلكتروني"
                                           value="{{ request('search') }}">
                                </div>

                                <!-- Enrollment Date From -->
                                <div class="mb-3">
                                    <label class="form-label">تاريخ الانضمام من</label>
                                    <input type="date" name="enrolled_from" class="form-control"
                                           value="{{ request('enrolled_from') }}">
                                </div>

                                <!-- Enrollment Date To -->
                                <div class="mb-3">
                                    <label class="form-label">تاريخ الانضمام إلى</label>
                                    <input type="date" name="enrolled_to" class="form-control"
                                           value="{{ request('enrolled_to') }}">
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <label class="form-label">الحالة</label>
                                    <select name="status" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                                    </select>
                                </div>

                                <!-- Enrolled in Course -->
                                <div class="mb-3">
                                    <label class="form-label">مسجل في كورس</label>
                                    <select name="enrolled_in_course" class="form-select">
                                        <option value="">الكل</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ request('enrolled_in_course') == $course->id ? 'selected' : '' }}>
                                                {{ $course->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sort By -->
                                <div class="mb-3">
                                    <label class="form-label">ترتيب حسب</label>
                                    <select name="sort_by" class="form-select">
                                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>تاريخ الانضمام</option>
                                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>الاسم</option>
                                        <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>البريد الإلكتروني</option>
                                    </select>
                                </div>

                                <!-- Sort Order -->
                                <div class="mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <select name="sort_order" class="form-select">
                                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>تنازلي</option>
                                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>تصاعدي</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>تطبيق الفلترة
                                    </button>
                                    <a href="{{ route('groups.bulk-enroll-page', $group->id) }}" class="btn btn-light">
                                        <i class="fas fa-redo me-2"></i>إعادة تعيين
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Students List -->
                <div class="col-lg-9">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-users me-2"></i>اختيار الطلاب للإضافة
                            </h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                    <i class="fas fa-check-double me-1"></i>تحديد الكل
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                    <i class="fas fa-times me-1"></i>إلغاء التحديد
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('groups.add-bulk-members', $group->id) }}" method="POST" id="bulkEnrollForm">
                                @csrf

                                @if($students->count() > 0)
                                    <!-- Instructions -->
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>تعليمات:</strong> حدد الطلاب من القائمة أدناه. يمكنك البحث بالاسم أو البريد الإلكتروني، أو استخدام أزرار "تحديد الكل" و "إلغاء التحديد" للتحكم السريع.
                                    </div>

                                    <!-- Role Selection -->
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-user-tag me-2"></i>الدور الافتراضي
                                            </label>
                                            <select name="default_role" class="form-select" required>
                                                <option value="member" selected>عضو</option>
                                                <option value="leader">قائد</option>
                                            </select>
                                            <small class="text-muted">سيتم تعيين هذا الدور لجميع الطلاب المحددين</small>
                                        </div>
                                    </div>

                                    <!-- Multiselect -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-list-check me-2"></i>حدد الطلاب
                                            <span class="badge bg-primary ms-2" id="selectedCount">0 محدد</span>
                                        </label>
                                        <select name="student_ids[]" id="studentsSelect" class="form-select" multiple required>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}"
                                                        data-email="{{ $student->email }}"
                                                        data-status="{{ $student->is_active ? 'نشط' : 'غير نشط' }}"
                                                        data-date="{{ $student->created_at->format('Y-m-d') }}">
                                                    {{ $student->name }} - {{ $student->email }}
                                                    @if($student->is_active) ✓ @else ✗ @endif
                                                    ({{ $student->created_at->format('Y-m-d') }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            استخدم البحث للعثور على الطلاب بسرعة، أو اضغط Ctrl/Cmd لتحديد عدة طلاب متفرقين
                                        </small>
                                    </div>

                                    <!-- Pagination Info -->
                                    @if($students->hasPages())
                                        <div class="alert alert-warning mb-4">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>ملاحظة:</strong> يتم عرض {{ $students->count() }} طالب من أصل {{ $students->total() }} طالب متاح.
                                            استخدم الفلاتر في الجانب لتضييق النتائج أو
                                            <a href="{{ route('groups.bulk-enroll-page', array_merge(['groupId' => $group->id], request()->except('page'))) }}" class="alert-link">
                                                عرض الكل
                                            </a>
                                        </div>
                                    @endif

                                    <!-- Submit Button -->
                                    <div class="mt-4 d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                                            <i class="fas fa-user-plus me-2"></i>
                                            إضافة <span id="submitCount">0</span> طالب إلى المجموعة
                                        </button>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-users fa-5x text-muted mb-4 opacity-25"></i>
                                        <h4 class="text-muted mb-3">لا يوجد طلاب متاحين</h4>
                                        <p class="text-muted">جميع الطلاب مسجلين بالفعل في هذه المجموعة أو لا يوجد طلاب يطابقون معايير البحث</p>
                                        <a href="{{ route('groups.bulk-enroll-page', $group->id) }}" class="btn btn-primary mt-3">
                                            <i class="fas fa-redo me-2"></i>إعادة تعيين الفلاتر
                                        </a>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function() {
    'use strict';

    // Update selected count
    function updateSelectedCount() {
        var select = document.getElementById('studentsSelect');
        var count = select ? select.selectedOptions.length : 0;

        var selectedCountEl = document.getElementById('selectedCount');
        var submitCountEl = document.getElementById('submitCount');
        var submitBtnEl = document.getElementById('submitBtn');

        if (selectedCountEl) selectedCountEl.textContent = count + ' محدد';
        if (submitCountEl) submitCountEl.textContent = count;
        if (submitBtnEl) submitBtnEl.disabled = count === 0;
    }

    // Initialize when DOM is ready
    function init() {
        // Initialize Select2
        var studentsSelect = $('#studentsSelect');
        if (studentsSelect.length) {
            studentsSelect.select2({
                theme: 'bootstrap-5',
                placeholder: 'ابحث عن الطلاب بالاسم أو البريد الإلكتروني...',
                allowClear: true,
                width: '100%',
                dir: 'rtl',
                language: {
                    noResults: function() {
                        return 'لا توجد نتائج';
                    },
                    searching: function() {
                        return 'جاري البحث...';
                    },
                    inputTooShort: function() {
                        return 'الرجاء إدخال حرف واحد على الأقل';
                    },
                    loadingMore: function() {
                        return 'جاري تحميل المزيد...';
                    }
                }
            });

            // Update count on change
            studentsSelect.on('change', function() {
                updateSelectedCount();
            });
        }

        // Select all button
        var selectAllBtn = document.getElementById('selectAllBtn');
        if (selectAllBtn) {
            selectAllBtn.onclick = function(e) {
                e.preventDefault();
                var select = document.getElementById('studentsSelect');
                if (select) {
                    // Select all options
                    for (var i = 0; i < select.options.length; i++) {
                        select.options[i].selected = true;
                    }
                    // Trigger Select2 change
                    $(select).trigger('change');
                }
                return false;
            };
        }

        // Deselect all button
        var deselectAllBtn = document.getElementById('deselectAllBtn');
        if (deselectAllBtn) {
            deselectAllBtn.onclick = function(e) {
                e.preventDefault();
                var select = document.getElementById('studentsSelect');
                if (select) {
                    // Deselect all options
                    for (var i = 0; i < select.options.length; i++) {
                        select.options[i].selected = false;
                    }
                    // Trigger Select2 change
                    $(select).trigger('change');
                }
                return false;
            };
        }

        // Form validation
        var form = document.getElementById('bulkEnrollForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                var select = document.getElementById('studentsSelect');
                var selectedCount = select ? select.selectedOptions.length : 0;

                if (selectedCount === 0) {
                    e.preventDefault();
                    alert('يرجى تحديد طالب واحد على الأقل');
                    return false;
                }

                if (!confirm('هل أنت متأكد من إضافة ' + selectedCount + ' طالب إلى المجموعة؟')) {
                    e.preventDefault();
                    return false;
                }
            });
        }

        // Initialize count on page load
        updateSelectedCount();
    }

    // Check if DOM is already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@stop
