@extends('admin.layouts.master')

@section('page-title')
    التحديات البرمجية
@stop

@push('styles')
<style>
    .pch-admin {
        --pa-ink: #0f172a;
        --pa-muted: #64748b;
        --pa-border: #e2e8f0;
        --pa-card: #fff;
        --pa-soft: #f8fafc;
        --pa-primary: #2563eb;
    }

    .pch-admin__hero {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin: 1.25rem 0;
        padding: 1.25rem 1.4rem;
        border: 1px solid var(--pa-border);
        border-radius: 16px;
        background: var(--pa-soft);
    }

    .pch-admin__hero h5 {
        margin: 0 0 0.35rem;
        font-weight: 800;
        color: var(--pa-ink);
    }

    .pch-admin__hero p {
        margin: 0;
        color: var(--pa-muted);
        font-size: 0.9rem;
    }

    .pch-admin__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .pch-admin__btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.55rem 0.95rem;
        border-radius: 0.55rem;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .pch-admin__btn--primary {
        background: var(--pa-primary);
        color: #fff !important;
    }

    .pch-admin__btn--primary:hover {
        background: #1d4ed8;
        color: #fff !important;
    }

    .pch-admin__btn--soft {
        background: #fff;
        border-color: var(--pa-border);
        color: var(--pa-ink) !important;
    }

    .pch-admin__btn--soft:hover {
        background: #f1f5f9;
        color: var(--pa-ink) !important;
    }

    .pch-admin__btn--warn {
        background: #fff7ed;
        border-color: #fdba74;
        color: #c2410c !important;
    }

    .pch-admin__btn--warn:hover {
        background: #ffedd5;
        color: #9a3412 !important;
    }

    .pch-admin__stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 767.98px) {
        .pch-admin__stats { grid-template-columns: 1fr; }
    }

    .pch-admin__stat {
        border: 1px solid var(--pa-border);
        border-radius: 14px;
        background: var(--pa-card);
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .pch-admin__stat-icon {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .pch-admin__stat--total .pch-admin__stat-icon { background: #dbeafe; color: #1d4ed8; }
    .pch-admin__stat--live .pch-admin__stat-icon { background: #dcfce7; color: #15803d; }
    .pch-admin__stat--pending .pch-admin__stat-icon { background: #ffedd5; color: #c2410c; }

    .pch-admin__stat-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--pa-muted);
        margin-bottom: 0.15rem;
    }

    .pch-admin__stat-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--pa-ink);
        line-height: 1;
    }

    .pch-admin__list {
        border: 1px solid var(--pa-border);
        border-radius: 16px;
        background: var(--pa-card);
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .pch-admin__list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.95rem 1.15rem;
        border-bottom: 1px solid var(--pa-border);
        background: var(--pa-soft);
    }

    .pch-admin__list-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--pa-ink);
    }

    .pch-admin__item {
        display: grid;
        grid-template-columns: auto minmax(0, 1.4fr) minmax(0, 0.9fr) auto;
        gap: 1rem;
        align-items: center;
        padding: 1.05rem 1.15rem;
        border-bottom: 1px solid var(--pa-border);
    }

    .pch-admin__item:last-child { border-bottom: 0; }
    .pch-admin__item:hover { background: #f8fafc; }

    @media (max-width: 991.98px) {
        .pch-admin__item {
            grid-template-columns: auto 1fr;
            gap: 0.7rem 0.85rem;
        }
        .pch-admin__item-meta,
        .pch-admin__item-actions {
            grid-column: 2;
        }
    }

    .pch-admin__cover {
        width: 3.25rem;
        height: 3.25rem;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.25rem;
        flex-shrink: 0;
        background: #2563eb;
    }

    .pch-admin__cover--code { background: #0f766e; }

    .pch-admin__title {
        margin: 0 0 0.3rem;
        font-size: 1rem;
        font-weight: 800;
        color: var(--pa-ink);
    }

    .pch-admin__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .pch-admin__tag {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
    }

    .pch-admin__tag--web { background: #dbeafe; color: #1d4ed8; }
    .pch-admin__tag--code { background: #ccfbf1; color: #0f766e; }
    .pch-admin__tag--easy { background: #dcfce7; color: #15803d; }
    .pch-admin__tag--medium { background: #fef3c7; color: #b45309; }
    .pch-admin__tag--hard { background: #fee2e2; color: #b91c1c; }
    .pch-admin__tag--expert { background: #ede9fe; color: #6d28d9; }
    .pch-admin__tag--live { background: #dcfce7; color: #15803d; }
    .pch-admin__tag--draft { background: #e2e8f0; color: #475569; }
    .pch-admin__tag--lib { background: #e0e7ff; color: #3730a3; }
    .pch-admin__tag--restricted { background: #ffedd5; color: #c2410c; }

    .pch-admin__meta-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--pa-muted);
        margin-bottom: 0.15rem;
    }

    .pch-admin__meta-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--pa-ink);
    }

    .pch-admin__item-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        justify-content: flex-end;
    }

    .pch-admin__action {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.4rem 0.7rem;
        border-radius: 0.5rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid var(--pa-border);
        background: #fff;
        color: var(--pa-ink) !important;
    }

    .pch-admin__action:hover {
        background: #f8fafc;
        color: var(--pa-ink) !important;
    }

    .pch-admin__action--main {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8 !important;
    }

    .pch-admin__action--main:hover {
        background: #dbeafe;
        color: #1e40af !important;
    }

    .pch-admin__action--danger {
        color: #b91c1c !important;
        border-color: #fecaca;
        background: #fff;
    }

    .pch-admin__action--danger:hover {
        background: #fef2f2;
        color: #991b1b !important;
    }

    .pch-admin__count {
        display: inline-flex;
        min-width: 1.25rem;
        justify-content: center;
        padding: 0.05rem 0.35rem;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .pch-admin__empty {
        text-align: center;
        padding: 3.5rem 1.5rem;
    }

    .pch-admin__empty-icon {
        width: 3.75rem;
        height: 3.75rem;
        margin: 0 auto 1rem;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 1.6rem;
    }

    [data-theme-mode="dark"] .pch-admin {
        --pa-ink: #f1f5f9;
        --pa-muted: #94a3b8;
        --pa-border: rgba(148, 163, 184, 0.25);
        --pa-card: rgba(15, 23, 42, 0.55);
        --pa-soft: rgba(15, 23, 42, 0.4);
    }

    [data-theme-mode="dark"] .pch-admin__item:hover,
    [data-theme-mode="dark"] .pch-admin__action {
        background: rgba(255, 255, 255, 0.03);
    }

    /* Delete modal — matches pch-admin language */
    .pch-delete-modal .modal-content {
        border: 1px solid var(--pa-border, #e2e8f0);
        border-radius: 18px;
        background: var(--pa-card, #fff);
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.14);
        overflow: hidden;
    }

    .pch-delete-modal .modal-header {
        padding: 0.85rem 1rem 0;
        border: 0;
        background: transparent;
    }

    .pch-delete-modal .btn-close {
        margin: 0;
        opacity: 0.55;
        box-shadow: none;
    }

    .pch-delete-modal .modal-body {
        padding: 0.35rem 1.5rem 1rem;
        text-align: center;
    }

    .pch-delete-modal__icon {
        width: 3.5rem;
        height: 3.5rem;
        margin: 0 auto 1rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        font-size: 1.35rem;
    }

    .pch-delete-modal__title {
        margin: 0 0 0.4rem;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--pa-ink, #0f172a);
    }

    .pch-delete-modal__message {
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.65;
        color: var(--pa-muted, #64748b);
    }

    .pch-delete-modal__warn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.85rem;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: #fff7ed;
        border: 1px solid #fdba74;
        color: #c2410c;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .pch-delete-modal .modal-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.55rem;
        padding: 0 1.5rem 1.35rem;
        border: 0;
        background: transparent;
    }

    .pch-delete-modal__btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-width: 7.5rem;
        padding: 0.55rem 1rem;
        border-radius: 0.55rem;
        font-size: 0.86rem;
        font-weight: 700;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .pch-delete-modal__btn--cancel {
        background: #fff;
        border-color: var(--pa-border, #e2e8f0);
        color: var(--pa-ink, #0f172a);
    }

    .pch-delete-modal__btn--cancel:hover {
        background: var(--pa-soft, #f8fafc);
        color: var(--pa-ink, #0f172a);
    }

    .pch-delete-modal__btn--danger {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }

    .pch-delete-modal__btn--danger:hover {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #fff;
    }

    [data-theme-mode="dark"] .pch-delete-modal .modal-content {
        background: #0f172a;
        border-color: rgba(148, 163, 184, 0.25);
    }

    [data-theme-mode="dark"] .pch-delete-modal__icon {
        background: rgba(185, 28, 28, 0.18);
        border-color: rgba(252, 165, 165, 0.35);
        color: #fca5a5;
    }

    [data-theme-mode="dark"] .pch-delete-modal__btn--cancel {
        background: rgba(15, 23, 42, 0.6);
        border-color: rgba(148, 163, 184, 0.25);
        color: #f1f5f9;
    }

    [data-theme-mode="dark"] .pch-delete-modal__warn {
        background: rgba(194, 65, 12, 0.18);
        border-color: rgba(253, 186, 116, 0.35);
        color: #fdba74;
    }
</style>
@endpush

@section('content')
    @php
        $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
        $gradeLabels = ['manual' => 'يدوي', 'auto' => 'آلي', 'hybrid' => 'هجين'];
    @endphp

    <div class="main-content app-content pch-admin">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="pch-admin__hero">
                <div>
                    <nav>
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">التحديات البرمجية</li>
                        </ol>
                    </nav>
                    <h5 class="page-title fs-21">التحديات البرمجية</h5>
                    <p>أنشئ تحديات ويب أو تنفيذ كود، وراجع محاولات الطلاب من مكان واحد.</p>
                </div>
                <div class="pch-admin__actions">
                    <a href="{{ route('admin.challenge-grading.index') }}" class="pch-admin__btn pch-admin__btn--warn">
                        <i class="fe fe-check-square"></i>
                        تقييم التسليمات
                        @if(($stats['pending'] ?? 0) > 0)
                            <span class="pch-admin__count">{{ $stats['pending'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('programming-challenges.create') }}" class="pch-admin__btn pch-admin__btn--primary">
                        <i class="fe fe-plus"></i>
                        تحدي جديد
                    </a>
                </div>
            </div>

            <div class="pch-admin__stats">
                <div class="pch-admin__stat pch-admin__stat--total">
                    <span class="pch-admin__stat-icon"><i class="fe fe-layers"></i></span>
                    <div>
                        <span class="pch-admin__stat-label">إجمالي التحديات</span>
                        <span class="pch-admin__stat-value">{{ $stats['total'] }}</span>
                    </div>
                </div>
                <div class="pch-admin__stat pch-admin__stat--live">
                    <span class="pch-admin__stat-icon"><i class="fe fe-check-circle"></i></span>
                    <div>
                        <span class="pch-admin__stat-label">منشورة</span>
                        <span class="pch-admin__stat-value">{{ $stats['published'] }}</span>
                    </div>
                </div>
                <div class="pch-admin__stat pch-admin__stat--pending">
                    <span class="pch-admin__stat-icon"><i class="fe fe-clock"></i></span>
                    <div>
                        <span class="pch-admin__stat-label">بانتظار التقييم</span>
                        <span class="pch-admin__stat-value">{{ $stats['pending'] }}</span>
                    </div>
                </div>
            </div>

            <div class="pch-admin__list">
                <div class="pch-admin__list-head">
                    <h6 class="pch-admin__list-title"><i class="fe fe-code me-1"></i>قائمة التحديات</h6>
                    <span class="badge bg-primary-transparent">{{ $challenges->total() }} تحدي</span>
                </div>

                @forelse($challenges as $challenge)
                    @php
                        $isWeb = $challenge->challenge_type === 'web_sandbox';
                    @endphp
                    <div class="pch-admin__item">
                        <div class="pch-admin__cover {{ $isWeb ? '' : 'pch-admin__cover--code' }}">
                            <i class="fe {{ $isWeb ? 'fe-layout' : 'fe-terminal' }}"></i>
                        </div>

                        <div>
                            <h6 class="pch-admin__title">{{ $challenge->title }}</h6>
                            <div class="pch-admin__tags">
                                <span class="pch-admin__tag {{ $isWeb ? 'pch-admin__tag--web' : 'pch-admin__tag--code' }}">
                                    {{ $isWeb ? 'ويب' : 'تنفيذ كود' }}
                                </span>
                                <span class="pch-admin__tag pch-admin__tag--{{ $challenge->difficulty }}">
                                    {{ $diffLabels[$challenge->difficulty] ?? $challenge->difficulty }}
                                </span>
                                <span class="pch-admin__tag">
                                    تقييم {{ $gradeLabels[$challenge->grading_mode] ?? $challenge->grading_mode }}
                                </span>
                                @if($challenge->is_published)
                                    <span class="pch-admin__tag pch-admin__tag--live">منشور</span>
                                @else
                                    <span class="pch-admin__tag pch-admin__tag--draft">مسودة</span>
                                @endif
                                @if($challenge->is_standalone)
                                    <span class="pch-admin__tag pch-admin__tag--lib">مكتبة</span>
                                @endif
                                @php
                                    $audienceTargets = $challenge->targets;
                                    $courseCount = $audienceTargets->pluck('course_id')->unique()->count();
                                    $groupCount = $audienceTargets->whereNotNull('group_id')->count();
                                    $audienceTitle = $audienceTargets->map(function ($t) {
                                        $label = $t->course?->title ?? ('كورس #'.$t->course_id);
                                        if ($t->group) {
                                            $label .= ' · '.$t->group->name;
                                        } else {
                                            $label .= ' · كل الطلاب';
                                        }
                                        return $label;
                                    })->implode("\n");
                                @endphp
                                @if($courseCount > 0)
                                    <span class="pch-admin__tag pch-admin__tag--restricted" title="{{ $audienceTitle }}">
                                        مقيّد · {{ $courseCount }} كورس
                                        @if($groupCount > 0)
                                            · {{ $groupCount }} مجموعة
                                        @endif
                                    </span>
                                @else
                                    <span class="pch-admin__tag">عام</span>
                                @endif
                            </div>
                        </div>

                        <div class="pch-admin__item-meta">
                            <span class="pch-admin__meta-label">محاولات الطلاب</span>
                            <div class="pch-admin__meta-value">
                                {{ $challenge->submitted_attempts_count }} تسليم
                                @if(($challenge->pending_attempts_count ?? 0) > 0)
                                    <span class="text-warning small fw-bold">· {{ $challenge->pending_attempts_count }} بانتظار</span>
                                @endif
                            </div>
                            @if($challenge->languages->isNotEmpty())
                                <div class="small text-muted mt-1">
                                    {{ $challenge->languages->pluck('name')->take(3)->implode(' · ') }}
                                </div>
                            @endif
                        </div>

                        <div class="pch-admin__item-actions">
                            <a href="{{ route('programming-challenges.attempts', $challenge->id) }}"
                               class="pch-admin__action pch-admin__action--main"
                               title="كل محاولات الطلاب">
                                <i class="fe fe-users"></i>
                                محاولات
                                @if(($challenge->submitted_attempts_count ?? 0) > 0)
                                    <span class="pch-admin__count">{{ $challenge->submitted_attempts_count }}</span>
                                @endif
                            </a>
                            <a href="{{ route('programming-challenges.manage-languages', $challenge->id) }}" class="pch-admin__action" title="اللغات">
                                <i class="fe fe-globe"></i> لغات
                            </a>
                            <a href="{{ route('programming-challenges.manage-starter', $challenge->id) }}" class="pch-admin__action" title="الكود الابتدائي">
                                <i class="fe fe-file-text"></i> كود
                            </a>
                            <a href="{{ route('programming-challenges.edit', $challenge->id) }}" class="pch-admin__action" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                            <button type="button"
                                    class="pch-admin__action pch-admin__action--danger"
                                    title="حذف"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteChallengeModal"
                                    data-challenge-id="{{ $challenge->id }}"
                                    data-challenge-title="{{ e($challenge->title) }}">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="pch-admin__empty">
                        <div class="pch-admin__empty-icon"><i class="fe fe-code"></i></div>
                        <h6 class="fw-bold mb-1">لا توجد تحديات بعد</h6>
                        <p class="text-muted mb-3">ابدأ بإنشاء أول تحدٍ برمجي للطلاب.</p>
                        <a href="{{ route('programming-challenges.create') }}" class="pch-admin__btn pch-admin__btn--primary">
                            <i class="fe fe-plus"></i> تحدي جديد
                        </a>
                    </div>
                @endforelse

                @if($challenges->hasPages())
                    <div class="p-3 border-top">{{ $challenges->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Delete confirmation styled like pch-admin --}}
    <div class="modal fade pch-delete-modal" id="deleteChallengeModal" tabindex="-1" aria-labelledby="deleteChallengeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="pch-delete-modal__icon">
                        <i class="fe fe-trash-2"></i>
                    </div>
                    <h5 class="pch-delete-modal__title" id="deleteChallengeModalLabel">حذف التحدي</h5>
                    <p class="pch-delete-modal__message" id="deleteChallengeMessage">هل أنت متأكد من حذف هذا التحدي؟</p>
                    <span class="pch-delete-modal__warn">
                        <i class="fe fe-alert-triangle"></i>
                        لن يمكن التراجع عن هذا الإجراء
                    </span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="pch-delete-modal__btn pch-delete-modal__btn--cancel" data-bs-dismiss="modal">
                        <i class="fe fe-x"></i>إلغاء
                    </button>
                    <form id="deleteChallengeForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pch-delete-modal__btn pch-delete-modal__btn--danger" id="confirmDeleteChallenge">
                            <i class="fe fe-trash-2"></i>حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('deleteChallengeModal');
    var form = document.getElementById('deleteChallengeForm');
    var messageEl = document.getElementById('deleteChallengeMessage');
    if (!modalEl || !form) return;

    var destroyBase = @json(url('/admin/programming-challenges'));

    modalEl.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        if (!btn) return;
        var id = btn.getAttribute('data-challenge-id');
        var title = btn.getAttribute('data-challenge-title') || 'هذا التحدي';
        form.action = destroyBase + '/' + id;
        if (messageEl) {
            messageEl.textContent = 'هل أنت متأكد من حذف التحدي «' + title + '»؟';
        }
    });
});
</script>
@endpush
