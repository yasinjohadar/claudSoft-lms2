@extends('admin.layouts.master')

@section('page-title')
    تقييم تسليم — {{ $submission->stage->title ?? '' }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}?v={{ filemtime(public_path('assets/css/project-challenge.css')) }}">
@endpush

@section('content')
    @php
        $maxScore = (float) ($submission->max_score ?? $submission->stage->max_score ?? 100);
        $linkTypes = config('project_challenges.link_types', []);
    @endphp

    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-3 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.project-grading.index') }}">تقييم التسليمات</a></li>
                        <li class="breadcrumb-item active">{{ $submission->stage->title ?? 'تقييم' }}</li>
                    </ol>
                </nav>
            </div>

            <div class="pc-form-hero dashboard-fade-in">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <i class="fe fe-check-square fa-lg"></i>
                            <span class="pc-form-hero__badge">تقييم المرحلة</span>
                        </div>
                        <h1 class="pc-form-hero__title">{{ $submission->stage->title }}</h1>
                        <p class="pc-grading-meta">
                            التحدي: <strong>{{ $submission->team->challenge->title }}</strong> —
                            الفريق: <strong>{{ $submission->team->name }}</strong> —
                            المُسلِّم: {{ $submission->submitter->name ?? $submission->submitter->email }}
                        </p>
                    </div>
                    <a href="{{ route('admin.project-grading.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fe fe-arrow-right me-1"></i> العودة للقائمة
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="pc-form-panel">
                        <div class="pc-form-panel__head">
                            <span class="pc-form-panel__icon"><i class="fe fe-link"></i></span>
                            <div>
                                <h2 class="pc-form-panel__title">روابط التسليم</h2>
                                <p class="pc-form-panel__sub">{{ $submission->links->count() }} رابط</p>
                            </div>
                        </div>
                        @if($submission->links->isNotEmpty())
                            @foreach($submission->links as $link)
                                <a href="{{ $link->url }}" target="_blank" rel="noopener" class="pc-grading-link">
                                    <div>
                                        <span class="pc-tag me-2">{{ $linkTypes[$link->link_type] ?? $link->link_type }}</span>
                                        <span>{{ $link->title ?: $link->url }}</span>
                                    </div>
                                    <i class="fe fe-external-link"></i>
                                </a>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">لا توجد روابط في هذا التسليم</p>
                        @endif
                    </div>

                    <div class="pc-form-panel">
                        <div class="pc-form-panel__head">
                            <span class="pc-form-panel__icon"><i class="fe fe-users"></i></span>
                            <div>
                                <h2 class="pc-form-panel__title">أعضاء الفريق</h2>
                                <p class="pc-form-panel__sub">{{ $submission->team->activeMembers->count() }} عضو</p>
                            </div>
                        </div>
                        <div class="pc-members">
                            @foreach($submission->team->activeMembers as $member)
                                <span class="pc-member-chip @if($member->user_id === $submission->team->leader_id) pc-member-chip--leader @endif">
                                    <i class="fe fe-user"></i>
                                    {{ $member->user->name ?? $member->user->email }}
                                    @if($member->user_id === $submission->team->leader_id)
                                        <small>(قائد)</small>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <form action="{{ route('admin.project-grading.grade', $submission->id) }}" method="POST">
                        @csrf
                        <div class="pc-form-panel pc-sidebar-sticky">
                            <div class="pc-form-panel__head">
                                <span class="pc-form-panel__icon"><i class="fe fe-edit-3"></i></span>
                                <div>
                                    <h2 class="pc-form-panel__title">التقييم</h2>
                                    <p class="pc-form-panel__sub">الدرجة من {{ $maxScore }}</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="pc-form-label">الدرجة (من {{ $maxScore }}) <span class="text-danger">*</span></label>
                                <input type="number" name="score" class="form-control pc-form-input" required min="0"
                                       max="{{ $maxScore }}" step="0.01"
                                       value="{{ old('score', $submission->score) }}">
                            </div>
                            <div class="mb-3">
                                <label class="pc-form-label">نسبة التقدم (%)</label>
                                <input type="number" name="progress_percent" class="form-control pc-form-input" min="0" max="100" step="0.01"
                                       value="{{ old('progress_percent', $submission->team->progress_percent) }}">
                            </div>
                            <div class="mb-3">
                                <label class="pc-form-label">التعليقات / الملاحظات</label>
                                <textarea name="feedback" class="form-control pc-form-input" rows="4">{{ old('feedback', $submission->feedback) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="pc-form-label">القرار <span class="text-danger">*</span></label>
                                <select name="status" class="form-select pc-form-input" required id="grade-status">
                                    <option value="approved" @selected(old('status') === 'approved')>موافقة</option>
                                    <option value="rejected" @selected(old('status') === 'rejected')>رفض</option>
                                    <option value="resubmit_required" @selected(old('status') === 'resubmit_required')>طلب إعادة تسليم</option>
                                </select>
                            </div>
                            <div class="mb-3" id="reject-reason-wrap" style="display:none">
                                <label class="pc-form-label">سبب الرفض</label>
                                <textarea name="reject_reason" class="form-control pc-form-input" rows="2">{{ old('reject_reason', $submission->reject_reason) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 rounded-pill">
                                <i class="fe fe-save me-1"></i>حفظ التقييم
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('grade-status');
    const rejectWrap = document.getElementById('reject-reason-wrap');
    function toggleReject() {
        rejectWrap.style.display = ['rejected', 'resubmit_required'].includes(statusSelect.value) ? 'block' : 'none';
    }
    statusSelect.addEventListener('change', toggleReject);
    toggleReject();
});
</script>
@endpush
