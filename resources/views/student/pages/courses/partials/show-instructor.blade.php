@if($course->instructor)
    <div class="card custom-card group-show-members-card dashboard-fade-in">
        <div class="card-body">
            <div class="student-course-instructor-card">
                @if($course->instructor->avatar)
                    <img src="{{ asset('storage/' . $course->instructor->avatar) }}"
                         alt="{{ $course->instructor->name }}"
                         class="student-course-instructor-card__avatar">
                @else
                    <div class="student-course-instructor-card__avatar student-course-instructor-card__avatar--placeholder">
                        {{ mb_substr($course->instructor->name, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0 flex-fill">
                    <h5 class="mb-1 fw-bold">{{ $course->instructor->name }}</h5>
                    @if($course->instructor->title)
                        <p class="text-muted mb-2 fs-13">{{ $course->instructor->title }}</p>
                    @endif
                    <span class="group-show-chip group-show-chip--sm">
                        <i class="fe fe-book me-1"></i>
                        {{ $course->instructor->courses_count ?? 0 }} كورس
                    </span>
                </div>
            </div>

            @if($course->instructor->bio)
                <div class="student-course-instructor-bio mt-4 pt-4 border-top">
                    <h6 class="fw-semibold mb-3">نبذة عن المدرب</h6>
                    <p class="text-muted mb-0 lh-lg">{{ $course->instructor->bio }}</p>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="card custom-card group-show-members-card dashboard-fade-in">
        <div class="card-body">
            <div class="group-show-empty py-4">
                <i class="fe fe-user group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لا توجد معلومات عن المدرب</h5>
                <p class="group-show-empty__desc mb-0">سيتم إضافة بيانات المدرب قريباً.</p>
            </div>
        </div>
    </div>
@endif
