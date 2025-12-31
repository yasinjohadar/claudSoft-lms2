@extends('admin.layouts.master')

@section('page-title')
    إضافة مقدم خدمة جديد
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة مقدم خدمة جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.ai.providers.index') }}">مقدمي الخدمة</a></li>
                            <li class="breadcrumb-item active">إضافة جديد</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Form -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">معلومات مقدم الخدمة</div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.ai.providers.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">النوع <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="">اختر النوع</option>
                                            <option value="openai" {{ old('type') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                                            <option value="gemini" {{ old('type') == 'gemini' ? 'selected' : '' }}>Google Gemini</option>
                                            <option value="glm" {{ old('type') == 'glm' ? 'selected' : '' }}>GLM (智谱AI)</option>
                                            <option value="openrouter" {{ old('type') == 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                                            <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>مخصص</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                                        <input type="text" name="api_key" class="form-control @error('api_key') is-invalid @enderror" value="{{ old('api_key') }}" required>
                                        @error('api_key')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API URL</label>
                                        <input type="url" name="api_url" class="form-control @error('api_url') is-invalid @enderror" value="{{ old('api_url') }}" placeholder="اتركه فارغاً للاستخدام الافتراضي">
                                        @error('api_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">اسم النموذج <span class="text-danger">*</span></label>
                                        <input type="text" name="model_name" class="form-control @error('model_name') is-invalid @enderror" value="{{ old('model_name', 'gpt-4') }}" required>
                                        @error('model_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الأولوية</label>
                                        <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror" value="{{ old('priority', 0) }}" min="0" max="100">
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">نشط</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_default">افتراضي</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.ai.providers.index') }}" class="btn btn-light">إلغاء</a>
                                    <button type="submit" class="btn btn-primary">حفظ</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

