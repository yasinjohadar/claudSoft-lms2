@extends('admin.layouts.master')

@section('page-title')
    تفاصيل السؤال
@stop

@section('styles')
    @include('admin.pages.question-bank.partials.page-styles')
@stop

@php
    $lessonShow = $question->lesson_name ?? ($question->metadata['lesson_name'] ?? null);
    $responseCount = $question->responses ? $question->responses->count() : 0;
    $correctPct = $responseCount > 0
        ? number_format(($question->responses->where('is_correct', true)->count() / $responseCount) * 100, 1)
        : null;
    $avgScore = $responseCount > 0
        ? number_format($question->responses->avg('score_obtained'), 1)
        : null;
@endphp

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid px-3 px-lg-4">

            @include('admin.components.alerts')

            <div class="admin-show-layout">

                <div class="my-4 page-header-breadcrumb qb-page-animate dashboard-fade-in">
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item active">تفاصيل السؤال</li>
                        </ol>
                    </nav>
                </div>

                <div class="group-show-hero dashboard-fade-in qb-page-animate mb-4">
                    <div class="row align-items-start g-3">
                        <div class="col-lg-8">
                            <span class="group-show-hero__eyebrow"><i class="fe fe-help-circle me-1"></i>تفاصيل السؤال</span>
                            <h2 class="group-show-hero__title mb-2">{{ Str::limit(strip_tags($question->question_text), 90) }}</h2>
                            <p class="group-show-hero__desc mb-0">
                                @if($question->questionType)
                                    {{ $question->questionType->display_name }}
                                    @if($question->course)
                                        · <span class="d-inline-block text-truncate align-middle" style="max-width: min(100%, 420px);" title="{{ $question->course->title }}">{{ $question->course->title }}</span>
                                    @endif
                                @else
                                    عرض نص السؤال وخيارات الإجابة والإحصائيات.
                                @endif
                            </p>
                        </div>
                        <div class="col-lg-4">
                            <div class="group-show-actions">
                                <a href="{{ route('question-bank.edit', $question->id) }}" class="group-show-action group-show-action--primary">
                                    <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                    <span class="group-show-action__text">تعديل السؤال</span>
                                </a>
                                <a href="{{ route('question-bank.index') }}" class="group-show-action">
                                    <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                    <span class="group-show-action__text">العودة للقائمة</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8 order-lg-1">
                        <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-file-text"></i></span>
                                    نص السؤال
                                </h4>
                                @if($question->is_active)
                                    <span class="assignments-status-chip assignments-status-chip--published">نشط</span>
                                @else
                                    <span class="assignments-status-chip assignments-status-chip--draft">غير نشط</span>
                                @endif
                            </div>
                            <div class="card-body pt-3">
                                <div class="qb-show-question-text mb-3">
                                    {!! mixed_bidi_html($question->question_text) !!}
                                </div>

                                @if($lessonShow)
                                    <div class="qb-show-meta-list__item mb-3 pb-3 border-bottom">
                                        <div class="qb-show-meta-list__label">اسم الدرس</div>
                                        <div class="qb-show-meta-list__value">{{ $lessonShow }}</div>
                                    </div>
                                @endif

                                @if($question->question_image)
                                    <div class="mb-3">
                                        <p class="mb-2 fw-semibold">صورة السؤال</p>
                                        <img src="{{ asset('storage/' . $question->question_image) }}" alt="صورة السؤال"
                                             class="img-fluid rounded" style="max-width: 100%;">
                                    </div>
                                @endif

                                @if($question->options && $question->options->count() > 0)
                                    <p class="mb-2 fw-semibold">خيارات الإجابة</p>
                                    <div class="d-flex flex-column gap-2 mb-3">
                                        @foreach($question->options->sortBy('option_order') as $index => $option)
                                            <div class="qb-show-option {{ $option->is_correct ? 'qb-show-option--correct' : '' }}">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <span class="badge bg-secondary-transparent">{{ $index + 1 }}</span>
                                                            <span class="fw-semibold">{!! mixed_bidi_html($option->option_text) !!}</span>
                                                            @if($option->is_correct)
                                                                <i class="fe fe-check-circle text-success"></i>
                                                            @endif
                                                        </div>
                                                        @if($option->feedback)
                                                            <small class="text-muted d-block">
                                                                <i class="fe fe-message-square me-1"></i>{!! mixed_bidi_html($option->feedback) !!}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    <div class="text-end small text-muted">
                                                        <div>الوزن: {{ $option->score_weight }}</div>
                                                        <div>الترتيب: {{ $option->option_order }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($question->explanation)
                                    <div class="mb-3">
                                        <p class="mb-2 fw-semibold">شرح الإجابة</p>
                                        <div class="alert alert-info mb-0">
                                            <i class="fe fe-info me-2"></i>{!! mixed_bidi_html($question->explanation) !!}
                                        </div>
                                    </div>
                                @endif

                                @if($question->tags && count($question->tags) > 0)
                                    <div class="mb-0">
                                        <p class="mb-2 fw-semibold">الوسوم</p>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($question->tags as $tag)
                                                <span class="badge bg-warning-transparent">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-bar-chart-2"></i></span>
                                    إحصائيات الاستخدام
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="qb-stat-tile">
                                            <div class="qb-stat-tile__value text-primary">{{ $question->quizQuestions ? $question->quizQuestions->count() : 0 }}</div>
                                            <div class="qb-stat-tile__label">عدد الاختبارات المستخدمة فيها</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="qb-stat-tile">
                                            <div class="qb-stat-tile__value text-success">{{ $responseCount }}</div>
                                            <div class="qb-stat-tile__label">عدد الإجابات المسجلة</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="qb-stat-tile">
                                            <div class="qb-stat-tile__value text-warning">{{ $correctPct !== null ? $correctPct . '%' : '-' }}</div>
                                            <div class="qb-stat-tile__label">نسبة الإجابات الصحيحة</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="qb-stat-tile">
                                            <div class="qb-stat-tile__value text-info">{{ $avgScore ?? '-' }}</div>
                                            <div class="qb-stat-tile__label">متوسط الدرجات المحصلة</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 order-lg-2">
                        <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-info"></i></span>
                                    المعلومات الأساسية
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="qb-show-meta-list">
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">الكورس</div>
                                        <div class="qb-show-meta-list__value">
                                            @if($question->course)
                                                {{ $question->course->title }}
                                            @else
                                                <span class="text-muted fw-normal">عام (غير مرتبط بكورس)</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">نوع السؤال</div>
                                        <div class="qb-show-meta-list__value">
                                            <span class="qb-type-chip">{{ $question->questionType->display_name ?? 'غير محدد' }}</span>
                                        </div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">مستوى الصعوبة</div>
                                        <div class="qb-show-meta-list__value">
                                            @if($question->difficulty_level == 'easy')
                                                <span class="qb-difficulty-chip qb-difficulty-chip--easy">سهل</span>
                                            @elseif($question->difficulty_level == 'medium')
                                                <span class="qb-difficulty-chip qb-difficulty-chip--medium">متوسط</span>
                                            @elseif($question->difficulty_level == 'hard')
                                                <span class="qb-difficulty-chip qb-difficulty-chip--hard">صعب</span>
                                            @elseif($question->difficulty_level == 'expert')
                                                <span class="qb-difficulty-chip qb-difficulty-chip--expert">خبير</span>
                                            @else
                                                <span class="text-muted fw-normal">{{ $question->difficulty_level ?? 'غير محدد' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">الدرجة</div>
                                        <div class="qb-show-meta-list__value">
                                            <span class="assignments-grade-chip">{{ $question->default_grade ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">تاريخ الإنشاء</div>
                                        <div class="qb-show-meta-list__value">{{ $question->created_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                    <div class="qb-show-meta-list__item">
                                        <div class="qb-show-meta-list__label">آخر تعديل</div>
                                        <div class="qb-show-meta-list__value">{{ $question->updated_at->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($question->pools && $question->pools->count() > 0)
                            <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate mb-4">
                                <div class="card-header border-0 pb-0">
                                    <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                        <span class="assignments-section-icon"><i class="fe fe-layers"></i></span>
                                        المجموعات
                                    </h4>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="list-group list-group-flush">
                                        @foreach($question->pools as $pool)
                                            <a href="{{ route('question-pools.show', $pool->id) }}" class="list-group-item list-group-item-action border-0 px-0">
                                                <i class="fe fe-layers me-2 text-warning"></i>{{ $pool->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="card custom-card group-show-members-card dashboard-fade-in qb-page-animate">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title mb-1 d-flex align-items-center gap-2">
                                    <span class="assignments-section-icon"><i class="fe fe-zap"></i></span>
                                    إجراءات سريعة
                                </h4>
                            </div>
                            <div class="card-body pt-3">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('question-bank.edit', $question->id) }}" class="btn btn-warning-light btn-sm">
                                        <i class="fe fe-edit-2 me-1"></i>تعديل السؤال
                                    </a>
                                    <form action="{{ route('question-bank.duplicate', $question->id) }}" method="POST"
                                          onsubmit="return confirm('هل تريد إنشاء نسخة من هذا السؤال؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-info-light btn-sm w-100">
                                            <i class="fe fe-copy me-1"></i>نسخ السؤال
                                        </button>
                                    </form>
                                    <hr class="my-2">
                                    <form action="{{ route('question-bank.destroy', $question->id) }}" method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-light btn-sm w-100">
                                            <i class="fe fe-trash-2 me-1"></i>حذف السؤال
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@stop
