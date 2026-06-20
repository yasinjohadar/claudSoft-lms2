<div class="card custom-card group-show-members-card dashboard-fade-in">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-0">
            <i class="fe fe-star me-2 text-warning"></i>تقييمات الطلاب
        </h6>
    </div>
    <div class="card-body">
        @if(($course->reviews_count ?? 0) > 0)
            <div class="student-course-reviews-summary text-center mb-4 pb-4 border-bottom">
                <div class="student-course-reviews-summary__score">{{ number_format($course->average_rating ?? 0, 1) }}</div>
                <div class="student-course-reviews-summary__stars mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fe fe-star {{ $i <= round($course->average_rating ?? 0) ? '' : 'opacity-25' }}"></i>
                    @endfor
                </div>
                <p class="text-muted fs-13 mb-0">{{ $course->reviews_count }} تقييم</p>
            </div>

            <div class="group-show-empty py-3">
                <i class="fe fe-message-square group-show-empty__icon"></i>
                <p class="group-show-empty__desc mb-0">لا توجد تقييمات نصية حتى الآن</p>
            </div>
        @else
            <div class="group-show-empty py-5">
                <i class="fe fe-star group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لا توجد تقييمات بعد</h5>
                <p class="group-show-empty__desc mb-0">كن أول من يقيّم هذا الكورس بعد إتمامه.</p>
            </div>
        @endif
    </div>
</div>
