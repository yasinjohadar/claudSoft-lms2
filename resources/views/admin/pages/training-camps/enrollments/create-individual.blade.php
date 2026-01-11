@extends('admin.layouts.master')

@section('page-title')
    إضافة عضو جديد - {{ $camp->name }}
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-right: 20px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    
    /* Select2 Dropdown Styling */
    .select2-dropdown {
        z-index: 9999 !important;
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }
    
    .select2-results__options {
        background-color: #fff !important;
        padding: 0 !important;
    }
    
    .select2-results__option {
        background-color: #fff !important;
        padding: 0.5rem 1rem !important;
    }
    
    .select2-results__option--highlighted {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    
    .select2-results__option[aria-selected="true"] {
        background-color: #e7f1ff !important;
        color: #0d6efd !important;
    }
    
    .select2-container {
        position: relative !important;
    }
    
    /* Ensure Select2 dropdown appears within the page */
    .select2-container--default .select2-results > .select2-results__options {
        max-height: 200px;
        overflow-y: auto;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle me-2"></i>نجح!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>يوجد أخطاء في النموذج:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة عضو جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.index') }}">المعسكرات التدريبية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('training-camps.show', $camp->id) }}">{{ $camp->name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إضافة عضو جديد</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        إضافة عضو جديد - {{ $camp->name }}
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('training-camps.show', $camp->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i>العودة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('training-camps.enrollments.store', $camp->id) }}" method="POST" id="add-enrollment-form">
                        @csrf
                        <div class="mb-3">
                            <label for="student_id" class="form-label">الطالب <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-select" required>
                                <option value="">ابحث عن طالب...</option>
                            </select>
                            <small class="text-muted">ابحث بالاسم، البريد الإلكتروني، أو الهاتف</small>
                            @error('student_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">الحالة</label>
                            <select name="status" id="status" class="form-select" value="{{ old('status', 'pending') }}">
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                                <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">حالة الدفع</label>
                            <select name="payment_status" id="payment_status" class="form-select" value="{{ old('payment_status', 'unpaid') }}">
                                <option value="unpaid" {{ old('payment_status', 'unpaid') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                <option value="refunded" {{ old('payment_status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('training-camps.show', $camp->id) }}" class="btn btn-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>إضافة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#student_id').select2({
            placeholder: 'ابحث عن طالب...',
            allowClear: true,
            dir: 'rtl',
            dropdownParent: $('.card-body'),
            language: {
                noResults: function() {
                    return 'لا توجد نتائج';
                },
                searching: function() {
                    return 'جاري البحث...';
                }
            },
            ajax: {
                url: `{{ route('training-camps.enrollments.search-students', $camp->id) }}`,
                dataType: 'json',
                delay: 300,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results || []
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        // Restore old value if validation fails
        @if(old('student_id'))
            var oldStudentId = {{ old('student_id') }};
            var oldStudentName = '{{ old('student_name', '') }}';
            if (oldStudentId) {
                var newOption = new Option(oldStudentName, oldStudentId, true, true);
                $('#student_id').append(newOption).trigger('change');
            }
        @endif
    });
</script>
@stop

