@extends('student.layouts.master')

@section('page-title')
    سجل السلسلة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">سجل السلسلة</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('gamification.streak.index') }}">السلسلة اليومية</a></li>
                            <li class="breadcrumb-item active">السجل</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('gamification.streak.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <!-- معلومات -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                يعرض هذا السجل آخر 90 يوم من نشاطك اليومي
            </div>

            <!-- جدول السجل -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>سجل النشاط اليومي</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>عدد الأنشطة</th>
                                    <th>النقاط المكتسبة</th>
                                    <th>XP المكتسب</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history ?? [] as $day)
                                    <tr>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($day->date)->format('Y/m/d') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($day->date)->locale('ar')->translatedFormat('l') }}</small>
                                        </td>
                                        <td>
                                            @if($day->activities_count > 0)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>نشط
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle me-1"></i>غير نشط
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($day->activities_count > 0)
                                                <span class="fw-bold">{{ $day->activities_count }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($day->points_earned > 0)
                                                <span class="text-success fw-bold">+{{ number_format($day->points_earned) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($day->xp_earned > 0)
                                                <span class="text-info fw-bold">+{{ number_format($day->xp_earned) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا يوجد سجل نشاط حتى الآن
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($history) && $history->hasPages())
                        <div class="mt-3">
                            {{ $history->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop



