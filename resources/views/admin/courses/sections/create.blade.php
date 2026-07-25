@extends('admin.layouts.master')

@section('page-title')
    إضافة قسم جديد
@stop

@section('content')
    <div class="main-content app-content admin-section-form-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                        <li class="breadcrumb-item active">إضافة قسم</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-course-form-page__icon">
                                <i class="fe fe-folder-plus"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-layers me-1"></i>أقسام الكورس
                                </span>
                                <h2 class="group-show-hero__title mb-2">إضافة قسم جديد</h2>
                                <p class="group-show-hero__desc mb-2">أنشئ قسماً منظماً بمعلومات واضحة ونوع بصري مميز.</p>
                                <span class="group-show-chip group-show-chip--sm">
                                    <i class="fe fe-book-open me-1"></i>{{ Str::limit($course->title, 48) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('courses.show', $course->id) }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للكورس</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('courses.sections.store', $course->id) }}" method="POST">
                @csrf
                @include('admin.courses.sections.partials.form-fields', ['course' => $course])
            </form>

        </div>
    </div>
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.admin-section-type-option__input').forEach(function (input) {
        input.addEventListener('change', function () {
            document.querySelectorAll('.admin-section-type-option').forEach(function (el) {
                el.classList.toggle('is-selected', el.querySelector('input')?.checked === true);
            });
        });
    });
});
</script>
@endpush
