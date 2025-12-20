@extends('student.layouts.master')

@section('page-title')
    كورساتي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">كورساتي التعليمية</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">كورساتي</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center">
                <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>تصفح الكورسات
                </a>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-primary-transparent">
                                    <i class="fas fa-book fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0">إجمالي الكورسات</h6>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $stats['total_courses'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-success-transparent">
                                    <i class="fas fa-play fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0">كورسات نشطة</h6>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $stats['active_courses'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-info-transparent">
                                    <i class="fas fa-check-circle fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0">كورسات مكتملة</h6>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ $stats['completed_courses'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-lg bg-warning-transparent">
                                    <i class="fas fa-chart-line fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="mb-0">متوسط التقدم</h6>
                                </div>
                                <div>
                                    <h3 class="mb-0">{{ number_format($stats['average_progress'], 0) }}%</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="card custom-card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('status') || request('status') == 'all' ? 'active' : '' }}"
                           href="{{ route('student.courses.my-courses') }}">
                            <i class="fas fa-th-large me-2"></i>الكل
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'active' ? 'active' : '' }}"
                           href="{{ route('student.courses.my-courses', ['status' => 'active']) }}">
                            <i class="fas fa-play me-2"></i>قيد الدراسة
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'completed' ? 'active' : '' }}"
                           href="{{ route('student.courses.my-courses', ['status' => 'completed']) }}">
                            <i class="fas fa-check-circle me-2"></i>مكتملة
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'suspended' ? 'active' : '' }}"
                           href="{{ route('student.courses.my-courses', ['status' => 'suspended']) }}">
                            <i class="fas fa-pause me-2"></i>متوقفة
                        </a>
                    </li>
                </ul>

                <!-- Courses Grid -->
                <div class="row">
                    @forelse($enrollments as $enrollment)
                        @php
                            $course = $enrollment->course;
                            $progress = $enrollment->completion_percentage ?? 0;
                            $courseId = $enrollment->course_id;
                        @endphp

                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                            <div class="card custom-card">
                                <div class="position-relative">
                                    @if($course && $course->thumbnail)
                                        <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                             alt="{{ $course->title }}"
                                             class="card-img-top"
                                             style="height: 200px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top bg-primary-gradient d-flex align-items-center justify-content-center"
                                             style="height: 200px;">
                                            <i class="fas fa-graduation-cap fa-4x text-white opacity-50"></i>
                                        </div>
                                    @endif

                                    <!-- Status Badge -->
                                    @if($enrollment->enrollment_status == 'active')
                                        <span class="badge bg-success position-absolute top-0 end-0 m-3">
                                            <i class="fas fa-play me-1"></i>قيد الدراسة
                                        </span>
                                    @elseif($enrollment->enrollment_status == 'completed')
                                        <span class="badge bg-primary position-absolute top-0 end-0 m-3">
                                            <i class="fas fa-check-circle me-1"></i>مكتمل
                                        </span>
                                    @elseif($enrollment->enrollment_status == 'suspended')
                                        <span class="badge bg-warning position-absolute top-0 end-0 m-3">
                                            <i class="fas fa-pause me-1"></i>متوقف
                                        </span>
                                    @endif
                                </div>

                                <div class="card-body">
                                    <!-- Category -->
                                    @if($course && $course->category)
                                        <span class="badge bg-light text-dark mb-2">
                                            {{ $course->category->name }}
                                        </span>
                                    @endif

                                    <!-- Title -->
                                    <h5 class="card-title fw-semibold mb-2">
                                        <a href="{{ route('student.courses.show', $courseId) }}"
                                           class="text-dark text-decoration-none">
                                            {{ $course ? $course->title : 'كورس غير متوفر' }}
                                        </a>
                                    </h5>

                                    <!-- Progress -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">نسبة الإنجاز</small>
                                            <small class="fw-semibold">{{ number_format($progress, 0) }}%</small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary"
                                                 role="progressbar"
                                                 style="width: {{ $progress }}%"
                                                 aria-valuenow="{{ $progress }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Stats -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <div class="fw-semibold text-primary">{{ $course ? $course->sections()->count() : 0 }}</div>
                                                <small class="text-muted">أقسام</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center p-2 bg-light rounded">
                                                <div class="fw-semibold text-success">{{ $course ? $course->modules()->count() : 0 }}</div>
                                                <small class="text-muted">دروس</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Info -->
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ $enrollment->enrollment_date->format('Y-m-d') }}
                                    </div>

                                    @if($enrollment->last_accessed)
                                        <div class="small text-muted mb-3">
                                            <i class="fas fa-clock me-1"></i>
                                            آخر دخول: {{ $enrollment->last_accessed->diffForHumans() }}
                                        </div>
                                    @endif

                                    <!-- Actions -->
                                    <div class="d-grid gap-2">
                                        @if($enrollment->enrollment_status == 'completed')
                                            <a href="{{ route('student.courses.show', $courseId) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-2"></i>مراجعة الكورس
                                            </a>
                                            @if($course && $course->certificate_enabled)
                                                <a href="{{ route('student.progress.certificate', $courseId) }}"
                                                   class="btn btn-warning btn-sm"
                                                   target="_blank">
                                                    <i class="fas fa-certificate me-2"></i>تحميل الشهادة
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('student.courses.show', $courseId) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-play me-2"></i>متابعة التعلم
                                            </a>
                                        @endif

                                        <a href="{{ route('student.progress.show', $courseId) }}"
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-chart-line me-2"></i>التقدم التفصيلي
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <div class="col-12">
                            <div class="card custom-card">
                                <div class="card-body text-center py-5">
                                    <div class="mb-4">
                                        <i class="fas fa-book-reader display-1 text-muted opacity-25"></i>
                                    </div>
                                    <h4 class="mb-3">لا توجد كورسات مسجلة</h4>
                                    <p class="text-muted mb-4">ابدأ رحلتك التعليمية الآن واستكشف الكورسات المتاحة!</p>
                                    <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>تصفح الكورسات المتاحة
                                    </a>
                                </div>
                            </div>
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
        </div>

    </div>
</div>
@stop

@section('script')
<script>
    // Auto hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop
