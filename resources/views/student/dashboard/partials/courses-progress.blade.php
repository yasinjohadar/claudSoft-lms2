<div class="card custom-card group-show-members-card dashboard-fade-in">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
        <div>
            <h4 class="card-title mb-1">الكورسات قيد التقدم</h4>
            <p class="fs-12 text-muted mb-0">تابع من حيث توقفت في كورساتك النشطة.</p>
        </div>
        <a href="{{ route('student.courses.my-courses') }}" class="btn btn-sm btn-primary-light">
            <i class="fe fe-arrow-left me-1"></i>عرض الكل
        </a>
    </div>
    <div class="card-body pt-3">
        @if(($inProgressCourses ?? collect())->isNotEmpty())
            <div class="d-flex flex-column gap-3">
                @foreach($inProgressCourses as $enrollment)
                    @php
                        $progress = min(100, max(0, (float) ($enrollment->completion_percentage ?? 0)));
                    @endphp
                    <div class="dashboard-stat-row dashboard-stagger-item p-3" style="--stagger-delay: {{ $loop->index * 50 }}ms">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="min-w-0">
                                <a href="{{ route('student.progress.show', $enrollment->course_id) }}"
                                   class="fw-semibold text-decoration-none d-block text-truncate">
                                    {{ $enrollment->course->title ?? 'كورس' }}
                                </a>
                                <small class="text-muted">نسبة الإنجاز {{ number_format($progress, 0) }}%</small>
                            </div>
                            <span class="badge bg-primary-transparent text-primary flex-shrink-0">
                                {{ number_format($progress, 0) }}%
                            </span>
                        </div>
                        <div class="progress progress-xs">
                            <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            @if(!empty($pendingMembershipNotices))
                <div class="student-pending-review-empty student-pending-review-empty--compact text-center py-4 px-3">
                    <div class="student-pending-review-empty__icon mb-2" aria-hidden="true">
                        <i class="fe fe-clock"></i>
                    </div>
                    <h5 class="student-pending-review-empty__title mb-1">طلبكم قيد المراجعة</h5>
                    <p class="student-pending-review-empty__text mb-0">
                        ستظهر كورسات المجموعة هنا بعد موافقة الإدارة.
                    </p>
                </div>
            @else
                <div class="group-show-empty py-5">
                    <i class="fe fe-book-open group-show-empty__icon"></i>
                    <h5 class="group-show-empty__title">لا توجد كورسات قيد التقدم</h5>
                    <p class="group-show-empty__text mb-0">ابدأ التعلم من كورساتك المسجّلة لتظهر هنا.</p>
                </div>
            @endif
        @endif
    </div>
</div>
