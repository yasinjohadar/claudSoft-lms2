@extends('student.layouts.master')

@section('page-title', 'إعدادات الإشعارات')

@section('content')
<div class="main-content app-content student-notif-settings-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="card custom-card group-show-hero dashboard-fade-in mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-bell me-1"></i>تخصيص تجربتك
                    </span>
                    <h4 class="group-show-hero__title mb-2">إعدادات الإشعارات</h4>
                    <p class="group-show-hero__desc mb-2">
                        تحكم في الإشعارات الداخلية والبريد الإلكتروني — فعّل ما يهمك فقط وابقَ على اطلاع دون إزعاج.
                    </p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">إعدادات الإشعارات</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fe fe-arrow-right me-1"></i>رجوع
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 student-notif-settings-stats">
            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 student-notif-settings-stagger" style="--stagger-delay: 0ms">
                <div class="card admin-stats-card admin-stats-card--blue">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap">
                            <i class="fe fe-layers admin-stats-card__icon"></i>
                        </div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">أنواع الإشعارات</p>
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $stats['total'] ?? 0 }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 student-notif-settings-stagger" style="--stagger-delay: 60ms">
                <div class="card admin-stats-card admin-stats-card--green">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap">
                            <i class="fe fe-bell admin-stats-card__icon"></i>
                        </div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">إشعارات داخلية مفعّلة</p>
                            <h3 class="admin-stats-card__value mb-0" id="notif-internal-count" data-countup="{{ $stats['internal_enabled'] ?? 0 }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 student-notif-settings-stagger" style="--stagger-delay: 120ms">
                <div class="card admin-stats-card admin-stats-card--orange">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap">
                            <i class="fe fe-mail admin-stats-card__icon"></i>
                        </div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">بريد إلكتروني مفعّل</p>
                            <h3 class="admin-stats-card__value mb-0" id="notif-email-count" data-countup="{{ $stats['email_enabled'] ?? 0 }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('student.settings.notifications.update') }}" method="POST" id="student-notif-settings-form">
            @csrf

            <div class="card custom-card student-notif-settings-toolbar dashboard-fade-in mb-4">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-notif-preset="internal-all">
                                <i class="fe fe-bell me-1"></i>تفعيل الداخلية
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill" data-notif-preset="email-all">
                                <i class="fe fe-mail me-1"></i>تفعيل البريد
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm rounded-pill" data-notif-preset="important">
                                <i class="fe fe-star me-1"></i>المهم فقط
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" data-notif-preset="none">
                                <i class="fe fe-bell-off me-1"></i>إيقاف الكل
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="notif-save-btn">
                            <i class="fe fe-save me-1"></i>حفظ الإعدادات
                        </button>
                    </div>
                </div>
            </div>

            @php
                $grouped = collect($notificationTypes)->groupBy(fn ($type) => $type['category'] ?? 'other');
                $importantKeys = ['badge_earned', 'achievement_unlocked', 'level_up', 'competition_won'];
            @endphp

            @foreach($categories as $catKey => $category)
                @if(!isset($grouped[$catKey]) || $grouped[$catKey]->isEmpty())
                    @continue
                @endif
                <div class="card custom-card student-notif-settings-panel dashboard-fade-in mb-4">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-{{ $category['color'] }}-transparent">
                                <i class="fe {{ $category['icon'] }} text-{{ $category['color'] }}"></i>
                            </span>
                            <div>
                                <h6 class="card-title mb-0">{{ $category['name'] }}</h6>
                                <p class="text-muted fs-12 mb-0">{{ $grouped[$catKey]->count() }} نوع إشعار</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="student-notif-settings-list">
                            <div class="student-notif-settings-list__head d-none d-md-grid">
                                <span>نوع الإشعار</span>
                                <span class="text-center"><i class="fe fe-bell me-1"></i>داخلي</span>
                                <span class="text-center"><i class="fe fe-mail me-1"></i>بريد</span>
                            </div>

                            @foreach($grouped[$catKey] as $key => $type)
                                @php
                                    $internalOn = (bool) (auth()->user()->notification_preferences[$key] ?? true);
                                    $emailOn = (bool) (auth()->user()->email_preferences[$key] ?? false);
                                    $color = $type['color'] ?? 'primary';
                                @endphp
                                <div class="student-notif-settings-row {{ $internalOn || $emailOn ? 'is-active' : 'is-muted' }}"
                                     data-notif-key="{{ $key }}"
                                     data-important="{{ in_array($key, $importantKeys, true) ? '1' : '0' }}">
                                    <div class="student-notif-settings-row__info">
                                        <span class="student-notif-settings-row__icon student-notif-settings-row__icon--{{ $color }}">
                                            <i class="fe {{ $type['icon'] }}"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <h6 class="student-notif-settings-row__title mb-1">{{ $type['name'] }}</h6>
                                            <p class="student-notif-settings-row__desc mb-0">{{ $type['description'] }}</p>
                                        </div>
                                    </div>

                                    <div class="student-notif-settings-row__toggle">
                                        <label class="student-notif-toggle" title="إشعار داخلي">
                                            <input type="checkbox"
                                                   class="student-notif-toggle__input js-notif-internal"
                                                   name="notification_preferences[{{ $key }}]"
                                                   id="notification_{{ $key }}"
                                                   value="1"
                                                   {{ $internalOn ? 'checked' : '' }}>
                                            <span class="student-notif-toggle__track student-notif-toggle__track--primary">
                                                <span class="student-notif-toggle__thumb"></span>
                                            </span>
                                            <span class="student-notif-toggle__label d-md-none">داخلي</span>
                                        </label>
                                    </div>

                                    <div class="student-notif-settings-row__toggle">
                                        <label class="student-notif-toggle" title="بريد إلكتروني">
                                            <input type="checkbox"
                                                   class="student-notif-toggle__input js-notif-email"
                                                   name="email_preferences[{{ $key }}]"
                                                   id="email_{{ $key }}"
                                                   value="1"
                                                   {{ $emailOn ? 'checked' : '' }}>
                                            <span class="student-notif-toggle__track student-notif-toggle__track--success">
                                                <span class="student-notif-toggle__thumb"></span>
                                            </span>
                                            <span class="student-notif-toggle__label d-md-none">بريد</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="student-notif-settings-tip student-notif-settings-tip--primary">
                        <span class="student-notif-settings-tip__icon"><i class="fe fe-bell"></i></span>
                        <div>
                            <h6 class="mb-1">الإشعارات الداخلية</h6>
                            <p class="mb-0">تظهر في جرس الإشعارات داخل النظام فور حدوث الحدث.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="student-notif-settings-tip student-notif-settings-tip--success">
                        <span class="student-notif-settings-tip__icon"><i class="fe fe-mail"></i></span>
                        <div>
                            <h6 class="mb-1">البريد الإلكتروني</h6>
                            <p class="mb-0">تُرسل إلى: <strong>{{ auth()->user()->email }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('student-notif-settings-form');
    const internalCountEl = document.getElementById('notif-internal-count');
    const emailCountEl = document.getElementById('notif-email-count');
    const saveBtn = document.getElementById('notif-save-btn');
    const importantKeys = @json($importantKeys);

    function formatNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function animateCount(el, target) {
        if (!el) return;
        var duration = 500;
        var start = performance.now();
        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        animateCount(el, parseFloat(el.dataset.countup || '0'));
    });

    function updateCounts() {
        var internal = document.querySelectorAll('.js-notif-internal:checked').length;
        var email = document.querySelectorAll('.js-notif-email:checked').length;
        animateCount(internalCountEl, internal);
        animateCount(emailCountEl, email);
    }

    function refreshRowState(row) {
        if (!row) return;
        var hasAny = row.querySelector('.js-notif-internal:checked') || row.querySelector('.js-notif-email:checked');
        row.classList.toggle('is-active', !!hasAny);
        row.classList.toggle('is-muted', !hasAny);
    }

    function setChecked(selector, checked) {
        document.querySelectorAll(selector).forEach(function (input) {
            input.checked = checked;
            refreshRowState(input.closest('.student-notif-settings-row'));
        });
        updateCounts();
        markDirty();
    }

    var dirty = false;
    function markDirty() {
        dirty = true;
        if (saveBtn) {
            saveBtn.classList.add('btn-pulse');
        }
    }

    form?.addEventListener('change', function (e) {
        if (e.target.matches('.js-notif-internal, .js-notif-email')) {
            refreshRowState(e.target.closest('.student-notif-settings-row'));
            updateCounts();
            markDirty();
        }
    });

    document.querySelectorAll('[data-notif-preset]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var preset = btn.getAttribute('data-notif-preset');
            if (preset === 'internal-all') {
                setChecked('.js-notif-internal', true);
            } else if (preset === 'email-all') {
                setChecked('.js-notif-email', true);
            } else if (preset === 'none') {
                setChecked('.js-notif-internal, .js-notif-email', false);
            } else if (preset === 'important') {
                document.querySelectorAll('.student-notif-settings-row').forEach(function (row) {
                    var key = row.getAttribute('data-notif-key');
                    var isImportant = importantKeys.indexOf(key) !== -1;
                    var internal = row.querySelector('.js-notif-internal');
                    var email = row.querySelector('.js-notif-email');
                    if (internal) internal.checked = isImportant;
                    if (email) email.checked = isImportant;
                    refreshRowState(row);
                });
                updateCounts();
                markDirty();
            }
        });
    });

    form?.addEventListener('submit', function () {
        dirty = false;
        if (saveBtn) saveBtn.classList.remove('btn-pulse');
    });

    window.addEventListener('beforeunload', function (e) {
        if (dirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
})();
</script>
@endpush
