@extends('admin.layouts.master')

@section('page-title')
    الإنجازات
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
                            <h5 class="mb-0 fw-bold">جدول الإنجازات</h5>
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.gamification.achievements.create') }}">
                                <i class="fas fa-plus me-1"></i> إضافة إنجاز جديد
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
                                            <th>المستوى</th>
                                            <th>النقاط</th>
                                            <th>الحالة</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($achievements as $achievement)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><span style="font-size: 24px;">{{ $achievement->icon ?? '🏆' }}</span></td>
                                                <td>{{ $achievement->name }}</td>
                                                <td>
                                                    @switch($achievement->tier)
                                                        @case('bronze')
                                                            <span class="badge bg-warning text-dark">برونزي</span>
                                                            @break
                                                        @case('silver')
                                                            <span class="badge bg-secondary">فضي</span>
                                                            @break
                                                        @case('gold')
                                                            <span class="badge bg-warning">ذهبي</span>
                                                            @break
                                                        @case('platinum')
                                                            <span class="badge bg-info">بلاتيني</span>
                                                            @break
                                                        @case('diamond')
                                                            <span class="badge bg-primary">ماسي</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $achievement->tier }}</span>
                                                    @endswitch
                                                </td>
                                                <td>{{ $achievement->points_reward ?? 0 }}</td>
                                                <td>
                                                    @if($achievement->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.gamification.achievements.edit', $achievement->id) }}" class="btn btn-sm btn-info text-white">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $achievement->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="delete{{ $achievement->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف "{{ $achievement->name }}"؟
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('admin.gamification.achievements.destroy', $achievement->id) }}" method="POST" style="display: inline;">
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
                            @if(isset($achievements) && $achievements->hasPages())
                                <div class="mt-3">{{ $achievements->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
