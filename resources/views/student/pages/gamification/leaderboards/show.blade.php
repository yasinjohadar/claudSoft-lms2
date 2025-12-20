@extends('student.layouts.master')

@section('page-title')
    {{ $leaderboard->title ?? 'لوحة المتصدرين' }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">{{ $leaderboard->title ?? 'لوحة المتصدرين' }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('gamification.leaderboards.index') }}">لوحة المتصدرين</a></li>
                            <li class="breadcrumb-item active">{{ $leaderboard->title ?? 'التفاصيل' }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('gamification.leaderboards.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <!-- معلومات اللوحة -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>معلومات اللوحة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>النوع:</strong> 
                            <span class="badge bg-primary">{{ $leaderboard->type ?? 'عام' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>الفترة:</strong> 
                            <span class="badge bg-info">{{ $leaderboard->period ?? 'كل الأوقات' }}</span>
                        </div>
                        @if($leaderboard->description)
                            <div class="col-12 mb-3">
                                <strong>الوصف:</strong> {{ $leaderboard->description }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- إحصائيات -->
            @if(isset($stats))
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-primary mb-2"><i class="fas fa-users fa-2x"></i></div>
                                <h4 class="fw-bold mb-1">{{ number_format($stats['total_participants'] ?? 0) }}</h4>
                                <p class="text-muted mb-0">إجمالي المشاركين</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-success mb-2"><i class="fas fa-percentage fa-2x"></i></div>
                                <h4 class="fw-bold mb-1 text-success">{{ number_format($stats['average_score'] ?? 0, 1) }}</h4>
                                <p class="text-muted mb-0">متوسط النتيجة</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-warning mb-2"><i class="fas fa-trophy fa-2x"></i></div>
                                <h4 class="fw-bold mb-1 text-warning">{{ number_format($stats['top_score'] ?? 0) }}</h4>
                                <p class="text-muted mb-0">أعلى نتيجة</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-info mb-2"><i class="fas fa-chart-line fa-2x"></i></div>
                                <h4 class="fw-bold mb-1 text-info">{{ number_format($stats['active_users'] ?? 0) }}</h4>
                                <p class="text-muted mb-0">مستخدمين نشطين</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- رتبتي -->
            @if(isset($userRank))
                <div class="card border-0 shadow-sm mb-4 {{ $userRank['rank'] <= 3 ? 'border-warning border-2' : '' }}">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2 text-primary"></i>رتبتي
                            @if($userRank['rank'] <= 3)
                                <span class="badge bg-warning ms-2">في المراكز الأولى!</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <h2 class="fw-bold text-primary mb-0">#{{ $userRank['rank'] ?? 'N/A' }}</h2>
                                <small class="text-muted">الترتيب</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="avatar avatar-lg mx-auto mb-2">
                                    <img src="{{ auth()->user()->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle" width="60">
                                </div>
                                <h6 class="fw-bold mb-0">{{ auth()->user()->name }}</h6>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="fw-bold text-success mb-0">{{ number_format($userRank['score'] ?? 0) }}</h4>
                                <small class="text-muted">النتيجة</small>
                            </div>
                            <div class="col-md-4">
                                @if(isset($userRank['change']) && $userRank['change'] != 0)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-arrow-{{ $userRank['change'] > 0 ? 'up text-success' : 'down text-danger' }} me-2"></i>
                                        <span class="{{ $userRank['change'] > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ abs($userRank['change']) }} {{ $userRank['change'] > 0 ? 'صعود' : 'هبوط' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- المتصدرون -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-crown me-2 text-warning"></i>المتصدرون</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">الترتيب</th>
                                    <th>المستخدم</th>
                                    <th>النتيجة</th>
                                    <th>التغيير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries ?? [] as $index => $entry)
                                    <tr class="{{ $entry->user_id == auth()->id() ? 'table-primary' : '' }}">
                                        <td>
                                            @if($index < 3)
                                                <span class="fs-3">
                                                    @if($index == 0) 🥇
                                                    @elseif($index == 1) 🥈
                                                    @else 🥉
                                                    @endif
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">#{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <img src="{{ $entry->user->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle" width="40">
                                                </div>
                                                <div>
                                                    <strong>{{ $entry->user->name ?? 'غير محدد' }}</strong>
                                                    @if($entry->user_id == auth()->id())
                                                        <span class="badge bg-primary ms-2">أنت</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ number_format($entry->score ?? 0) }}</span>
                                        </td>
                                        <td>
                                            @if(isset($entry->rank_change))
                                                @if($entry->rank_change > 0)
                                                    <span class="text-success">
                                                        <i class="fas fa-arrow-up me-1"></i>+{{ $entry->rank_change }}
                                                    </span>
                                                @elseif($entry->rank_change < 0)
                                                    <span class="text-danger">
                                                        <i class="fas fa-arrow-down me-1"></i>{{ $entry->rank_change }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد نتائج حتى الآن
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
@stop



