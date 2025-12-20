@extends('admin.layouts.master')

@section('page-title')
    إنشاء مجموعة جديدة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء مجموعة جديدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('groups.all') }}">المجموعات</a></li>
                            <li class="breadcrumb-item active">إنشاء مجموعة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Select Course Card -->
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-book me-2 text-primary"></i>اختر الكورس
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle me-2"></i>
                                الرجاء اختيار الكورس الذي تريد إنشاء مجموعة له
                            </div>

                            <form method="GET" action="{{ route('groups.create-with-course') }}">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-graduation-cap me-1"></i>الكورس <span class="text-danger">*</span>
                                    </label>
                                    <select name="course_id" class="form-select form-select-lg" required>
                                        <option value="">-- اختر كورساً --</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->title }}
                                                @if($course->code)
                                                    ({{ $course->code }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-question-circle me-1"></i>
                                        اختر الكورس الذي سيتم إنشاء المجموعة له
                                    </small>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('groups.all') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-arrow-right me-2"></i>إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-arrow-left me-2"></i>متابعة
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Info Card -->
                    <div class="card custom-card mt-4">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-lightbulb me-2 text-warning"></i>معلومات هامة
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    المجموعات تساعد في تنظيم الطلاب داخل الكورس
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    يمكنك تحديد عدد أقصى للأعضاء في كل مجموعة
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    يمكن تعيين قادة للمجموعات
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>
                                    يمكنك إخفاء أو إظهار المجموعات حسب الحاجة
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('styles')
<style>
    .form-select-lg {
        padding: 1rem 1.5rem;
        font-size: 1.1rem;
    }

    .card.custom-card {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .card.custom-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
</style>
@endsection
