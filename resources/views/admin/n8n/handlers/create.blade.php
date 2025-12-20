@extends('admin.layouts.master')

@section('page-title')
    إضافة معالج جديد - n8n
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-plus me-2"></i>إضافة معالج جديد
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.handlers.index') }}">المعالجات</a></li>
                        <li class="breadcrumb-item active">إضافة جديد</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 offset-xl-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات المعالج</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.n8n.handlers.store') }}" method="POST">
                            @csrf

                            <!-- Handler Type -->
                            <div class="mb-3">
                                <label for="handler_type" class="form-label">نوع المعالج <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('handler_type') is-invalid @enderror"
                                       id="handler_type" name="handler_type" value="{{ old('handler_type') }}"
                                       placeholder="مثال: user.create" required>
                                @error('handler_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Handler Class -->
                            <div class="mb-3">
                                <label for="handler_class" class="form-label">فئة المعالج <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('handler_class') is-invalid @enderror"
                                       id="handler_class" name="handler_class" value="{{ old('handler_class') }}"
                                       placeholder="مثال: App\Webhooks\N8n\Handlers\CreateUserHandler" required>
                                @error('handler_class')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">الوصف</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Required Fields (JSON Array) -->
                            <div class="mb-3">
                                <label for="required_fields" class="form-label">الحقول المطلوبة (JSON Array)</label>
                                <textarea class="form-control @error('required_fields') is-invalid @enderror"
                                          id="required_fields" name="required_fields" rows="3"
                                          placeholder='["name", "email", "password"]'>{{ old('required_fields') }}</textarea>
                                @error('required_fields')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Optional Fields (JSON Array) -->
                            <div class="mb-3">
                                <label for="optional_fields" class="form-label">الحقول الاختيارية (JSON Array)</label>
                                <textarea class="form-control @error('optional_fields') is-invalid @enderror"
                                          id="optional_fields" name="optional_fields" rows="3"
                                          placeholder='["phone", "address"]'>{{ old('optional_fields') }}</textarea>
                                @error('optional_fields')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Example Payload (JSON) -->
                            <div class="mb-3">
                                <label for="example_payload" class="form-label">مثال على الطلب (JSON)</label>
                                <textarea class="form-control @error('example_payload') is-invalid @enderror"
                                          id="example_payload" name="example_payload" rows="5"
                                          placeholder='{"name": "أحمد", "email": "ahmad@example.com"}'>{{ old('example_payload') }}</textarea>
                                @error('example_payload')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Is Active -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active"
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    تفعيل المعالج
                                </label>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.n8n.handlers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>حفظ
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
