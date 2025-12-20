@extends('student.layouts.master')

@section('page-title')
    {{ ucfirst($division) }} Division - {{ $leaderboard->title ?? 'لوحة المتصدرين' }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">قسم {{ ucfirst($division) }} - {{ $leaderboard->title ?? 'لوحة المتصدرين' }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('gamification.leaderboards.index') }}">لوحة المتصدرين</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('gamification.leaderboards.show', $leaderboard->id) }}">{{ $leaderboard->title }}</a></li>
                            <li class="breadcrumb-item active">قسم {{ ucfirst($division) }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('gamification.leaderboards.show', $leaderboard->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <!-- معلومات القسم -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        قسم {{ ucfirst($division) }}
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $divisionColors = [
                            'bronze' => 'warning',
                            'silver' => 'secondary',
                            'gold' => 'warning',
                            'platinum' => 'info',
                            'diamond' => 'primary'
                        ];
                        $color = $divisionColors[$division] ?? 'secondary';
                    @endphp
                    <div class="alert alert-{{ $color }}">
                        <i class="fas fa-info-circle me-2"></i>
                        يعرض هذا القسم المتصدرين في فئة <strong>{{ ucfirst($division) }}</strong>
                    </div>
                </div>
            </div>

            <!-- المتصدرون في القسم -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-crown me-2 text-warning"></i>المتصدرون في قسم {{ ucfirst($division) }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">الترتيب</th>
                                    <th>المستخدم</th>
                                    <th>النتيجة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries ?? [] as $index => $entry)
                                    <tr class="{{ $entry->user_id == auth()->id() ? 'table-primary' : '' }}">
                                        <td>
                                            <span class="badge bg-secondary">#{{ $index + 1 }}</span>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد نتائج في هذا القسم حتى الآن
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



