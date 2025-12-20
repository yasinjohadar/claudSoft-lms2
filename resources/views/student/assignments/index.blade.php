@extends('student.layouts.master')

@section('page-title')
    واجباتي
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
                    <h5 class="page-title fs-21 mb-1">واجباتي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">واجباتي</li>
                        </ol>
                    </nav>
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
                                    <p class="text-muted mb-0">إجمالي الواجبات</p>
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
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
                                    <p class="text-muted mb-0">تم التقييم</p>
                                    <h4 class="mb-0">{{ $stats['graded'] }}</h4>
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
                                    <p class="text-muted mb-0">في انتظار التقييم</p>
                                    <h4 class="mb-0">{{ $stats['submitted'] }}</h4>
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
                                    <h4 class="mb-0">{{ $stats['average_grade'] }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            @if($stats['pending'] > 0 || $stats['overdue'] > 0)
                <div class="row mb-4">
                    @if($stats['pending'] > 0)
                        <div class="col-md-6">
                            <div class="alert alert-primary d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                لديك <strong class="mx-1">{{ $stats['pending'] }}</strong> واجب بانتظار التسليم
                            </div>
                        </div>
                    @endif
                    @if($stats['overdue'] > 0)
                        <div class="col-md-6">
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                لديك <strong class="mx-1">{{ $stats['overdue'] }}</strong> واجب متأخر
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Course Statistics -->
            @if(count($courseStats) > 0)
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-bar me-2"></i>إحصائيات الواجبات حسب الكورس
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>الكورس</th>
                                        <th>إجمالي الواجبات</th>
                                        <th>تم التقييم</th>
                                        <th>في انتظار التقييم</th>
                                        <th>لم يُسلّم</th>
                                        <th>متوسط الدرجات</th>
                                        <th>النقاط المحققة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courseStats as $courseStat)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-book text-primary me-2"></i>
                                                    <strong>{{ $courseStat['course']->title }}</strong>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-transparent">
                                                    {{ $courseStat['total'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ $courseStat['graded'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    {{ $courseStat['submitted'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $courseStat['pending'] }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($courseStat['average_grade'] > 0)
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                            <div class="progress-bar {{ $courseStat['average_grade'] >= 60 ? 'bg-success' : 'bg-danger' }}"
                                                                 role="progressbar"
                                                                 style="width: {{ $courseStat['average_grade'] }}%">
                                                                {{ $courseStat['average_grade'] }}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-semibold">
                                                    {{ $courseStat['earned_points'] }} / {{ $courseStat['total_points'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <!-- Overall Total Row -->
                                    <tr class="table-active fw-bold">
                                        <td><i class="fas fa-calculator me-2"></i>المجموع الكلي</td>
                                        <td><span class="badge bg-primary">{{ $stats['total'] }}</span></td>
                                        <td><span class="badge bg-success">{{ $stats['graded'] }}</span></td>
                                        <td><span class="badge bg-warning">{{ $stats['submitted'] }}</span></td>
                                        <td><span class="badge bg-secondary">{{ $stats['pending'] }}</span></td>
                                        <td>
                                            @if($stats['average_grade'] > 0)
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                        <div class="progress-bar {{ $stats['average_grade'] >= 60 ? 'bg-success' : 'bg-danger' }}"
                                                             role="progressbar"
                                                             style="width: {{ $stats['average_grade'] }}%">
                                                            {{ $stats['average_grade'] }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">
                                                {{ $stats['earned_points'] }} / {{ $stats['total_points'] }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Assignments List -->
            @if(count($assignmentsData) > 0)
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-list me-2"></i>قائمة الواجبات
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>الواجب</th>
                                        <th>الكورس</th>
                                        <th>تاريخ التسليم</th>
                                        <th>الحالة</th>
                                        <th>الدرجة</th>
                                        <th>المحاولات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignmentsData as $data)
                                        <tr>
                                            <td>
                                                <div>
                                                    <a href="{{ route('student.assignments.show', $data['assignment']->id) }}" class="fw-semibold text-dark">
                                                        {{ $data['assignment']->title }}
                                                    </a>
                                                    @if($data['assignment']->lesson)
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-book-open me-1"></i>{{ $data['assignment']->lesson->title }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-transparent">
                                                    {{ $data['assignment']->course->title }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($data['assignment']->due_date)
                                                    <span class="{{ $data['status'] == 'overdue' ? 'text-danger fw-semibold' : '' }}">
                                                        {{ $data['assignment']->due_date->format('Y/m/d') }}
                                                        <br>
                                                        <small class="text-muted">{{ $data['assignment']->due_date->format('h:i A') }}</small>
                                                    </span>
                                                @else
                                                    <span class="text-muted">بدون موعد</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($data['status'])
                                                    @case('graded')
                                                        <span class="badge bg-success">تم التقييم</span>
                                                        @break
                                                    @case('submitted')
                                                        <span class="badge bg-warning">بانتظار التقييم</span>
                                                        @break
                                                    @case('overdue')
                                                        <span class="badge bg-danger">متأخر</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">لم يُسلّم</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($data['grade'] !== null)
                                                    <span class="fw-semibold {{ $data['grade'] >= ($data['assignment']->max_grade * 0.6) ? 'text-success' : 'text-danger' }}">
                                                        {{ $data['grade'] }} / {{ $data['assignment']->max_grade }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $data['submissions_count'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('student.assignments.show', $data['assignment']->id) }}"
                                                   class="btn btn-sm {{ $data['can_submit'] ? 'btn-primary' : 'btn-outline-primary' }}">
                                                    @if($data['can_submit'])
                                                        @if($data['submissions_count'] > 0)
                                                            <i class="fas fa-redo me-1"></i>إعادة التسليم
                                                        @else
                                                            <i class="fas fa-upload me-1"></i>تسليم
                                                        @endif
                                                    @else
                                                        <i class="fas fa-eye me-1"></i>عرض
                                                    @endif
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card custom-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-tasks fs-50 text-muted mb-3 opacity-25"></i>
                        <h5 class="mb-2">لا توجد واجبات متاحة</h5>
                        <p class="text-muted">لم يتم تعيين أي واجبات لك حتى الآن</p>
                        <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                            <i class="fas fa-book me-2"></i>تصفح الكورسات
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
