@extends('admin.layouts.master')

@section('page-title')
    إدارة الاختبارات
@stop

@section('styles')
    @include('admin.pages.quizzes.partials.page-styles')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb quizzes-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الاختبارات</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in quizzes-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-clipboard me-1"></i>
                            إدارة التقييم
                        </span>
                        <h2 class="group-show-hero__title mb-2">إدارة الاختبارات</h2>
                        <p class="group-show-hero__desc mb-0">
                            إنشاء الاختبارات وربطها بالكورسات، إدارة الأسئلة والمحاولات، ومتابعة حالة النشر.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="group-show-actions">
                            <a href="{{ route('quizzes.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة اختبار جديد</span>
                            </a>
                            <a href="{{ route('question-bank.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-database"></i></span>
                                <span class="group-show-action__text">بنك الأسئلة</span>
                            </a>
                            <a href="{{ route('question-pools.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                                <span class="group-show-action__text">مجموعات الأسئلة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 quizzes-page-animate">
                @include('admin.pages.quizzes.partials.stats', [
                    'totalQuizzes' => $totalQuizzes,
                    'publishedQuizzes' => $publishedQuizzes,
                    'draftQuizzes' => $draftQuizzes,
                    'questionBankCount' => $questionBankCount,
                ])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الاختبارات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بعنوان الاختبار أو فلتر حسب الكورس والنوع والحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('quizzes.index') }}" id="filterForm" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="quizzesSearch">البحث</label>
                                <input type="text" id="quizzesSearch" name="search" class="form-control"
                                       placeholder="ابحث بعنوان الاختبار..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="quizzesCourse">الكورس</label>
                                <select name="course_id" id="quizzesCourse" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="quizzesType">نوع الاختبار</label>
                                <select name="quiz_type" id="quizzesType" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="practice" {{ request('quiz_type') == 'practice' ? 'selected' : '' }}>تدريبي</option>
                                    <option value="graded" {{ request('quiz_type') == 'graded' ? 'selected' : '' }}>مُقيّم</option>
                                    <option value="final_exam" {{ request('quiz_type') == 'final_exam' ? 'selected' : '' }}>اختبار نهائي</option>
                                    <option value="survey" {{ request('quiz_type') == 'survey' ? 'selected' : '' }}>استبيان</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="quizzesStatus">الحالة</label>
                                <select name="status" id="quizzesStatus" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-xl-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('quizzes.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الاختبارات
                        <span class="group-show-members-card__count">{{ $quizzes->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @include('admin.pages.quizzes._quizzes_table', ['quizzes' => $quizzes])
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    document.querySelectorAll('[data-countup]').forEach(function (el) {
        const target = parseFloat(el.dataset.countup || '0');
        const duration = 800;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });
})();
</script>
@stop
