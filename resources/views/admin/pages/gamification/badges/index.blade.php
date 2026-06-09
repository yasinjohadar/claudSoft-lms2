@extends('admin.layouts.master')

@section('page-title')
    الشارات
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            </div>
            <!-- Page Header Close -->

            @if (\Session::has('success'))
                <div class="alert alert-success">
                    <ul>
                        <li>{!! \Session::get('success') !!}</li>
                    </ul>
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger">
                    <ul>
                        <li>{!! \Session::get('error') !!}</li>
                    </ul>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">

                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">جدول الشارات</h5>
                            <div class="d-flex gap-2">
                                <a class="btn btn-sm btn-success" href="{{ route('admin.gamification.badges.award.form') }}">
                                    <i class="fas fa-award me-1"></i> منح يدوي
                                </a>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.gamification.badges.create') }}">
                                    <i class="fas fa-plus me-1"></i> إضافة شارة جديدة
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>الأيقونة</th>
                                            <th>الاسم</th>
                                            <th>الندرة</th>
                                            <th>النقاط</th>
                                            <th>الحالة</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($badges as $badge)
                                            <tr>
                                                <td>{{ $badges->firstItem() + $loop->index }}</td>
                                                <td>
                                                    @if($badge->icon)
                                                        <span style="font-size: 24px;">{{ $badge->icon }}</span>
                                                    @else
                                                        <i class="fas fa-medal text-warning fs-4"></i>
                                                    @endif
                                                </td>
                                                <td>{{ $badge->name }}</td>
                                                <td>
                                                    @switch($badge->rarity)
                                                        @case('common')
                                                            <span class="badge bg-secondary">عادي</span>
                                                            @break
                                                        @case('mythic')
                                                            <span class="badge bg-dark">أسطوري+</span>
                                                            @break
                                                        @case('rare')
                                                            <span class="badge bg-info">نادر</span>
                                                            @break
                                                        @case('epic')
                                                            <span class="badge bg-purple">ملحمي</span>
                                                            @break
                                                        @case('legendary')
                                                            <span class="badge bg-warning">أسطوري</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $badge->rarity }}</span>
                                                    @endswitch
                                                </td>
                                                <td>{{ $badge->points_value ?? 0 }}</td>
                                                <td>
                                                    @if($badge->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.gamification.badges.show', $badge->id) }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.gamification.badges.edit', $badge->id) }}" class="btn btn-sm btn-info text-white">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $badge->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="delete{{ $badge->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف الشارة "{{ $badge->name }}"؟
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('admin.gamification.badges.destroy', $badge->id) }}" method="POST" style="display: inline;">
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
                                                <td colspan="7" class="text-danger fw-bold text-center">
                                                    لا توجد بيانات متاحة
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($badges->hasPages())
                                <div class="mt-3">
                                    {{ $badges->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop
