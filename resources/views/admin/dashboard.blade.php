

@extends('admin.layouts.master')

@section('page-title')
لوحة التحكم الرئيسية
@stop






@section('content')
  <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                    <div>
                        <h4 class="mb-0">مرحباً بك في لوحة تحكم النظام</h4>
                        <p class="mb-0 text-muted">نظرة شاملة على أهم مؤشرات الأداء والروابط الهامة في المنصة.</p>
                    </div>
                    <div class="main-dashboard-header-right">
                        <div>
                            <label class="fs-13 text-muted">المستخدمون خلال آخر 30 يوم</label>
                            <div class="main-star">
                                <span class="fs-5 fw-semibold text-primary">
                                    {{ number_format($userStats['new_last_30_days'] ?? 0) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="fs-13 text-muted">المستخدمون النشطون اليوم</label>
                            <h5 class="mb-0 fw-semibold">
                                {{ number_format($userStats['active_today'] ?? 0) }}
                            </h5>
                        </div>
                        <div>
                            <label class="fs-13 text-muted">إجمالي المستخدمين</label>
                            <h5 class="mb-0 fw-semibold">
                                {{ number_format($userStats['total_users'] ?? 0) }}
                            </h5>
                        </div>
                    </div>
                </div>
                <!-- End Page Header -->

                <!-- الإحصائيات الرئيسية -->
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-primary-gradient">
                            <div class="px-3 pt-3  pb-2 pt-0">
                                <div >
                                    <h6 class="mb-3 fs-12 text-fixed-white">الطلاب المسجلون</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div >
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">
                                                {{ number_format($userStats['students'] ?? 0) }}
                                            </h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">إجمالي حسابات الطلاب في النظام</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="compositeline"></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-danger-gradient">
                            <div class="px-3 pt-3  pb-2 pt-0">
                                <div >
                                    <h6 class="mb-3 fs-12 text-fixed-white">الكورسات النشطة</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div >
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">
                                                {{ number_format($courseStats['published_courses'] ?? 0) }}
                                            </h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">
                                                من أصل {{ number_format($courseStats['total_courses'] ?? 0) }} كورس
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="compositeline2"></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-success-gradient">
                            <div class="px-3 pt-3  pb-2 pt-0">
                                <div >
                                    <h6 class="mb-3 fs-12 text-fixed-white">الالتحاقات النشطة</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div >
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">
                                                {{ number_format($courseStats['active_enrollments'] ?? 0) }}
                                            </h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">
                                                إجمالي الالتحاقات في النظام:
                                                {{ number_format($courseStats['total_enrollments'] ?? 0) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="compositeline3"></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-warning-gradient">
                            <div class="px-3 pt-3  pb-2 pt-0">
                                <div >
                                    <h6 class="mb-3 fs-12 text-fixed-white">الشهادات الصادرة</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div >
                                            <h4 class="fs-20 fw-bold mb-1 text-fixed-white">
                                                {{ number_format($learningStats['certificates_issued'] ?? 0) }}
                                            </h4>
                                            <p class="mb-0 fs-12 text-fixed-white op-7">
                                                الشهادات الصادرة اليوم:
                                                {{ number_format($todayStats['certificates_today'] ?? 0) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="compositeline4"></div>
                        </div>
                    </div>
                </div>
                <!-- /الإحصائيات الرئيسية -->

                <!-- مخطط بسيط + ملخص اليوم -->
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-xl-7">
                        <div class="card">
                            <div class="card-header pb-0">
                                <div class="d-flex justify-content-between">
                                    <h4 class="card-title mb-0">تطور الالتحاقات خلال آخر 6 أشهر</h4>
                                    <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light bg-transparent rounded-pill" data-bs-toggle="dropdown"><i
                                        class="fe fe-more-horizontal"></i></a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('enrollments.all') }}">عرض كل الالتحاقات</a>
                                    </div>
                                </div>
                                <p class="fs-12 text-muted mb-0">
                                    هذا المخطط يوضح عدد الالتحاقات بالكورسات شهرياً. البيانات تقريبية ويمكن تطويرها لاحقاً لعرض مزيد من التفاصيل.
                                </p>
                            </div>
                            <div class="card-body">
                                <div id="enrollments-chart"
                                     data-labels='@json($chartData['enrollments']['labels'] ?? [])'
                                     data-values='@json($chartData['enrollments']['data'] ?? [])'>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-xl-5">
                        <div class="card card-dashboard-map-one">
                            <h4 class="card-title">ملخص اليوم</h4>
                            <p class="fs-12 text-muted mb-3">أهم أرقام اليوم الحالي في النظام.</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>مستخدمون جدد</span>
                                    <span class="fw-semibold">
                                        {{ number_format($todayStats['new_users'] ?? 0) }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>التحاقات جديدة</span>
                                    <span class="fw-semibold">
                                        {{ number_format($todayStats['new_enrollments'] ?? 0) }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>كورسات أُكملت اليوم</span>
                                    <span class="fw-semibold">
                                        {{ number_format($todayStats['completed_today'] ?? 0) }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>محاولات اختبارات جديدة</span>
                                    <span class="fw-semibold">
                                        {{ number_format($learningStats['quiz_attempts'] ?? 0) }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- /مخطط + ملخص اليوم -->

                <!-- روابط سريعة + أحدث النشاطات -->
                <div class="row">
                    <div class="col-xl-4 col-md-12 col-lg-12">
                        <div class="card overflow-hidden">
                            <div class="card-header pb-1">
                                <h3 class="card-title mb-2">روابط سريعة</h3>
                                <p class="fs-12 mb-0 text-muted">أكثر الصفحات التي ستحتاجها في إدارة المنصة.</p>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-6 mb-2">
                                        <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm w-100">
                                            <i class="fe fe-users me-1"></i> إدارة المستخدمين
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="{{ route('courses.index') }}" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fe fe-book-open me-1"></i> الكورسات
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="{{ route('enrollments.all') }}" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fe fe-user-check me-1"></i> الالتحاقات
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fe fe-award me-1"></i> الشهادات
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="{{ route('admin.n8n.index') }}" class="btn btn-outline-info btn-sm w-100">
                                            <i class="fe fe-zap me-1"></i> تكامل n8n
                                        </a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <a href="{{ route('admin.webhooks.index') }}" class="btn btn-outline-info btn-sm w-100">
                                            <i class="fe fe-git-commit me-1"></i> الويب هوكس
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-12 col-lg-6">
                        <div class="card">
                            <div class="card-header pb-1">
                                <h3 class="card-title mb-2">أحدث الالتحاقات</h3>
                                <p class="fs-12 mb-0 text-muted">آخر الطلاب المسجلين في الكورسات.</p>
                            </div>
                            <div class="product-timeline card-body pt-2 mt-1">
                                <ul class="timeline-1 mb-0">
                                    @forelse($recentEnrollments as $enrollment)
                                        <li class="mt-0">
                                            <i class="fe fe-user-check bg-primary-gradient text-fixed-white product-icon"></i>
                                            <span class="fw-medium mb-4 fs-14">
                                                {{ $enrollment->student->name ?? 'طالب' }}
                                            </span>
                                            <span class="float-end fs-11 text-muted">
                                                {{ optional($enrollment->enrollment_date)->diffForHumans() }}
                                            </span>
                                            <p class="mb-0 text-muted fs-12">
                                                تم تسجيله في كورس
                                                <strong>{{ $enrollment->course->title ?? '-' }}</strong>
                                            </p>
                                        </li>
                                    @empty
                                        <li class="mt-0 mb-0 text-muted fs-12">
                                            لا توجد تسجيلات حديثة.
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-12 col-lg-6">
                        <div class="card">
                            <div class="card-header pb-0">
                                <h3 class="card-title mb-2">أحدث الأنشطة الآلية (n8n / Webhooks)</h3>
                                <p class="fs-12 mb-0 text-muted">آخر عمليات التكامل مع الأنظمة الخارجية.</p>
                            </div>
                            <div class="card-body sales-info pb-0 pt-0">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>الحدث</th>
                                                <th>الحالة</th>
                                                <th class="text-end">التوقيت</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentWebhooks as $log)
                                                <tr>
                                                    <td class="fw-medium">
                                                        {{ $log->event_name ?? $log->type ?? 'Webhook' }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $status = $log->status ?? 'unknown';
                                                        @endphp
                                                        <span class="badge bg-{{ $status === 'success' ? 'success' : ($status === 'failed' ? 'danger' : 'secondary') }}-transparent">
                                                            {{ $status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end text-muted fs-12">
                                                        {{ optional($log->created_at)->diffForHumans() }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted fs-12">
                                                        لا توجد سجلات حديثة.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card ">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center pb-2">
                                            <p class="mb-0">إجمالي المحاولات في الاختبارات</p>
                                        </div>
                                        <h4 class="fw-bold mb-2">
                                            {{ number_format($learningStats['quiz_attempts'] ?? 0) }}
                                        </h4>
                                        <div class="progress progress-style progress-sm">
                                            <div class="progress-bar bg-primary-gradient" style="width: 80%" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="78"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-4 mt-md-0">
                                        <div class="d-flex align-items-center pb-2">
                                            <p class="mb-0">المحاولات الناجحة</p>
                                        </div>
                                        <h4 class="fw-bold mb-2">
                                            {{ number_format($learningStats['passed_attempts'] ?? 0) }}
                                        </h4>
                                        <div class="progress progress-style progress-sm">
                                            <div class="progress-bar bg-danger-gradient" style="width: 45%" role="progressbar"  aria-valuenow="45" aria-valuemin="0" aria-valuemax="45"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /روابط سريعة + أحدث النشاطات -->

                <!-- مراجعة مختصرة للأرقام الأخرى -->
                <div class="row">
                    <div class="col-md-12 col-lg-4 col-xl-4">
                        <div class="card top-countries-card">
                            <div class="card-header p-0">
                                <h6 class="card-title fs-13 mb-2">أرقام سريعة</h6>
                                <span class="d-block mg-b-10 text-muted fs-12 mb-2">نظرة عامة على بعض المؤشرات الأخرى في النظام.</span>
                            </div>
                            <div class="list-group border">
                                <div class="list-group-item border-top-0" id="br-t-0">
                                    <p>عدد المدرسين</p><span>{{ number_format($userStats['instructors'] ?? 0) }}</span>
                                </div>
                                <div class="list-group-item">
                                    <p>عدد المشرفين (Admins)</p><span>{{ number_format($userStats['admins'] ?? 0) }}</span>
                                </div>
                                <div class="list-group-item">
                                    <p>الكورسات الظاهرة في الواجهة</p><span>{{ number_format($courseStats['visible_courses'] ?? 0) }}</span>
                                </div>
                                <div class="list-group-item">
                                    <p>إجمالي التقييمات على الكورسات</p><span>{{ number_format($learningStats['course_reviews'] ?? 0) }}</span>
                                </div>
                                <div class="list-group-item">
                                    <p>نقاط تكامل n8n (Endpoints)</p><span>{{ number_format($integrationStats['n8n_endpoints'] ?? 0) }}</span>
                                </div>
                                <div class="list-group-item border-bottom-0 mb-0">
                                    <p>إجمالي سجلات الويب هوكس</p><span>{{ number_format($integrationStats['incoming_webhooks'] ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-8 col-xl-8">
                        <div class="card card-table">
                            <div class=" card-header p-0 d-flex justify-content-between">
                                <h4 class="card-title mb-1">أحدث الشهادات / الاختبارات</h4>
                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light bg-transparent rounded-pill" data-bs-toggle="dropdown"><i
                                    class="fe fe-more-horizontal"></i></a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.certificates.index') }}">كل الشهادات</a>
                                    <a class="dropdown-item" href="{{ route('quizzes.index') }}">كل الاختبارات</a>
                                </div>
                            </div>
                            <span class="fs-12 text-muted mb-3 ">عرض مختصر لأحدث الشهادات الصادرة وأحدث محاولات الاختبارات.</span>
                            <div class="table-responsive country-table">
                                <table class="table table-striped table-bordered mb-0 text-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="wd-lg-25p">الطالب</th>
                                            <th class="wd-lg-25p">النوع</th>
                                            <th class="wd-lg-25p">العنصر</th>
                                            <th class="wd-lg-25p">التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentCertificates as $certificate)
                                            <tr>
                                                <td>{{ $certificate->user->name ?? '-' }}</td>
                                                <td class="fw-medium">شهادة</td>
                                                <td class="fw-medium">{{ $certificate->course->title ?? '-' }}</td>
                                                <td class="text-muted fw-medium">
                                                    {{ optional($certificate->created_at)->format('Y-m-d H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        @foreach($recentQuizAttempts as $attempt)
                                            <tr>
                                                <td>{{ $attempt->user->name ?? '-' }}</td>
                                                <td class="fw-medium">اختبار</td>
                                                <td class="fw-medium">{{ $attempt->quiz->title ?? '-' }}</td>
                                                <td class="text-muted fw-medium">
                                                    {{ optional($attempt->created_at)->format('Y-m-d H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /row -->

            </div>
        </div>
        <!-- End::app-content -->
@stop

@push('dashboard-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('enrollments-chart');
            if (!el || typeof ApexCharts === 'undefined') {
                return;
            }

            const labels = JSON.parse(el.dataset.labels || '[]');
            const values = JSON.parse(el.dataset.values || '[]');

            const options = {
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: { show: false },
                },
                series: [{
                    name: 'التحاقات',
                    data: values,
                }],
                xaxis: {
                    categories: labels,
                    labels: { style: { fontFamily: 'inherit' } },
                },
                colors: ['#0555a2'],
                dataLabels: { enabled: false },
                grid: { strokeDashArray: 4 },
            };

            const chart = new ApexCharts(el, options);
            chart.render();
        });
    </script>
@endpush
