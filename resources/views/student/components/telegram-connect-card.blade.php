@php
    $tg = $studentTelegram ?? null;
    $compact = $compact ?? false;
    $showUnlink = $showUnlink ?? false;
@endphp

@if($tg)
    <div class="card custom-card student-quizzes-panel student-telegram-card {{ $compact ? 'student-telegram-card--compact' : '' }} {{ $class ?? '' }}">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm" style="background: rgba(34, 158, 217, 0.12);">
                        <i class="ri-telegram-line" style="color: #229ED9;"></i>
                    </span>
                    <div>
                        <h6 class="card-title mb-0">Telegram</h6>
                        @unless($compact)
                            <p class="text-muted fs-12 mb-0">استلم الإشعارات ودعوات المجموعات على تيليجرام</p>
                        @endunless
                    </div>
                </div>
                @if($tg['linked'])
                    <span class="badge bg-success-transparent">مرتبط</span>
                @else
                    <span class="badge bg-warning-transparent">غير مرتبط</span>
                @endif
            </div>

            @if($tg['linked'])
                <div class="student-profile-field mb-3">
                    <span class="student-profile-field__icon">
                        <i class="ri-telegram-line"></i>
                    </span>
                    <div class="min-w-0">
                        <span class="student-profile-field__label">الحساب المربوط</span>
                        <span class="student-profile-field__value">
                            @if($tg['username'])
                                {{ '@'.$tg['username'] }}
                            @else
                                حساب Telegram
                            @endif
                            @if($tg['linked_at'])
                                <span class="text-muted fs-12 d-block">منذ {{ $tg['linked_at']->format('Y-m-d') }}</span>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('student.telegram.link') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="fe fe-settings me-1"></i>إدارة الربط
                    </a>
                    @if($showUnlink)
                        <form method="POST" action="{{ route('student.telegram.unlink') }}" class="d-inline" onsubmit="return confirm('فك ربط Telegram؟');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                                <i class="fe fe-link-2 me-1"></i>فك الربط
                            </button>
                        </form>
                    @endif
                </div>
            @else
                @if($tg['enabled'])
                    <p class="text-muted fs-13 mb-3">
                        اربط حسابك مرة واحدة لتصلك رسائل المنصة ودعوات المجموعات مباشرة على Telegram.
                    </p>
                    <a href="{{ route('student.telegram.link') }}" class="btn btn-primary rounded-pill w-100" style="background: linear-gradient(135deg, #229ED9, #0088cc); border: none;">
                        <i class="fe fe-send me-1"></i>ربط حساب Telegram
                    </a>
                @else
                    <p class="text-muted fs-13 mb-3">
                        خدمة Telegram لم تُفعَّل بعد على المنصة. يمكنك زيارة صفحة الربط لمعرفة الحالة.
                    </p>
                    <a href="{{ route('student.telegram.link') }}" class="btn btn-outline-primary rounded-pill w-100">
                        <i class="fe fe-send me-1"></i>صفحة ربط Telegram
                    </a>
                @endif
            @endif
        </div>
    </div>
@endif
