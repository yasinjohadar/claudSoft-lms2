@extends('student.layouts.master')

@section('page-title')
    {{ $quiz->title }}
@stop

@section('content')
    <div class="main-content app-content quiz-intro-page">
        <div class="container-fluid">

            @include('student.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 gap-3">
                <div class="min-w-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.index') }}">الاختبارات</a></li>
                            <li class="breadcrumb-item active">{{ $quiz->title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            @php
                $lastCompleted = $attempts->where('status', '!=', 'in_progress')->first();
                $introStats = [
                    ['icon' => 'fe-help-circle', 'label' => 'عدد الأسئلة', 'value' => $quiz->getQuestionCount(), 'color' => 'blue'],
                    ['icon' => 'fe-star', 'label' => 'الدرجة الكلية', 'value' => number_format($quiz->max_score, 0), 'color' => 'gold'],
                    ['icon' => 'fe-award', 'label' => 'درجة النجاح', 'value' => $quiz->passing_grade . '%', 'color' => 'green'],
                    ['icon' => 'fe-clock', 'label' => 'الوقت المحدد', 'value' => $quiz->time_limit ? $quiz->time_limit . ' <small>دقيقة</small>' : 'غير محدد', 'color' => 'red'],
                ];
                if ($quiz->attempts_allowed) {
                    $introStats[] = ['icon' => 'fe-refresh-cw', 'label' => 'المحاولات', 'value' => $quiz->attempts_allowed, 'color' => 'cyan'];
                }
            @endphp

            <div class="quiz-intro__layout">
                <div>
                    @include('shared.quizzes.intro-panel', [
                        'title' => $quiz->title,
                        'description' => $quiz->description,
                        'instructions' => $quiz->instructions,
                        'stats' => $introStats,
                        'chips' => collect([
                            ['icon' => 'fe-book', 'label' => $quiz->course->title ?? ''],
                            $quiz->quiz_type === 'practice' ? ['icon' => 'fe-book-open', 'label' => 'تدريبي'] : null,
                            $quiz->quiz_type === 'graded' ? ['icon' => 'fe-award', 'label' => 'مُقيّم'] : null,
                            $quiz->quiz_type === 'final_exam' ? ['icon' => 'fe-flag', 'label' => 'اختبار نهائي'] : null,
                        ])->filter(fn ($c) => $c && ($c['label'] ?? '') !== '')->values()->all(),
                        'showHistory' => true,
                        'completedAttempts' => $attempts->where('status', '!=', 'in_progress')->count(),
                        'attemptsAllowed' => $quiz->attempts_allowed,
                        'lastScore' => $lastCompleted && $lastCompleted->total_score !== null ? number_format($lastCompleted->percentage_score, 1) . '%' : null,
                        'lastPassed' => $lastCompleted ? (bool) $lastCompleted->passed : false,
                        'reviewUrl' => $lastCompleted ? route('student.quizzes.review.show', $lastCompleted->id) : null,
                        'inProgressAttempt' => $currentAttempt,
                        'continueUrl' => $currentAttempt ? route('student.quizzes.take', $currentAttempt->id) : null,
                        'canAttempt' => $canAttempt,
                        'startFormAction' => route('student.quizzes.start', $quiz->id),
                        'confirmStart' => !($quiz->settings && $quiz->settings->requiresPassword()),
                        'passwordRequired' => $quiz->settings && $quiz->settings->requiresPassword(),
                        'startLabel' => ($quiz->settings && $quiz->settings->requiresPassword()) ? 'بدء الاختبار' : 'بدء الاختبار الآن',
                        'remainingAttempts' => $remainingAttempts,
                        'blockedLabel' => $remainingAttempts === 0 ? 'استنفدت جميع المحاولات' : 'الاختبار غير متاح',
                        'blockedHint' => $remainingAttempts === 0 ? 'لقد استنفدت جميع المحاولات المسموحة' : 'الاختبار غير متاح حالياً',
                        'tips' => [
                            ['icon' => 'fe-wifi', 'text' => 'اتصال إنترنت مستقر'],
                            ['icon' => 'fe-shuffle', 'text' => $quiz->shuffle_questions ? 'أسئلة عشوائية' : 'ترتيب ثابت للأسئلة'],
                            ['icon' => 'fe-eye', 'text' => $quiz->allow_review ? 'مراجعة بعد التسليم' : 'بدون مراجعة'],
                        ],
                    ])

                    @if($attempts->count() > 0)
                        <div class="quiz-intro-panel">
                            <h3 class="quiz-intro-panel__title">
                                <i class="fe fe-list text-primary"></i>
                                محاولاتك السابقة ({{ $attempts->count() }})
                            </h3>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 student-quizzes-table">
                                    <thead>
                                        <tr>
                                            <th>المحاولة</th>
                                            <th>التاريخ</th>
                                            <th>الدرجة</th>
                                            <th>الحالة</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attempts as $attempt)
                                            <tr>
                                                <td><span class="badge bg-primary-transparent">#{{ $attempt->attempt_number }}</span></td>
                                                <td><small>{{ $attempt->started_at->format('Y-m-d H:i') }}</small></td>
                                                <td>
                                                    @if($attempt->total_score !== null)
                                                        <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}-transparent">
                                                            {{ number_format($attempt->percentage_score, 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($attempt->status == 'in_progress')
                                                        <span class="badge bg-info-transparent">جاري</span>
                                                    @elseif($attempt->status == 'submitted')
                                                        <span class="badge bg-warning-transparent">مُسلّم</span>
                                                    @else
                                                        <span class="badge bg-success-transparent">مُصحح</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($attempt->status == 'in_progress')
                                                        <a href="{{ route('student.quizzes.take', $attempt->id) }}" class="btn btn-sm btn-primary rounded-pill">متابعة</a>
                                                    @else
                                                        <a href="{{ route('student.quizzes.review.show', $attempt->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">مراجعة</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <aside class="quiz-intro__sidebar">
                    @if($quiz->due_date || $quiz->available_from || $quiz->available_until)
                        <div class="quiz-intro-panel">
                            <h3 class="quiz-intro-panel__title"><i class="fe fe-calendar text-danger"></i>المواعيد</h3>
                            @if($quiz->available_from)
                                <div class="mb-2"><small class="text-muted d-block">متاح من</small><strong>{{ $quiz->available_from->format('Y-m-d H:i') }}</strong></div>
                            @endif
                            @if($quiz->due_date)
                                <div class="mb-2"><small class="text-muted d-block">موعد الاستحقاق</small><strong class="text-{{ $quiz->due_date->isPast() ? 'danger' : 'warning' }}">{{ $quiz->due_date->format('Y-m-d H:i') }}</strong></div>
                            @endif
                            @if($quiz->available_until)
                                <div><small class="text-muted d-block">متاح حتى</small><strong>{{ $quiz->available_until->format('Y-m-d H:i') }}</strong></div>
                            @endif
                        </div>
                    @endif

                    <div class="quiz-intro-panel">
                        <h3 class="quiz-intro-panel__title"><i class="fe fe-shield text-warning"></i>قواعد مهمة</h3>
                        <ul class="list-unstyled mb-0 small">
                            @if($quiz->time_limit)
                                <li class="mb-2"><i class="fe fe-clock text-danger me-2"></i>{{ $quiz->time_limit }} دقيقة</li>
                            @endif
                            <li class="mb-2"><i class="fe fe-check-circle text-success me-2"></i>النجاح: {{ $quiz->passing_grade }}%</li>
                            @if($quiz->shuffle_questions)
                                <li class="mb-2"><i class="fe fe-shuffle text-info me-2"></i>ترتيب عشوائي</li>
                            @endif
                            @if($quiz->show_correct_answers)
                                <li class="mb-2"><i class="fe fe-zap text-warning me-2"></i>عرض الإجابات الصحيحة</li>
                            @endif
                            @if($quiz->allow_review)
                                <li><i class="fe fe-eye text-primary me-2"></i>مراجعة الإجابات</li>
                            @endif
                        </ul>
                    </div>
                </aside>
            </div>

        </div>
    </div>
@stop
