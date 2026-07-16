@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/quiz-intro.css') }}?v={{ @filemtime(public_path('assets/css/quiz-intro.css')) ?: '1' }}">
@endpush

@php
    $heroVariant = $heroVariant ?? 'quiz';
    $heroIcon = $heroIcon ?? ($heroVariant === 'question_module' ? 'fe-clipboard' : 'fe-help-circle');
    $stats = $stats ?? [];
    $chips = $chips ?? [];
    $richHtml = (bool) ($richHtml ?? false);
@endphp

<div class="quiz-intro mb-4">
    <div class="quiz-intro__card">
        <div class="quiz-intro__hero {{ $heroVariant === 'question_module' ? 'quiz-intro__hero--info' : '' }}">
            <div class="quiz-intro__hero-inner">
                <span class="quiz-intro__hero-icon"><i class="fe {{ $heroIcon }}"></i></span>
                <div class="min-w-0">
                    <h2 class="quiz-intro__title">{{ $title }}</h2>
                    @if(!empty($description))
                        @if($richHtml)
                            <div class="quiz-intro__subtitle quiz-intro__rich">{!! $description !!}</div>
                        @else
                            <p class="quiz-intro__subtitle">{{ $description }}</p>
                        @endif
                    @endif
                    @if(count($chips))
                        <div class="quiz-intro__chips">
                            @foreach($chips as $chip)
                                <span class="quiz-intro__chip">
                                    @if(!empty($chip['icon']))<i class="fe {{ $chip['icon'] }}"></i>@endif
                                    {{ $chip['label'] }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="quiz-intro__body">
            @if(!empty($instructions))
                <div class="quiz-intro__instructions">
                    <span class="quiz-intro__instructions-icon"><i class="fe fe-info"></i></span>
                    <div>
                        <strong class="d-block mb-1">تعليمات مهمة</strong>
                        @if($richHtml)
                            <div class="quiz-intro__rich">{!! $instructions !!}</div>
                        @else
                            {!! nl2br(e($instructions)) !!}
                        @endif
                    </div>
                </div>
            @endif

            @if(count($stats))
                <div class="quiz-intro__stats">
                    @foreach($stats as $stat)
                        <div class="quiz-intro__stat quiz-intro__stat--{{ $stat['color'] ?? 'blue' }}">
                            <span class="quiz-intro__stat-icon"><i class="fe {{ $stat['icon'] }}"></i></span>
                            <span class="quiz-intro__stat-label">{{ $stat['label'] }}</span>
                            <span class="quiz-intro__stat-value">{!! $stat['value'] !!}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(!empty($showHistory) && ($completedAttempts ?? 0) > 0)
                <div class="quiz-intro__history">
                    <div>
                        <div class="quiz-intro__history-title"><i class="fe fe-clock me-1"></i>محاولاتك السابقة</div>
                        <span class="small">
                            {{ $completedAttempts }} / {{ $attemptsAllowed ?? '∞' }} محاولة
                            @if(!empty($lastScore))
                                — آخر درجة: <strong class="{{ $lastPassed ? 'text-success' : 'text-danger' }}">{{ $lastScore }}</strong>
                            @endif
                        </span>
                    </div>
                    @if(!empty($reviewUrl))
                        <a href="{{ $reviewUrl }}" class="btn btn-sm btn-outline-primary rounded-pill" data-turbo="false">
                            <i class="fe fe-eye me-1"></i>عرض آخر محاولة
                        </a>
                    @endif
                </div>
            @endif

            <div class="quiz-intro__cta">
                @if(!empty($inProgressAttempt))
                    <a href="{{ $continueUrl }}" class="btn btn-warning quiz-intro__cta-btn quiz-intro__cta-btn--warning" data-turbo="false">
                        <i class="fe fe-play-circle"></i>
                        متابعة الاختبار
                    </a>
                    <p class="quiz-intro__cta-hint text-warning">
                        <i class="fe fe-alert-triangle me-1"></i>
                        لديك محاولة جارية — أكملها لاستهلاك المحاولة
                    </p>
                @elseif(!empty($canAttempt))
                    @if(!empty($startFormAction))
                        <form action="{{ $startFormAction }}" method="POST" class="d-inline" data-turbo="false"
                              @if(!empty($confirmStart)) onsubmit="return confirm('هل أنت متأكد من بدء الاختبار؟')" @endif>
                            @csrf
                            @foreach($startFormHidden ?? [] as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            @if(!empty($passwordRequired))
                                <div class="mb-3 text-start" style="max-width: 280px; margin-inline: auto;">
                                    <label class="form-label small fw-semibold">كلمة مرور الاختبار</label>
                                    <input type="password" name="quiz_password" class="form-control" required>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary quiz-intro__cta-btn">
                                <i class="fe fe-play"></i>
                                {{ $startLabel ?? 'بدء الاختبار' }}
                            </button>
                        </form>
                    @elseif(!empty($startUrl))
                        <a href="{{ $startUrl }}" class="btn btn-primary quiz-intro__cta-btn" data-turbo="false">
                            <i class="fe fe-play"></i>
                            {{ $startLabel ?? 'بدء الاختبار' }}
                        </a>
                    @endif

                    @if(isset($remainingAttempts) && !empty($attemptsAllowed))
                        <div class="quiz-intro__cta-meta">
                            <i class="fe fe-refresh-cw"></i>
                            المحاولات المتبقية: <strong>{{ $remainingAttempts }}</strong> / {{ $attemptsAllowed }}
                        </div>
                    @elseif(!empty($ctaNote))
                        <p class="quiz-intro__cta-hint">{{ $ctaNote }}</p>
                    @else
                        <p class="quiz-intro__cta-hint">
                            <i class="fe fe-info me-1"></i>
                            بمجرد البدء، يبدأ احتساب الوقت إن وُجد
                        </p>
                    @endif
                @else
                    <button type="button" class="btn btn-secondary quiz-intro__cta-btn" disabled>
                        <i class="fe fe-slash"></i>
                        {{ $blockedLabel ?? 'لا يمكن بدء الاختبار' }}
                    </button>
                    <p class="quiz-intro__cta-hint text-danger">
                        {{ $blockedHint ?? 'استنفدت جميع المحاولات أو الاختبار غير متاح' }}
                    </p>
                @endif

                @if(!empty($tips))
                    <div class="quiz-intro__tips">
                        @foreach($tips as $tip)
                            <span class="quiz-intro__tip"><i class="fe {{ $tip['icon'] }}"></i>{{ $tip['text'] }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@isset($slot)
    {{ $slot }}
@endisset
