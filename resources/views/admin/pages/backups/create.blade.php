@extends('admin.layouts.master')

@section('page-title')
    إنشاء نسخة احتياطية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backups.index') }}">النسخ الاحتياطية</a></li>
                    <li class="breadcrumb-item active">إنشاء</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fe fe-alert-circle me-2"></i>
                <strong>يرجى تصحيح الأخطاء:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-plus-circle me-1"></i>
                        نسخة احتياطية جديدة
                    </span>
                    <h2 class="group-show-hero__title mb-2">إنشاء نسخة احتياطية</h2>
                    <p class="group-show-hero__desc mb-0">
                        اختر نوع المحتوى، مكان التخزين، ومدة الاحتفاظ. لنسخ قاعدة البيانات الكبيرة يُفضَّل تشغيل عامل الطابور.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('backups.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع للنسخ</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">إعدادات النسخة</h6>
                <p class="fs-12 text-muted mb-0">الحقول المطلوبة معلّمة بعلامة (*).</p>
            </div>
            <div class="card-body pt-3">
                <form action="{{ route('backups.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">اسم النسخة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', 'backup_' . now()->format('Y-m-d_H-i-s')) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="backup_type" class="form-label">نوع النسخ <span class="text-danger">*</span></label>
                            <select class="form-select @error('backup_type') is-invalid @enderror" id="backup_type" name="backup_type" required>
                                @foreach($backupTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('backup_type', 'database') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('backup_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="compression_type" class="form-label">نوع الضغط <span class="text-danger">*</span></label>
                            <select class="form-select @error('compression_type') is-invalid @enderror" id="compression_type" name="compression_type" required>
                                @foreach($compressionTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('compression_type', 'zip') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('compression_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">لنوع قاعدة البيانات قد يُستخدم gzip تلقائياً في المحرك الجديد.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="storage_config_id" class="form-label">مكان التخزين <span class="text-danger">*</span></label>
                            <select class="form-select @error('storage_config_id') is-invalid @enderror" id="storage_config_id" name="storage_config_id" required>
                                <option value="">اختر مكان التخزين</option>
                                @foreach($storageConfigs as $config)
                                    <option value="{{ $config->id }}" {{ old('storage_config_id') == $config->id ? 'selected' : '' }}>
                                        {{ $config->name }} ({{ \App\Models\AppStorageConfig::DRIVERS[$config->driver] ?? $config->driver }})
                                    </option>
                                @endforeach
                            </select>
                            @error('storage_config_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($storageConfigs->isEmpty())
                                <small class="text-danger d-block mt-1">
                                    لا توجد أماكن تخزين نشطة.
                                    <a href="{{ route('app-storage.configs.create') }}">أضف مكان تخزين</a>
                                </small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="retention_days" class="form-label">أيام الاحتفاظ <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('retention_days') is-invalid @enderror" id="retention_days" name="retention_days" value="{{ old('retention_days', 30) }}" min="1" max="365" required>
                            @error('retention_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary" {{ $storageConfigs->isEmpty() ? 'disabled' : '' }}>
                            <i class="fe fe-save me-1"></i> إنشاء النسخة
                        </button>
                        <a href="{{ route('backups.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="fe fe-info text-primary mt-1"></i>
                    <div class="fs-13 text-muted">
                        بعد الإنشاء ستُنقل لصفحة التقدّم. لنسخ قاعدة البيانات تأكد من تشغيل الطابور:
                        <code dir="ltr" class="d-inline-block mt-1">php artisan queue:work --timeout=3600</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
