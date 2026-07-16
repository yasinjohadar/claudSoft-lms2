@extends('student.layouts.master')

@section('page-title')
    {{ $challenge->title }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/programming-challenge-student.css') }}?v={{ @filemtime(public_path('assets/css/programming-challenge-student.css')) ?: '1' }}">
@endpush

@section('content')
    @php
        $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
        $isWeb = $challenge->isWebSandbox();
        $completedAttempts = $attempts->whereIn('status', ['submitted', 'graded', 'returned']);
        $gradedAttempts = $attempts->filter(function ($a) {
            return $a->isGraded() || filled($a->feedback);
        });
        $latestGraded = $gradedAttempts->first();
        $remaining = max(0, ($challenge->attempts_allowed ?? 0) - $completedAttempts->count());
        if ($challenge->attempts_allowed === null) {
            $remaining = null;
        }

        $statusLabels = [
            'in_progress' => 'جارية',
            'submitted' => 'بانتظار التقييم',
            'graded' => 'مُقيَّمة',
            'returned' => 'مُعادة',
        ];
    @endphp

    <div class="main-content app-content pch-page">
        <div class="container-fluid">
            @include('student.components.alerts')

            <div class="pch-breadcrumb">
                <a href="{{ route('student.challenges.index') }}">التحديات</a>
                <span>/</span>
                <span>{{ $challenge->title }}</span>
            </div>

            <div class="pch-show-hero">
                <div class="pch-show-hero__main">
                    <div class="pch-show-hero__badges">
                        <span class="pch-tag {{ $isWeb ? 'pch-tag--web' : 'pch-tag--code' }}">
                            {{ $isWeb ? 'ويب HTML/CSS/JS' : 'تنفيذ كود' }}
                        </span>
                        @if($challenge->difficulty)
                            <span class="pch-tag pch-tag--{{ $challenge->difficulty }}">
                                {{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
                            </span>
                        @endif
                    </div>
                    <h1 class="pch-show-hero__title">{{ $challenge->title }}</h1>
                    @if($challenge->description)
                        <div class="pch-show-hero__desc pch-rich">{!! $challenge->description !!}</div>
                    @endif
                    @if($challenge->languages->isNotEmpty())
                        <div class="pch-card__langs mt-3">
                            @foreach($challenge->languages as $lang)
                                <span class="pch-tag">{{ $lang->pivot->editor_tab_label ?: $lang->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="pch-show-hero__aside">
                    <div class="pch-show-stat">
                        <span class="pch-show-stat__label">الدرجة القصوى</span>
                        <span class="pch-show-stat__value">{{ $challenge->max_score }}</span>
                    </div>
                    <div class="pch-show-stat">
                        <span class="pch-show-stat__label">المحاولات</span>
                        <span class="pch-show-stat__value">
                            {{ $completedAttempts->count() }}
                            <small>/ {{ $challenge->attempts_allowed ?? '∞' }}</small>
                        </span>
                    </div>
                    <div class="pch-show-cta">
                        @if($inProgress)
                            <a href="{{ route('student.challenges.work', $challenge->id) }}" class="btn btn-warning w-100" data-turbo="false">
                                <i class="fe fe-play-circle me-1"></i>
                                متابعة التحدي
                            </a>
                            <p class="pch-show-cta__hint text-warning mb-0">لديك محاولة جارية — أكملها من المحرر</p>
                        @elseif($canAttempt)
                            <a href="{{ route('student.challenges.start', $challenge->id) }}" class="btn btn-primary w-100" data-turbo="false">
                                <i class="fe fe-code me-1"></i>
                                فتح بيئة العمل
                            </a>
                            @if($remaining !== null)
                                <p class="pch-show-cta__hint mb-0">المحاولات المتبقية: {{ $remaining }} / {{ $challenge->attempts_allowed }}</p>
                            @endif
                        @else
                            <button type="button" class="btn btn-secondary w-100" disabled>
                                <i class="fe fe-slash me-1"></i>
                                استنفدت المحاولات
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if($challenge->instructions)
                <section class="pch-panel">
                    <div class="pch-panel__head">
                        <h2 class="pch-panel__title"><i class="fe fe-info"></i> تعليمات التحدي</h2>
                    </div>
                    <div class="pch-panel__body pch-rich">{!! $challenge->instructions !!}</div>
                </section>
            @endif

            @if($latestGraded && $latestGraded->isGraded())
                @php
                    $latestPct = ((float) $latestGraded->max_score) > 0
                        ? ((float) $latestGraded->score / (float) $latestGraded->max_score) * 100
                        : 0;
                    $latestTone = $latestPct >= 80 ? 'great' : ($latestPct >= 60 ? 'good' : 'grow');
                    $latestToneLabel = [
                        'great' => 'أداء رائع',
                        'good' => 'نتيجة جيدة',
                        'grow' => 'فرصة للتطوير',
                    ][$latestTone];
                @endphp
                <section class="pch-celebrate pch-celebrate--{{ $latestTone }}">
                    <div class="pch-celebrate__ribbon">
                        <span class="pch-celebrate__ribbon-icon"><i class="fe fe-award"></i></span>
                        <div>
                            <span class="pch-celebrate__eyebrow">نتيجة آخر محاولة</span>
                            <h2 class="pch-celebrate__heading">{{ $latestToneLabel }} · محاولة #{{ $latestGraded->attempt_number }}</h2>
                        </div>
                        <div class="pch-celebrate__score">
                            <span class="pch-celebrate__score-num">{{ rtrim(rtrim(number_format((float) $latestGraded->score, 2, '.', ''), '0'), '.') }}</span>
                            <span class="pch-celebrate__score-of">من {{ rtrim(rtrim(number_format((float) $latestGraded->max_score, 2, '.', ''), '0'), '.') }}</span>
                        </div>
                    </div>

                    @if(filled($latestGraded->feedback))
                        <div class="pch-note">
                            <div class="pch-note__head">
                                <span class="pch-note__avatar"><i class="fe fe-message-circle"></i></span>
                                <div>
                                    <div class="pch-note__title">ملاحظة المقيّم لك</div>
                                    <div class="pch-note__sub">رسالة خاصة تساعدك تكمل وتطوّر عملك</div>
                                </div>
                            </div>
                            <div class="pch-note__body pch-rich">{!! $latestGraded->feedback !!}</div>
                            @if($latestGraded->graded_at)
                                <div class="pch-note__foot">
                                    <i class="fe fe-calendar"></i>
                                    تم التقييم {{ $latestGraded->graded_at->format('Y/m/d H:i') }}
                                </div>
                            @endif
                        </div>
                    @elseif($latestGraded->graded_at)
                        <div class="pch-celebrate__meta">تم التقييم: {{ $latestGraded->graded_at->format('Y/m/d H:i') }}</div>
                    @endif
                </section>
            @endif

            @if($attempts->isNotEmpty())
                <section class="pch-panel">
                    <div class="pch-panel__head">
                        <h2 class="pch-panel__title"><i class="fe fe-clock"></i> سجل المحاولات</h2>
                    </div>
                    <div class="pch-attempts">
                        @foreach($attempts as $attempt)
                            @php
                                $attemptPct = $attempt->isGraded() && (float) $attempt->max_score > 0
                                    ? ((float) $attempt->score / (float) $attempt->max_score) * 100
                                    : null;
                                $attemptTone = $attemptPct === null ? null : ($attemptPct >= 80 ? 'great' : ($attemptPct >= 60 ? 'good' : 'grow'));
                            @endphp
                            <article class="pch-attempt {{ $attempt->isGraded() ? 'pch-attempt--graded' : '' }} {{ $attemptTone ? 'pch-attempt--'.$attemptTone : '' }}">
                                <div class="pch-attempt__row">
                                    <div>
                                        <strong>محاولة #{{ $attempt->attempt_number }}</strong>
                                        <span class="pch-attempt__status pch-attempt__status--{{ $attempt->status }}">
                                            {{ $statusLabels[$attempt->status] ?? $attempt->status }}
                                        </span>
                                    </div>
                                    <div class="pch-attempt__side">
                                        @if($attempt->isGraded())
                                            <span class="pch-attempt__grade">
                                                {{ rtrim(rtrim(number_format((float) $attempt->score, 2, '.', ''), '0'), '.') }}
                                                <small>من {{ rtrim(rtrim(number_format((float) $attempt->max_score, 2, '.', ''), '0'), '.') }}</small>
                                            </span>
                                        @elseif($attempt->isInProgress())
                                            <a href="{{ route('student.challenges.work', $challenge->id) }}" class="btn btn-sm btn-outline-warning" data-turbo="false">متابعة</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="pch-attempt__dates small text-muted">
                                    @if($attempt->started_at) بدء: {{ $attempt->started_at->format('Y/m/d H:i') }} @endif
                                    @if($attempt->submitted_at) · تسليم: {{ $attempt->submitted_at->format('Y/m/d H:i') }} @endif
                                </div>
                                @if(filled($attempt->feedback))
                                    <div class="pch-note pch-note--compact">
                                        <div class="pch-note__head">
                                            <span class="pch-note__avatar"><i class="fe fe-message-circle"></i></span>
                                            <div>
                                                <div class="pch-note__title">ملاحظة المقيّم</div>
                                                <div class="pch-note__sub">احتفظ بها وارجع لها قبل المحاولة التالية</div>
                                            </div>
                                        </div>
                                        <div class="pch-note__body pch-rich">{!! $attempt->feedback !!}</div>
                                    </div>
                                @endif
                                @php
                                    $attemptFiles = $attempt->latestSubmission?->files ?? collect();
                                    if ($attemptFiles->isNotEmpty() && ($attempt->latestSubmission?->status === 'draft')) {
                                        $attemptFiles = collect();
                                    }
                                @endphp
                                @if($attemptFiles->isNotEmpty())
                                    <details class="pch-attempt__code">
                                        <summary><i class="fe fe-code me-1"></i>عرض كود هذه المحاولة</summary>
                                        <div class="pch-attempt__code-files">
                                            @foreach($attemptFiles as $file)
                                                <div class="pch-attempt__code-file">
                                                    <div class="pch-attempt__code-name">{{ $file->filename }}</div>
                                                    <pre class="pch-attempt__code-pre" dir="ltr"><code>{{ $file->content }}</code></pre>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="pch-show-tips">
                <span><i class="fe fe-monitor"></i> استخدم متصفحاً حديثاً</span>
                <span><i class="fe fe-save"></i> يُحفظ العمل تلقائياً كل 30 ثانية</span>
                @if($isWeb)
                    <span><i class="fe fe-eye"></i> معاينة حية داخل المحرر</span>
                @endif
            </div>
        </div>
    </div>
@stop
