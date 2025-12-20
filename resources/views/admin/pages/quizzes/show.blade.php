@extends('admin.layouts.master')

@section('page-title')
    عرض الاختبار
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $quiz->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active">عرض الاختبار</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('quizzes.edit', $quiz->id) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-1"></i>تعديل
                    </a>
                    <a href="{{ route('grading.index', ['quiz_id' => $quiz->id]) }}" class="btn btn-info">
                        <i class="fas fa-pen me-1"></i>التصحيح
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Quiz Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2 text-primary"></i>معلومات الاختبار
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>الكورس:</strong></p>
                                    <span class="badge bg-primary-transparent fs-13">
                                        <i class="fas fa-book me-1"></i>{{ $quiz->course->title }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>الدرس:</strong></p>
                                    @if($quiz->lesson)
                                        <span class="badge bg-info-transparent fs-13">
                                            <i class="fas fa-bookmark me-1"></i>{{ $quiz->lesson->title }}
                                        </span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>نوع الاختبار:</strong></p>
                                    @if($quiz->quiz_type == 'practice')
                                        <span class="badge bg-info">تدريبي</span>
                                    @elseif($quiz->quiz_type == 'graded')
                                        <span class="badge bg-warning">مُقيّم</span>
                                    @elseif($quiz->quiz_type == 'final_exam')
                                        <span class="badge bg-danger">اختبار نهائي</span>
                                    @else
                                        <span class="badge bg-secondary">استبيان</span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>الحالة:</strong></p>
                                    @if($quiz->is_published)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>منشور
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-file me-1"></i>مسودة
                                        </span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><strong>الظهور:</strong></p>
                                    @if($quiz->is_visible)
                                        <span class="badge bg-success-transparent">ظاهر</span>
                                    @else
                                        <span class="badge bg-secondary-transparent">مخفي</span>
                                    @endif
                                </div>
                            </div>

                            @if($quiz->description)
                                <div class="mb-3">
                                    <p class="mb-2"><strong>الوصف:</strong></p>
                                    <p class="text-muted">{{ $quiz->description }}</p>
                                </div>
                            @endif

                            @if($quiz->instructions)
                                <div>
                                    <p class="mb-2"><strong>التعليمات:</strong></p>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>{{ $quiz->instructions }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-cog me-2 text-info"></i>الإعدادات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md bg-primary-transparent me-3">
                                            <i class="fas fa-question fs-18"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted fs-12">عدد الأسئلة</p>
                                            <h5 class="mb-0">{{ $quiz->getQuestionCount() }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md bg-success-transparent me-3">
                                            <i class="fas fa-star fs-18"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted fs-12">الدرجة القصوى</p>
                                            <h5 class="mb-0">{{ number_format($quiz->max_score, 1) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
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
                                <div class="col-md-6 mb-3">
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

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-{{ $quiz->shuffle_questions ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            ترتيب الأسئلة عشوائياً
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-{{ $quiz->shuffle_answers ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            ترتيب الخيارات عشوائياً
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-{{ $quiz->show_correct_answers ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            عرض الإجابات الصحيحة
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-{{ $quiz->allow_review ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            السماح بالمراجعة
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-{{ $quiz->show_grade_immediately ? 'check text-success' : 'times text-danger' }} me-2"></i>
                                            عرض الدرجة فوراً
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-redo me-2"></i>
                                            المحاولات: {{ $quiz->attempts_allowed ?? 'غير محدود' }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                <i class="fas fa-list me-2 text-secondary"></i>الأسئلة ({{ $quiz->quizQuestions->count() }})
                            </div>
                            <a href="{{ route('question-bank.index', ['course_id' => $quiz->course_id]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>إضافة أسئلة
                            </a>
                        </div>
                        <div class="card-body p-0">
                            @if($quiz->quizQuestions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="50%">السؤال</th>
                                                <th width="15%">النوع</th>
                                                <th width="10%">الدرجة</th>
                                                <th width="10%">الترتيب</th>
                                                <th width="10%">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quiz->quizQuestions as $quizQuestion)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 400px;">
                                                            {{ $quizQuestion->question->question_text }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-transparent">
                                                            {{ $quizQuestion->question->questionType->display_name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">{{ $quizQuestion->max_score }}</span>
                                                    </td>
                                                    <td>{{ $quizQuestion->question_order }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-info" title="معاينة">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-question-circle fs-48 text-muted mb-3"></i>
                                    <p class="text-muted">لم يتم إضافة أسئلة بعد</p>
                                    <a href="{{ route('question-bank.index') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>إضافة أسئلة
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Attempts List -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-users me-2 text-purple"></i>المحاولات ({{ $attempts->total() }})
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($attempts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>الطالب</th>
                                                <th>المحاولة</th>
                                                <th>التاريخ</th>
                                                <th>الدرجة</th>
                                                <th>الوقت</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attempts as $attempt)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm bg-primary-transparent me-2">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <span>{{ $attempt->student->name }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary-transparent">
                                                            #{{ $attempt->attempt_number }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ $attempt->started_at->format('Y-m-d H:i') }}
                                                        </small>
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
                                                        <small>{{ $attempt->getTimeSpentHumanReadable() }}</small>
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
                                                        <a href="{{ route('grading.show', $attempt->id) }}"
                                                           class="btn btn-sm btn-primary" title="التصحيح">
                                                            <i class="fas fa-pen"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($attempts->hasPages())
                                    <div class="card-footer">
                                        {{ $attempts->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fs-48 text-muted mb-3"></i>
                                    <p class="text-muted">لا توجد محاولات بعد</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Statistics -->
                <div class="col-lg-4">
                    <!-- Statistics Card -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-chart-bar me-2 text-success"></i>الإحصائيات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">إجمالي المحاولات</span>
                                    <span class="fw-bold">{{ $stats['total_attempts'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: 100%"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">المحاولات المكتملة</span>
                                    <span class="fw-bold">{{ $stats['completed_attempts'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ $stats['total_attempts'] > 0 ? ($stats['completed_attempts'] / $stats['total_attempts']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">قيد التقدم</span>
                                    <span class="fw-bold">{{ $stats['in_progress'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info"
                                         style="width: {{ $stats['total_attempts'] > 0 ? ($stats['in_progress'] / $stats['total_attempts']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">مُصححة</span>
                                    <span class="fw-bold">{{ $stats['graded'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning"
                                         style="width: {{ $stats['total_attempts'] > 0 ? ($stats['graded'] / $stats['total_attempts']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">بانتظار التصحيح</span>
                                    <span class="fw-bold text-danger">{{ $stats['pending_grading'] }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger"
                                         style="width: {{ $stats['total_attempts'] > 0 ? ($stats['pending_grading'] / $stats['total_attempts']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="text-center mb-3">
                                <p class="text-muted mb-1">متوسط الدرجات</p>
                                <h3 class="mb-0 text-primary">
                                    {{ $stats['average_score'] ? number_format($stats['average_score'], 1) . '%' : '-' }}
                                </h3>
                            </div>

                            <div class="text-center">
                                <p class="text-muted mb-1">معدل النجاح</p>
                                <h3 class="mb-0 text-success">
                                    {{ number_format($stats['pass_rate'], 1) }}%
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Dates Card -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-calendar me-2 text-danger"></i>المواعيد
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1 text-muted fs-12">متاح من:</p>
                                <p class="mb-0">{{ $quiz->available_from ? $quiz->available_from->format('Y-m-d H:i') : 'غير محدد' }}</p>
                            </div>
                            <div class="mb-3">
                                <p class="mb-1 text-muted fs-12">موعد الاستحقاق:</p>
                                <p class="mb-0">{{ $quiz->due_date ? $quiz->due_date->format('Y-m-d H:i') : 'غير محدد' }}</p>
                            </div>
                            <div>
                                <p class="mb-1 text-muted fs-12">متاح حتى:</p>
                                <p class="mb-0">{{ $quiz->available_until ? $quiz->available_until->format('Y-m-d H:i') : 'غير محدد' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-bolt me-2 text-warning"></i>إجراءات سريعة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('quiz-analytics.quiz', $quiz->id) }}" class="btn btn-outline-info">
                                    <i class="fas fa-chart-line me-2"></i>عرض التحليلات
                                </a>
                                <form action="{{ route('quizzes.recalculate-score', $quiz->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-calculator me-2"></i>إعادة حساب الدرجات
                                    </button>
                                </form>
                                <form action="{{ route('quizzes.toggle-publish', $quiz->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $quiz->is_published ? 'warning' : 'success' }} w-100">
                                        <i class="fas fa-{{ $quiz->is_published ? 'eye-slash' : 'check' }} me-2"></i>
                                        {{ $quiz->is_published ? 'إلغاء النشر' : 'نشر الاختبار' }}
                                    </button>
                                </form>
                                <hr>
                                <form action="{{ route('quizzes.destroy', $quiz->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف الاختبار
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
