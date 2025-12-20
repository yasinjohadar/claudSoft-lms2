@extends('admin.layouts.master')

@section('page-title')
    إصدار شهادة جديدة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إصدار شهادة جديدة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.certificates.index') }}">الشهادات</a></li>
                            <li class="breadcrumb-item active">إصدار جديد</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <!-- Form Card -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">بيانات الشهادة</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.certificates.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <!-- Student Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الطالب <span class="text-danger">*</span></label>
                                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                            <option value="">-- اختر الطالب --</option>
                                            @if(isset($students))
                                                @foreach($students as $student)
                                                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">اختر الطالب المراد إصدار الشهادة له</small>
                                    </div>

                                    <!-- Course Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                        <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                            <option value="">-- اختر الكورس --</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('course_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Template Selection -->
                                    <div class="col-md-12 mb-4">
                                        <label class="form-label">قالب الشهادة <span class="text-danger">*</span></label>
                                        <select name="template_id" class="form-select @error('template_id') is-invalid @enderror" required>
                                            <option value="">-- اختر القالب --</option>
                                            @foreach($templates as $template)
                                                <option value="{{ $template->id }}" {{ $template->is_default ? 'selected' : '' }}>
                                                    {{ $template->name }}
                                                    @if($template->is_default) (افتراضي) @endif
                                                    - {{ $template->getRequirementsSummary() }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('template_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            سيتم التحقق من أهلية الطالب بناءً على متطلبات القالب
                                        </small>
                                    </div>
                                </div>

                                <!-- Info Alert -->
                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                    <i class="fas fa-info-circle fs-20 me-3"></i>
                                    <div>
                                        <strong>ملاحظة:</strong> سيتم توليد رقم الشهادة وكود التحقق والـ QR Code تلقائياً عند الإصدار.
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.certificates.index') }}" class="btn btn-light">
                                        <i class="fas fa-times me-2"></i>إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-certificate me-2"></i>إصدار الشهادة
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Certificate creation scripts can be added here
</script>
@endsection
