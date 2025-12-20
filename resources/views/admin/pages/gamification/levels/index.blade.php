@extends('admin.layouts.master')

@section('page-title')
    المستويات
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

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">

                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">جدول المستويات</h5>
                            <div>
                                <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#generateLevels">
                                    <i class="fas fa-magic me-1"></i> توليد تلقائي
                                </button>
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.gamification.levels.create') }}">
                                    <i class="fas fa-plus me-1"></i> إضافة مستوى
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>المستوى</th>
                                            <th>الاسم</th>
                                            <th>XP المطلوب</th>
                                            <th>مكافأة النقاط</th>
                                            <th>مكافأة الجواهر</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($levels as $level)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary fs-6">{{ $level->level }}</span>
                                                </td>
                                                <td>{{ $level->name }}</td>
                                                <td>{{ number_format($level->xp_required) }}</td>
                                                <td>{{ $level->points_reward ?? 0 }}</td>
                                                <td>{{ $level->gems_reward ?? 0 }}</td>
                                                <td>
                                                    <a href="{{ route('admin.gamification.levels.edit', $level->id) }}" class="btn btn-sm btn-info text-white">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $level->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="delete{{ $level->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف المستوى {{ $level->level }}؟
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('admin.gamification.levels.destroy', $level->id) }}" method="POST" style="display: inline;">
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
                                                <td colspan="6" class="text-danger fw-bold text-center">
                                                    لا توجد مستويات. استخدم التوليد التلقائي لإنشاء المستويات.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if(isset($levels) && $levels->hasPages())
                                <div class="mt-3">
                                    {{ $levels->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generate Levels Modal -->
            <div class="modal fade" id="generateLevels" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.gamification.levels.generate') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">توليد المستويات تلقائياً</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="max_level" class="form-label">عدد المستويات</label>
                                    <input type="number" class="form-control" id="max_level" name="max_level" value="50" min="10" max="100">
                                </div>
                                <div class="mb-3">
                                    <label for="base_xp" class="form-label">XP الأساسي للمستوى الأول</label>
                                    <input type="number" class="form-control" id="base_xp" name="base_xp" value="100" min="50">
                                </div>
                                <div class="mb-3">
                                    <label for="multiplier" class="form-label">معامل الزيادة</label>
                                    <input type="number" class="form-control" id="multiplier" name="multiplier" value="1.5" min="1.1" max="3" step="0.1">
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    سيتم حذف جميع المستويات الحالية واستبدالها بالمستويات الجديدة.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success">توليد</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop
