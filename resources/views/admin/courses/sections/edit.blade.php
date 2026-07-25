@extends('admin.layouts.master')

@section('page-title')
    تعديل القسم
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
                        <li class="breadcrumb-item"><a href="{{ route('courses.show', $section->course_id) }}">{{ $section->course->title }}</a></li>
                        <li class="breadcrumb-item active">تعديل القسم</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            @php $sectionVisual = $section->visualPresentation(); @endphp
                            <span class="admin-course-form-page__icon admin-section-form-page__hero-type admin-section-form-page__hero-type--{{ $sectionVisual['tone'] }}">
                                <i class="fe {{ $sectionVisual['icon'] }}"></i>
                            </span>
                            <div class="min-w-0">
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-edit-2 me-1"></i>تعديل القسم
                                </span>
                                <h2 class="group-show-hero__title mb-2">{{ Str::limit($section->title, 60) }}</h2>
                                <p class="group-show-hero__desc mb-2">حدّث العنوان والنوع وإعدادات الظهور والإتاحة.</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="group-show-chip group-show-chip--sm">
                                        <i class="fe fe-book-open me-1"></i>{{ Str::limit($section->course->title, 36) }}
                                    </span>
                                    <span class="group-show-chip group-show-chip--sm">
                                        <i class="fe {{ $sectionVisual['icon'] }} me-1"></i>{{ $sectionVisual['label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('courses.show', $section->course_id) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-eye"></i></span>
                                <span class="group-show-action__text">معاينة الكورس</span>
                            </a>
                            <a href="{{ route('courses.show', $section->course_id) }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للكورس</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('courses.sections.update', [$section->course_id, $section->id]) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.courses.sections.partials.form-fields', [
                    'course' => $section->course,
                    'section' => $section,
                ])
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
