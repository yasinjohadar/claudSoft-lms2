@extends('student.layouts.master')

@section('page-title')
    تقارير الدراسة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تقارير الدراسة (AI)</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">تقارير الدراسة</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="alert alert-info small">
            هنا تجد كل التقارير الصادرة لك. يمكنك أيضاً فتح صفحة التقارير لكل كورس من القائمة أدناه.
        </div>

        @if($enrolledCourses->isNotEmpty())
        <div class="card custom-card mb-4">
            <div class="card-header">
                <span class="fw-semibold">وصول سريع حسب الكورس</span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($enrolledCourses as $c)
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('student.progress.ai-reports.index', $c) }}" class="btn btn-outline-primary btn-sm w-100 text-start text-truncate" title="{{ $c->title }}">
                                <i class="fas fa-graduation-cap me-1"></i>{{ $c->title }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-semibold">كل التقارير</span>
                <a href="{{ route('student.progress.overview') }}" class="btn btn-sm btn-light">تقدمي في الكورسات</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>الكورس</th>
                                <th>المجموعة</th>
                                <th>التاريخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $r)
                                <tr>
                                    <td>{{ $r->course?->title ?? '—' }}</td>
                                    <td>{{ $r->courseGroup?->name ?? '—' }}</td>
                                    <td class="text-muted small">{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('student.progress.ai-reports.show', $r) }}" class="btn btn-sm btn-primary">عرض التقرير</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">لا توجد تقارير دراسة بعد. عند إصدار المدرّس لتقرير سيظهر هنا، وستصلك إشعار.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($reports->hasPages())
                    <div class="mt-3">{{ $reports->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
