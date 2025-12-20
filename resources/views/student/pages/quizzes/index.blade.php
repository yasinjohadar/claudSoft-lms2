@extends('student.layouts.master')

@section('page-title')
    الاختبارات المتاحة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">الاختبارات المتاحة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الاختبارات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.quizzes.review.index') }}" class="btn btn-secondary">
                        <i class="fas fa-history me-2"></i>محاولاتي السابقة
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.quizzes.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">اختر الكورس</label>
                                <select name="course_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع الكورسات</option>
                                    @if(isset($courses))
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->title }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">نوع الاختبار</label>
                                <select name="quiz_type" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="practice" {{ request('quiz_type') == 'practice' ? 'selected' : '' }}>تدريبي</option>
                                    <option value="graded" {{ request('quiz_type') == 'graded' ? 'selected' : '' }}>مُقيّم</option>
                                    <option value="final_exam" {{ request('quiz_type') == 'final_exam' ? 'selected' : '' }}>اختبار نهائي</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quizzes List -->
            @if($quizzes->count() > 0)
                <div class="row">
                    @foreach($quizzes as $quiz)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                            <div class="card custom-card">
                                <div class="card-header border-bottom-0 pb-0">
                                    <div class="d-flex justify-content-between align-items-start w-100">
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-2">
                                                <a href="{{ route('student.quizzes.show', $quiz->id) }}" class="text-dark">
                                                    {{ $quiz->title }}
                                                </a>
                                            </h6>
                                            <div class="mb-2">
                                                <span class="badge bg-primary-transparent">
                                                    <i class="fas fa-book me-1"></i>{{ $quiz->course->title }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            @if($quiz->quiz_type == 'practice')
                                                <span class="badge bg-info">تدريبي</span>
                                            @elseif($quiz->quiz_type == 'graded')
                                                <span class="badge bg-warning">مُقيّم</span>
                                            @elseif($quiz->quiz_type == 'final_exam')
                                                <span class="badge bg-danger">نهائي</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($quiz->description)
                                        <p class="text-muted fs-13 mb-3">{{ Str::limit($quiz->description, 100) }}</p>
                                    @endif

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary-transparent me-2">
                                                    <i class="fas fa-question fs-14"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">الأسئلة</small>
                                                    <strong>{{ $quiz->getQuestionCount() }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-success-transparent me-2">
                                                    <i class="fas fa-star fs-14"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">الدرجة</small>
                                                    <strong>{{ number_format($quiz->max_score, 0) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-warning-transparent me-2">
                                                    <i class="fas fa-clock fs-14"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">الوقت</small>
                                                    <strong>{{ $quiz->time_limit ? $quiz->time_limit . ' دقيقة' : 'مفتوح' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-info-transparent me-2">
                                                    <i class="fas fa-redo fs-14"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">المحاولات</small>
                                                    <strong>{{ $quiz->remaining_attempts ?? 'غير محدود' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($quiz->due_date)
                                        <div class="alert alert-{{ $quiz->due_date->isPast() ? 'danger' : 'warning' }} py-2 mb-3">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            <small>
                                                @if($quiz->due_date->isPast())
                                                    انتهى في: {{ $quiz->due_date->format('Y-m-d H:i') }}
                                                @else
                                                    ينتهي في: {{ $quiz->due_date->format('Y-m-d H:i') }}
                                                @endif
                                            </small>
                                        </div>
                                    @endif

                                    @if($quiz->student_attempts_count > 0)
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">المحاولات: {{ $quiz->student_attempts_count }}</small>
                                                @if($quiz->best_attempt)
                                                    <span class="badge bg-{{ $quiz->best_attempt->passed ? 'success' : 'danger' }}">
                                                        أفضل درجة: {{ number_format($quiz->best_attempt->percentage_score, 1) }}%
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $quiz->best_attempt && $quiz->best_attempt->passed ? 'success' : 'warning' }}"
                                                     style="width: {{ $quiz->best_attempt ? $quiz->best_attempt->percentage_score : 0 }}%">
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-grid gap-2">
                                        @if($quiz->can_attempt)
                                            <a href="{{ route('student.quizzes.show', $quiz->id) }}" class="btn btn-primary">
                                                <i class="fas fa-play me-2"></i>
                                                {{ $quiz->student_attempts_count > 0 ? 'محاولة جديدة' : 'بدء الاختبار' }}
                                            </a>
                                        @else
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-lock me-2"></i>غير متاح
                                            </button>
                                        @endif

                                        @if($quiz->student_attempts_count > 0)
                                            <a href="{{ route('student.quizzes.review.history', $quiz->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-history me-1"></i>عرض المحاولات السابقة
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($quizzes->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $quizzes->links() }}
                    </div>
                @endif
            @else
                <div class="card custom-card">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-file-alt fs-48 text-muted"></i>
                        </div>
                        <h5 class="text-muted mb-3">لا توجد اختبارات متاحة حالياً</h5>
                        <p class="text-muted">سيتم إضافة الاختبارات من قبل المدرسين قريباً</p>
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop
