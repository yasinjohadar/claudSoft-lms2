@extends('admin.layouts.master')

@section('page-title')
    تقييم التحديات البرمجية
@stop

@push('styles')
<style>
    .cg-queue {
        --cg-ink: #0f172a;
        --cg-muted: #64748b;
        --cg-border: #e2e8f0;
        --cg-card: #fff;
        --cg-soft: #f8fafc;
        --cg-primary: #2563eb;
        --cg-warn: #d97706;
    }

    .cg-queue__hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin: 1.25rem 0 1.25rem;
        padding: 1.25rem 1.4rem;
        border: 1px solid var(--cg-border);
        border-radius: 16px;
        background: var(--cg-soft);
    }

    .cg-queue__hero h5 {
        margin: 0 0 0.35rem;
        font-weight: 800;
        color: var(--cg-ink);
    }

    .cg-queue__hero p {
        margin: 0;
        color: var(--cg-muted);
        font-size: 0.9rem;
    }

    .cg-queue__stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .cg-queue__stats { grid-template-columns: 1fr; }
    }

    .cg-queue__stat {
        border: 1px solid var(--cg-border);
        border-radius: 14px;
        background: var(--cg-card);
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .cg-queue__stat-icon {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .cg-queue__stat--pending .cg-queue__stat-icon {
        background: #ffedd5;
        color: #c2410c;
    }

    .cg-queue__stat--today .cg-queue__stat-icon {
        background: #dcfce7;
        color: #15803d;
    }

    .cg-queue__stat--total .cg-queue__stat-icon {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .cg-queue__stat-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--cg-muted);
        margin-bottom: 0.15rem;
    }

    .cg-queue__stat-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--cg-ink);
        line-height: 1;
    }

    .cg-queue__list {
        border: 1px solid var(--cg-border);
        border-radius: 16px;
        background: var(--cg-card);
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .cg-queue__list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1.15rem;
        border-bottom: 1px solid var(--cg-border);
        background: var(--cg-soft);
    }

    .cg-queue__list-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--cg-ink);
    }

    .cg-queue__item {
        display: grid;
        grid-template-columns: auto minmax(0, 1.2fr) minmax(0, 1fr) auto auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--cg-border);
        transition: background-color 0.15s ease;
    }

    .cg-queue__item:last-child { border-bottom: 0; }
    .cg-queue__item:hover { background: #f8fafc; }

    @media (max-width: 991.98px) {
        .cg-queue__item {
            grid-template-columns: auto 1fr;
            gap: 0.65rem 0.85rem;
        }
        .cg-queue__item-meta,
        .cg-queue__item-attempt,
        .cg-queue__item-action {
            grid-column: 2;
        }
        .cg-queue__item-action { justify-self: start; }
    }

    .cg-queue__avatar {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 800;
        font-size: 0.95rem;
    }

    .cg-queue__challenge {
        margin: 0 0 0.25rem;
        font-size: 0.98rem;
        font-weight: 800;
        color: var(--cg-ink);
    }

    .cg-queue__student {
        margin: 0;
        color: var(--cg-muted);
        font-size: 0.86rem;
    }

    .cg-queue__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.45rem;
    }

    .cg-queue__tag {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
    }

    .cg-queue__tag--web { background: #dbeafe; color: #1d4ed8; }
    .cg-queue__tag--code { background: #ccfbf1; color: #0f766e; }
    .cg-queue__tag--wait { background: #ffedd5; color: #c2410c; }

    .cg-queue__meta-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--cg-muted);
        margin-bottom: 0.15rem;
    }

    .cg-queue__meta-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--cg-ink);
    }

    .cg-queue__attempt {
        min-width: 4.5rem;
        text-align: center;
        padding: 0.45rem 0.7rem;
        border-radius: 12px;
        border: 1px solid var(--cg-border);
        background: var(--cg-soft);
    }

    .cg-queue__attempt strong {
        display: block;
        font-size: 1.05rem;
        color: var(--cg-ink);
    }

    .cg-queue__attempt span {
        font-size: 0.72rem;
        color: var(--cg-muted);
        font-weight: 700;
    }

    .cg-queue__btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 0.95rem;
        border-radius: 0.55rem;
        background: var(--cg-primary);
        color: #fff !important;
        font-size: 0.84rem;
        font-weight: 700;
        text-decoration: none;
        border: 0;
    }

    .cg-queue__btn:hover {
        background: #1d4ed8;
        color: #fff !important;
    }

    .cg-queue__empty {
        text-align: center;
        padding: 3.5rem 1.5rem;
    }

    .cg-queue__empty-icon {
        width: 3.75rem;
        height: 3.75rem;
        margin: 0 auto 1rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #dcfce7;
        color: #15803d;
        font-size: 1.6rem;
    }

    .cg-queue__empty h6 {
        margin: 0 0 0.4rem;
        font-weight: 800;
        color: var(--cg-ink);
    }

    .cg-queue__empty p {
        margin: 0 0 1rem;
        color: var(--cg-muted);
        font-size: 0.9rem;
    }

    [data-theme-mode="dark"] .cg-queue {
        --cg-ink: #f1f5f9;
        --cg-muted: #94a3b8;
        --cg-border: rgba(148, 163, 184, 0.25);
        --cg-card: rgba(15, 23, 42, 0.55);
        --cg-soft: rgba(15, 23, 42, 0.4);
    }

    [data-theme-mode="dark"] .cg-queue__item:hover {
        background: rgba(255, 255, 255, 0.03);
    }
</style>
@endpush

@section('content')
    <div class="main-content app-content cg-queue">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="cg-queue__hero">
                <div>
                    <nav>
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('programming-challenges.index') }}">التحديات البرمجية</a></li>
                            <li class="breadcrumb-item active">التقييم</li>
                        </ol>
                    </nav>
                    <h5 class="page-title fs-21">تسليمات بانتظار التقييم</h5>
                    <p>راجع كود الطالب، شاهد المعاينة، ثم احفظ الدرجة والملاحظة.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('programming-challenges.index') }}" class="btn btn-light btn-sm">
                        <i class="fe fe-code me-1"></i>إدارة التحديات
                    </a>
                </div>
            </div>

            @if($groups->isNotEmpty())
                <div class="cg-queue__list mb-3">
                    <div class="p-3">
                        <form method="GET" class="row g-2 align-items-end">
                            <div class="col-sm-4 col-md-3">
                                <label class="form-label small text-muted mb-1">المجموعة</label>
                                <select name="group_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">كل المجموعات</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ (string) $groupId === (string) $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($groupId)
                                <div class="col-sm-4 col-md-3">
                                    <a href="{{ route('admin.challenge-grading.index') }}" class="btn btn-light btn-sm">إلغاء الفلتر</a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endif

            <div class="cg-queue__stats">
                <div class="cg-queue__stat cg-queue__stat--pending">
                    <span class="cg-queue__stat-icon"><i class="fe fe-clock"></i></span>
                    <div>
                        <span class="cg-queue__stat-label">بانتظار التقييم</span>
                        <span class="cg-queue__stat-value">{{ $stats['pending'] }}</span>
                    </div>
                </div>
                <div class="cg-queue__stat cg-queue__stat--today">
                    <span class="cg-queue__stat-icon"><i class="fe fe-check-circle"></i></span>
                    <div>
                        <span class="cg-queue__stat-label">قُيِّمت اليوم</span>
                        <span class="cg-queue__stat-value">{{ $stats['graded_today'] }}</span>
                    </div>
                </div>
                <div class="cg-queue__stat cg-queue__stat--total">
                    <span class="cg-queue__stat-icon"><i class="fe fe-award"></i></span>
                    <div>
                        <span class="cg-queue__stat-label">إجمالي المُقيَّمة</span>
                        <span class="cg-queue__stat-value">{{ $stats['graded_total'] }}</span>
                    </div>
                </div>
            </div>

            <div class="cg-queue__list">
                <div class="cg-queue__list-head">
                    <h6 class="cg-queue__list-title"><i class="fe fe-inbox me-1"></i>قائمة التسليمات</h6>
                    <span class="badge bg-warning-transparent">{{ $attempts->total() }} عنصر</span>
                </div>

                @forelse($attempts as $attempt)
                    @php
                        $studentName = $attempt->student->name ?? $attempt->student->email ?? 'طالب';
                        $initials = collect(preg_split('/\s+/u', trim($studentName)))
                            ->filter()
                            ->take(2)
                            ->map(function ($p) {
                                return mb_substr($p, 0, 1);
                            })
                            ->implode('');
                        $isWeb = $attempt->challenge?->challenge_type === 'web_sandbox';
                        $filesCount = $attempt->latestSubmission?->files?->count() ?? 0;
                    @endphp
                    <div class="cg-queue__item">
                        <div class="cg-queue__avatar" aria-hidden="true">{{ $initials ?: 'ط' }}</div>

                        <div>
                            <h6 class="cg-queue__challenge">{{ $attempt->challenge->title ?? 'تحدي' }}</h6>
                            <p class="cg-queue__student">{{ $studentName }}</p>
                            <div class="cg-queue__tags">
                                <span class="cg-queue__tag {{ $isWeb ? 'cg-queue__tag--web' : 'cg-queue__tag--code' }}">
                                    {{ $isWeb ? 'ويب HTML/CSS/JS' : 'تنفيذ كود' }}
                                </span>
                                <span class="cg-queue__tag cg-queue__tag--wait">بانتظار التقييم</span>
                                @if($filesCount > 0)
                                    <span class="cg-queue__tag"><i class="fe fe-file-text"></i> {{ $filesCount }} ملف</span>
                                @endif
                            </div>
                        </div>

                        <div class="cg-queue__item-meta">
                            <span class="cg-queue__meta-label">تاريخ التسليم</span>
                            <div class="cg-queue__meta-value">
                                {{ $attempt->submitted_at?->format('Y-m-d H:i') ?? '—' }}
                            </div>
                            @if($attempt->submitted_at)
                                <div class="small text-muted mt-1">{{ $attempt->submitted_at->diffForHumans() }}</div>
                            @endif
                        </div>

                        <div class="cg-queue__item-attempt cg-queue__attempt">
                            <strong>#{{ $attempt->attempt_number }}</strong>
                            <span>المحاولة</span>
                        </div>

                        <div class="cg-queue__item-action">
                            <a href="{{ route('admin.challenge-grading.show', $attempt->id) }}" class="cg-queue__btn">
                                <i class="fe fe-edit-3"></i>
                                بدء التقييم
                            </a>
                            @if($attempt->challenge)
                                <div class="mt-2">
                                    <a href="{{ route('programming-challenges.attempts', $attempt->challenge->id) }}" class="small">
                                        كل محاولات التحدي
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="cg-queue__empty">
                        <div class="cg-queue__empty-icon"><i class="fe fe-check"></i></div>
                        <h6>لا توجد تسليمات معلّقة</h6>
                        <p>أحسنت — قائمة التقييم فارغة حالياً.</p>
                        <a href="{{ route('programming-challenges.index') }}" class="btn btn-outline-primary btn-sm">
                            العودة للتحديات
                        </a>
                    </div>
                @endforelse

                @if($attempts->hasPages())
                    <div class="p-3 border-top">{{ $attempts->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@stop
