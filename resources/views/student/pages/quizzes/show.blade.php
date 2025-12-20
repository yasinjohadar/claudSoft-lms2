@extends('student.layouts.master')

@section('page-title')
    {{ $quiz->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active">تفاصيل الاختبار</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Quiz Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2 text-primary"></i>معلومات الاختبار
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary-transparent fs-13 me-2">
                                    <i class="fas fa-book me-1"></i>{{ $quiz->course->title }}
                                </span>
                                @if($quiz->quiz_type == 'practice')
                                    <span class="badge bg-info">تدريبي</span>
                                @elseif($quiz->quiz_type == 'graded')
                                    <span class="badge bg-warning">مُقيّم</span>
                                @elseif($quiz->quiz_type == 'final_exam')
                                    <span class="badge bg-danger">اختبار نهائي</span>
                                @endif
                            </div>

                            @if($quiz->description)
                                <div class="mb-3">
                                    <h6 class="mb-2">الوصف:</h6>
                                    <p class="text-muted">{{ $quiz->description }}</p>
                                </div>
                            @endif

                            @if($quiz->instructions)
                                <div class="alert alert-info">
                                    <h6 class="alert-heading mb-2">
                                        <i class="fas fa-exclamation-circle me-2"></i>تعليمات مهمة
                                    </h6>
                                    <p class="mb-0">{{ $quiz->instructions }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quiz Details -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-list me-2 text-success"></i>تفاصيل الاختبار
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md bg-primary-transparent me-3">
                                            <i class="fas fa-question fs-18"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted fs-12">عدد الأسئلة</p>
                                            <h5 class="mb-0">{{ $quiz->getQuestionCount() }} سؤال</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md bg-success-transparent me-3">
                                            <i class="fas fa-star fs-18"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted fs-12">الدرجة الكلية</p>
                                            <h5 class="mb-0">{{ number_format($quiz->max_score, 0) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md bg-warning-transparent me-3">
                                            <i class="fas fa-trophy fs-18"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted fs-12">درجة النجاح</p>
                                            <h5 class="mb-0">{{ $quiz->passing_grade }}%</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md bg-info-transparent me-3">
                                            <i class="fas fa-clock fs-18"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted fs-12">الوقت المحدد</p>
                                            <h5 class="mb-0">{{ $quiz->time_limit ? $quiz->time_limit . ' دقيقة' : 'غير محدد' }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Attempts -->
                    @if($attempts->count() > 0)
                        <div class="card custom-card mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-history me-2 text-purple"></i>محاولاتك السابقة ({{ $attempts->count() }})
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>المحاولة</th>
                                                <th>التاريخ</th>
                                                <th>الدرجة</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attempts as $attempt)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary-transparent">#{{ $attempt->attempt_number }}</span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $attempt->started_at->format('Y-m-d H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        @if($attempt->total_score !== null)
                                                            <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                                                {{ number_format($attempt->percentage_score, 1) }}%
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($attempt->status == 'in_progress')
                                                            <span class="badge bg-info">جاري</span>
                                                        @elseif($attempt->status == 'submitted')
                                                            <span class="badge bg-warning">مُسلّم</span>
                                                        @elseif($attempt->status == 'graded')
                                                            <span class="badge bg-success">مُصحح</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($attempt->status == 'in_progress')
                                                            <a href="{{ route('student.quizzes.take', $attempt->id) }}"
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-play me-1"></i>متابعة
                                                            </a>
                                                        @else
                                                            <a href="{{ route('student.quizzes.review.show', $attempt->id) }}"
                                                               class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye me-1"></i>مراجعة
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Start Quiz Card -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="card-title text-white mb-0">
                                <i class="fas fa-play-circle me-2"></i>بدء الاختبار
                            </div>
                        </div>
                        <div class="card-body">
                            @if($currentAttempt)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    لديك محاولة جارية، يمكنك متابعتها الآن
                                </div>
                                <a href="{{ route('student.quizzes.take', $currentAttempt->id) }}"
                                   class="btn btn-warning w-100">
                                    <i class="fas fa-play me-2"></i>متابعة المحاولة
                                </a>
                            @elseif($canAttempt)
                                @if($quiz->settings && $quiz->settings->requiresPassword())
                                    <form action="{{ route('student.quizzes.start', $quiz->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">كلمة مرور الاختبار:</label>
                                            <input type="password" name="quiz_password" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-lock-open me-2"></i>بدء الاختبار
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('student.quizzes.start', $quiz->id) }}" method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من بدء الاختبار؟')">
                                        @csrf
                                        <p class="text-muted mb-3">بمجرد البدء، سيتم بدء العداد التنازلي</p>
                                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                                            <i class="fas fa-play me-2"></i>بدء الاختبار الآن
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-ban me-2"></i>
                                    @if($remainingAttempts === 0)
                                        لقد استنفدت جميع المحاولات المسموحة
                                    @else
                                        الاختبار غير متاح حالياً
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Attempts Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-redo me-2 text-info"></i>المحاولات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">المحاولات المستخدمة:</span>
                                <strong>{{ $attempts->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">المحاولات المتبقية:</span>
                                <strong class="text-{{ $remainingAttempts > 0 ? 'success' : 'danger' }}">
                                    {{ $remainingAttempts ?? 'غير محدود' }}
                                </strong>
                            </div>
                            @if($quiz->attempts_allowed)
                                <div class="progress mt-3" style="height: 8px;">
                                    <div class="progress-bar bg-primary"
                                         style="width: {{ ($attempts->count() / $quiz->attempts_allowed) * 100 }}%">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Dates -->
                    @if($quiz->due_date || $quiz->available_from || $quiz->available_until)
                        <div class="card custom-card mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-calendar me-2 text-danger"></i>المواعيد المهمة
                                </div>
                            </div>
                            <div class="card-body">
                                @if($quiz->available_from)
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">متاح من:</small>
                                        <strong>{{ $quiz->available_from->format('Y-m-d H:i') }}</strong>
                                    </div>
                                @endif
                                @if($quiz->due_date)
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1">موعد الاستحقاق:</small>
                                        <strong class="text-{{ $quiz->due_date->isPast() ? 'danger' : 'warning' }}">
                                            {{ $quiz->due_date->format('Y-m-d H:i') }}
                                        </strong>
                                    </div>
                                @endif
                                @if($quiz->available_until)
                                    <div>
                                        <small class="text-muted d-block mb-1">متاح حتى:</small>
                                        <strong>{{ $quiz->available_until->format('Y-m-d H:i') }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Rules -->
                    <div class="card custom-card">
                        <div class="card-header bg-warning-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i>قواعد مهمة
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @if($quiz->time_limit)
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-danger me-2"></i>
                                        الوقت المحدد: {{ $quiz->time_limit }} دقيقة
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    درجة النجاح: {{ $quiz->passing_grade }}%
                                </li>
                                @if($quiz->shuffle_questions)
                                    <li class="mb-2">
                                        <i class="fas fa-random text-info me-2"></i>
                                        الأسئلة بترتيب عشوائي
                                    </li>
                                @endif
                                @if($quiz->allow_review)
                                    <li class="mb-2">
                                        <i class="fas fa-eye text-primary me-2"></i>
                                        يمكنك مراجعة إجاباتك بعد التسليم
                                    </li>
                                @endif
                                @if($quiz->show_correct_answers)
                                    <li class="mb-2">
                                        <i class="fas fa-lightbulb text-warning me-2"></i>
                                        سيتم عرض الإجابات الصحيحة
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
