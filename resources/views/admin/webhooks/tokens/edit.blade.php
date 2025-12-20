@extends('admin.layouts.master')

@section('page-title')
    تعديل التوكن
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تعديل التوكن</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.webhooks.index') }}">Webhooks</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.webhooks.tokens.index') }}">التوكنات</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Page Header Close -->

            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات التوكن</div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('admin.webhooks.tokens.update', $token) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Name -->
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">اسم التوكن <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $token->name) }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Source -->
                                    <div class="col-md-6 mb-3">
                                        <label for="source" class="form-label">المصدر <span class="text-danger">*</span></label>
                                        <select class="form-select @error('source') is-invalid @enderror" 
                                                id="source" 
                                                name="source" 
                                                required>
                                            @foreach($sources as $key => $label)
                                                <option value="{{ $key }}" {{ old('source', $token->source) == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('source')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Token -->
                                    <div class="col-md-12 mb-3">
                                        <label for="token" class="form-label">التوكن / المفتاح السري</label>
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control @error('token') is-invalid @enderror" 
                                                   id="token" 
                                                   name="token" 
                                                   value=""
                                                   placeholder="اتركه فارغاً للحفاظ على التوكن الحالي">
                                            <button type="button" 
                                                    class="btn btn-outline-secondary" 
                                                    id="generateTokenBtn"
                                                    title="إنشاء توكن عشوائي">
                                                <i class="fas fa-key"></i> توليد
                                            </button>
                                        </div>
                                        @error('token')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">اتركه فارغاً للحفاظ على التوكن الحالي، أو أدخل توكن جديد</small>
                                    </div>

                                    <!-- Allowed IPs -->
                                    <div class="col-md-6 mb-3">
                                        <label for="allowed_ips" class="form-label">IPs المسموحة</label>
                                        <input type="text" 
                                               class="form-control @error('allowed_ips') is-invalid @enderror" 
                                               id="allowed_ips" 
                                               name="allowed_ips" 
                                               value="{{ old('allowed_ips', $token->allowed_ips ? implode(', ', $token->allowed_ips) : '') }}"
                                               placeholder="123.456.789.0, 98.76.54.32">
                                        @error('allowed_ips')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">افصل بين الـ IPs بفواصل</small>
                                    </div>

                                    <!-- Form Types -->
                                    <div class="col-md-6 mb-3">
                                        <label for="form_types" class="form-label">ربط Form IDs</label>
                                        <input type="text" 
                                               class="form-control @error('form_types') is-invalid @enderror" 
                                               id="form_types" 
                                               name="form_types" 
                                               value="{{ old('form_types', $token->form_types ? json_encode($token->form_types) : '') }}"
                                               placeholder='{"123": "enrollment", "456": "contact"}' 
                                               pattern=".*">
                                        @error('form_types')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">JSON أو key:value مفصولة بفواصل</small>
                                    </div>

                                    <!-- Description -->
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">الوصف</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" 
                                                  name="description" 
                                                  rows="3">{{ old('description', $token->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Is Active -->
                                    <div class="col-md-12 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="is_active" 
                                                   name="is_active" 
                                                   value="1"
                                                   {{ old('is_active', $token->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                تفعيل التوكن
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.webhooks.tokens.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>إلغاء
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>حفظ التغييرات
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End::row-1 -->

        </div>
    </div>
    <!-- End::app-content -->
@stop

@section('script')
<script>
    // Generate Random Token
    document.getElementById('generateTokenBtn').addEventListener('click', function() {
        fetch('{{ route('admin.webhooks.tokens.generate') }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('token').value = data.token;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء توليد التوكن');
        });
    });
</script>
@stop


