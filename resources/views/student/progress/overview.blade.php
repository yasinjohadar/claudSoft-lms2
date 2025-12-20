@extends('student.layouts.master')

@section('page-title')
    تقدمي في الكورسات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تقدمي في الكورسات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">تقدمي في الكورسات</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Overall Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-primary-transparent me-3">
                                    <i class="fas fa-book fs-20 text-primary"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">إجمالي الكورسات</p>
                                    <h4 class="mb-0">{{ $stats['total_courses'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-success-transparent me-3">
                                    <i class="fas fa-check-circle fs-20 text-success"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">كورسات مكتملة</p>
                                    <h4 class="mb-0">{{ $stats['completed_courses'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-warning-transparent me-3">
                                    <i class="fas fa-play-circle fs-20 text-warning"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">كورسات نشطة</p>
                                    <h4 class="mb-0">{{ $stats['active_courses'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-info-transparent me-3">
                                    <i class="fas fa-percentage fs-20 text-info"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">متوسط التقدم</p>
                                    <h4 class="mb-0">{{ number_format($stats['average_progress'], 1) }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses Progress List -->
            @if(count($coursesProgress) > 0)
                <div class="row">
                    @foreach($coursesProgress as $progress)
                        <div class="col-xl-6 col-lg-12">
                            <div class="card custom-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="flex-grow-1">
                                            <h6 class="fw-semibold mb-1">
                                                <a href="{{ route('student.courses.show', $progress['course']->id) }}"
                                                   class="text-dark">
                                                    {{ $progress['course']->title }}
                                                </a>
                                            </h6>
                                            <p class="text-muted mb-2 fs-12">
                                                <i class="fas fa-user me-1"></i>{{ $progress['course']->instructor->name ?? 'غير محدد' }}
                                            </p>
                                        </div>
                                        <div>
                                            @switch($progress['status'])
                                                @case('completed')
                                                    <span class="badge bg-success">مكتمل</span>
                                                    @break
                                                @case('active')
                                                    <span class="badge bg-primary">نشط</span>
                                                    @break
                                                @case('suspended')
                                                    <span class="badge bg-warning">معلق</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $progress['status'] }}</span>
                                            @endswitch
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted fs-12">نسبة الإكمال</span>
                                            <span class="fw-semibold">{{ number_format($progress['completion_percentage'], 1) }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary"
                                                 role="progressbar"
                                                 style="width: {{ $progress['completion_percentage'] }}%"
                                                 aria-valuenow="{{ $progress['completion_percentage'] }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Course Info -->
                                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                        <div class="text-muted fs-12">
                                            @if($progress['last_accessed'])
                                                <i class="fas fa-clock me-1"></i>
                                                آخر دخول: {{ $progress['last_accessed']->diffForHumans() }}
                                            @else
                                                <i class="fas fa-info-circle me-1"></i>
                                                لم يتم الدخول بعد
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('student.progress.show', $progress['course']->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-chart-line me-1"></i>عرض التفاصيل
                                            </a>
                                            @if($progress['enrollment']->certificate_issued)
                                                <a href="{{ route('student.progress.certificate', $progress['course']->id) }}"
                                                   class="btn btn-sm btn-success ms-1">
                                                    <i class="fas fa-certificate me-1"></i>الشهادة
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card custom-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book-open fs-50 text-muted mb-3 opacity-25"></i>
                        <h5 class="mb-2">لم تسجل في أي كورس بعد</h5>
                        <p class="text-muted">ابدأ رحلتك التعليمية بالتسجيل في كورس</p>
                        <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>تصفح الكورسات
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop

@section('script')
<script>
    // Add any additional JavaScript if needed
</script>
@stop
