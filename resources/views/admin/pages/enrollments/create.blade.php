@extends('admin.layouts.master')

@section('page-title')
    تسجيل طالب - {{ $course->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تسجيل طالب جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">تسجيل طالب</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <div class="row">
                <div class="col-md-8">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">اختر الطالب</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('courses.enrollments.enroll-individual', $course->id) }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">الطالب <span class="text-danger">*</span></label>
                                    @if($students->count() > 0)
                                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                            <option value="">اختر طالب</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                    {{ $student->name }} - {{ $student->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('student_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            لا يوجد طلاب متاحين للتسجيل. جميع الطلاب مسجلون بالفعل في هذا الكورس.
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="send_notification" id="sendNotification" checked>
                                        <label class="form-check-label" for="sendNotification">
                                            إرسال إشعار بالبريد الإلكتروني
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('courses.enrollments.index', $course->id) }}" class="btn btn-light">
                                        <i class="fas fa-arrow-right me-2"></i>رجوع
                                    </a>
                                    @if($students->count() > 0)
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-user-plus me-2"></i>تسجيل الطالب
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات الكورس</h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $course->title }}</h5>
                            <p class="text-muted">{{ $course->short_description }}</p>
                            <hr>
                            <div class="mb-2">
                                <strong>المسجلين حالياً:</strong> {{ $course->enrollments_count ?? 0 }}
                            </div>
                            @if($course->max_students)
                                <div class="mb-2">
                                    <strong>الحد الأقصى:</strong> {{ $course->max_students }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action*="enroll-individual"]');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const studentSelect = form.querySelector('select[name="student_id"]');
                
                if (!studentSelect.value) {
                    e.preventDefault();
                    alert('يرجى اختيار طالب');
                    studentSelect.focus();
                    return false;
                }
            });
        }
    });
</script>
@stop
