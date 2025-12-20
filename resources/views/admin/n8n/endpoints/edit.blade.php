@extends('admin.layouts.master')

@section('page-title')
    تعديل نقطة النهاية - n8n
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
                    <i class="fas fa-edit me-2"></i>تعديل نقطة النهاية: {{ $endpoint->name }}
                </h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.index') }}">n8n Integration</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.n8n.endpoints.index') }}">نقاط النهاية</a></li>
                        <li class="breadcrumb-item active">تعديل</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 offset-xl-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">معلومات نقطة النهاية</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.n8n.endpoints.update', $endpoint) }}" method="POST">
                            @method('PUT')
                            @csrf

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">الاسم <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $endpoint->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Event Type -->
                            <div class="mb-3">
                                <label for="event_type" class="form-label">نوع الحدث <span class="text-danger">*</span></label>
                                <select class="form-select @error('event_type') is-invalid @enderror"
                                        id="event_type" name="event_type" required>
                                    <option value="">اختر نوع الحدث</option>
                                    @foreach($availableEvents as $key => $label)
                                        <option value="{{ $key }}" {{ old('event_type', $endpoint->event_type) == $key ? 'selected' : '' }}>
                                            {{ $label }} ({{ $key }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('event_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- URL -->
                            <div class="mb-3">
                                <label for="url" class="form-label">n8n Webhook URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('url') is-invalid @enderror"
                                       id="url" name="url" value="{{ old('url', ->url) }}"
                                       placeholder="https://your-n8n-instance.com/webhook/..." required>
                                @error('url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    URL الـ webhook الخاص بـ n8n الذي سيتم إرسال البيانات إليه
                                </small>
                            </div>

                            <!-- Secret Key -->
                            <div class="mb-3">
                                <label for="secret_key" class="form-label">مفتاح الأمان (Secret Key)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('secret_key') is-invalid @enderror"
                                           id="secret_key" name="secret_key" value="{{ old('secret_key', ->secret_key) }}">
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateSecretKey()">
                                        <i class="fas fa-key"></i> توليد
                                    </button>
                                </div>
                                @error('secret_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    سيتم توليد مفتاح عشوائي إذا تركت هذا الحقل فارغاً
                                </small>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">الوصف</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3">{{ old('description', ->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Advanced Settings -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-cog me-1"></i>إعدادات متقدمة
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Retry Attempts -->
                                        <div class="col-md-6 mb-3">
                                            <label for="retry_attempts" class="form-label">عدد محاولات الإعادة</label>
                                            <input type="number" class="form-control @error('retry_attempts') is-invalid @enderror"
                                                   id="retry_attempts" name="retry_attempts"
                                                   value="{{ old('retry_attempts', ->retry_attempts) }}" min="0" max="10">
                                            @error('retry_attempts')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Timeout -->
                                        <div class="col-md-6 mb-3">
                                            <label for="timeout" class="form-label">المهلة (بالثواني)</label>
                                            <input type="number" class="form-control @error('timeout') is-invalid @enderror"
                                                   id="timeout" name="timeout"
                                                   value="{{ old('timeout', ->timeout) }}" min="5" max="120">
                                            @error('timeout')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Custom Headers -->
                                    <div class="mb-3">
                                        <label for="headers" class="form-label">Custom Headers (JSON)</label>
                                        <textarea class="form-control @error('headers') is-invalid @enderror"
                                                  id="headers" name="headers" rows="3"
                                                  placeholder='{"Authorization": "Bearer token", "Custom-Header": "value"}'>{{ old('headers') }}</textarea>
                                        @error('headers')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            صيغة JSON لإضافة headers مخصصة للطلب
                                        </small>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="mb-3">
                                        <label for="metadata" class="form-label">Metadata (JSON)</label>
                                        <textarea class="form-control @error('metadata') is-invalid @enderror"
                                                  id="metadata" name="metadata" rows="3"
                                                  placeholder='{"workflow_id": "123", "environment": "production"}'>{{ old('metadata') }}</textarea>
                                        @error('metadata')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            بيانات إضافية لتخزينها مع نقطة النهاية
                                        </small>
                                    </div>

                                    <!-- Is Active -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active"
                                               name="is_active" value="1" {{ old('is_active', ->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            تفعيل نقطة النهاية
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.n8n.endpoints.index') }}" class="btn btn-secondary">
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

<script>
function generateSecretKey() {
    const length = 32;
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('secret_key').value = result;
}
</script>
@endsection
