@extends('student.layouts.master')

@section('page-title')
    تقدمي - {{ $course->title }}
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
                    <h5 class="page-title fs-21 mb-1">تقرير التقدم - {{ $course->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.progress.overview') }}">تقدمي في الكورسات</a></li>
                            <li class="breadcrumb-item active">{{ $course->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    @if($stats['can_get_certificate'])
                        <a href="{{ route('student.progress.certificate', $course->id) }}"
                           class="btn btn-success">
                            <i class="fas fa-certificate me-1"></i>تحميل الشهادة
                        </a>
                    @endif
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-primary-transparent me-3">
                                    <i class="fas fa-tasks fs-20 text-primary"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">إجمالي المحتوى</p>
                                    <h4 class="mb-0">{{ $stats['total_modules'] }}</h4>
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
                                    <p class="text-muted mb-0">تم الإكمال</p>
                                    <h4 class="mb-0">{{ $stats['completed_modules'] }}</h4>
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
                                    <i class="fas fa-clock fs-20 text-warning"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-0">الوقت المستغرق</p>
                                    <h4 class="mb-0">{{ $stats['time_spent'] }} دقيقة</h4>
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
                                    <p class="text-muted mb-0">متوسط الدرجات</p>
                                    <h4 class="mb-0">{{ number_format($stats['average_score'], 1) }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overall Progress -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-chart-line me-2"></i>التقدم الإجمالي
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold">نسبة الإكمال</span>
                        <span class="fw-semibold text-primary fs-18">{{ number_format($stats['completion_percentage'], 1) }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-primary"
                             role="progressbar"
                             style="width: {{ $stats['completion_percentage'] }}%"
                             aria-valuenow="{{ $stats['completion_percentage'] }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ number_format($stats['completion_percentage'], 1) }}%
                        </div>
                    </div>
                    @if($stats['can_get_certificate'])
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="fas fa-trophy me-2 fs-20"></i>
                            <div>
                                <strong>مبروك!</strong> أنت مؤهل للحصول على شهادة إتمام هذا الكورس
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sections Progress -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-list me-2"></i>التقدم حسب الأقسام
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>القسم</th>
                                    <th>المحتوى المكتمل</th>
                                    <th>نسبة التقدم</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectionsProgress as $sectionData)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-folder text-primary me-2"></i>
                                                <strong>{{ $sectionData['section']->title }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $sectionData['completed_modules'] }} / {{ $sectionData['total_modules'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px; min-width: 150px;">
                                                <div class="progress-bar {{ $sectionData['percentage'] == 100 ? 'bg-success' : 'bg-primary' }}"
                                                     role="progressbar"
                                                     style="width: {{ $sectionData['percentage'] }}%">
                                                    {{ number_format($sectionData['percentage'], 0) }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($sectionData['percentage'] == 100)
                                                <span class="badge bg-success">مكتمل</span>
                                            @elseif($sectionData['percentage'] > 0)
                                                <span class="badge bg-warning">قيد التقدم</span>
                                            @else
                                                <span class="badge bg-secondary">لم يبدأ</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Completions -->
            @if(count($recentCompletions) > 0)
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-history me-2"></i>آخر الإنجازات
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($recentCompletions as $completion)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-icon bg-success-transparent me-3">
                                            <i class="fas fa-check text-success"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $completion->module->title }}</h6>
                                            <p class="text-muted mb-0 fs-12">
                                                <i class="fas fa-clock me-1"></i>{{ $completion->completed_at->diffForHumans() }}
                                                @if($completion->score)
                                                    <span class="ms-2">
                                                        <i class="fas fa-star text-warning me-1"></i>الدرجة: {{ $completion->score }}%
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
