@extends('admin.layouts.master')

@section('page-title')
    إنشاء جدولة جديدة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backup-schedules.index') }}">الجدولة</a></li>
                    <li class="breadcrumb-item active">إنشاء</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-plus-circle me-1"></i>
                        جدولة جديدة
                    </span>
                    <h2 class="group-show-hero__title mb-2">إنشاء جدولة نسخ</h2>
                    <p class="group-show-hero__desc mb-0">حدّد التكرار والوقت ومكان التخزين لتشغيل النسخ تلقائياً.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('backup-schedules.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع للجدولة</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">بيانات الجدولة</h6>
                <p class="fs-12 text-muted mb-0">الحقول المطلوبة معلّمة (*).</p>
            </div>
            <div class="card-body pt-3">
                <form action="{{ route('backup-schedules.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">اسم الجدولة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="backup_type" class="form-label">نوع النسخ <span class="text-danger">*</span></label>
                            <select class="form-select @error('backup_type') is-invalid @enderror" id="backup_type" name="backup_type" required>
                                @foreach($backupTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('backup_type', 'full') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('backup_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="frequency" class="form-label">التكرار <span class="text-danger">*</span></label>
                            <select class="form-select @error('frequency') is-invalid @enderror" id="frequency" name="frequency" required>
                                @foreach($frequencies as $key => $label)
                                    <option value="{{ $key }}" {{ old('frequency', 'daily') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="time" class="form-label">الوقت <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('time') is-invalid @enderror" id="time" name="time" value="{{ old('time', '02:00') }}" required>
                            @error('time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" id="day_of_month_field" style="display: none;">
                            <label for="day_of_month" class="form-label">يوم الشهر</label>
                            <input type="number" class="form-control" id="day_of_month" name="day_of_month" value="{{ old('day_of_month', 1) }}" min="1" max="31">
                        </div>
                        <div class="col-12" id="days_of_week_field" style="display: none;">
                            <label class="form-label">أيام الأسبوع</label>
                            <div class="d-flex gap-3 flex-wrap">
                                @foreach([0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت'] as $day => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days_of_week[]" value="{{ $day }}" id="day_{{ $day }}" {{ in_array($day, old('days_of_week', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day_{{ $day }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="storage_config_id" class="form-label">مكان التخزين <span class="text-danger">*</span></label>
                            <select class="form-select @error('storage_config_id') is-invalid @enderror" id="storage_config_id" name="storage_config_id" required>
                                <option value="">-- اختر مكان التخزين --</option>
                                @foreach($storageConfigs as $config)
                                    <option value="{{ $config->id }}" {{ old('storage_config_id') == $config->id ? 'selected' : '' }}>
                                        {{ $config->name }} ({{ $config->driver }})
                                    </option>
                                @endforeach
                            </select>
                            @error('storage_config_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="retention_days" class="form-label">أيام الاحتفاظ <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('retention_days') is-invalid @enderror" id="retention_days" name="retention_days" value="{{ old('retention_days', 30) }}" min="1" max="365" required>
                            @error('retention_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">أنواع الضغط <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 flex-wrap">
                                @foreach($compressionTypes as $key => $label)
                                    <div class="form-check">
                                        <input class="form-check-input @error('compression_types') is-invalid @enderror" type="checkbox" name="compression_types[]" value="{{ $key }}" id="comp_{{ $key }}" {{ in_array($key, old('compression_types', ['zip'])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="comp_{{ $key }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('compression_types')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> حفظ</button>
                        <a href="{{ route('backup-schedules.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const frequencySelect = document.getElementById('frequency');
    const daysOfWeekField = document.getElementById('days_of_week_field');
    const dayOfMonthField = document.getElementById('day_of_month_field');
    function toggleFields() {
        daysOfWeekField.style.display = frequencySelect.value === 'weekly' ? 'block' : 'none';
        dayOfMonthField.style.display = frequencySelect.value === 'monthly' ? 'block' : 'none';
    }
    frequencySelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endpush
@stop
