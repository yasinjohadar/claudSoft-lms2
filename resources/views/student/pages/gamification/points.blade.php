@extends('student.layouts.master')

@section('page-title')
    النقاط
@stop

@section('content')
<div class="main-content app-content student-points-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">النقاط</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">النقاط</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">تابع رصيدك وطرق كسب النقاط واستخدمها في المتجر</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                <a href="{{ route('gamification.points.history') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri ri-history-line me-1"></i>سجل النقاط
                </a>
                <a href="{{ route('gamification.points.how-to-earn') }}" class="btn btn-outline-info btn-sm">
                    <i class="ri ri-question-line me-1"></i>كيف أكسب نقاط؟
                </a>
            </div>
        </div>

        @include('student.pages.gamification.points.partials.stats', [
            'totalPoints' => $totalPoints,
            'availablePoints' => $availablePoints,
            'spentPoints' => $spentPoints,
            'monthlyEarned' => $monthlyEarned,
        ])

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card custom-card student-quizzes-panel mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm bg-primary-transparent">
                                    <i class="ri ri-coin-line text-primary"></i>
                                </span>
                                <div>
                                    <h6 class="card-title mb-0">طرق كسب النقاط</h6>
                                    <p class="text-muted fs-12 mb-0">أهم الأنشطة التي تمنحك نقاطاً</p>
                                </div>
                            </div>
                            <a href="{{ route('gamification.points.how-to-earn') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                        </div>
                        @include('student.pages.gamification.points.partials.earning-methods', ['earningMethods' => $earningMethods])
                    </div>
                </div>

                <div class="card custom-card student-quizzes-panel">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar avatar-sm bg-info-transparent">
                                <i class="ri ri-history-line text-info"></i>
                            </span>
                            <h6 class="card-title mb-0">آخر المعاملات</h6>
                        </div>
                        @if(($recentTransactions ?? collect())->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>الوصف</th>
                                            <th>النقاط</th>
                                            <th>التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTransactions as $tx)
                                            <tr>
                                                <td>{{ $tx->description ?? $tx->source }}</td>
                                                <td>
                                                    <span class="{{ $tx->points >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $tx->points >= 0 ? '+' : '' }}{{ number_format($tx->points) }}
                                                    </span>
                                                </td>
                                                <td class="text-muted fs-12">{{ $tx->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">لا توجد معاملات بعد. ابدأ التعلم لكسب النقاط!</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card custom-card student-quizzes-panel mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar avatar-sm bg-warning-transparent">
                                <i class="ri ri-store-2-line text-warning"></i>
                            </span>
                            <h6 class="card-title mb-0">استخدم نقاطك</h6>
                        </div>
                        <p class="text-muted fs-13">يمكنك شراء معززات XP، عناصر تجميلية، وحماية السلسلة من <a href="{{ route('gamification.shop.index') }}">المتجر</a>.</p>
                        <a href="{{ route('gamification.shop.index') }}" class="btn btn-primary w-100">
                            <i class="ri ri-store-2-line me-1"></i>زيارة المتجر
                        </a>
                    </div>
                </div>

                <div class="card custom-card student-quizzes-panel">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="avatar avatar-sm bg-success-transparent">
                                <i class="ri ri-user-add-line text-success"></i>
                            </span>
                            <h6 class="card-title mb-0">ادعُ صديقاً</h6>
                        </div>
                        <p class="text-muted fs-13 mb-3">شارك رابط الدعوة واحصل على نقاط عند تسجيل صديقك.</p>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" id="referral-link-input" value="{{ $referralLink }}" readonly>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('referral-link-input').value)">
                                نسخ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    function animateCount(el, target) {
        if (!el) return;
        const duration = 600;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('.student-points-page [data-countup]').forEach(function (el) {
        animateCount(el, parseFloat(el.dataset.countup || '0'));
    });
})();
</script>
@endpush
