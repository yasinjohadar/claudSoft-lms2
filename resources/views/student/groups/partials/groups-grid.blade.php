<div class="row g-4" id="student-groups-cards">
    @forelse ($groups as $index => $group)
        @php
            $isFull = $group->max_members && $group->members_count >= $group->max_members;
        @endphp
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index % 12) * 40 }}ms">
            <article class="student-group-card h-100">
                <div class="student-group-card__media">
                    @if($group->image)
                        <img src="{{ asset('storage/' . $group->image) }}"
                             alt="{{ $group->name }}"
                             class="student-group-card__image">
                    @else
                        <div class="student-group-card__placeholder">
                            <i class="fe fe-users"></i>
                        </div>
                    @endif

                    <span class="student-group-card__members-badge">
                        <i class="fe fe-users me-1"></i>
                        {{ $group->members_count ?? 0 }}
                        @if($group->max_members)
                            / {{ $group->max_members }}
                        @endif
                    </span>
                </div>

                <div class="student-group-card__body">
                    <h6 class="student-group-card__title" title="{{ $group->name }}">{{ $group->name }}</h6>

                    @if($group->description)
                        <p class="student-group-card__desc">{{ Str::limit($group->description, 100, '...') }}</p>
                    @endif

                    <div class="student-group-card__tags">
                        @if($group->courses->count() > 0)
                            @foreach($group->courses->take(2) as $course)
                                <span class="badge bg-primary-transparent fs-11">{{ $course->title }}</span>
                            @endforeach
                            @if($group->courses->count() > 2)
                                <span class="badge bg-secondary-transparent fs-11">+{{ $group->courses->count() - 2 }}</span>
                            @endif
                        @endif

                        @if($isFull)
                            <span class="badge bg-danger-transparent fs-11">المجموعة ممتلئة</span>
                        @elseif($group->has_pending_request)
                            <span class="badge bg-warning-transparent fs-11">
                                <i class="fe fe-clock me-1"></i>طلب قيد المراجعة
                            </span>
                        @else
                            <span class="badge bg-success-transparent fs-11">متاح للانضمام</span>
                        @endif
                    </div>

                    <a href="{{ route('student.groups.show', $group->id) }}"
                       class="btn btn-primary btn-sm rounded-pill w-100 mt-auto">
                        <i class="fe fe-eye me-1"></i>عرض التفاصيل
                    </a>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="student-my-courses-empty text-center py-5">
                <div class="student-my-courses-empty__icon mb-4">
                    <i class="fe fe-users"></i>
                </div>
                <h4 class="mb-2">لا توجد مجموعات متاحة</h4>
                <p class="text-muted mb-0">لا توجد مجموعات مفتوحة لطلب الانضمام حالياً، أو لا توجد نتائج مطابقة للبحث.</p>
            </div>
        </div>
    @endforelse
</div>

@if($groups->hasPages())
    <div class="d-flex justify-content-center mt-4 pt-2">
        {{ $groups->withQueryString()->links() }}
    </div>
@endif
