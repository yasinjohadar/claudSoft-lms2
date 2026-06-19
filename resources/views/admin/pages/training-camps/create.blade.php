@extends('admin.layouts.master')

@section('page-title')
    إضافة معسكر تدريبي جديد
@stop

@section('content')
    <div class="main-content app-content admin-course-form-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                        <li class="breadcrumb-item active">إضافة جديد</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-course-form-page__icon">
                                <i class="fe fe-flag"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-plus-circle me-1"></i>معسكر تدريبي جديد
                                </span>
                                <h2 class="group-show-hero__title mb-2">إضافة معسكر تدريبي</h2>
                                <p class="group-show-hero__desc mb-0">
                                    أدخل البيانات الأساسية، التواريخ والأسعار، ثم اختر الإعدادات قبل الحفظ.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('training-camps.index') }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للمعسكرات</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('training-camps.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.pages.training-camps.partials.form-fields', [
                    'categories' => $categories,
                    'submitLabel' => 'حفظ المعسكر',
                ])
            </form>

        </div>
    </div>
@stop

@section('script')
@include('admin.pages.courses.partials.form-scripts')
<script>
(function () {
    initCourseThumbnail('campImageInput', 'campImagePreview');

    const startDate = document.getElementById('campStartDate');
    const endDate = document.getElementById('campEndDate');

    function updateEndDateMin() {
        if (startDate && endDate && startDate.value) {
            endDate.min = startDate.value;
        }
    }

    startDate?.addEventListener('change', updateEndDateMin);
    updateEndDateMin();
})();
</script>
@stop
