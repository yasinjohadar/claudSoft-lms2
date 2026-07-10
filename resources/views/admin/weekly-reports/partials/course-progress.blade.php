@php
    $percentage = (int) ($courseProgress['percentage'] ?? 0);
    $completed = (int) ($courseProgress['completed_modules'] ?? 0);
    $total = (int) ($courseProgress['total_modules'] ?? 0);
    $barClass = match (true) {
        $percentage >= 75 => 'bg-success',
        $percentage >= 40 => 'bg-primary',
        $percentage > 0 => 'bg-warning',
        default => 'bg-secondary',
    };
@endphp

<div class="card custom-card group-show-members-card dashboard-fade-in weekly-report-course-progress-card mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h6 class="mb-1 fw-semibold">نسبة الإنجاز في الكورس</h6>
                <p class="fs-12 text-muted mb-0">
                    محسوبة من المحتوى الظاهر لمجموعة الطالب
                    @if(!empty($courseProgress['course_titles']))
                        ({{ implode('، ', $courseProgress['course_titles']) }})
                    @endif
                </p>
            </div>
            <div class="text-end">
                <span class="fs-24 fw-bold weekly-report-course-progress-card__percent {{ $percentage >= 75 ? 'text-success' : ($percentage >= 40 ? 'text-primary' : 'text-muted') }}">
                    {{ $percentage }}%
                </span>
            </div>
        </div>

        @if($total > 0)
            <div class="progress weekly-report-course-progress-card__bar mb-2" style="height: 10px;">
                <div class="progress-bar {{ $barClass }}"
                     role="progressbar"
                     style="width: {{ $percentage }}%;"
                     aria-valuenow="{{ $percentage }}"
                     aria-valuemin="0"
                     aria-valuemax="100"></div>
            </div>
            <p class="fs-12 text-muted mb-0">
                أكمل الطالب <strong class="text-body">{{ $completed }}</strong> من <strong class="text-body">{{ $total }}</strong> وحدة ظاهرة له.
            </p>
        @else
            <div class="alert alert-light border mb-0 py-2 fs-13">
                لا يوجد محتوى ظاهر لحساب نسبة الإنجاز حالياً.
            </div>
        @endif
    </div>
</div>

<style>
    .weekly-report-course-progress-card__percent {
        line-height: 1;
    }
</style>
