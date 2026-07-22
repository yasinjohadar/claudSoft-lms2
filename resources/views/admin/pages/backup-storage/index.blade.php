@extends('admin.layouts.master')

@section('page-title')
    إعدادات التخزين
@stop

@section('content')
@php
    $activeCount = $configs->where('is_active', true)->count();
    $inactiveCount = $configs->where('is_active', false)->count();
@endphp
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backups.index') }}">النسخ الاحتياطية</a></li>
                    <li class="breadcrumb-item active">أماكن التخزين</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-database me-1"></i>
                        تخزين النسخ
                    </span>
                    <h2 class="group-show-hero__title mb-2">أماكن التخزين</h2>
                    <p class="group-show-hero__desc mb-0">
                        إدارة S3 / IDrive / محلي واختبار الاتصال قبل إنشاء النسخ.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('backup-storage.create') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                            <span class="group-show-action__text">إضافة مكان تخزين</span>
                        </a>
                        <a href="{{ route('backup-storage.analytics') }}" class="group-show-action group-show-action--success">
                            <span class="group-show-action__icon"><i class="fe fe-bar-chart-2"></i></span>
                            <span class="group-show-action__text">التحليلات</span>
                        </a>
                        <a href="{{ route('backups.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع للنسخ</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 dashboard-fade-in mb-4">
            <div class="col-md-4 dashboard-stagger-item" style="--stagger-delay: 0ms">
                <div class="card admin-stats-card admin-stats-card--blue">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap"><i class="fe fe-server admin-stats-card__icon"></i></div>
                        <div>
                            <p class="admin-stats-card__label mb-1">الإجمالي</p>
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $configs->count() }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 dashboard-stagger-item" style="--stagger-delay: 70ms">
                <div class="card admin-stats-card admin-stats-card--green">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap"><i class="fe fe-check-circle admin-stats-card__icon"></i></div>
                        <div>
                            <p class="admin-stats-card__label mb-1">نشط</p>
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $activeCount }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 dashboard-stagger-item" style="--stagger-delay: 140ms">
                <div class="card admin-stats-card admin-stats-card--orange">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap"><i class="fe fe-pause-circle admin-stats-card__icon"></i></div>
                        <div>
                            <p class="admin-stats-card__label mb-1">متوقف</p>
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $inactiveCount }}">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة أماكن التخزين
                    <span class="group-show-members-card__count">{{ $configs->count() }}</span>
                </h6>
            </div>
            <div class="card-body pt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>الأولوية</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($configs as $config)
                                <tr>
                                    <td>{{ $config->id }}</td>
                                    <td class="text-start">{{ $config->name }}</td>
                                    <td><span class="badge bg-info-transparent text-info">{{ $config->driver }}</span></td>
                                    <td>
                                        @if($config->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">متوقف</span>
                                        @endif
                                    </td>
                                    <td>{{ $config->priority }}</td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                                            <a href="{{ route('backup-storage.edit', $config->id) }}" class="btn btn-sm btn-info" title="تعديل">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <form action="{{ route('backup-storage.test', $config->id) }}" method="POST" class="d-inline" id="test-form-{{ $config->id }}">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-warning test-storage" data-config-id="{{ $config->id }}" title="اختبار">
                                                    <i class="fe fe-zap"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('backup-storage.destroy', $config->id) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه الإعدادات؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted py-4">لا توجد إعدادات تخزين.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.test-storage').forEach(btn => {
        btn.addEventListener('click', function() {
            const configId = this.dataset.configId;
            const form = document.getElementById('test-form-' + configId);
            const button = this;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => alert((data.success ? '✓ ' : '✗ ') + data.message))
            .catch(error => alert('حدث خطأ: ' + error.message))
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fe fe-zap"></i>';
            });
        });
    });
});
</script>
@endpush
@stop
