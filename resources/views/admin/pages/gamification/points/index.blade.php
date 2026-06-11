@extends('admin.layouts.master')

@section('page-title')
    إدارة النقاط
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
                        <li class="breadcrumb-item"><a href="{{ route('admin.gamification.points.index') }}">التلعيب</a></li>
                        <li class="breadcrumb-item active">سجل النقاط</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-award me-1"></i>
                            نظام النقاط
                        </span>
                        <h2 class="group-show-hero__title mb-2">سجل معاملات النقاط</h2>
                        <p class="group-show-hero__desc mb-0">
                            تتبّع مصدر كل نقطة: دروس، اختبارات، شارات، مكافآت إدارية، ومصروفات المتجر.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            @include('admin.pages.gamification.partials.recalculate-button', ['modalId' => 'pointsRecalculateModal'])
                            <a href="{{ route('admin.gamification.points.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus-circle"></i></span>
                                <span class="group-show-action__text">منح / تعويض نقاط</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                @include('admin.pages.gamification.points.partials.stats', ['stats' => $stats ?? []])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية السجل</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالطالب، أو فلتر حسب المصدر ونوع العملية والتاريخ.</p>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.gamification.points.index') }}" method="GET" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="pointsSearchInput">بحث بالطالب</label>
                                <input id="pointsSearchInput" type="text" name="q" class="form-control"
                                    placeholder="الاسم أو البريد..." value="{{ request('q') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="pointsSource">المصدر</label>
                                <select name="source" id="pointsSource" class="form-select">
                                    <option value="">كل المصادر</option>
                                    @foreach ($sourceOptions ?? [] as $value => $label)
                                        <option value="{{ $value }}" {{ request('source') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="pointsType">نوع العملية</label>
                                <select name="type" id="pointsType" class="form-select">
                                    <option value="">كل الأنواع</option>
                                    <option value="earn" {{ request('type') === 'earn' ? 'selected' : '' }}>مكتسب</option>
                                    <option value="spend" {{ request('type') === 'spend' ? 'selected' : '' }}>مصروف</option>
                                    <option value="bonus" {{ request('type') === 'bonus' ? 'selected' : '' }}>مكافأة</option>
                                    <option value="penalty" {{ request('type') === 'penalty' ? 'selected' : '' }}>خصم</option>
                                    <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>استرداد</option>
                                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>تعديل</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="pointsFromDate">من تاريخ</label>
                                <input type="date" name="from_date" id="pointsFromDate" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="pointsToDate">إلى تاريخ</label>
                                <input type="date" name="to_date" id="pointsToDate" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-xl-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('admin.gamification.points.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>مسح
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        المعاملات
                        <span class="group-show-members-card__count">{{ $transactions->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 group-show-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الطالب</th>
                                    <th>النقاط</th>
                                    <th>النوع</th>
                                    <th>المصدر</th>
                                    <th>التفاصيل</th>
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
                                            @if ($transaction->user)
                                                <div class="fw-semibold">{{ $transaction->user->name }}</div>
                                                <small class="text-muted">{{ $transaction->user->email }}</small>
                                            @else
                                                <span class="text-muted">غير معروف</span>
                                            @endif
                                        </td>
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
                                            <div class="fs-11 text-muted mt-1">{{ $transaction->source }}</div>
                                        </td>
                                        <td>
                                            <span title="{{ $transaction->description }}">
                                                {{ Str::limit($transaction->description ?: '—', 40) }}
                                            </span>
                                            @if ($transaction->admin)
                                                <div class="fs-11 text-muted mt-1">بواسطة: {{ $transaction->admin->name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $transaction->created_at->format('Y-m-d') }}</div>
                                            <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if ($transaction->user)
                                                <a href="{{ route('admin.gamification.points.user-transactions', $transaction->user) }}"
                                                    class="btn btn-sm btn-icon btn-outline-primary" title="سجل الطالب">
                                                    <i class="fe fe-user"></i>
                                                </a>
                                            @endif
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
                                            لا توجد معاملات مطابقة للبحث
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
                            <li>الطالب: {{ $transaction->user->name ?? '—' }}</li>
                            <li>النقاط: {{ $transaction->points > 0 ? '+' : '' }}{{ $transaction->points }}</li>
                            <li>المصدر: {{ $modalSourceLabel }}</li>
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
