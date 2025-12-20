@extends('admin.layouts.master')

@section('page-title')
    تحليلات الـ Gamification
@stop

@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            </div>

            <!-- إحصائيات عامة -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent text-primary">
                                        <i class="fas fa-users fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">الطلاب النشطين</span>
                                    <h4 class="fw-semibold mb-0">{{ number_format($stats['overview']['active_students'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent text-success">
                                        <i class="fas fa-coins fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">إجمالي النقاط</span>
                                    <h4 class="fw-semibold mb-0">{{ number_format($stats['points']['total'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent text-warning">
                                        <i class="fas fa-medal fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">الشارات الممنوحة</span>
                                    <h4 class="fw-semibold mb-0">{{ number_format($stats['badges']['total_earned'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent text-info">
                                        <i class="fas fa-percent fs-18"></i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-muted fs-12">معدل الاحتفاظ</span>
                                    <h4 class="fw-semibold mb-0">{{ number_format($stats['engagement']['retention_rate'] ?? 0, 1) }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- إحصائيات تفصيلية -->
            <div class="row">
                <!-- النقاط -->
                <div class="col-xl-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-bold">إحصائيات النقاط</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th>إجمالي النقاط</th>
                                    <td>{{ number_format($stats['points']['total'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <th>متوسط النقاط</th>
                                    <td>{{ number_format($stats['points']['average'] ?? 0, 1) }}</td>
                                </tr>
                                <tr>
                                    <th>أعلى نقاط</th>
                                    <td>{{ number_format($stats['points']['highest'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <th>نقاط اليوم</th>
                                    <td>{{ number_format($stats['points']['today'] ?? 0) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- المستويات -->
                <div class="col-xl-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-bold">إحصائيات المستويات</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th>متوسط المستوى</th>
                                    <td>{{ number_format($stats['levels']['average'] ?? 0, 1) }}</td>
                                </tr>
                                <tr>
                                    <th>أعلى مستوى</th>
                                    <td>{{ $stats['levels']['highest'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>ترقيات اليوم</th>
                                    <td>{{ $stats['levels']['level_ups_today'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>ترقيات الأسبوع</th>
                                    <td>{{ $stats['levels']['level_ups_week'] ?? 0 }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الشارات والتحديات -->
            <div class="row">
                <div class="col-xl-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-bold">إحصائيات الشارات</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th>إجمالي الشارات</th>
                                    <td>{{ $stats['badges']['total'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>الشارات الممنوحة</th>
                                    <td>{{ $stats['badges']['total_earned'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>متوسط لكل طالب</th>
                                    <td>{{ number_format($stats['badges']['average_per_user'] ?? 0, 1) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-bold">إحصائيات التحديات</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tr>
                                    <th>التحديات النشطة</th>
                                    <td>{{ $stats['challenges']['active'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>التحديات المكتملة</th>
                                    <td>{{ $stats['challenges']['completed'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>نسبة الإكمال</th>
                                    <td>{{ number_format($stats['challenges']['completion_rate'] ?? 0, 1) }}%</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- زر مسح الكاش -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <form action="{{ route('admin.gamification.analytics.clear-cache') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-sync me-2"></i> مسح الكاش وتحديث البيانات
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
