@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الشارة
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

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">

                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">تفاصيل الشارة: {{ $badge->name }}</h5>
                            <div>
                                <a class="btn btn-sm btn-info text-white" href="{{ route('admin.gamification.badges.edit', $badge->id) }}">
                                    <i class="fas fa-edit me-1"></i> تعديل
                                </a>
                                <a class="btn btn-sm btn-secondary" href="{{ route('admin.gamification.badges.index') }}">
                                    <i class="fas fa-arrow-right me-1"></i> رجوع
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    @if($badge->icon)
                                        <span style="font-size: 80px;">{{ $badge->icon }}</span>
                                    @elseif($badge->image)
                                        <img src="{{ asset('storage/' . $badge->image) }}" alt="{{ $badge->name }}" class="img-fluid rounded" style="max-width: 150px;">
                                    @else
                                        <i class="fas fa-medal text-warning" style="font-size: 80px;"></i>
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">الاسم</th>
                                            <td>{{ $badge->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>الوصف</th>
                                            <td>{{ $badge->description ?? 'لا يوجد وصف' }}</td>
                                        </tr>
                                        <tr>
                                            <th>الندرة</th>
                                            <td>
                                                @switch($badge->rarity)
                                                    @case('common')
                                                        <span class="badge bg-secondary">عادي</span>
                                                        @break
                                                    @case('uncommon')
                                                        <span class="badge bg-success">غير شائع</span>
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
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الفئة</th>
                                            <td>{{ $badge->category ?? 'غير محدد' }}</td>
                                        </tr>
                                        <tr>
                                            <th>مكافأة النقاط</th>
                                            <td>{{ $badge->points_reward ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>مكافأة XP</th>
                                            <td>{{ $badge->xp_reward ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>نوع المتطلب</th>
                                            <td>{{ $badge->requirement_type ?? 'لا يوجد' }}</td>
                                        </tr>
                                        <tr>
                                            <th>قيمة المتطلب</th>
                                            <td>{{ $badge->requirement_value ?? 'لا يوجد' }}</td>
                                        </tr>
                                        <tr>
                                            <th>الحالة</th>
                                            <td>
                                                @if($badge->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>عدد الحاصلين</th>
                                            <td>{{ $badge->users_count ?? $badge->users()->count() ?? 0 }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- End::app-content -->
@stop
