@extends('student.layouts.master')

@section('page-title')
    سجل النقاط
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">سجل النقاط</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('gamification.points.index') }}">النقاط</a></li>
                            <li class="breadcrumb-item active">السجل</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('gamification.points.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-primary mb-2"><i class="fas fa-list fa-2x"></i></div>
                            <h4 class="fw-bold mb-1">{{ number_format($stats['total_transactions'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">إجمالي المعاملات</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-success mb-2"><i class="fas fa-arrow-up fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-success">+{{ number_format($stats['total_earned'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">إجمالي المكتسب</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-danger mb-2"><i class="fas fa-arrow-down fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-danger">-{{ number_format($stats['total_spent'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">إجمالي المستهلك</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-info mb-2"><i class="fas fa-calendar-alt fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-info">+{{ number_format($stats['this_month_earned'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">هذا الشهر</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الفلاتر -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('gamification.points.history') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">النوع</label>
                            <select name="type" class="form-select">
                                <option value="">الكل</option>
                                <option value="earned" {{ request('type') == 'earned' ? 'selected' : '' }}>مكتسب</option>
                                <option value="spent" {{ request('type') == 'spent' ? 'selected' : '' }}>مستهلك</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المصدر</label>
                            <select name="source" class="form-select">
                                <option value="">الكل</option>
                                @if(isset($sources))
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>{{ $source }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>تصفية
                            </button>
                            <a href="{{ route('gamification.points.history') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i>إعادة تعيين
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- جدول المعاملات -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>سجل المعاملات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>المصدر</th>
                                    <th>النقاط</th>
                                    <th>الوصف</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions ?? [] as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('Y/m/d H:i') }}</td>
                                        <td>
                                            @if($transaction->points > 0)
                                                <span class="badge bg-success">مكتسب</span>
                                            @else
                                                <span class="badge bg-danger">مستهلك</span>
                                            @endif
                                        </td>
                                        <td>{{ $transaction->source ?? 'غير محدد' }}</td>
                                        <td>
                                            <span class="fw-bold {{ $transaction->points > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->points > 0 ? '+' : '' }}{{ number_format($transaction->points) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->description ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد معاملات حتى الآن
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($transactions) && $transactions->hasPages())
                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop



