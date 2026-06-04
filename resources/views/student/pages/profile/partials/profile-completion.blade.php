@php
    $profileCompletion = $student->profile_completion_data;
    $pct = (float) ($profileCompletion['percentage'] ?? 0);
    $isComplete = $pct >= 100;
@endphp

<div class="card custom-card student-quizzes-panel student-profile-completion mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm bg-warning-transparent">
                    <i class="fe fe-award text-warning"></i>
                </span>
                <h6 class="card-title mb-0">اكتمال الملف الشخصي</h6>
            </div>
            <span class="badge {{ $isComplete ? 'bg-success-transparent' : 'bg-warning-transparent' }} fs-12">
                {{ $profileCompletion['percentage'] }}%
            </span>
        </div>

        <div class="student-profile-completion__header mb-2">
            <span class="text-muted fs-12">التقدم</span>
            <span class="fw-semibold {{ $isComplete ? 'text-success' : 'text-warning' }}">{{ $profileCompletion['percentage'] }}%</span>
        </div>
        <div class="student-progress-overall__track student-profile-completion__track">
            <div class="student-progress-overall__bar {{ $isComplete ? 'is-complete' : '' }}"
                 style="width: {{ max(0, min(100, $pct)) }}%"
                 role="progressbar"
                 aria-valuenow="{{ $pct }}"
                 aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>

        <p class="student-profile-completion__text mb-0 mt-3">
            تم إكمال {{ $profileCompletion['completed'] }} من أصل {{ $profileCompletion['total'] }} حقول.
            @if($profileCompletion['missing_count'] > 0)
                المتبقي: {{ $profileCompletion['missing_count'] }} حقول.
            @endif
        </p>

        @if($profileCompletion['missing_count'] > 0)
            @php $missingPreview = array_slice($profileCompletion['missing_fields'], 0, 3); @endphp
            <div class="student-profile-completion__missing mt-2">
                <i class="fe fe-info me-1"></i>
                الحقول الناقصة: {{ implode('، ', $missingPreview) }}
                @if($profileCompletion['missing_count'] > count($missingPreview))
                    ... والمزيد
                @endif
            </div>
        @endif
    </div>
</div>
