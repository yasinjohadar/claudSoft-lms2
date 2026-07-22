@extends('admin.layouts.master')

@section('page-title')
    تحليلات التخزين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backup-storage.index') }}">أماكن التخزين</a></li>
                    <li class="breadcrumb-item active">التحليلات</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        @if(isset($budgetAlert) && $budgetAlert)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fe fe-alert-triangle me-1"></i><strong>تنبيه!</strong> {{ $budgetAlert['message'] }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-bar-chart-2 me-1"></i>
                        تحليلات التخزين
                    </span>
                    <h2 class="group-show-hero__title mb-2">استخدام وتكلفة التخزين</h2>
                    <p class="group-show-hero__desc mb-0">راقب حجم الرفع والتحميل والتكلفة حسب المكان والفترة.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('backup-storage.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع لأماكن التخزين</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">تصفية التحليلات</h6>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('backup-storage.analytics') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">مكان التخزين</label>
                            <select name="config_id" class="form-select" required>
                                <option value="">اختر مكان التخزين</option>
                                @foreach($configs as $config)
                                    <option value="{{ $config->id }}" {{ request('config_id') == $config->id ? 'selected' : '' }}>
                                        {{ $config->name }} ({{ \App\Models\BackupStorageConfig::DRIVERS[$config->driver] ?? $config->driver }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الفترة</label>
                            <select name="period" class="form-select">
                                <option value="day" {{ $period == 'day' ? 'selected' : '' }}>اليوم</option>
                                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>هذا الأسبوع</option>
                                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>هذا الشهر</option>
                                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>هذه السنة</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-search me-1"></i> عرض التحليلات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedConfig && $stats)
            <div class="row g-3 dashboard-fade-in mb-4">
                <div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay: 0ms">
                    <div class="card admin-stats-card admin-stats-card--blue">
                        <div class="card-body">
                            <p class="admin-stats-card__label mb-1">إجمالي التخزين</p>
                            <h3 class="admin-stats-card__value admin-stats-card__value--text mb-0">{{ number_format($stats['total_bytes_stored'] / (1024**3), 2) }} GB</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay: 70ms">
                    <div class="card admin-stats-card admin-stats-card--green">
                        <div class="card-body">
                            <p class="admin-stats-card__label mb-1">إجمالي الرفع</p>
                            <h3 class="admin-stats-card__value admin-stats-card__value--text mb-0">{{ number_format($stats['total_bytes_uploaded'] / (1024**3), 2) }} GB</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay: 140ms">
                    <div class="card admin-stats-card admin-stats-card--cyan">
                        <div class="card-body">
                            <p class="admin-stats-card__label mb-1">إجمالي التحميل</p>
                            <h3 class="admin-stats-card__value admin-stats-card__value--text mb-0">{{ number_format($stats['total_bytes_downloaded'] / (1024**3), 2) }} GB</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 dashboard-stagger-item" style="--stagger-delay: 210ms">
                    <div class="card admin-stats-card admin-stats-card--orange">
                        <div class="card-body">
                            <p class="admin-stats-card__label mb-1">إجمالي التكلفة</p>
                            <h3 class="admin-stats-card__value admin-stats-card__value--text mb-0">${{ number_format($stats['total_cost'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 dashboard-fade-in mb-4">
                <div class="col-md-6">
                    <div class="card custom-card group-show-members-card h-100">
                        <div class="card-body">
                            <small class="text-muted d-block">متوسط التكلفة اليومية</small>
                            <h4 class="mb-0">${{ number_format($stats['daily_average_cost'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card custom-card group-show-members-card h-100">
                        <div class="card-body">
                            <small class="text-muted d-block">عدد العمليات</small>
                            <h4 class="mb-0">{{ number_format($stats['total_operations']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@stop
