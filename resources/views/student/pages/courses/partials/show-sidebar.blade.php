<div class="card custom-card group-show-members-card dashboard-fade-in student-course-show-sidebar sticky-top" style="top: 5.5rem;">
    <div class="card-body">
        <div class="student-course-show-price text-center mb-4">
            @if($course->price > 0)
                @if(method_exists($course, 'hasDiscount') && $course->hasDiscount())
                    <div class="student-course-show-price__value">${{ number_format($course->discount_price, 2) }}</div>
                    <div class="text-muted fs-13 mt-1">
                        <del>${{ number_format($course->price, 2) }}</del>
                        @if(method_exists($course, 'getDiscountPercentage') && $course->getDiscountPercentage())
                            <span class="badge bg-danger-transparent text-danger ms-1">
                                خصم {{ $course->getDiscountPercentage() }}%
                            </span>
                        @endif
                    </div>
                @else
                    <div class="student-course-show-price__value">${{ number_format($course->price, 2) }}</div>
                @endif
            @else
                <div class="student-course-show-price__value student-course-show-price__value--free">مجاني</div>
            @endif
        </div>

        @if($enrollment)
            @php
                $completedCount = count($completedModules ?? []);
                $totalModules = $stats['total_modules'] ?? 0;
                $progressPct = $totalModules > 0 ? min(100, round(($completedCount / $totalModules) * 100)) : 0;
            @endphp
            <div class="student-course-show-progress mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fs-12 text-muted">تقدمك في الكورس</span>
                    <strong class="text-primary">{{ $progressPct }}%</strong>
                </div>
                <div class="student-quizzes-result__track">
                    <div class="student-quizzes-result__bar is-passed" style="width: {{ $progressPct }}%"></div>
                </div>
                <p class="text-muted fs-11 mb-0 mt-2">{{ $completedCount }} من {{ $totalModules }} درس مكتمل</p>
            </div>
            <a href="{{ route('student.learn.continue', $course->id) }}"
               class="btn btn-primary rounded-pill w-100 mb-4">
                <i class="fe fe-play-circle me-1"></i>متابعة التعلم
            </a>
        @else
            <form action="{{ route('student.courses.enroll', $course->id) }}" method="POST" class="mb-4">
                @csrf
                <button type="submit" class="btn btn-primary rounded-pill w-100">
                    <i class="fe fe-user-plus me-1"></i>التسجيل في الكورس
                </button>
            </form>
        @endif

        <h6 class="fw-semibold mb-3">
            <i class="fe fe-gift me-2 text-primary"></i>هذا الكورس يشمل
        </h6>
        <ul class="student-course-show-features mb-4">
            <li>
                <span class="student-course-show-features__icon bg-primary-transparent text-primary"><i class="fe fe-layers"></i></span>
                <span>
                    <strong>{{ $stats['total_sections'] ?? 0 }} قسم تعليمي</strong>
                    <small>محتوى منظم ومرتب</small>
                </span>
            </li>
            <li>
                <span class="student-course-show-features__icon bg-success-transparent text-success"><i class="fe fe-book-open"></i></span>
                <span>
                    <strong>{{ $stats['total_modules'] ?? 0 }} درس</strong>
                    <small>محاضرات شاملة</small>
                </span>
            </li>
            @if($course->certificate_enabled)
                <li>
                    <span class="student-course-show-features__icon bg-warning-transparent text-warning"><i class="fe fe-award"></i></span>
                    <span>
                        <strong>شهادة إتمام</strong>
                        <small>عند إكمال الكورس</small>
                    </span>
                </li>
            @endif
            <li>
                <span class="student-course-show-features__icon bg-info-transparent text-info"><i class="fe fe-smartphone"></i></span>
                <span>
                    <strong>متوافق مع الجوال</strong>
                    <small>تعلّم من أي مكان</small>
                </span>
            </li>
        </ul>

        <h6 class="fw-semibold mb-3">
            <i class="fe fe-share-2 me-2 text-primary"></i>شارك الكورس
        </h6>
        <div class="d-flex flex-wrap gap-2">
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
               target="_blank" rel="noopener noreferrer"
               class="btn btn-sm btn-outline-primary rounded-pill flex-fill" title="Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($course->title) }}"
               target="_blank" rel="noopener noreferrer"
               class="btn btn-sm btn-outline-info rounded-pill flex-fill" title="Twitter">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="https://api.whatsapp.com/send?text={{ urlencode($course->title . ' - ' . url()->current()) }}"
               target="_blank" rel="noopener noreferrer"
               class="btn btn-sm btn-outline-success rounded-pill flex-fill" title="WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
            <button type="button" onclick="copyCourseLink('{{ url()->current() }}')"
                    class="btn btn-sm btn-outline-secondary rounded-pill flex-fill" title="نسخ الرابط">
                <i class="fe fe-link"></i>
            </button>
        </div>
    </div>
</div>
