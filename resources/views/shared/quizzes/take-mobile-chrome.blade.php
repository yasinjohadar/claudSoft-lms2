{{-- Mobile chrome for quiz take pages --}}
<div class="quiz-take-mobile-top d-lg-none" id="quiz-take-mobile-top">
    <div class="quiz-take-mobile-top__inner">
        <span class="quiz-take-mobile-top__timer" id="quiz-take-mobile-timer">--:--</span>
        <span class="badge bg-primary-transparent fw-bold" id="quiz-take-mobile-counter">1 / {{ $questions->count() }}</span>
        <button type="button" class="btn btn-sm btn-primary-light" id="quiz-take-open-sidebar" aria-label="فتح قائمة الأسئلة">
            <i class="fe fe-grid me-1"></i>الأسئلة
        </button>
    </div>
</div>

<div class="quiz-take-sidebar-backdrop" id="quiz-take-sidebar-backdrop" aria-hidden="true"></div>

<div class="quiz-take-mobile-bar d-lg-none" id="quiz-take-mobile-bar">
    <div class="quiz-take-mobile-bar__inner">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="quiz-take-mobile-prev" disabled>
            <i class="fe fe-chevron-right me-1"></i>السابق
        </button>
        <button type="button" class="btn btn-primary-light btn-sm quiz-take-mobile-bar__center" id="quiz-take-open-sidebar-2" onclick="document.getElementById('quiz-take-open-sidebar')?.click()">
            <i class="fe fe-list"></i>
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="quiz-take-mobile-next">
            التالي<i class="fe fe-chevron-left ms-1"></i>
        </button>
    </div>
</div>
