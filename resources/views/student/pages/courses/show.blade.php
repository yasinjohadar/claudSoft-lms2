@extends('student.layouts.master')

@section('page-title')
    {{ $course->title }}
@stop

@section('content')
@php
    $levelLabel = match ($course->level ?? null) {
        'beginner' => 'مبتدئ',
        'intermediate' => 'متوسط',
        'advanced' => 'متقدم',
        default => null,
    };

    $languageLabel = match ($course->language ?? null) {
        'ar' => 'العربية',
        'en' => 'الإنجليزية',
        default => $course->language,
    };
@endphp

<div class="main-content app-content student-course-show-page">
    <div class="container-fluid pb-3">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 dashboard-fade-in">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">معاينة الكورس</h4>
                <nav aria-label="breadcrumb" class="d-none d-lg-block">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.courses.my-courses') }}">كورساتي</a></li>
                        @if($course->category)
                            <li class="breadcrumb-item">
                                <a href="{{ route('student.courses.my-courses') }}" class="text-muted">
                                    {{ $course->category->name }}
                                </a>
                            </li>
                        @endif
                        <li class="breadcrumb-item active">{{ Str::limit($course->title, 50) }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('student.courses.my-courses') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-arrow-right me-1"></i>الكورسات
                </a>
                @if($enrollment)
                    <a href="{{ route('student.learn.continue', $course->id) }}" class="btn btn-primary rounded-pill">
                        <i class="fe fe-play-circle me-1"></i>متابعة التعلم
                    </a>
                @endif
            </div>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4 student-course-show-hero">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    @if($course->category)
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-folder me-1"></i>{{ $course->category->name }}
                        </span>
                    @endif
                    <h2 class="group-show-hero__title mb-2">{{ $course->title }}</h2>
                    @if($course->short_description)
                        <p class="group-show-hero__desc mb-3">{{ $course->short_description }}</p>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($levelLabel)
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-bar-chart-2 me-1"></i>{{ $levelLabel }}
                            </span>
                        @endif
                        @if($languageLabel)
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-globe me-1"></i>{{ $languageLabel }}
                            </span>
                        @endif
                        <span class="group-show-chip group-show-chip--sm">
                            <i class="fe fe-clock me-1"></i>{{ $course->duration_in_hours ?? 0 }} ساعة
                        </span>
                        @if($course->price > 0)
                            <span class="group-show-chip group-show-chip--sm">
                                <i class="fe fe-dollar-sign me-1"></i>${{ number_format($course->price, 2) }}
                            </span>
                        @else
                            <span class="badge bg-success-transparent text-success">مجاني</span>
                        @endif
                    </div>
                    @if($enrollment)
                        <div class="student-course-show-enrolled-banner">
                            <i class="fe fe-check-circle"></i>
                            <span>أنت مسجل في هذا الكورس</span>
                        </div>
                    @endif
                </div>
                @php $courseImage = $course->thumbnail ?? $course->image ?? null; @endphp
                @if($courseImage)
                    <div class="col-lg-4 text-center">
                        <div class="student-course-show-hero__media">
                            <img src="{{ course_image_url($courseImage) }}"
                                 alt="{{ $course->title }}"
                                 class="student-course-show-hero__image">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @include('student.pages.courses.partials.show-stats')

        <div class="row align-items-start g-4 student-course-show-content-row">
            <div class="col-xl-8 col-lg-7">
                <div class="card custom-card student-my-courses-panel dashboard-fade-in mb-4">
                    <div class="card-body p-3">
                        <ul class="nav student-my-courses-filters student-course-show-tabs mb-0" id="courseTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="student-my-courses-filter is-active" id="curriculum-tab"
                                        data-bs-toggle="tab" data-bs-target="#curriculum" type="button" role="tab"
                                        aria-controls="curriculum" aria-selected="true">
                                    <i class="fe fe-list"></i>المنهج الدراسي
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="student-my-courses-filter" id="overview-tab"
                                        data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab"
                                        aria-controls="overview" aria-selected="false">
                                    <i class="fe fe-info"></i>نظرة عامة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="student-my-courses-filter" id="instructor-tab"
                                        data-bs-toggle="tab" data-bs-target="#instructor" type="button" role="tab"
                                        aria-controls="instructor" aria-selected="false">
                                    <i class="fe fe-user"></i>عن المدرب
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="student-my-courses-filter" id="reviews-tab"
                                        data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab"
                                        aria-controls="reviews" aria-selected="false">
                                    <i class="fe fe-star"></i>التقييمات
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="courseTabsContent">
                    <div class="tab-pane fade show active" id="curriculum" role="tabpanel" aria-labelledby="curriculum-tab">
                        @include('student.pages.courses.partials.show-curriculum')
                    </div>
                    <div class="tab-pane fade" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                        @include('student.pages.courses.partials.show-overview')
                    </div>
                    <div class="tab-pane fade" id="instructor" role="tabpanel" aria-labelledby="instructor-tab">
                        @include('student.pages.courses.partials.show-instructor')
                    </div>
                    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                        @include('student.pages.courses.partials.show-reviews')
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                @include('student.pages.courses.partials.show-sidebar')
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    function copyCourseLink(text) {
        navigator.clipboard.writeText(text).then(function () {
            var button = event.currentTarget;
            var originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fe fe-check"></i>';
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            setTimeout(function () {
                button.innerHTML = originalHTML;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }).catch(function (err) {
            console.error('Failed to copy:', err);
        });
    }
    window.copyCourseLink = copyCourseLink;

    var tabButtons = document.querySelectorAll('#courseTabs .student-my-courses-filter');
    tabButtons.forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            tabButtons.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
        });
        btn.addEventListener('click', function () {
            tabButtons.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
        });
    });
})();
</script>
@endpush
