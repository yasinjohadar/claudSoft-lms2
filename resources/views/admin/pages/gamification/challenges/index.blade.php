@extends('admin.layouts.master')

@section('page-title')
    التحديات
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
                            <h5 class="mb-0 fw-bold">جدول التحديات</h5>
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.gamification.challenges.create') }}">
                                <i class="fas fa-plus me-1"></i> إضافة تحدي جديد
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>الأيقونة</th>
                                            <th>الاسم</th>
                                            <th>النوع</th>
                                            <th>الهدف</th>
                                            <th>المكافأة</th>
                                            <th>الحالة</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($challenges as $challenge)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><span style="font-size: 24px;">{{ $challenge->icon ?? '🎯' }}</span></td>
                                                <td>{{ $challenge->name }}</td>
                                                <td>
                                                    @switch($challenge->type)
                                                        @case('daily')
                                                            <span class="badge bg-info">يومي</span>
                                                            @break
                                                        @case('weekly')
                                                            <span class="badge bg-primary">أسبوعي</span>
                                                            @break
                                                        @case('monthly')
                                                            <span class="badge bg-warning">شهري</span>
                                                            @break
                                                        @case('special')
                                                            <span class="badge bg-danger">خاص</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $challenge->type }}</span>
                                                    @endswitch
                                                </td>
                                                <td>{{ $challenge->target_value }}</td>
                                                <td>{{ $challenge->points_reward ?? 0 }} نقطة</td>
                                                <td>
                                                    @if($challenge->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.gamification.challenges.show', $challenge->id) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.gamification.challenges.edit', $challenge->id) }}" class="btn btn-sm btn-info text-white">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $challenge->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="delete{{ $challenge->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف "{{ $challenge->name }}"؟
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('admin.gamification.challenges.destroy', $challenge->id) }}" method="POST" style="display: inline;">
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
                                                <td colspan="8" class="text-danger fw-bold text-center">لا توجد بيانات متاحة</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if(isset($challenges) && $challenges->hasPages())
                                <div class="mt-3">{{ $challenges->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
