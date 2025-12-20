@extends('admin.layouts.master')

@section('page-title')
    التسجيلات - {{ $course->title }}
@stop

@section('css')
<style>
    /* Enrollment Card Styles */
    .enrollment-card {
        border-radius: 20px !important;
        border: 2px solid #e9ecef !important;
        padding: 0 !important;
        margin-bottom: 1.5rem !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        position: relative !important;
        overflow: hidden !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .enrollment-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        opacity: 1;
        transition: opacity 0.3s;
    }

    .enrollment-card:hover {
        border-color: #667eea;
        box-shadow: 0 15px 45px rgba(102, 126, 234, 0.25);
        transform: translateY(-8px);
    }

    .enrollment-card:hover::before {
        height: 8px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 30%, #f093fb 60%, #4facfe 100%);
    }

    .enrollment-card-body {
        padding: 1.75rem !important;
        position: relative !important;
    }

    /* زيادة المسافات بين العناصر */
    .enrollment-card-body > div {
        margin-bottom: 1.5rem !important;
    }

    .enrollment-card-body > div:last-child {
        margin-bottom: 0 !important;
    }

    .enrollment-card-body .mb-4 {
        margin-bottom: 1.75rem !important;
    }

    .enrollment-card-body .mb-3 {
        margin-bottom: 1.25rem !important;
    }

    .enrollment-card-body .mb-2 {
        margin-bottom: 0.875rem !important;
    }

    .enrollment-card-body .mb-1 {
        margin-bottom: 0.625rem !important;
    }

    .enrollment-card-body .mt-3 {
        margin-top: 1.5rem !important;
    }

    /* زيادة المسافة بين الاسم والبريد */
    .enrollment-card-body .student-name {
        margin-bottom: 0.5rem !important;
    }

    /* زيادة المسافة في قسم المعلومات */
    .enrollment-card-body .bg-light {
        margin-bottom: 1.75rem !important;
        padding: 1rem !important;
    }

    .student-avatar {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .enrollment-card:hover .student-avatar {
        border-color: #667eea;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        transform: scale(1.1) rotate(5deg);
    }

    .student-avatar-placeholder {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .enrollment-card:hover .student-avatar-placeholder {
        border-color: #667eea;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        transform: scale(1.1) rotate(-5deg);
        background: linear-gradient(135deg, #764ba2 0%, #667eea 50%, #4facfe 100%);
    }

    .progress-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: conic-gradient(
            #667eea 0deg,
            #667eea calc(var(--progress) * 3.6deg),
            #e9ecef calc(var(--progress) * 3.6deg),
            #e9ecef 360deg
        );
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .enrollment-card:hover .progress-circle {
        transform: scale(1.15) rotate(10deg);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }

    .progress-circle-inner {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-weight: 800;
        box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .student-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .student-email {
        font-size: 0.85rem;
        color: #6c757d;
        display: block;
        margin-top: 0.25rem;
    }

    .enrollment-method-card {
        border-radius: 16px;
        border: 2px solid #e9ecef;
        padding: 2rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: #fff;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .enrollment-method-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .enrollment-method-card:hover {
        border-color: #667eea;
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.2);
        transform: translateY(-8px);
    }

    .enrollment-method-card:hover::before {
        opacity: 1;
    }

    .enrollment-method-card .avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        transition: all 0.3s;
    }

    .enrollment-method-card:hover .avatar {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
    }

    .enrollment-method-card h5 {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.75rem;
    }

    .enrollment-method-card p {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 1.5rem;
        min-height: 48px;
    }

    .badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .bg-gradient-primary {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%) !important;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .stats-card {
        border-radius: 16px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.1);
        border-color: rgba(102, 126, 234, 0.3);
    }

    .filter-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .student-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .student-email {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .enrollment-date {
        font-size: 0.8rem;
        color: #95a5a6;
    }

    .enrollment-date i {
        color: #667eea;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة التسجيلات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">التسجيلات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('courses.enrollments.progress-report', $course->id) }}" class="btn btn-primary">
                        <i class="fas fa-chart-line me-2"></i>تقرير التقدم
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Course Info Card -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-graduation-cap fs-3 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-0">{{ $course->title }}</h6>
                            <small class="text-muted">إدارة تسجيلات الطلاب في هذا الكورس</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-users fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">إجمالي المسجلين</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['total'] ?? 0 }}</h4>
                                    <span class="badge bg-primary-transparent">طالب</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-user-check fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">نشط</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['active'] ?? 0 }}</h4>
                                    <span class="badge bg-success-transparent">طالب نشط</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-trophy fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">مكتمل</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['completed'] ?? 0 }}</h4>
                                    <span class="badge bg-info-transparent">طالب مكتمل</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-pause-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">متوقف</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['suspended'] ?? 0 }}</h4>
                                    <span class="badge bg-warning-transparent">طالب متوقف</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <ul class="nav nav-tabs nav-tabs-header mb-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#enrollments-list" role="tab">
                                <i class="fas fa-list me-2"></i>قائمة المسجلين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#quick-enroll" role="tab">
                                <i class="fas fa-user-plus me-2"></i>تسجيل جديد
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        <!-- Enrollments List Tab -->
                        <div class="tab-pane fade show active" id="enrollments-list" role="tabpanel">

                            <!-- Filters -->
                            <form action="{{ route('courses.enrollments.index', $course->id) }}" method="GET" class="filter-section">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">بحث</label>
                                        <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">الحالة</label>
                                        <select name="status" class="form-select">
                                            <option value="">جميع الحالات</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>متوقف</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">الترتيب</label>
                                        <select name="sort" class="form-select">
                                            <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>الأحدث</option>
                                            <option value="progress" {{ request('sort') == 'progress' ? 'selected' : '' }}>حسب التقدم</option>
                                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>الاسم</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-secondary w-100">
                                            <i class="fas fa-search me-1"></i>بحث
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Enrollments Grid -->
                            <div class="row">
                                @forelse($enrollments as $enrollment)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="enrollment-card">
                                            <div class="enrollment-card-body">
                                                <!-- Header Section -->
                                                <div class="d-flex justify-content-between align-items-start mb-4">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        @if($enrollment->student->avatar)
                                                            <img src="{{ asset('storage/' . $enrollment->student->avatar) }}" alt="{{ $enrollment->student->name }}" class="student-avatar me-3">
                                                        @else
                                                            <div class="student-avatar-placeholder me-3">
                                                                {{ substr($enrollment->student->name, 0, 1) }}
                                                            </div>
                                                        @endif
                                                        <div class="flex-grow-1">
                                                            <h6 class="student-name mb-2">{{ $enrollment->student->name }}</h6>
                                                            <small class="student-email d-block">
                                                                <i class="fas fa-envelope me-1"></i>{{ $enrollment->student->email }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="progress-circle" style="--progress: {{ $enrollment->completion_percentage ?? 0 }}">
                                                        <div class="progress-circle-inner">
                                                            {{ number_format($enrollment->completion_percentage ?? 0, 0) }}%
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Status Badge -->
                                                <div class="mb-4">
                                                    <span class="badge
                                                        {{ $enrollment->enrollment_status == 'active' ? 'bg-success' : '' }}
                                                        {{ $enrollment->enrollment_status == 'completed' ? 'bg-primary' : '' }}
                                                        {{ $enrollment->enrollment_status == 'suspended' ? 'bg-warning' : '' }}
                                                        {{ $enrollment->enrollment_status == 'pending' ? 'bg-secondary' : '' }}
                                                        px-3 py-2" style="font-size: 0.9rem;">
                                                        @if($enrollment->enrollment_status == 'active')
                                                            <i class="fas fa-check-circle me-1"></i>نشط
                                                        @elseif($enrollment->enrollment_status == 'completed')
                                                            <i class="fas fa-trophy me-1"></i>مكتمل
                                                        @elseif($enrollment->enrollment_status == 'suspended')
                                                            <i class="fas fa-pause-circle me-1"></i>متوقف
                                                        @else
                                                            <i class="fas fa-clock me-1"></i>قيد الانتظار
                                                        @endif
                                                    </span>
                                                </div>

                                                <!-- Progress Bar -->
                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <small class="text-muted fw-semibold">التقدم</small>
                                                        <small class="text-muted">{{ number_format($enrollment->completion_percentage ?? 0, 1) }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 8px; border-radius: 10px;">
                                                        <div class="progress-bar bg-gradient-primary" role="progressbar" 
                                                             style="width: {{ $enrollment->completion_percentage ?? 0 }}%; border-radius: 10px;"
                                                             aria-valuenow="{{ $enrollment->completion_percentage ?? 0 }}" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Info Section -->
                                                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded">
                                                    <div class="text-center flex-fill">
                                                        <i class="fas fa-calendar-alt text-primary mb-2 d-block" style="font-size: 1.2rem;"></i>
                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">التسجيل</small>
                                                        <small class="fw-semibold d-block" style="font-size: 0.85rem;">{{ $enrollment->enrollment_date->format('Y-m-d') }}</small>
                                                    </div>
                                                    @if($enrollment->last_accessed_at)
                                                        <div class="text-center flex-fill border-start ps-3">
                                                            <i class="fas fa-clock text-info mb-2 d-block" style="font-size: 1.2rem;"></i>
                                                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">آخر زيارة</small>
                                                            <small class="fw-semibold d-block" style="font-size: 0.85rem;">{{ $enrollment->last_accessed_at->diffForHumans() }}</small>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="d-flex gap-2 mt-3">
                                                    <button type="button" class="btn btn-sm btn-primary flex-fill" onclick="viewProgress({{ $enrollment->id }})" style="border-radius: 10px; font-weight: 600;">
                                                        <i class="fas fa-chart-line me-1"></i>التقدم
                                                    </button>
                                                    <form action="{{ route('courses.enrollments.unenroll', $enrollment->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('هل أنت متأكد من إلغاء تسجيل هذا الطالب؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" style="border-radius: 10px; font-weight: 600;">
                                                            <i class="fas fa-user-times me-1"></i>إلغاء
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="fas fa-users fa-5x text-muted mb-4 opacity-25"></i>
                                        <h4 class="text-muted mb-3">لا يوجد طلاب مسجلين</h4>
                                        <p class="text-muted">ابدأ بتسجيل الطلاب في هذا الكورس</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Pagination -->
                            @if($enrollments->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $enrollments->appends(request()->query())->links() }}
                                </div>
                            @endif

                        </div>

                        <!-- Quick Enroll Tab -->
                        <div class="tab-pane fade" id="quick-enroll" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="enrollment-method-card">
                                        <div class="text-center">
                                            <div class="avatar bg-primary-transparent">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <h5>تسجيل فردي</h5>
                                            <p>تسجيل طالب واحد في الكورس</p>
                                            <a href="{{ route('courses.enrollments.create', $course->id) }}" class="btn btn-primary w-100">
                                                <i class="fas fa-user-plus me-2"></i>تسجيل طالب
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="enrollment-method-card">
                                        <div class="text-center">
                                            <div class="avatar bg-success-transparent">
                                                <i class="fas fa-user-check"></i>
                                            </div>
                                            <h5>اختيار متعدد</h5>
                                            <p>اختيار عدة طلاب من القائمة</p>
                                            <a href="{{ route('courses.enrollments.select-multiple', $course->id) }}" class="btn btn-success w-100">
                                                <i class="fas fa-user-check me-2"></i>اختيار طلاب
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="enrollment-method-card">
                                        <div class="text-center">
                                            <div class="avatar bg-info-transparent">
                                                <i class="fas fa-file-excel"></i>
                                            </div>
                                            <h5>تسجيل جماعي</h5>
                                            <p>رفع ملف Excel/CSV</p>
                                            <a href="{{ route('courses.enrollments.bulk', $course->id) }}" class="btn btn-info w-100">
                                                <i class="fas fa-file-excel me-2"></i>رفع ملف
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="enrollment-method-card">
                                        <div class="text-center">
                                            <div class="avatar bg-warning-transparent">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <h5>تسجيل مجموعة</h5>
                                            <p>تسجيل مجموعة كاملة دفعة واحدة</p>
                                            <a href="{{ route('courses.enrollments.group', $course->id) }}" class="btn btn-warning w-100">
                                                <i class="fas fa-users me-2"></i>اختيار مجموعة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    function viewProgress(enrollmentId) {
        // Redirect to progress page
        window.location.href = '{{ route("courses.enrollments.progress", ":id") }}'.replace(':id', enrollmentId);
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
