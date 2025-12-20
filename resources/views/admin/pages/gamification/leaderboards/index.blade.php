@extends('admin.layouts.master')

@section('page-title')
    لوحات المتصدرين
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
                            <h5 class="mb-0 fw-bold">لوحات المتصدرين</h5>
                            <div>
                                <form action="{{ route('admin.gamification.leaderboards.update-all') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success me-2">
                                        <i class="fas fa-sync me-1"></i> تحديث الكل
                                    </button>
                                </form>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.gamification.leaderboards.create') }}">
                                    <i class="fas fa-plus me-1"></i> إضافة لوحة
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>الاسم</th>
                                            <th>النوع</th>
                                            <th>الفترة</th>
                                            <th>الحالة</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($leaderboards as $leaderboard)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $leaderboard->name }}</td>
                                                <td>
                                                    @switch($leaderboard->type)
                                                        @case('points')
                                                            <span class="badge bg-success">النقاط</span>
                                                            @break
                                                        @case('xp')
                                                            <span class="badge bg-info">XP</span>
                                                            @break
                                                        @case('badges')
                                                            <span class="badge bg-warning">الشارات</span>
                                                            @break
                                                        @case('streak')
                                                            <span class="badge bg-danger">السلسلة</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $leaderboard->type }}</span>
                                                    @endswitch
                                                </td>
                                                <td>
                                                    @switch($leaderboard->period)
                                                        @case('daily')
                                                            يومي
                                                            @break
                                                        @case('weekly')
                                                            أسبوعي
                                                            @break
                                                        @case('monthly')
                                                            شهري
                                                            @break
                                                        @case('all_time')
                                                            كل الأوقات
                                                            @break
                                                        @default
                                                            {{ $leaderboard->period }}
                                                    @endswitch
                                                </td>
                                                <td>
                                                    @if($leaderboard->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.gamification.leaderboards.show', $leaderboard->id) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.gamification.leaderboards.edit', $leaderboard->id) }}" class="btn btn-sm btn-info text-white">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $leaderboard->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="delete{{ $leaderboard->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف "{{ $leaderboard->name }}"؟
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('admin.gamification.leaderboards.destroy', $leaderboard->id) }}" method="POST" style="display: inline;">
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
                                                <td colspan="6" class="text-danger fw-bold text-center">لا توجد بيانات متاحة</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
