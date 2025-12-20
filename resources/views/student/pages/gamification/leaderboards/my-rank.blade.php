@extends('student.layouts.master')

@section('page-title')
    رتبتي في لوحات المتصدرين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">رتبتي في لوحات المتصدرين</h4>
                <a href="{{ route('gamification.leaderboards.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>عرض جميع اللوحات
                </a>
            </div>

            <!-- ملخص -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                يعرض هذا القسم رتبتك في جميع لوحات المتصدرين النشطة
            </div>

            <!-- لوحات المتصدرين -->
            @forelse($rankings ?? [] as $ranking)
                @php
                    $leaderboard = $ranking['leaderboard'];
                    $rank = $ranking['rank'];
                @endphp
                <div class="card border-0 shadow-sm mb-4 {{ $rank['rank'] <= 3 ? 'border-warning border-2' : '' }}">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-trophy me-2 text-warning"></i>
                                {{ $leaderboard->title ?? 'لوحة المتصدرين' }}
                            </h5>
                            @if($rank['rank'] <= 3)
                                <span class="badge bg-warning">في المراكز الأولى!</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <h2 class="fw-bold text-primary mb-0">#{{ $rank['rank'] ?? 'N/A' }}</h2>
                                <small class="text-muted">الترتيب</small>
                            </div>
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <h4 class="fw-bold text-success mb-0">{{ number_format($rank['score'] ?? 0) }}</h4>
                                <small class="text-muted">النتيجة</small>
                            </div>
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <h5 class="fw-bold text-info mb-0">{{ number_format($rank['percentile'] ?? 0, 1) }}%</h5>
                                <small class="text-muted">النسبة المئوية</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('gamification.leaderboards.show', $leaderboard->id) }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>عرض اللوحة
                                </a>
                            </div>
                        </div>
                        @if(isset($rank['change']) && $rank['change'] != 0)
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-arrow-{{ $rank['change'] > 0 ? 'up text-success' : 'down text-danger' }} me-2"></i>
                                    <span class="{{ $rank['change'] > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ abs($rank['change']) }} {{ $rank['change'] > 0 ? 'صعود' : 'هبوط' }} من آخر تحديث
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">لا توجد لوحات متصدرين نشطة</h5>
                        <p class="text-muted">ابدأ في التعلم والمشاركة للظهور في لوحات المتصدرين!</p>
                        <a href="{{ route('student.courses.index') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-book me-1"></i>تصفح الكورسات
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@stop



