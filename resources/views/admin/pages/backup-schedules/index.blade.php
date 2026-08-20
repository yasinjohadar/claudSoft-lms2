@extends('admin.layouts.master')

@section('page-title')
    جدولة النسخ الاحتياطية
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backups.index') }}">النسخ الاحتياطية</a></li>
                    <li class="breadcrumb-item active">الجدولة</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        @php
            // جدولة نشطة فات موعدها بأكثر من ساعة = المجدول متوقف على الأرجح
            $overdueSchedules = $schedules->filter(function ($s) {
                return $s->is_active
                    && $s->next_run_at
                    && $s->next_run_at->lt(now()->subHour());
            });
        @endphp

        @if($overdueSchedules->isNotEmpty())
            <div class="alert alert-danger border-0 mb-4" role="alert">
                <h6 class="alert-heading mb-2">
                    <i class="fe fe-alert-triangle me-1"></i>
                    المجدول متوقف على الأرجح — {{ $overdueSchedules->count() }} جدولة فات موعدها
                </h6>
                <ul class="mb-2 ps-3">
                    @foreach($overdueSchedules as $late)
                        <li>
                            <strong>{{ $late->name }}</strong> —
                            كان موعدها {{ $late->next_run_at->format('Y-m-d H:i') }}
                            (متأخرة {{ $late->next_run_at->diffForHumans(now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }})
                        </li>
                    @endforeach
                </ul>
                <hr class="my-2">
                <span class="d-block small">
                    تحقق على السيرفر: <code>supervisorctl status laravel-scheduler</code> أو <code>crontab -l | grep schedule:run</code>.
                    التفاصيل في <code>docs/backup-scheduler-runbook.md</code>.
                </span>
            </div>
        @endif

        <div class="alert alert-info border-0 mb-4" role="alert">
            <strong>للتشغيل التلقائي:</strong>
            يلزم تشغيل <code>php artisan schedule:work</code> مع <code>php artisan queue:work --timeout=3600</code>
            (أو cron يستدعي <code>schedule:run</code> كل دقيقة). زر «تشغيل الآن» يعمل بدون الـ Scheduler.
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-calendar me-1"></i>
                        جدولة تلقائية
                    </span>
                    <h2 class="group-show-hero__title mb-2">جدولة النسخ الاحتياطية</h2>
                    <p class="group-show-hero__desc mb-0">
                        تشغيل نسخ دورية يومية أو أسبوعية أو شهرية عبر الطابور.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('backup-schedules.create') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                            <span class="group-show-action__text">جدولة جديدة</span>
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
            <div class="col-xl-4 col-md-4 dashboard-stagger-item" style="--stagger-delay: 0ms">
                <div class="card admin-stats-card admin-stats-card--blue">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap"><i class="fe fe-calendar admin-stats-card__icon"></i></div>
                        <div>
                            <p class="admin-stats-card__label mb-1">إجمالي الجداول</p>
                            <h3 class="admin-stats-card__value mb-0">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 dashboard-stagger-item" style="--stagger-delay: 70ms">
                <div class="card admin-stats-card admin-stats-card--green">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap"><i class="fe fe-check-circle admin-stats-card__icon"></i></div>
                        <div>
                            <p class="admin-stats-card__label mb-1">نشطة</p>
                            <h3 class="admin-stats-card__value mb-0">{{ $stats['active'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 dashboard-stagger-item" style="--stagger-delay: 140ms">
                <div class="card admin-stats-card admin-stats-card--orange">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap"><i class="fe fe-pause-circle admin-stats-card__icon"></i></div>
                        <div>
                            <p class="admin-stats-card__label mb-1">متوقفة</p>
                            <h3 class="admin-stats-card__value mb-0">{{ $stats['inactive'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    قائمة الجداول
                    <span class="group-show-members-card__count">{{ $schedules->total() }}</span>
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
                                <th>التكرار</th>
                                <th>الوقت</th>
                                <th>الحالة</th>
                                <th>آخر تشغيل</th>
                                <th>التشغيل التالي</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->id }}</td>
                                    <td class="text-start">{{ $schedule->name }}</td>
                                    <td>
                                        <span class="badge bg-info-transparent text-info">{{ \App\Models\BackupSchedule::BACKUP_TYPES[$schedule->backup_type] ?? $schedule->backup_type }}</span>
                                    </td>
                                    <td>{{ \App\Models\BackupSchedule::FREQUENCIES[$schedule->frequency] ?? $schedule->frequency }}</td>
                                    <td>
                                        {{ \Illuminate\Support\Str::of((string) $schedule->time)->substr(0, 5) }}
                                        <small class="d-block text-muted">{{ $schedule->scheduleTimezone() }}</small>
                                    </td>
                                    <td>
                                        @if($schedule->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">متوقف</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->last_run_at)
                                            {{ $schedule->last_run_at->format('Y-m-d H:i') }}
                                            <small class="d-block text-muted">{{ $schedule->last_run_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">لم تعمل بعد</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $schedule->next_run_at?->format('Y-m-d H:i') ?? '—' }}
                                        @if($schedule->is_active && $schedule->next_run_at && $schedule->next_run_at->lt(now()->subHour()))
                                            <span class="badge bg-danger d-block mt-1">متأخرة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                                            <a href="{{ route('backup-schedules.edit', $schedule->id) }}" class="btn btn-sm btn-info" title="تعديل">
                                                <i class="fe fe-edit-2"></i>
                                            </a>
                                            <form action="{{ route('backup-schedules.execute', $schedule->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" title="تشغيل الآن">
                                                    <i class="fe fe-play"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('backup-schedules.toggle-active', $schedule->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $schedule->is_active ? 'warning' : 'success' }}" title="{{ $schedule->is_active ? 'إيقاف' : 'تفعيل' }}">
                                                    <i class="fe fe-{{ $schedule->is_active ? 'pause' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('backup-schedules.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذه الجدولة؟');">
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
                                    <td colspan="9" class="text-muted py-4">لا توجد جداول بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($schedules->hasPages())
                    <div class="mt-3">{{ $schedules->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
