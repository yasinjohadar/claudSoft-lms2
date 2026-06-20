<div class="card custom-card group-show-members-card dashboard-fade-in">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-0">
            <i class="fe fe-info me-2 text-primary"></i>وصف الكورس
        </h6>
    </div>
    <div class="card-body">
        @if($course->description)
            <div class="student-course-show-content">
                {!! nl2br(e($course->description)) !!}
            </div>
        @else
            <div class="group-show-empty py-4">
                <p class="text-muted fs-13 mb-0">لم يُضف وصف للكورس بعد.</p>
            </div>
        @endif
    </div>
</div>

@if($course->learning_outcomes)
    <div class="card custom-card group-show-members-card dashboard-fade-in mt-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-0">
                <i class="fe fe-award me-2 text-success"></i>ماذا ستتعلم
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($course->learning_outcomes as $outcome)
                    <div class="col-md-6">
                        <div class="student-course-outcome-item">
                            <span class="student-course-outcome-item__icon">
                                <i class="fe fe-check"></i>
                            </span>
                            <span>{{ $outcome }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if($course->requirements)
    <div class="card custom-card group-show-members-card dashboard-fade-in mt-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-0">
                <i class="fe fe-clipboard me-2 text-warning"></i>المتطلبات
            </h6>
        </div>
        <div class="card-body">
            <ul class="student-course-requirements-list mb-0">
                @foreach($course->requirements as $requirement)
                    <li>
                        <i class="fe fe-chevron-left"></i>
                        <span>{{ $requirement }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
