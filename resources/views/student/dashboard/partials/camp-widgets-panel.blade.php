@if (($activeCampEnrollments ?? collect())->isNotEmpty())
<div class="card custom-card admin-shortcuts-panel dashboard-fade-in mt-2">
    <div class="card-header border-0 pb-2">
        <div class="d-flex align-items-start gap-2">
            <span class="avatar avatar-sm bg-success-transparent">
                <i class="fe fe-flag text-success"></i>
            </span>
            <div>
                <h5 class="card-title mb-1">معسكراتي</h5>
                <p class="text-muted fs-12 mb-0">متابعة معسكراتك المسجّلة والأيام المتبقية</p>
            </div>
        </div>
    </div>
    @php
        $campCount = $activeCampEnrollments->count();
        $campColumnClass = $campCount >= 3
            ? 'col-xl-4 col-lg-4 col-md-4 col-sm-12'
            : 'col-xl-6 col-lg-6 col-md-6 col-sm-12';
    @endphp
    <div class="card-body pt-2">
        <div class="row g-3">
            @foreach ($activeCampEnrollments as $campIndex => $enrollment)
                @include('student.dashboard.partials.camp-countdown-widget', [
                    'enrollment' => $enrollment,
                    'platformJoinedAt' => $platformJoinedAt,
                    'columnClass' => $campColumnClass,
                    'staggerDelay' => $campIndex * 35,
                ])
            @endforeach
        </div>
    </div>
</div>
@endif
