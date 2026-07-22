@extends('admin.layouts.master')

@section('page-title')
    النسخ الاحتياطية
@stop

@section('content')
@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-layers',
            'label' => 'إجمالي النسخ',
            'value' => $stats['total'] ?? 0,
            'sub' => 'كل النسخ المسجّلة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'مكتملة',
            'value' => $stats['completed'] ?? 0,
            'sub' => 'جاهزة للتحميل/الاستعادة',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-alert-circle',
            'label' => 'فاشلة',
            'value' => $stats['failed'] ?? 0,
            'sub' => 'تحتاج مراجعة',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-hard-drive',
            'label' => 'الحجم الإجمالي',
            'value' => number_format(($stats['total_size'] ?? 0) / 1024 / 1024, 2) . ' MB',
            'sub' => 'مجموع أحجام الملفات',
            'text' => true,
        ],
    ];
@endphp
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item active">النسخ الاحتياطية</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-shield me-1"></i>
                        إدارة النسخ الاحتياطية
                    </span>
                    <h2 class="group-show-hero__title mb-2">كافة النسخ</h2>
                    <p class="group-show-hero__desc mb-0">
                        إنشاء ومتابعة وتحميل واستعادة النسخ الاحتياطية لقاعدة البيانات والملفات.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('backups.create') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                            <span class="group-show-action__text">نسخة احتياطية جديدة</span>
                        </a>
                        <a href="{{ route('backup-schedules.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-calendar"></i></span>
                            <span class="group-show-action__text">الجدولة</span>
                        </a>
                        <a href="{{ route('backup-storage.index') }}" class="group-show-action group-show-action--success">
                            <span class="group-show-action__icon"><i class="fe fe-database"></i></span>
                            <span class="group-show-action__text">أماكن التخزين</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 dashboard-fade-in mb-4">
            @foreach ($kpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                @if(!empty($card['text']))
                                    <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1">{{ $card['value'] }}</h3>
                                @else
                                    <h3 class="admin-stats-card__value mb-1" data-countup="{{ (int) $card['value'] }}">0</h3>
                                @endif
                                <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة النسخ
                    <span class="group-show-members-card__count">{{ $backups->total() }}</span>
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
                                <th>الحجم</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td>{{ $backup->id }}</td>
                                    <td class="text-start">{{ $backup->name }}</td>
                                    <td>
                                        <span class="badge bg-info-transparent text-info">{{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] ?? $backup->backup_type }}</span>
                                    </td>
                                    <td>
                                        @if($backup->status === 'completed')
                                            <span class="badge bg-success">مكتمل</span>
                                        @elseif($backup->status === 'failed')
                                            <span class="badge bg-danger">فشل</span>
                                        @elseif($backup->status === 'running')
                                            <span class="badge bg-warning"><i class="fas fa-spinner fa-spin me-1"></i>قيد التنفيذ</span>
                                        @else
                                            <span class="badge bg-secondary">معلق</span>
                                        @endif
                                    </td>
                                    <td>{{ $backup->getFileSize() }}</td>
                                    <td>{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            <a href="{{ route('backups.show', $backup->id) }}" class="btn btn-sm btn-info" title="عرض">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            @if($backup->status === 'completed')
                                                <a href="{{ route('backups.download', $backup->id) }}" class="btn btn-sm btn-primary" title="تحميل">
                                                    <i class="fe fe-download"></i>
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteBackupModal{{ $backup->id }}" title="حذف">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">لا توجد نسخ احتياطية بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($backups->hasPages())
                    <div class="mt-3">{{ $backups->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@foreach($backups as $backup)
<div class="modal fade" id="deleteBackupModal{{ $backup->id }}" tabindex="-1" aria-labelledby="deleteBackupModalLabel{{ $backup->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteBackupModalLabel{{ $backup->id }}">
                    <i class="fe fe-alert-triangle me-2"></i>تأكيد الحذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fe fe-trash-2 fs-1 text-danger mb-3 d-block"></i>
                    <h5>هل أنت متأكد من حذف هذه النسخة؟</h5>
                </div>
                <div class="alert alert-warning mb-0">
                    <strong>الاسم:</strong> {{ $backup->name }}<br>
                    <strong>النوع:</strong> {{ \App\Models\Backup::BACKUP_TYPES[$backup->backup_type] ?? $backup->backup_type }}<br>
                    <strong>الحجم:</strong> {{ $backup->getFileSize() }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('backups.destroy', $backup->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">حذف النسخة</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@stop
