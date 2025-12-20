@extends('admin.layouts.master')

@section('page-title')
    إدارة النقاط
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            </div>

            @if (\Session::has('success'))
                <div class="alert alert-success">
                    <ul><li>{!! \Session::get('success') !!}</li></ul>
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger">
                    <ul><li>{!! \Session::get('error') !!}</li></ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">سجل النقاط</h5>
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.gamification.points.create') }}">
                                <i class="fas fa-plus me-1"></i> منح نقاط
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>الطالب</th>
                                            <th>النقاط</th>
                                            <th>النوع</th>
                                            <th>السبب</th>
                                            <th>التاريخ</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transactions as $transaction)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $transaction->user->name ?? 'غير معروف' }}</td>
                                                <td>
                                                    @if($transaction->points > 0)
                                                        <span class="badge bg-success">+{{ $transaction->points }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ $transaction->points }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @switch($transaction->type)
                                                        @case('earned')
                                                            <span class="badge bg-success">مكتسب</span>
                                                            @break
                                                        @case('spent')
                                                            <span class="badge bg-warning">مصروف</span>
                                                            @break
                                                        @case('bonus')
                                                            <span class="badge bg-info">مكافأة</span>
                                                            @break
                                                        @case('penalty')
                                                            <span class="badge bg-danger">عقوبة</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $transaction->type }}</span>
                                                    @endswitch
                                                </td>
                                                <td>{{ Str::limit($transaction->reason, 30) }}</td>
                                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $transaction->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="delete{{ $transaction->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف هذه العملية؟
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('admin.gamification.points.destroy', $transaction->id) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">حذف</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-danger fw-bold text-center">لا توجد بيانات متاحة</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if(isset($transactions) && $transactions->hasPages())
                                <div class="mt-3">{{ $transactions->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
