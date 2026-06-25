@extends('admin.layouts.master')

@section('page-title')
    تقييم مشاريع الفرق
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تسليمات بانتظار التقييم</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">تقييم المشاريع</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.project-challenges.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fe fe-layers me-1"></i>تحديات المشاريع
                    </a>
                </div>
            </div>

            <div class="card custom-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>التحدي</th>
                                <th>المرحلة</th>
                                <th>الفريق</th>
                                <th>المُسلِّم</th>
                                <th>تاريخ التسليم</th>
                                <th>الحالة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusLabels = [
                                    'submitted' => 'مُرسَل',
                                    'under_review' => 'قيد المراجعة',
                                ];
                            @endphp
                            @forelse($submissions as $submission)
                                <tr>
                                    <td>{{ $submission->team->challenge->title ?? '—' }}</td>
                                    <td>{{ $submission->stage->title ?? '—' }}</td>
                                    <td>{{ $submission->team->name ?? '—' }}</td>
                                    <td>{{ $submission->submitter->name ?? $submission->submitter->email ?? '—' }}</td>
                                    <td>{{ $submission->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td>
                                        <span class="pc-stage-status pc-stage-status--submitted">
                                            {{ $statusLabels[$submission->status] ?? $submission->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.project-grading.show', $submission->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fe fe-check-square me-1"></i>تقييم
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد تسليمات بانتظار التقييم</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($submissions->hasPages())
                    <div class="card-footer">{{ $submissions->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@stop
