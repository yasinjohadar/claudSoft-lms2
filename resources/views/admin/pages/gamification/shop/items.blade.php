@extends('admin.layouts.master')

@section('page-title')
    عناصر المتجر
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
                            <h5 class="mb-0 fw-bold">عناصر المتجر</h5>
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.gamification.shop.items.create') }}">
                                <i class="fas fa-plus me-1"></i> إضافة عنصر
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle table-hover table-bordered mb-0 text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>الصورة</th>
                                            <th>الاسم</th>
                                            <th>الفئة</th>
                                            <th>السعر</th>
                                            <th>المخزون</th>
                                            <th>الحالة</th>
                                            <th>العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($items as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if($item->image)
                                                        <img src="{{ asset('storage/' . $item->image) }}" width="40" height="40" class="rounded">
                                                    @else
                                                        <span style="font-size: 24px;">{{ $item->icon ?? '🎁' }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->category->name ?? 'غير محدد' }}</td>
                                                <td>
                                                    @if($item->price_points)
                                                        {{ $item->price_points }} نقطة
                                                    @endif
                                                    @if($item->price_gems)
                                                        <br>{{ $item->price_gems }} جوهرة
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->stock === null)
                                                        <span class="badge bg-info">غير محدود</span>
                                                    @elseif($item->stock > 0)
                                                        <span class="badge bg-success">{{ $item->stock }}</span>
                                                    @else
                                                        <span class="badge bg-danger">نفد</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->is_active)
                                                        <span class="badge bg-success">نشط</span>
                                                    @else
                                                        <span class="badge bg-danger">غير نشط</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.gamification.shop.items.edit', $item->id) }}" class="btn btn-sm btn-info text-white">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#delete{{ $item->id }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="delete{{ $item->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">تأكيد الحذف</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            هل أنت متأكد من حذف "{{ $item->name }}"؟
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('admin.gamification.shop.items.destroy', $item->id) }}" method="POST" style="display: inline;">
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
                            @if(isset($items) && $items->hasPages())
                                <div class="mt-3">{{ $items->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
