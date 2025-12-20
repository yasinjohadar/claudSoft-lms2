@extends('admin.layouts.master')

@section('page-title')
    تفاصيل لوحة المتصدرين: {{ $leaderboard->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل لوحة المتصدرين</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.gamification.leaderboards.index') }}">لوحات المتصدرين</a></li>
                            <li class="breadcrumb-item active">{{ $leaderboard->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.gamification.leaderboards.edit', $leaderboard->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('admin.gamification.leaderboards.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-right me-2"></i>رجوع
                    </a>
                </div>
            </div>

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Leaderboard Info -->
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2"></i>معلومات اللوحة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">الاسم:</th>
                                            <td>{{ $leaderboard->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>النوع:</th>
                                            <td>
                                                @switch($leaderboard->type)
                                                    @case('global')
                                                        <span class="badge bg-primary">عام</span>
                                                        @break
                                                    @case('course')
                                                        <span class="badge bg-info">كورس</span>
                                                        @break
                                                    @case('weekly')
                                                        <span class="badge bg-success">أسبوعي</span>
                                                        @break
                                                    @case('monthly')
                                                        <span class="badge bg-warning">شهري</span>
                                                        @break
                                                    @case('speed')
                                                        <span class="badge bg-danger">السرعة</span>
                                                        @break
                                                    @case('accuracy')
                                                        <span class="badge bg-purple">الدقة</span>
                                                        @break
                                                    @case('streak')
                                                        <span class="badge bg-orange">السلسلة</span>
                                                        @break
                                                    @case('social')
                                                        <span class="badge bg-teal">اجتماعي</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $leaderboard->type }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الفترة:</th>
                                            <td>
                                                @switch($leaderboard->period)
                                                    @case('all_time')
                                                        <span class="badge bg-dark">كل الأوقات</span>
                                                        @break
                                                    @case('daily')
                                                        <span class="badge bg-primary">يومي</span>
                                                        @break
                                                    @case('weekly')
                                                        <span class="badge bg-info">أسبوعي</span>
                                                        @break
                                                    @case('monthly')
                                                        <span class="badge bg-success">شهري</span>
                                                        @break
                                                    @case('yearly')
                                                        <span class="badge bg-warning">سنوي</span>
                                                        @break
                                                    @case('season')
                                                        <span class="badge bg-danger">موسم</span>
                                                        @break
                                                    @default
                                                        {{ $leaderboard->period }}
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الوصف:</th>
                                            <td>{{ $leaderboard->description ?? 'لا يوجد وصف' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="200">الحالة:</th>
                                            <td>
                                                @if($leaderboard->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>مرئي للطلاب:</th>
                                            <td>
                                                @if($leaderboard->is_visible)
                                                    <span class="badge bg-success">نعم</span>
                                                @else
                                                    <span class="badge bg-secondary">لا</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الحد الأقصى للمشاركين:</th>
                                            <td>{{ $leaderboard->max_entries ?? 100 }}</td>
                                        </tr>
                                        <tr>
                                            <th>آخر تحديث:</th>
                                            <td>
                                                @if($leaderboard->last_updated_at)
                                                    {{ $leaderboard->last_updated_at->format('Y-m-d H:i') }}
                                                @else
                                                    <span class="text-muted">لم يتم التحديث بعد</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($leaderboard->start_date || $leaderboard->end_date)
                                            <tr>
                                                <th>الفترة الزمنية:</th>
                                                <td>
                                                    @if($leaderboard->start_date)
                                                        من: {{ $leaderboard->start_date->format('Y-m-d') }}
                                                    @endif
                                                    @if($leaderboard->end_date)
                                                        <br>إلى: {{ $leaderboard->end_date->format('Y-m-d') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            @if(isset($stats))
            <div class="row mb-4">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-chart-bar me-2"></i>الإحصائيات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary-transparent">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $stats['total_participants'] ?? 0 }}</h3>
                                            <p class="mb-0">إجمالي المشاركين</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success-transparent">
                                        <div class="card-body text-center">
                                            <h3 class="text-success">{{ number_format($stats['average_score'] ?? 0, 2) }}</h3>
                                            <p class="mb-0">متوسط النقاط</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning-transparent">
                                        <div class="card-body text-center">
                                            <h3 class="text-warning">{{ number_format($stats['highest_score'] ?? 0) }}</h3>
                                            <p class="mb-0">أعلى نقاط</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info-transparent">
                                        <div class="card-body text-center">
                                            <h3 class="text-info">{{ number_format($stats['lowest_score'] ?? 0) }}</h3>
                                            <p class="mb-0">أقل نقاط</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(isset($stats['by_division']) && count($stats['by_division']) > 0)
                            <div class="mt-4">
                                <h6>التوزيع حسب الفئات:</h6>
                                <div class="row">
                                    @foreach($stats['by_division'] as $division => $count)
                                        <div class="col-md-2 mb-2">
                                            <div class="card">
                                                <div class="card-body text-center p-2">
                                                    <strong>{{ $count }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ ucfirst($division) }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Leaderboard Entries -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-trophy me-2"></i>قائمة المتصدرين
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="80" class="text-center">الترتيب</th>
                                            <th>المستخدم</th>
                                            <th class="text-center">النقاط</th>
                                            <th class="text-center">الفئة</th>
                                            <th class="text-center">التغيير</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($entries as $entry)
                                            <tr>
                                                <td class="text-center">
                                                    @if($entry->rank <= 3)
                                                        <span class="badge bg-{{ $entry->rank == 1 ? 'warning' : ($entry->rank == 2 ? 'secondary' : 'danger') }} fs-16">
                                                            {{ $entry->rank }}
                                                        </span>
                                                    @else
                                                        <strong class="text-muted">#{{ $entry->rank }}</strong>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($entry->user && $entry->user->avatar)
                                                            <img src="{{ asset('storage/' . $entry->user->avatar) }}" 
                                                                 alt="{{ $entry->user->name }}" 
                                                                 class="rounded-circle me-2" 
                                                                 width="40" 
                                                                 height="40">
                                                        @else
                                                            <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                                <span class="text-primary">{{ substr($entry->user->name ?? 'U', 0, 1) }}</span>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <strong>{{ $entry->user->name ?? 'مستخدم محذوف' }}</strong>
                                                            @if($entry->user)
                                                                <br>
                                                                <small class="text-muted">{{ $entry->user->email }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <strong class="text-primary">{{ number_format($entry->score) }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    @if($entry->division)
                                                        <span class="badge bg-{{ $entry->division == 'diamond' ? 'primary' : ($entry->division == 'platinum' ? 'info' : ($entry->division == 'gold' ? 'warning' : ($entry->division == 'silver' ? 'secondary' : 'danger'))) }}">
                                                            {{ ucfirst($entry->division) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($entry->rank_change > 0)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-arrow-up"></i> +{{ $entry->rank_change }}
                                                        </span>
                                                    @elseif($entry->rank_change < 0)
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-arrow-down"></i> {{ $entry->rank_change }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-minus"></i> 0
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <i class="fas fa-inbox fs-48 text-muted mb-3"></i>
                                                    <p class="text-muted">لا توجد إدخالات في هذه اللوحة</p>
                                                    <p class="text-muted">
                                                        <small>قم بتحديث اللوحة لإضافة المشاركين</small>
                                                    </p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

