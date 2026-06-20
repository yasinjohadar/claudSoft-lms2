@extends('student.layouts.master')

@section('page-title')
    تعديل {{ $work->title }}
@stop

@section('content')
<div class="main-content app-content student-work-form-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="my-4 page-header-breadcrumb dashboard-fade-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.works.index') }}">جدول أعمالي</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.works.show', $work) }}">{{ Str::limit($work->title, 30) }}</a></li>
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-edit me-1"></i>
                        تعديل العمل
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $work->title }}</h2>
                    <p class="group-show-hero__desc mb-0">
                        حدّث معلومات عملك وأضف التحسينات قبل إعادة التقديم.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('student.works.show', $work) }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">عرض العمل</span>
                        </a>
                        <a href="{{ route('student.works.index') }}"
                           class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                            <span class="group-show-action__text">جدول الأعمال</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('student.works.update', $work) }}" method="POST" enctype="multipart/form-data" id="student-work-form">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-xl-8">
                    @include('student.works.partials.form-fields', [
                        'work' => $work,
                        'courses' => $courses,
                        'categories' => $categories,
                    ])
                </div>
                <div class="col-xl-4">
                    @include('student.works.partials.form-sidebar', [
                        'work' => $work,
                        'cancelUrl' => route('student.works.show', $work),
                        'submitLabel' => 'حفظ التعديلات',
                    ])
                </div>
            </div>
        </form>

    </div>
</div>
@stop

@push('scripts')
    @include('student.works.partials.form-scripts', [
        'initialTags' => old('tags', $work->tags ?? []),
    ])
@endpush
