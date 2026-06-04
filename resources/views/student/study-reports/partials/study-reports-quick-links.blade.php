@if($enrolledCourses->isNotEmpty())
    <div class="card custom-card student-quizzes-panel admin-shortcuts-panel mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="avatar avatar-sm bg-primary-transparent">
                    <i class="fe fe-zap text-primary"></i>
                </span>
                <div>
                    <h6 class="card-title mb-0">وصول سريع حسب الكورس</h6>
                    <p class="text-muted fs-12 mb-0">افتح تقارير الذكاء الاصطناعي لكل كورس</p>
                </div>
            </div>
            <div class="row g-3">
                @foreach($enrolledCourses as $index => $course)
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 40 }}ms">
                        <a href="{{ route('student.progress.ai-reports.index', $course) }}"
                           class="admin-quick-link text-decoration-none d-block h-100"
                           title="{{ $course->title }}">
                            <span class="admin-quick-link__icon bg-primary-transparent">
                                <i class="fe fe-book-open text-primary"></i>
                            </span>
                            <span class="admin-quick-link__title text-truncate d-block">{{ $course->title }}</span>
                            <span class="admin-quick-link__subtitle">عرض تقارير الكورس</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
