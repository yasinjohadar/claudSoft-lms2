@extends('admin.layouts.master')

@section('page-title')
    تقييم تسليم — {{ $submission->stage->title ?? '' }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            @php
                $maxScore = (float) ($submission->max_score ?? $submission->stage->max_score ?? 100);
                $linkTypes = config('project_challenges.link_types', []);
            @endphp

            <div class="my-4">
                <h5 class="page-title fs-21 mb-1">تقييم: {{ $submission->stage->title }}</h5>
                <p class="text-muted mb-0">
                    التحدي: {{ $submission->team->challenge->title }} —
                    الفريق: {{ $submission->team->name }} —
                    المُسلِّم: {{ $submission->submitter->name ?? $submission->submitter->email }}
                </p>
            </div>

            <div class="row">
                <div class="col-xl-8">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">روابط التسليم</div></div>
                        <div class="card-body">
                            @if($submission->links->isNotEmpty())
                                <div class="list-group">
                                    @foreach($submission->links as $link)
                                        <a href="{{ $link->url }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-primary-transparent me-2">{{ $linkTypes[$link->link_type] ?? $link->link_type }}</span>
                                                <strong>{{ $link->title ?: $link->url }}</strong>
                                            </div>
                                            <i class="fe fe-external-link"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">لا توجد روابط في هذا التسليم</p>
                            @endif
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header"><div class="card-title">أعضاء الفريق</div></div>
                        <div class="card-body">
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
                </div>

                <div class="col-xl-4">
                    <form action="{{ route('admin.project-grading.grade', $submission->id) }}" method="POST">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header"><div class="card-title">التقييم</div></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">الدرجة (من {{ $maxScore }}) <span class="text-danger">*</span></label>
                                    <input type="number" name="score" class="form-control" required min="0"
                                           max="{{ $maxScore }}" step="0.01"
                                           value="{{ old('score', $submission->score) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">نسبة التقدم (%)</label>
                                    <input type="number" name="progress_percent" class="form-control" min="0" max="100" step="0.01"
                                           value="{{ old('progress_percent', $submission->team->progress_percent) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">التعليقات / الملاحظات</label>
                                    <textarea name="feedback" class="form-control" rows="4">{{ old('feedback', $submission->feedback) }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">القرار <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required id="grade-status">
                                        <option value="approved" @selected(old('status') === 'approved')>موافقة</option>
                                        <option value="rejected" @selected(old('status') === 'rejected')>رفض</option>
                                        <option value="resubmit_required" @selected(old('status') === 'resubmit_required')>طلب إعادة تسليم</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="reject-reason-wrap" style="display:none">
                                    <label class="form-label">سبب الرفض</label>
                                    <textarea name="reject_reason" class="form-control" rows="2">{{ old('reject_reason', $submission->reject_reason) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fe fe-save me-1"></i>حفظ التقييم
                                </button>
                            </div>
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
