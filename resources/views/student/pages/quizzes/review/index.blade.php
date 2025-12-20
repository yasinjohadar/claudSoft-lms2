@extends('student.layouts.master')

@section('page-title')
    مراجعة الاختبارات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">مراجعة الاختبارات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active">مراجعة المحاولات</li>
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
                                    <i class="fas fa-clipboard-list fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">إجمالي المحاولات</p>
                                    <h4 class="mb-0 fw-bold">{{ $stats['total_attempts'] ?? 0 }}</h4>
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
                                    <i class="fas fa-check-circle fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">النجاحات</p>
                                    <h4 class="mb-0 fw-bold">{{ $stats['passed'] ?? 0 }}</h4>
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
                                    <i class="fas fa-star fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">متوسط الدرجات</p>
                                    <h4 class="mb-0 fw-bold">{{ number_format($stats['average_score'] ?? 0, 1) }}%</h4>
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
                                    <i class="fas fa-graduation-cap fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">أعلى درجة</p>
                                    <h4 class="mb-0 fw-bold">{{ number_format($stats['highest_score'] ?? 0, 1) }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-filter me-2"></i>الفلاتر
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('student.quizzes.review.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>ناجح</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>راسب</option>
                                    <option value="graded" {{ request('status') == 'graded' ? 'selected' : '' }}>مُصحح</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">نوع الاختبار</label>
                                <select name="quiz_type" class="form-select">
                                    <option value="">جميع الأنواع</option>
                                    <option value="practice" {{ request('quiz_type') == 'practice' ? 'selected' : '' }}>تدريبي</option>
                                    <option value="graded" {{ request('quiz_type') == 'graded' ? 'selected' : '' }}>مُقيّم</option>
                                    <option value="final_exam" {{ request('quiz_type') == 'final_exam' ? 'selected' : '' }}>نهائي</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>بحث
                                </button>
                                <a href="{{ route('student.quizzes.review.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo me-1"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attempts List -->
            @if($attempts->count() > 0)
                <div class="row">
                    @foreach($attempts as $attempt)
                        <div class="col-lg-6 col-md-12 mb-4">
                            <div class="card custom-card h-100">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">
                                                <a href="{{ route('student.quizzes.review.show', $attempt->id) }}" class="text-primary">
                                                    {{ $attempt->quiz->title }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-book me-1"></i>{{ $attempt->quiz->course->title }}
                                            </small>
                                        </div>
                                        <div>
                                            @if($attempt->quiz->quiz_type == 'practice')
                                                <span class="badge bg-info">تدريبي</span>
                                            @elseif($attempt->quiz->quiz_type == 'graded')
                                                <span class="badge bg-warning">مُقيّم</span>
                                            @elseif($attempt->quiz->quiz_type == 'final_exam')
                                                <span class="badge bg-danger">نهائي</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Attempt Info -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <p class="mb-0 text-muted fs-12">المحاولة</p>
                                            <p class="mb-0 fw-semibold">#{{ $attempt->attempt_number }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-0 text-muted fs-12">التاريخ</p>
                                            <p class="mb-0 fw-semibold">{{ $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d') : '-' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-0 text-muted fs-12">الوقت المستغرق</p>
                                            <p class="mb-0 fw-semibold">{{ $attempt->getTimeSpentHumanReadable() }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-0 text-muted fs-12">الحالة</p>
                                            <p class="mb-0">
                                                @if($attempt->status == 'graded')
                                                    <span class="badge bg-success">مُصحح</span>
                                                @elseif($attempt->status == 'submitted')
                                                    <span class="badge bg-warning">مُسلّم</span>
                                                @else
                                                    <span class="badge bg-info">جاري</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Score Display -->
                                    @if($attempt->total_score !== null)
                                        <div class="alert alert-{{ $attempt->passed ? 'success' : 'danger' }} mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0">
                                                        <i class="fas fa-{{ $attempt->passed ? 'check-circle' : 'times-circle' }} me-2"></i>
                                                        {{ $attempt->passed ? 'ناجح' : 'راسب' }}
                                                    </h5>
                                                </div>
                                                <div class="text-end">
                                                    <h4 class="mb-0">{{ number_format($attempt->percentage_score, 1) }}%</h4>
                                                    <small>{{ number_format($attempt->total_score, 1) }}/{{ $attempt->max_score }}</small>
                                                </div>
                                            </div>
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $attempt->passed ? 'success' : 'danger' }}"
                                                     style="width: {{ $attempt->percentage_score }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-3">
                                            <i class="fas fa-clock me-2"></i>قيد التصحيح...
                                        </div>
                                    @endif

                                    <!-- Stats -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <div class="text-center p-2 bg-success-transparent rounded">
                                                <i class="fas fa-check text-success fs-18"></i>
                                                <p class="mb-0 mt-1 fw-semibold">{{ $attempt->responses()->where('is_correct', true)->count() }}</p>
                                                <small class="text-muted">صحيح</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center p-2 bg-danger-transparent rounded">
                                                <i class="fas fa-times text-danger fs-18"></i>
                                                <p class="mb-0 mt-1 fw-semibold">{{ $attempt->responses()->where('is_correct', false)->count() }}</p>
                                                <small class="text-muted">خطأ</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center p-2 bg-info-transparent rounded">
                                                <i class="fas fa-question text-info fs-18"></i>
                                                <p class="mb-0 mt-1 fw-semibold">{{ $attempt->responses()->count() }}</p>
                                                <small class="text-muted">الإجمالي</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Feedback -->
                                    @if($attempt->feedback)
                                        <div class="alert alert-info mb-3">
                                            <h6 class="alert-heading fs-13">
                                                <i class="fas fa-comment me-1"></i>ملاحظات المدرس
                                            </h6>
                                            <p class="mb-0 fs-12">{{ Str::limit($attempt->feedback, 100) }}</p>
                                        </div>
                                    @endif

                                    <!-- Actions -->
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('student.quizzes.review.show', $attempt->id) }}"
                                           class="btn btn-primary flex-fill">
                                            <i class="fas fa-eye me-1"></i>مراجعة المحاولة
                                        </a>
                                        <a href="{{ route('student.quizzes.review.download-report', $attempt->id) }}"
                                           class="btn btn-secondary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $attempt->created_at->diffForHumans() }}
                                        </small>
                                        @if($attempt->completed_at)
                                            <span class="badge bg-success-transparent">
                                                <i class="fas fa-check-circle me-1"></i>تم الإنجاز ✅
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                عرض {{ $attempts->firstItem() }} إلى {{ $attempts->lastItem() }} من {{ $attempts->total() }} محاولة
                            </div>
                            <div>
                                {{ $attempts->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card custom-card">
                    <div class="card-body text-center py-5">
                        <div class="avatar avatar-xl bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-clipboard-list fs-40"></i>
                        </div>
                        <h5 class="mb-2">لا توجد محاولات لمراجعتها</h5>
                        <p class="text-muted mb-3">ابدأ بحل الاختبارات لتتمكن من مراجعة محاولاتك هنا</p>
                        <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary">
                            <i class="fas fa-clipboard-list me-2"></i>تصفح الاختبارات
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop
