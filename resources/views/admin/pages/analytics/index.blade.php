@extends('admin.layouts.master')

@include('admin.pages.analytics.partials.quiz-assets')

@section('page-title')
    تحليلات الاختبارات
@stop

@section('styles')
    @include('admin.pages.assignments.partials.page-styles')
    @include('admin.pages.quizzes.partials.page-styles')
    <style>
        html:not(.loaded) .qa-index-animate {
            animation-play-state: paused !important;
            opacity: 0;
        }
        html.loaded .qa-index-animate {
            animation-play-state: running !important;
        }
    </style>
@stop

@section('content')
<div class="main-content app-content quiz-analytics-page quiz-analytics-dashboard">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb qa-index-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">تحليلات الاختبارات</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in qa-index-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-activity me-1"></i>
                        لوحة تحليلات متقدمة
                    </span>
                    <h2 class="group-show-hero__title mb-2">تحليلات الاختبارات</h2>
                    <p class="group-show-hero__desc mb-0">
                        رؤية شاملة لأداء الطلاب والاختبارات: معدلات النجاح، توزيع الدرجات، الاتجاهات الزمنية، والكورسات الأكثر نشاطاً.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('quizzes.index') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-file-text"></i></span>
                            <span class="group-show-action__text">إدارة الاختبارات</span>
                        </a>
                        <a href="{{ route('question-bank.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-database"></i></span>
                            <span class="group-show-action__text">بنك الأسئلة</span>
                        </a>
                        <form action="{{ route('quiz-analytics.export') }}" method="POST" class="d-contents">
                            @csrf
                            <input type="hidden" name="type" value="overall">
                            @if(request('from_date'))<input type="hidden" name="from_date" value="{{ request('from_date') }}">@endif
                            @if(request('to_date'))<input type="hidden" name="to_date" value="{{ request('to_date') }}">@endif
                            <button type="submit" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-download"></i></span>
                                <span class="group-show-action__text">تصدير التقرير</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in qa-index-animate mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية التحليلات</h4>
                <p class="fs-12 text-muted mb-0">حدّد الكورس، الاختبار، أو الفترة الزمنية لتحديث جميع المؤشرات أدناه.</p>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('quiz-analytics.index') }}" id="qaIndexFilterForm" class="group-show-filters mb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="qaPeriod">الفترة</label>
                            <select name="period" id="qaPeriod" class="form-select">
                                <option value="7" {{ request('period', '30') == '7' ? 'selected' : '' }}>آخر 7 أيام</option>
                                <option value="30" {{ request('period', '30') == '30' ? 'selected' : '' }}>آخر 30 يوماً</option>
                                <option value="90" {{ request('period') == '90' ? 'selected' : '' }}>آخر 90 يوماً</option>
                                <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>كل الوقت</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="qaFromDate">من تاريخ</label>
                            <input type="date" id="qaFromDate" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <label class="form-label" for="qaToDate">إلى تاريخ</label>
                            <input type="date" id="qaToDate" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="qaCourse">الكورس</label>
                            <select name="course_id" id="qaCourse" class="form-select">
                                <option value="">جميع الكورسات</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="form-label" for="qaQuiz">الاختبار</label>
                            <select name="quiz_id" id="qaQuiz" class="form-select">
                                <option value="">جميع الاختبارات</option>
                                @foreach($quizzes as $quiz)
                                    <option value="{{ $quiz->id }}" {{ request('quiz_id') == $quiz->id ? 'selected' : '' }}>
                                        {{ $quiz->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fe fe-filter me-1"></i>تطبيق الفلاتر
                                </button>
                                <button type="button" id="qaIndexResetBtn" class="btn btn-outline-secondary btn-sm">
                                    <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                </button>
                                @if($dateFrom && $dateTo)
                                    <span class="fs-12 text-muted">
                                        الفترة: {{ $dateFrom->format('Y-m-d') }} — {{ $dateTo->format('Y-m-d') }}
                                    </span>
                                @elseif(request('period') === 'all')
                                    <span class="fs-12 text-muted">الفترة: كل الوقت</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-4 qa-index-animate">
            @include('admin.pages.analytics.partials.index-stats', ['stats' => $stats])
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="card quiz-analytics-panel h-100">
                    <div class="card-header">
                        <h2 class="quiz-analytics-panel__title">
                            <span class="quiz-analytics-panel__title-icon"><i class="fe fe-bar-chart-2"></i></span>
                            توزيع الدرجات العام
                        </h2>
                    </div>
                    <div class="card-body">
                        @include('admin.pages.analytics.partials.quiz-grade-distribution', ['scoreDistribution' => $scoreDistribution])
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card quiz-analytics-panel h-100">
                    <div class="card-header">
                        <h2 class="quiz-analytics-panel__title">
                            <span class="quiz-analytics-panel__title-icon"><i class="fe fe-activity"></i></span>
                            اتجاهات المحاولات والدرجات
                        </h2>
                    </div>
                    <div class="card-body">
                        @if($attemptTrends->count() > 0)
                            <div class="quiz-analytics-chart-wrap" id="overallAnalyticsTrendsChart"></div>
                            <script type="application/json" id="overall-analytics-trends-data">
                                {!! json_encode($attemptTrends->map(fn ($t) => [
                                    'date' => \Carbon\Carbon::parse($t->date)->format('Y-m-d'),
                                    'count' => (int) $t->count,
                                    'avg_score' => round((float) ($t->avg_score ?? 0), 1),
                                ])->values()) !!}
                            </script>
                        @else
                            <div class="quiz-analytics-empty py-5">
                                <div><i class="fe fe-activity d-block"></i></div>
                                <p class="mb-0">لا توجد بيانات اتجاهات ضمن الفلاتر الحالية</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card quiz-analytics-panel h-100">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <h2 class="quiz-analytics-panel__title mb-0">
                            <span class="quiz-analytics-panel__title-icon qa-index-icon--gold"><i class="fe fe-award"></i></span>
                            أفضل الطلاب
                        </h2>
                        <span class="badge bg-primary-transparent">{{ $topStudents->count() }}</span>
                    </div>
                    <div class="card-body pt-2">
                        @include('admin.pages.analytics.partials.index-students-table', [
                            'students' => $topStudents,
                            'emptyMessage' => 'لا يوجد طلاب بمحاولات مكتملة ضمن الفلاتر',
                        ])
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card quiz-analytics-panel h-100">
                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                        <h2 class="quiz-analytics-panel__title mb-0">
                            <span class="quiz-analytics-panel__title-icon qa-index-icon--danger"><i class="fe fe-alert-triangle"></i></span>
                            طلاب يحتاجون متابعة
                        </h2>
                        <span class="badge bg-danger-transparent">متوسط &lt; 60%</span>
                    </div>
                    <div class="card-body pt-2">
                        @include('admin.pages.analytics.partials.index-students-table', [
                            'students' => $atRiskStudents,
                            'emptyMessage' => 'لا يوجد طلاب ضمن معايير المتابعة حالياً',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card quiz-analytics-panel h-100">
                    <div class="card-header">
                        <h2 class="quiz-analytics-panel__title mb-0">
                            <span class="quiz-analytics-panel__title-icon qa-index-icon--danger"><i class="fe fe-trending-down"></i></span>
                            التقييمات الأكثر صعوبة
                        </h2>
                    </div>
                    <div class="card-body pt-2">
                        @include('admin.pages.analytics.partials.index-assessments-table', [
                            'assessments' => $difficultQuizzes,
                            'emptyMessage' => 'لا توجد تقييمات بمحاولات كافية',
                        ])
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card quiz-analytics-panel h-100">
                    <div class="card-header">
                        <h2 class="quiz-analytics-panel__title mb-0">
                            <span class="quiz-analytics-panel__title-icon qa-index-icon--success"><i class="fe fe-trending-up"></i></span>
                            التقييمات الأعلى أداءً
                        </h2>
                    </div>
                    <div class="card-body pt-2">
                        @include('admin.pages.analytics.partials.index-assessments-table', [
                            'assessments' => $bestQuizzes,
                            'emptyMessage' => 'لا توجد تقييمات بمحاولات كافية',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="card quiz-analytics-panel mb-4">
            <div class="card-header">
                <h2 class="quiz-analytics-panel__title mb-0">
                    <span class="quiz-analytics-panel__title-icon"><i class="fe fe-book-open"></i></span>
                    أداء الكورسات
                </h2>
            </div>
            <div class="card-body pt-2">
                @include('admin.pages.analytics.partials.index-course-performance', ['coursePerformance' => $coursePerformance])
            </div>
        </div>

        <div class="card quiz-analytics-panel mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h2 class="quiz-analytics-panel__title mb-0">
                    <span class="quiz-analytics-panel__title-icon"><i class="fe fe-clock"></i></span>
                    آخر المحاولات
                </h2>
                <span class="fs-12 text-muted">{{ $recentAttempts->count() }} محاولة</span>
            </div>
            <div class="card-body pt-2">
                @include('admin.pages.analytics.partials.index-recent-attempts', ['recentAttempts' => $recentAttempts])
            </div>
        </div>

    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    const resetBtn = document.getElementById('qaIndexResetBtn');
    const form = document.getElementById('qaIndexFilterForm');
    if (resetBtn && form) {
        resetBtn.addEventListener('click', function () {
            window.location.href = form.getAttribute('action');
        });
    }
})();
</script>
@endsection
