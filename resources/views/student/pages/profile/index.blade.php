@extends('student.layouts.master')

@section('page-title')
    ملفي الشخصي
@stop

@section('content')
<div class="main-content app-content student-profile-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">ملفي الشخصي</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">ملفي الشخصي</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('student.profile.edit') }}" class="btn btn-primary rounded-pill">
                    <i class="fe fe-edit me-1"></i>تعديل الملف الشخصي
                </a>
            </div>
        </div>

        @include('student.pages.profile.partials.profile-hero', ['student' => $student])

        @include('student.pages.profile.partials.profile-completion', ['student' => $student])

        <div class="row g-4">
            <div class="col-xl-8">
                @php
                    $displayPhone = $student->full_phone ?? trim(($student->country_code ?? '') . ($student->phone ?? '')) ?: $student->phone;
                @endphp
                @include('student.pages.profile.partials.profile-details', [
                    'student' => $student,
                    'displayPhone' => $displayPhone,
                ])
            </div>
            <div class="col-xl-4">
                @include('student.pages.profile.partials.profile-sidebar', ['student' => $student])
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-photo-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!confirm('هل أنت متأكد من حذف الصورة الشخصية؟')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
