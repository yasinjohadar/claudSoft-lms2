<div class="card custom-card group-show-members-card dashboard-fade-in">
    <div class="student-group-show-hero__media">
        @if($group->image)
            <img src="{{ asset('storage/' . $group->image) }}"
                 alt="{{ $group->name }}"
                 class="student-group-show-hero__image">
        @else
            <div class="student-group-show-hero__placeholder">
                <i class="fe fe-users"></i>
            </div>
        @endif
    </div>

    <div class="card-body p-4">
        <span class="group-show-hero__eyebrow">
            <i class="fe fe-users me-1"></i>مجموعة تعليمية
        </span>
        <h2 class="group-show-hero__title mb-3">{{ $group->name }}</h2>

        @if($group->description)
            <div class="mb-4">
                <h6 class="text-muted fs-12 fw-semibold mb-2">الوصف</h6>
                <p class="group-show-hero__desc mb-0">{{ $group->description }}</p>
            </div>
        @endif

        @if($group->details)
            <div class="mb-4">
                <h6 class="text-muted fs-12 fw-semibold mb-2">التفاصيل</h6>
                <div class="student-group-details-content">
                    {!! $group->details !!}
                </div>
            </div>
        @endif

        @if($group->terms)
            <div class="mb-4">
                <h6 class="text-muted fs-12 fw-semibold mb-2">الشروط</h6>
                <div class="student-group-details-content">
                    {!! $group->terms !!}
                </div>
            </div>
        @endif

        @if($group->courses->count() > 0)
            <div>
                <h6 class="text-muted fs-12 fw-semibold mb-2">الكورسات المرتبطة</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($group->courses as $course)
                        <span class="group-show-chip">{{ $course->title }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
