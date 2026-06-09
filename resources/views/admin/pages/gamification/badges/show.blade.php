@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الشارة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @if (\Session::has('success'))
                <div class="alert alert-success">
                    {!! \Session::get('success') !!}
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger">
                    {!! \Session::get('error') !!}
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">تفاصيل الشارة: {{ $badge->name }}</h5>
                            <div>
                                <a class="btn btn-sm btn-success" href="{{ route('admin.gamification.badges.award.badge', $badge->id) }}">
                                    <i class="fas fa-award me-1"></i> منح للطلاب
                                </a>
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
                                            <th>Slug</th>
                                            <td><code>{{ $badge->slug }}</code></td>
                                        </tr>
                                        <tr>
                                            <th>الوصف</th>
                                            <td>{{ $badge->description ?? 'لا يوجد وصف' }}</td>
                                        </tr>
                                        <tr>
                                            <th>النوع</th>
                                            <td>{{ $badge->type }}</td>
                                        </tr>
                                        <tr>
                                            <th>الندرة</th>
                                            <td>{{ $badge->rarity }}</td>
                                        </tr>
                                        <tr>
                                            <th>الفئة</th>
                                            <td>{{ $badge->category ?? 'غير محدد' }}</td>
                                        </tr>
                                        <tr>
                                            <th>مكافأة النقاط</th>
                                            <td>{{ $badge->points_value ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <th>المعايير</th>
                                            <td>{{ $criteriaLabel }}</td>
                                        </tr>
                                        <tr>
                                            <th>الحالة</th>
                                            <td>
                                                @if($badge->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                                @if($badge->is_visible)
                                                    <span class="badge bg-info">ظاهر</span>
                                                @endif
                                                @if($badge->is_hidden)
                                                    <span class="badge bg-secondary">مخفي</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>عدد الحاصلين</th>
                                            <td>{{ $stats['total_earned'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if($recentEarners->isNotEmpty())
                                <h6 class="mt-4 mb-3">آخر الحاصلين على الشارة</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>الطالب</th>
                                                <th>تاريخ المنح</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentEarners as $userBadge)
                                                <tr>
                                                    <td>{{ $userBadge->user->name ?? '—' }}</td>
                                                    <td>{{ $userBadge->awarded_at?->format('Y-m-d H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
