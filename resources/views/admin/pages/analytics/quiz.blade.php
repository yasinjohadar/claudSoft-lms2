@extends('admin.layouts.master')

@include('admin.pages.analytics.partials.quiz-assets')

@section('page-title')
    تحليلات الاختبار: {{ $quiz->title }}
@stop

@section('content')
<div class="main-content app-content quiz-analytics-page">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 gap-3">
            <div class="min-w-0">
                <h4 class="mb-1 text-truncate">تحليلات الاختبار</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quiz-analytics.index') }}">تحليلات الاختبارات</a></li>
                        <li class="breadcrumb-item active">{{ $quiz->title }}</li>
                    </ol>
                </nav>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-arrow-right me-1"></i>العودة للاختبار
                </a>
            </div>
        </div>

        <div class="card quiz-analytics-hero mb-4">
            <div class="card-body p-4">
                <h1 class="quiz-analytics-hero__title">{{ $quiz->title }}</h1>
                @if($quiz->description)
                    <p class="text-muted mb-3">{{ $quiz->description }}</p>
                @endif
                <div class="quiz-analytics-chips">
                    @if($quiz->course)
                        <span class="quiz-analytics-chip quiz-analytics-chip--info">
                            <i class="fe fe-book"></i>{{ $quiz->course->title }}
                        </span>
                    @endif
                    <span class="quiz-analytics-chip">
                        <i class="fe fe-help-circle"></i>{{ $quiz->quizQuestions->count() }} أسئلة
                    </span>
                    @if($quiz->time_limit)
                        <span class="quiz-analytics-chip quiz-analytics-chip--warning">
                            <i class="fe fe-clock"></i>{{ $quiz->time_limit }} دقيقة
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @include('admin.pages.analytics.partials.quiz-stats', ['stats' => $stats])

        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="card quiz-analytics-panel h-100">
                    <div class="card-header">
                        <h2 class="quiz-analytics-panel__title">
                            <span class="quiz-analytics-panel__title-icon"><i class="fe fe-bar-chart-2"></i></span>
                            توزيع الدرجات
                        </h2>
                    </div>
                    <div class="card-body">
                        @include('admin.pages.analytics.partials.quiz-grade-distribution', ['scoreDistribution' => $scoreDistribution])
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card quiz-analytics-panel quiz-analytics-panel--questions h-100">
                    <div class="card-header">
                        <h2 class="quiz-analytics-panel__title">
                            <span class="quiz-analytics-panel__title-icon"><i class="fe fe-help-circle"></i></span>
                            تحليل الأسئلة
                        </h2>
                    </div>
                    <div class="card-body">
                        @include('admin.pages.analytics.partials.quiz-question-analysis', ['questionAnalysis' => $questionAnalysis])
                    </div>
                </div>
            </div>
        </div>

        <div class="card quiz-analytics-panel mb-4">
            <div class="card-header">
                <h2 class="quiz-analytics-panel__title">
                    <span class="quiz-analytics-panel__title-icon"><i class="fe fe-users"></i></span>
                    أداء الطلاب
                </h2>
            </div>
            <div class="card-body">
                @include('admin.pages.analytics.partials.quiz-student-performance', ['studentPerformance' => $studentPerformance])
            </div>
        </div>

        @if($attemptTrends && $attemptTrends->count() > 0)
            <div class="card quiz-analytics-panel mb-4">
                <div class="card-header">
                    <h2 class="quiz-analytics-panel__title">
                        <span class="quiz-analytics-panel__title-icon"><i class="fe fe-activity"></i></span>
                        اتجاهات المحاولات
                    </h2>
                </div>
                <div class="card-body">
                    <div class="quiz-analytics-chart-wrap" id="quizAnalyticsTrendsChart"></div>
                    <script type="application/json" id="quiz-analytics-trends-data">
                        {!! json_encode($attemptTrends->map(fn ($t) => [
                            'date' => \Carbon\Carbon::parse($t->date)->format('Y-m-d'),
                            'count' => (int) $t->count,
                            'avg_score' => round((float) ($t->avg_score ?? 0), 1),
                        ])->values()) !!}
                    </script>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
