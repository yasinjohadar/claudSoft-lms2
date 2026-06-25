@extends('admin.layouts.master')

@section('page-title')
    تقييم التحديات البرمجية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تسليمات بانتظار التقييم</h5>
                </div>
            </div>

            <div class="card custom-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>التحدي</th>
                                <th>الطالب</th>
                                <th>تاريخ التسليم</th>
                                <th>المحاولة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                                <tr>
                                    <td>{{ $attempt->challenge->title }}</td>
                                    <td>{{ $attempt->student->name ?? $attempt->student->email }}</td>
                                    <td>{{ $attempt->submitted_at?->format('Y-m-d H:i') }}</td>
                                    <td>#{{ $attempt->attempt_number }}</td>
                                    <td>
                                        <a href="{{ route('admin.challenge-grading.show', $attempt->id) }}" class="btn btn-sm btn-primary">تقييم</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">لا توجد تسليمات بانتظار التقييم</td></tr>
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
