@extends('admin.layouts.master')

@section('page-title')
    سجل نقاط {{ $user->name }}
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.gamification.points.index') }}">سجل النقاط</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-user me-1"></i>
                            سجل نقاط الطالب
                        </span>
                        <h2 class="group-show-hero__title mb-2">{{ $user->name }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            {{ $user->email }}
                            @if ($user->name_ar)
                                — {{ $user->name_ar }}
                            @endif
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('admin.gamification.points.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للسجل</span>
                            </a>
                            <a href="{{ route('admin.gamification.points.create') }}?target_type=single&user_id={{ $user->id }}"
                                class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus-circle"></i></span>
                                <span class="group-show-action__text">منح نقاط</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    [
                        'variant' => 'green',
                        'icon' => 'fe-award',
                        'label' => 'الرصيد المتاح',
                        'value' => $stats['available_points'] ?? 0,
                        'sub' => 'نقاط قابلة للاستخدام',
                    ],
                    [
                        'variant' => 'blue',
                        'icon' => 'fe-layers',
                        'label' => 'إجمالي النقاط',
                        'value' => $stats['total_points'] ?? 0,
                        'sub' => 'مجموع تراكمي',
                    ],
                    [
                        'variant' => 'cyan',
                        'icon' => 'fe-trending-up',
                        'label' => 'نقاط مكتسبة',
                        'value' => $stats['total_earned'] ?? 0,
                        'sub' => 'كل المكاسب',
                    ],
                    [
                        'variant' => 'orange',
                        'icon' => 'fe-trending-down',
                        'label' => 'نقاط مصروفة',
                        'value' => $stats['total_spent'] ?? 0,
                        'sub' => 'كل المصروفات',
                    ],
                ];
            @endphp

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
                                    <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        معاملات الطالب
                        <span class="group-show-members-card__count">{{ $transactions->total() }}</span>
                    </h6>
                    <form action="{{ route('admin.gamification.points.recalculate', $user) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="fe fe-refresh-cw me-1"></i>إعادة حساب الإحصائيات
                        </button>
                    </form>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 group-show-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>النقاط</th>
                                    <th>النوع</th>
                                    <th>المصدر</th>
                                    <th>التفاصيل</th>
                                    <th>الرصيد بعد</th>
                                    <th>التاريخ</th>
                                    <th class="text-center">عمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    @php
                                        $sourceLabel = app(\App\Services\Gamification\PointEarningCatalog::class)->getSourceLabel($transaction->source);
                                    @endphp
                                    <tr>
                                        <td>{{ $transactions->firstItem() + $loop->index }}</td>
                                        <td>
                                            @if ($transaction->points > 0)
                                                <span class="badge bg-success-transparent text-success">+{{ number_format($transaction->points) }}</span>
                                            @else
                                                <span class="badge bg-danger-transparent text-danger">{{ number_format($transaction->points) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($transaction->type)
                                                @case('earn')
                                                    <span class="badge bg-success">مكتسب</span>
                                                    @break
                                                @case('spend')
                                                    <span class="badge bg-warning">مصروف</span>
                                                    @break
                                                @case('bonus')
                                                    <span class="badge bg-info">مكافأة</span>
                                                    @break
                                                @case('penalty')
                                                    <span class="badge bg-danger">خصم</span>
                                                    @break
                                                @case('refund')
                                                    <span class="badge bg-primary">استرداد</span>
                                                    @break
                                                @case('adjustment')
                                                    <span class="badge bg-secondary">تعديل</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-light text-dark">{{ $transaction->type }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-transparent text-primary">{{ $sourceLabel }}</span>
                                        </td>
                                        <td>
                                            <span title="{{ $transaction->description }}">
                                                {{ Str::limit($transaction->description ?: '—', 50) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($transaction->balance_after) }}</td>
                                        <td>
                                            <div>{{ $transaction->created_at->format('Y-m-d') }}</div>
                                            <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteTransaction{{ $transaction->id }}"
                                                title="إلغاء المعاملة">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <i class="fe fe-inbox fs-24 d-block mb-2"></i>
                                            لا توجد معاملات لهذا الطالب
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($transactions->hasPages())
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @foreach ($transactions as $transaction)
        @php
            $modalSourceLabel = app(\App\Services\Gamification\PointEarningCatalog::class)->getSourceLabel($transaction->source);
        @endphp
        <div class="modal fade" id="deleteTransaction{{ $transaction->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إلغاء المعاملة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">هل تريد عكس هذه المعاملة؟</p>
                        <ul class="mb-0 text-muted fs-13">
                            <li>النقاط: {{ $transaction->points > 0 ? '+' : '' }}{{ $transaction->points }}</li>
                            <li>المصدر: {{ $modalSourceLabel }}</li>
                            <li>التفاصيل: {{ $transaction->description ?: '—' }}</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('admin.gamification.points.destroy', $transaction) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">تأكيد الإلغاء</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop
