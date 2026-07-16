@extends('admin.layouts.master')

@section('page-title')
    محاولات — {{ $challenge->title }}
@stop

@section('content')
    @php
        $statusLabels = [
            'in_progress' => 'جارية',
            'submitted' => 'بانتظار التقييم',
            'graded' => 'مُقيَّمة',
            'returned' => 'مُعادة',
        ];
        $statusBadge = [
            'in_progress' => 'bg-primary-transparent',
            'submitted' => 'bg-warning-transparent',
            'graded' => 'bg-success-transparent',
            'returned' => 'bg-info-transparent',
        ];
    @endphp

    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">محاولات الطلاب — {{ $challenge->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('programming-challenges.index') }}">التحديات البرمجية</a></li>
                            <li class="breadcrumb-item active">المحاولات</li>
                        </ol>
                    </nav>
                    <p class="text-muted mb-0 mt-2 small">كل التسليمات محفوظة هنا — المُقيَّمة والجارية وبانتظار التقييم.</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="{{ route('admin.challenge-grading.index') }}" class="btn btn-outline-warning btn-sm">
                        <i class="fe fe-check-square me-1"></i>قائمة التقييم
                    </a>
                    <a href="{{ route('programming-challenges.index') }}" class="btn btn-light btn-sm">العودة للتحديات</a>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <div class="card custom-card mb-0">
                        <div class="card-body py-3">
                            <div class="text-muted small">إجمالي المحاولات</div>
                            <div class="fs-4 fw-bold">{{ $attempts->total() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card custom-card mb-0">
                        <div class="card-body py-3">
                            <div class="text-muted small">بانتظار التقييم</div>
                            <div class="fs-4 fw-bold text-warning">{{ $attempts->getCollection()->where('status', 'submitted')->count() }}{{ $attempts->hasPages() ? '+' : '' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card custom-card mb-0">
                        <div class="card-body py-3">
                            <div class="text-muted small">مُقيَّمة (هذه الصفحة)</div>
                            <div class="fs-4 fw-bold text-success">{{ $attempts->getCollection()->where('status', 'graded')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>المحاولة</th>
                                <th>الحالة</th>
                                <th>الدرجة</th>
                                <th>التسليم</th>
                                <th>ملفات</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                                @php
                                    $files = $attempt->submissions->first()?->files ?? collect();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $attempt->student->name ?? '—' }}</strong>
                                        <div class="small text-muted">{{ $attempt->student->email ?? '' }}</div>
                                    </td>
                                    <td>#{{ $attempt->attempt_number }}</td>
                                    <td>
                                        <span class="badge {{ $statusBadge[$attempt->status] ?? 'bg-secondary-transparent' }}">
                                            {{ $statusLabels[$attempt->status] ?? $attempt->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($attempt->isGraded())
                                            <strong>{{ $attempt->score }}</strong>
                                            <span class="text-muted">/ {{ $attempt->max_score }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $attempt->submitted_at?->format('Y-m-d H:i') ?? ($attempt->started_at?->format('Y-m-d H:i') ?? '—') }}
                                    </td>
                                    <td>{{ $files->count() }}</td>
                                    <td class="text-nowrap">
                                        @if(in_array($attempt->status, ['submitted', 'graded', 'returned'], true))
                                            <a href="{{ route('admin.challenge-grading.show', $attempt->id) }}"
                                               class="btn btn-sm {{ $attempt->status === 'submitted' ? 'btn-primary' : 'btn-outline-primary' }}">
                                                @if($attempt->status === 'submitted')
                                                    تقييم
                                                @else
                                                    عرض الكود والتقييم
                                                @endif
                                            </a>
                                        @else
                                            <span class="small text-muted">لم يُسلَّم بعد</span>
                                        @endif
                                    </td>
                                </tr>
                                @if(filled($attempt->feedback))
                                    <tr class="table-light">
                                        <td colspan="7" class="small">
                                            <strong class="text-muted">ملاحظة المقيّم:</strong>
                                            <div class="mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($attempt->feedback), 220) }}</div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        لا توجد محاولات محفوظة لهذا التحدي بعد
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($attempts->hasPages())
                    <div class="card-footer">{{ $attempts->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@stop
