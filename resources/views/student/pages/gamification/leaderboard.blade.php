@extends('student.layouts.master')

@section('page-title')
    لوحة المتصدرين
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <h4 class="mb-0">لوحة المتصدرين</h4>
            </div>

            <!-- فلاتر -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <select class="form-select" id="period-filter">
                                <option value="all_time" {{ request('period') == 'all_time' ? 'selected' : '' }}>كل الأوقات</option>
                                <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>هذا الشهر</option>
                                <option value="weekly" {{ request('period') == 'weekly' ? 'selected' : '' }}>هذا الأسبوع</option>
                                <option value="daily" {{ request('period') == 'daily' ? 'selected' : '' }}>اليوم</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <select class="form-select" id="type-filter">
                                <option value="points" {{ request('type') == 'points' ? 'selected' : '' }}>النقاط</option>
                                <option value="xp" {{ request('type') == 'xp' ? 'selected' : '' }}>XP</option>
                                <option value="badges" {{ request('type') == 'badges' ? 'selected' : '' }}>الشارات</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- أعلى 3 -->
            <div class="row mb-4">
                @foreach($topThree ?? [] as $index => $user)
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm text-center {{ $user->id == auth()->id() ? 'border-primary border-2' : '' }}">
                            <div class="card-body">
                                <div class="fs-1 mb-2">
                                    @if($index == 0) 🥇 @elseif($index == 1) 🥈 @else 🥉 @endif
                                </div>
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{ $user->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle" width="60">
                                </div>
                                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                                <h4 class="text-primary mb-0">{{ number_format($user->score ?? 0) }}</h4>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- القائمة الكاملة -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" width="80">الترتيب</th>
                                    <th>الطالب</th>
                                    <th class="text-center">المستوى</th>
                                    <th class="text-center">النتيجة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaderboard ?? [] as $index => $user)
                                    <tr class="{{ $user->id == auth()->id() ? 'table-primary' : '' }}">
                                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $user->avatar ?? asset('assets/images/default-avatar.png') }}" class="rounded-circle me-2" width="35">
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->id == auth()->id())
                                                        <span class="badge bg-primary ms-1">أنت</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $user->level ?? 1 }}</span>
                                        </td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($user->score ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">لا توجد بيانات</td>
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
