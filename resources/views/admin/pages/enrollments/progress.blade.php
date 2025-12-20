@extends('admin.layouts.master')

@section('page-title')
    تفاصيل التقدم - {{ $enrollment->student->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            
            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        تفاصيل التقدم - {{ $enrollment->student->name }}
                    </h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $enrollment->course_id) }}">{{ $enrollment->course->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.enrollments.index', $enrollment->course_id) }}">التسجيلات</a></li>
                            <li class="breadcrumb-item active">تفاصيل التقدم</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('courses.enrollments.index', $enrollment->course_id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-2"></i>الرجوع
                    </a>
                </div>
            </div>

            <!-- Student Info Card -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                @if($enrollment->student->avatar)
                                    <img src="{{ asset('storage/' . $enrollment->student->avatar) }}" 
                                         alt="{{ $enrollment->student->name }}"
                                         class="rounded-circle me-3"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        {{ substr($enrollment->student->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="mb-1">{{ $enrollment->student->name }}</h4>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-envelope me-2"></i>{{ $enrollment->student->email }}
                                    </p>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-book me-2"></i>{{ $enrollment->course->title }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="mb-2">
                                <span class="badge bg-{{ $enrollment->enrollment_status == 'active' ? 'success' : ($enrollment->enrollment_status == 'completed' ? 'primary' : 'warning') }} fs-14 px-3 py-2">
                                    {{ $enrollment->enrollment_status == 'active' ? 'نشط' : ($enrollment->enrollment_status == 'completed' ? 'مكتمل' : 'معلق') }}
                                </span>
                            </div>
                            <div class="text-muted small">
                                <div><i class="fas fa-calendar me-1"></i>تاريخ التسجيل: {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : '-' }}</div>
                                @if($enrollment->last_accessed_at)
                                    <div class="mt-1"><i class="fas fa-clock me-1"></i>آخر زيارة: {{ $enrollment->last_accessed_at->diffForHumans() }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-chart-pie fa-3x text-primary"></i>
                            </div>
                            <h3 class="mb-1">{{ number_format($stats['completion_percentage'], 1) }}%</h3>
                            <p class="text-muted mb-3">نسبة الإنجاز</p>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ $stats['completion_percentage'] }}%"
                                     aria-valuenow="{{ $stats['completion_percentage'] }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['completed_modules'] }}/{{ $stats['total_modules'] }}</h3>
                            <p class="text-muted mb-0">الدروس المكتملة</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-star fa-3x text-warning"></i>
                            </div>
                            <h3 class="mb-1">{{ $stats['grade'] ? number_format($stats['grade'], 1) : '-' }}</h3>
                            <p class="text-muted mb-0">الدرجة النهائية</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sections Progress -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>التقدم حسب الأقسام
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($sectionsProgress as $sectionData)
                        <div class="mb-4 pb-4 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-folder me-2 text-primary"></i>
                                    {{ $sectionData['section']->title }}
                                </h6>
                                <span class="badge bg-primary">
                                    {{ number_format($sectionData['percentage'], 1) }}%
                                </span>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $sectionData['percentage'] }}%"
                                     aria-valuenow="{{ $sectionData['percentage'] }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $sectionData['completed_modules'] }} من {{ $sectionData['total_modules'] }} درس مكتمل
                            </small>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p>لا توجد أقسام في هذا الكورس</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Completions -->
            @if($recentCompletions->count() > 0)
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>آخر الإنجازات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($recentCompletions as $completion)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            {{ $completion->module->title ?? 'درس محذوف' }}
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $completion->completed_at ? $completion->completed_at->diffForHumans() : '-' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>مكتمل
                                    </span>
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

