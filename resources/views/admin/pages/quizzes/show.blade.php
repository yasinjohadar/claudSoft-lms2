@extends('admin.layouts.master')

@section('page-title')
    عرض الاختبار
@stop

@section('styles')
    @include('admin.pages.quizzes.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb quizzes-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">الاختبارات</a></li>
                        <li class="breadcrumb-item active">عرض الاختبار</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in quizzes-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow"><i class="fe fe-help-circle me-1"></i>تفاصيل الاختبار</span>
                        <h2 class="group-show-hero__title mb-2">{{ $quiz->title }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            @if($quiz->course)
                                {{ $quiz->course->title }}
                                @if($quiz->lesson) · {{ $quiz->lesson->title }} @endif
                            @else
                                متابعة المحاولات وإدارة أسئلة الاختبار.
                            @endif
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('quizzes.manage-questions', $quiz->id) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-list"></i></span>
                                <span class="group-show-action__text">إدارة الأسئلة</span>
                            </a>
                            <a href="{{ route('quizzes.edit', $quiz->id) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل</span>
                            </a>
                            <a href="{{ route('grading.index', ['quiz_id' => $quiz->id]) }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-check-square"></i></span>
                                <span class="group-show-action__text">التصحيح</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                                معلومات الاختبار
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="assignments-info-grid mb-3">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الكورس</div>
                                    <div class="assignments-info-item__value">
                                        <span class="assignments-course-chip">{{ $quiz->course->title }}</span>
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الدرس</div>
                                    <div class="assignments-info-item__value">
                                        @if($quiz->lesson)
                                            <span class="assignments-lesson-chip">{{ $quiz->lesson->title }}</span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">نوع الاختبار</div>
                                    <div class="assignments-info-item__value">
                                        @if($quiz->quiz_type == 'practice')
                                            <span class="quizzes-type-chip quizzes-type-chip--practice">تدريبي</span>
                                        @elseif($quiz->quiz_type == 'graded')
                                            <span class="quizzes-type-chip quizzes-type-chip--graded">مُقيّم</span>
                                        @elseif($quiz->quiz_type == 'final_exam')
                                            <span class="quizzes-type-chip quizzes-type-chip--final">اختبار نهائي</span>
                                        @else
                                            <span class="quizzes-type-chip quizzes-type-chip--survey">استبيان</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الحالة</div>
                                    <div class="assignments-info-item__value">
                                        @if($quiz->is_published)
                                            <span class="assignments-status-chip assignments-status-chip--published">منشور</span>
                                        @else
                                            <span class="assignments-status-chip assignments-status-chip--draft">مسودة</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الظهور</div>
                                    <div class="assignments-info-item__value">
                                        @if($quiz->is_visible)
                                            <span class="assignments-status-chip assignments-status-chip--published">ظاهر</span>
                                        @else
                                            <span class="assignments-status-chip assignments-status-chip--draft">مخفي</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($quiz->description)
                                <div class="mb-3">
                                    <p class="mb-2 fw-semibold">الوصف</p>
                                    <div class="text-muted">{{ $quiz->description }}</div>
                                </div>
                            @endif

                            @if($quiz->instructions)
                                <div class="mb-0">
                                    <p class="mb-2 fw-semibold">التعليمات</p>
                                    <div class="alert alert-info mb-0"><i class="fe fe-info me-2"></i>{{ $quiz->instructions }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-settings"></i></span>
                                الإعدادات
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="assignments-info-grid mb-3">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">عدد الأسئلة</div>
                                    <div class="assignments-info-item__value fw-semibold">{{ $quiz->getQuestionCount() }}</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الدرجة القصوى</div>
                                    <div class="assignments-info-item__value"><span class="assignments-grade-chip">{{ number_format($quiz->max_score, 1) }}</span></div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">درجة النجاح</div>
                                    <div class="assignments-info-item__value fw-semibold">{{ $quiz->passing_grade }}%</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">الوقت المحدد</div>
                                    <div class="assignments-info-item__value">{{ $quiz->time_limit ? $quiz->time_limit . ' دقيقة' : 'غير محدد' }}</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">المحاولات</div>
                                    <div class="assignments-info-item__value">{{ $quiz->attempts_allowed ?? 'غير محدود' }}</div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="mb-2">
                                            <i class="fe fe-{{ $quiz->shuffle_questions ? 'check text-success' : 'x text-danger' }} me-2"></i>
                                            ترتيب الأسئلة عشوائياً
                                        </li>
                                        <li class="mb-2">
                                            <i class="fe fe-{{ $quiz->shuffle_answers ? 'check text-success' : 'x text-danger' }} me-2"></i>
                                            ترتيب الخيارات عشوائياً
                                        </li>
                                        <li class="mb-2">
                                            <i class="fe fe-{{ $quiz->show_correct_answers ? 'check text-success' : 'x text-danger' }} me-2"></i>
                                            عرض الإجابات الصحيحة
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="mb-2">
                                            <i class="fe fe-{{ $quiz->allow_review ? 'check text-success' : 'x text-danger' }} me-2"></i>
                                            السماح بالمراجعة
                                        </li>
                                        <li class="mb-2">
                                            <i class="fe fe-{{ $quiz->show_grade_immediately ? 'check text-success' : 'x text-danger' }} me-2"></i>
                                            عرض الدرجة فوراً
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                الأسئلة
                                <span class="group-show-members-card__count">{{ $quiz->quizQuestions->count() }}</span>
                            </h6>
                            <a href="{{ route('quizzes.manage-questions', $quiz->id) }}" class="btn btn-success-light btn-sm">
                                <i class="fe fe-settings me-1"></i>إدارة الأسئلة
                            </a>
                        </div>
                        <div class="card-body pt-3 p-0">
                            @if($quiz->quizQuestions->count() > 0)
                                <div class="table-responsive px-3 pb-3">
                                    <table class="table table-hover text-nowrap dashboard-table mb-0">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th>السؤال</th>
                                                <th>النوع</th>
                                                <th>الدرجة</th>
                                                <th>الترتيب</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quiz->quizQuestions as $quizQuestion)
                                                @php $question = $quizQuestion->question; @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 360px;">
                                                            @if($question)
                                                                {{ $question->question_text }}
                                                            @else
                                                                <span class="text-danger">هذا السؤال محذوف من بنك الأسئلة</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($question && $question->questionType)
                                                            <span class="badge bg-info-transparent">{{ $question->questionType->display_name }}</span>
                                                        @else
                                                            <span class="badge bg-danger-transparent">نوع غير متوفر</span>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge bg-success-transparent">{{ $quizQuestion->max_score }}</span></td>
                                                    <td>{{ $quizQuestion->question_order }}</td>
                                                    <td>
                                                        @if($question)
                                                            <a href="{{ route('question-bank.preview', $question->id) }}"
                                                               class="btn btn-primary-light btn-sm" target="_blank" title="معاينة">
                                                                <i class="fe fe-eye"></i>
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-primary-light btn-sm" disabled title="معاينة">
                                                                <i class="fe fe-eye"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 px-3">
                                    <span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-help-circle"></i></span>
                                    <p class="text-muted mb-3">لم يتم إضافة أسئلة بعد</p>
                                    <a href="{{ route('quizzes.manage-questions', $quiz->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fe fe-plus me-1"></i>إضافة أسئلة
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                            <h6 class="group-show-members-card__title mb-0">
                                المحاولات
                                <span class="group-show-members-card__count">{{ $attempts->total() }}</span>
                            </h6>
                        </div>
                        <div class="card-body pt-3 p-0">
                            <div class="table-responsive px-3 pb-3">
                                <table class="table table-hover text-nowrap dashboard-table mb-0">
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
                                        @forelse($attempts as $attempt)
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold">{{ $attempt->student->name }}</span>
                                                    <br><small class="text-muted">{{ $attempt->student->email }}</small>
                                                </td>
                                                <td><span class="badge bg-secondary-transparent">#{{ $attempt->attempt_number }}</span></td>
                                                <td><small class="text-muted">{{ $attempt->started_at->format('Y-m-d H:i') }}</small></td>
                                                <td>
                                                    @if($attempt->total_score !== null)
                                                        <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}-transparent">
                                                            {{ number_format($attempt->percentage_score, 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td><small>{{ $attempt->getTimeSpentHumanReadable() }}</small></td>
                                                <td>
                                                    @if($attempt->status == 'in_progress')
                                                        <span class="assignments-status-chip assignments-status-chip--pending">جاري</span>
                                                    @elseif($attempt->status == 'submitted')
                                                        <span class="assignments-status-chip assignments-status-chip--submission-draft">مُسلّم</span>
                                                    @elseif($attempt->status == 'graded')
                                                        <span class="assignments-status-chip assignments-status-chip--graded">مُصحح</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('grading.show', $attempt->id) }}"
                                                       class="btn btn-primary-light btn-sm" title="التصحيح">
                                                        <i class="fe fe-edit-2"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-inbox"></i></span>
                                                    <p class="text-muted mb-0">لا توجد محاولات بعد</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($attempts->hasPages())
                            <div class="card-footer">{{ $attempts->links() }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-bar-chart-2"></i></span>
                                الإحصائيات
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            @php $totalAttempts = $stats['total_attempts'] ?? 0; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>إجمالي المحاولات</span>
                                    <span class="badge bg-primary">{{ $totalAttempts }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-primary" style="width: {{ $totalAttempts > 0 ? 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>المحاولات المكتملة</span>
                                    <span class="badge bg-success">{{ $stats['completed_attempts'] }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-success" style="width: {{ $totalAttempts > 0 ? ($stats['completed_attempts'] / $totalAttempts) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>قيد التقدم</span>
                                    <span class="badge bg-info">{{ $stats['in_progress'] }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-info" style="width: {{ $totalAttempts > 0 ? ($stats['in_progress'] / $totalAttempts) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>مُصححة</span>
                                    <span class="badge bg-warning">{{ $stats['graded'] }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-warning" style="width: {{ $totalAttempts > 0 ? ($stats['graded'] / $totalAttempts) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>بانتظار التصحيح</span>
                                    <span class="badge bg-danger">{{ $stats['pending_grading'] }}</span>
                                </div>
                                <div class="assignments-stat-progress">
                                    <div class="progress-bar bg-danger" style="width: {{ $totalAttempts > 0 ? ($stats['pending_grading'] / $totalAttempts) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center mb-3">
                                <p class="mb-1 text-muted">متوسط الدرجات</p>
                                <h3 class="text-primary mb-0">
                                    {{ $stats['average_score'] ? number_format($stats['average_score'], 1) . '%' : '-' }}
                                </h3>
                            </div>
                            <div class="text-center">
                                <p class="mb-1 text-muted">معدل النجاح</p>
                                <h3 class="text-success mb-0">{{ number_format($stats['pass_rate'], 1) }}%</h3>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-calendar"></i></span>
                                المواعيد
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="assignments-info-grid">
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">متاح من</div>
                                    <div class="assignments-info-item__value">{{ $quiz->available_from ? $quiz->available_from->format('Y-m-d H:i') : 'غير محدد' }}</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">موعد الاستحقاق</div>
                                    <div class="assignments-info-item__value">{{ $quiz->due_date ? $quiz->due_date->format('Y-m-d H:i') : 'غير محدد' }}</div>
                                </div>
                                <div class="assignments-info-item">
                                    <div class="assignments-info-item__label">متاح حتى</div>
                                    <div class="assignments-info-item__value">{{ $quiz->available_until ? $quiz->available_until->format('Y-m-d H:i') : 'غير محدد' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                <span class="assignments-section-icon"><i class="fe fe-zap"></i></span>
                                إجراءات سريعة
                            </h4>
                        </div>
                        <div class="card-body pt-3">
                            <div class="d-grid gap-2">
                                <a href="{{ route('quiz-analytics.quiz', $quiz->id) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fe fe-trending-up me-1"></i>عرض التحليلات
                                </a>
                                <form action="{{ route('quizzes.recalculate-score', $quiz->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fe fe-refresh-cw me-1"></i>إعادة حساب الدرجات
                                    </button>
                                </form>
                                <form action="{{ route('quizzes.toggle-publish', $quiz->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $quiz->is_published ? 'warning' : 'success' }} btn-sm w-100">
                                        <i class="fe fe-{{ $quiz->is_published ? 'eye-off' : 'check' }} me-1"></i>
                                        {{ $quiz->is_published ? 'إلغاء النشر' : 'نشر الاختبار' }}
                                    </button>
                                </form>
                                <hr class="my-2">
                                <form action="{{ route('quizzes.destroy', $quiz->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fe fe-trash-2 me-1"></i>حذف الاختبار
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
