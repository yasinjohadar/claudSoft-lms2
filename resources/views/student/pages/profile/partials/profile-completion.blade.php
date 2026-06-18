@php
    $profileCompletion = $student->profile_completion_data;
    $pct = (float) ($profileCompletion['percentage'] ?? 0);
    $isComplete = $pct >= 100;
    $requiredMode = ($requiredMode ?? false) && ! $isComplete;
@endphp

<div class="card custom-card student-quizzes-panel student-profile-completion mb-4 {{ $requiredMode ? 'is-required' : '' }}">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm {{ $requiredMode ? 'bg-danger-transparent' : ($isComplete ? 'bg-success-transparent' : 'bg-warning-transparent') }}">
                    <i class="fe {{ $requiredMode ? 'fe-alert-circle text-danger' : ($isComplete ? 'fe-check-circle text-success' : 'fe-award text-warning') }}"></i>
                </span>
                <h6 class="card-title mb-0">
                    {{ $requiredMode ? 'نسبة الإكمال — يجب الوصول إلى 100%' : 'اكتمال الملف الشخصي' }}
                </h6>
            </div>
            <span class="badge {{ $isComplete ? 'bg-success-transparent' : ($requiredMode ? 'bg-danger-transparent text-danger' : 'bg-warning-transparent') }} fs-12">
                {{ $profileCompletion['percentage'] }}%
            </span>
        </div>

        @if($requiredMode)
            <p class="student-profile-completion__required-hint mb-3">
                <i class="fe fe-info me-1"></i>
                ما زال ملفك غير مكتمل. أكمل الحقول الناقصة في هذه الصفحة فقط.
            </p>
        @endif

        <div class="student-profile-completion__header mb-2">
            <span class="text-muted fs-12">التقدم</span>
            <span class="fw-semibold {{ $isComplete ? 'text-success' : ($requiredMode ? 'text-danger' : 'text-warning') }}">{{ $profileCompletion['percentage'] }}%</span>
        </div>
        <div class="student-progress-overall__track student-profile-completion__track {{ $requiredMode ? 'is-required' : '' }}">
            <div class="student-progress-overall__bar {{ $isComplete ? 'is-complete' : ($requiredMode ? 'is-required' : '') }}"
                 style="width: {{ max(0, min(100, $pct)) }}%"
                 role="progressbar"
                 aria-valuenow="{{ $pct }}"
                 aria-valuemin="0"
                 aria-valuemax="100"></div>
        </div>

        <p class="student-profile-completion__text mb-0 mt-3">
            تم إكمال {{ $profileCompletion['completed'] }} من أصل {{ $profileCompletion['total'] }} حقول.
            @if($profileCompletion['missing_count'] > 0)
                <strong class="{{ $requiredMode ? 'text-danger' : '' }}">المتبقي: {{ $profileCompletion['missing_count'] }} حقول.</strong>
            @endif
        </p>

        @if($profileCompletion['missing_count'] > 0)
            <div class="student-profile-completion__missing mt-2 {{ $requiredMode ? 'is-required' : '' }}">
                <i class="fe fe-alert-triangle me-1"></i>
                <strong>الحقول الناقصة التي يجب تعبئتها:</strong>
                {{ implode(' — ', $profileCompletion['missing_fields']) }}
            </div>
        @endif
    </div>
</div>
