@if(count($recentCompletions) > 0)
    <div class="card custom-card student-my-courses-panel mt-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="avatar avatar-sm bg-success-transparent">
                    <i class="fe fe-activity text-success"></i>
                </span>
                <h6 class="card-title mb-0">آخر الإنجازات</h6>
            </div>

            <div class="student-progress-timeline">
                @foreach($recentCompletions as $completion)
                    <div class="student-progress-timeline__item">
                        <span class="student-progress-timeline__icon">
                            <i class="fe fe-check"></i>
                        </span>
                        <div class="student-progress-timeline__content">
                            <h6 class="student-progress-timeline__title mb-1">{{ $completion->module->title }}</h6>
                            <p class="student-progress-timeline__meta mb-0">
                                <span><i class="fe fe-clock me-1"></i>{{ $completion->completed_at?->diffForHumans() ?? '—' }}</span>
                                @if($completion->score)
                                    <span><i class="fe fe-star me-1"></i>الدرجة: {{ $completion->score }}%</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
