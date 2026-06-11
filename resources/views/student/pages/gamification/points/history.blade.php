@extends('student.layouts.master')

@section('page-title')
    سجل النقاط
@stop

@section('content')
<div class="main-content app-content student-points-page student-points-history-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">سجل النقاط</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('gamification.points.index') }}">النقاط</a></li>
                        <li class="breadcrumb-item active">السجل</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">تتبّع كل نقطة كسبتها أو أنفقتها مع مصدرها وتفاصيلها</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                <a href="{{ route('gamification.points.how-to-earn') }}" class="btn btn-outline-info btn-sm">
                    <i class="ri ri-question-line me-1"></i>كيف أكسب نقاط؟
                </a>
                <a href="{{ route('gamification.points.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri ri-arrow-right-line me-1"></i>العودة للنقاط
                </a>
            </div>
        </div>

        @include('student.pages.gamification.points.partials.history-stats', ['stats' => $stats ?? []])

        <div class="card custom-card student-quizzes-panel mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="ri ri-filter-3-line text-primary"></i>
                    </span>
                    <div>
                        <h6 class="card-title mb-0">تصفية السجل</h6>
                        <p class="text-muted fs-12 mb-0">ابحث حسب النوع أو المصدر أو الفترة الزمنية</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('gamification.points.history') }}" class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fs-12">النوع</label>
                        <select name="type" class="form-select">
                            <option value="">الكل</option>
                            <option value="earn" {{ request('type') === 'earn' ? 'selected' : '' }}>مكتسب</option>
                            <option value="spend" {{ request('type') === 'spend' ? 'selected' : '' }}>مصروف</option>
                            <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>مكافأة</option>
                            <option value="penalty" {{ request('type') === 'penalty' ? 'selected' : '' }}>خصم</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fs-12">المصدر</label>
                        <select name="source" class="form-select">
                            <option value="">كل المصادر</option>
                            @foreach ($sources ?? [] as $value => $label)
                                <option value="{{ $value }}" {{ request('source') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fs-12">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fs-12">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-lg-2 col-md-12">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri ri-search-line me-1"></i>تصفية
                            </button>
                            <a href="{{ route('gamification.points.history') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ri ri-refresh-line me-1"></i>مسح
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-info-transparent">
                            <i class="ri ri-history-line text-info"></i>
                        </span>
                        <div>
                            <h6 class="card-title mb-0">سجل المعاملات</h6>
                            <p class="text-muted fs-12 mb-0">
                                {{ number_format($transactions->total()) }} معاملة
                                @if (request()->hasAny(['type', 'source', 'date_from', 'date_to']))
                                    — نتائج مفلترة
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @php
                    $catalog = app(\App\Services\Gamification\PointEarningCatalog::class);
                @endphp

                @forelse ($transactions as $transaction)
                    @php
                        $sourceLabel = $catalog->getSourceLabel($transaction->source);
                        $sourceIcon = $catalog->getSourceIcon($transaction->source);
                        $isPositive = $transaction->points > 0;
                    @endphp
                    <div class="student-points-tx-item">
                        <div class="student-points-tx-item__icon {{ $isPositive ? 'is-earn' : 'is-spend' }}">
                            <i class="ri {{ $sourceIcon }}"></i>
                        </div>
                        <div class="student-points-tx-item__body">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                <div class="min-w-0">
                                    <h6 class="student-points-tx-item__title mb-1">
                                        {{ $transaction->description ?: $sourceLabel }}
                                    </h6>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge bg-primary-transparent">{{ $sourceLabel }}</span>
                                        @switch($transaction->type)
                                            @case('earn')
                                                <span class="badge bg-success-transparent">مكتسب</span>
                                                @break
                                            @case('spend')
                                                <span class="badge bg-warning-transparent">مصروف</span>
                                                @break
                                            @case('bonus')
                                                <span class="badge bg-info-transparent">مكافأة</span>
                                                @break
                                            @case('penalty')
                                                <span class="badge bg-danger-transparent">خصم</span>
                                                @break
                                            @default
                                                @if ($isPositive)
                                                    <span class="badge bg-success-transparent">مكتسب</span>
                                                @else
                                                    <span class="badge bg-danger-transparent">مصروف</span>
                                                @endif
                                        @endswitch
                                    </div>
                                </div>
                                <div class="student-points-tx-item__points {{ $isPositive ? 'is-earn' : 'is-spend' }}">
                                    {{ $isPositive ? '+' : '' }}{{ number_format($transaction->points) }}
                                </div>
                            </div>
                            <div class="student-points-tx-item__meta">
                                <span><i class="ri ri-calendar-line me-1"></i>{{ $transaction->created_at->format('Y/m/d') }}</span>
                                <span><i class="ri ri-time-line me-1"></i>{{ $transaction->created_at->format('H:i') }}</span>
                                @if ($transaction->balance_after !== null)
                                    <span><i class="ri ri-wallet-3-line me-1"></i>الرصيد: {{ number_format($transaction->balance_after) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="student-points-history-empty text-center py-5">
                        <span class="student-points-history-empty__icon">
                            <i class="ri ri-inbox-line"></i>
                        </span>
                        <h6 class="mb-2">لا توجد معاملات</h6>
                        <p class="text-muted fs-13 mb-3">
                            @if (request()->hasAny(['type', 'source', 'date_from', 'date_to']))
                                لا توجد نتائج مطابقة للتصفية الحالية.
                            @else
                                ابدأ التعلم وأكمل الدروس والاختبارات لكسب نقاطك الأولى!
                            @endif
                        </p>
                        <a href="{{ route('gamification.points.how-to-earn') }}" class="btn btn-primary btn-sm">
                            <i class="ri ri-lightbulb-line me-1"></i>اكتشف طرق الكسب
                        </a>
                    </div>
                @endforelse

                @if ($transactions->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
