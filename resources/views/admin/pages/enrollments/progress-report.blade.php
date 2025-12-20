@extends('admin.layouts.master')

@section('page-title')
    تقرير التقدم - {{ $course->title }}
@stop

@section('css')
<style>
    .stats-overview {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
    .stat-box {
        text-align: center;
        padding: 1rem;
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .stat-label {
        opacity: 0.95;
        font-size: 0.9rem;
    }
    .progress-bar-custom {
        height: 12px;
        border-radius: 10px;
        background: #e9ecef;
        position: relative;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        transition: width 0.5s ease;
    }
    .student-row {
        transition: all 0.2s;
        border-bottom: 1px solid #e9ecef;
    }
    .student-row:hover {
        background-color: #f8f9fa;
    }
    .status-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .chart-container {
        position: relative;
        height: 200px;
        margin: 1rem 0;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .progress-category {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    .progress-excellent {
        background: #d4edda;
        color: #155724;
    }
    .progress-good {
        background: #d1ecf1;
        color: #0c5460;
    }
    .progress-average {
        background: #fff3cd;
        color: #856404;
    }
    .progress-poor {
        background: #f8d7da;
        color: #721c24;
    }
    .time-spent-badge {
        background: #e3f2fd;
        color: #1565c0;
        padding: 0.3rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        display: inline-block;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تقرير التقدم التفصيلي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.enrollments.index', $course->id) }}">التسجيلات</a></li>
                            <li class="breadcrumb-item active">تقرير التقدم</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button type="button" class="btn btn-success btn-wave" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>تصدير Excel
                    </button>
                    <button type="button" class="btn btn-danger btn-wave" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>تصدير PDF
                    </button>
                    <button type="button" class="btn btn-primary btn-wave" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>طباعة
                    </button>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-primary-transparent me-3">
                                    <i class="fas fa-users fs-18 text-primary"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">إجمالي الطلاب</p>
                                    <h4 class="mb-0 fw-bold">{{ $totalEnrollments ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-success-transparent me-3">
                                    <i class="fas fa-check-circle fs-18 text-success"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">أكملوا الكورس</p>
                                    <h4 class="mb-0 fw-bold">{{ $completedCount ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-info-transparent me-3">
                                    <i class="fas fa-spinner fs-18 text-info"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">قيد الدراسة</p>
                                    <h4 class="mb-0 fw-bold">{{ $inProgressCount ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md bg-warning-transparent me-3">
                                    <i class="fas fa-chart-line fs-18 text-warning"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">متوسط التقدم</p>
                                    <h4 class="mb-0 fw-bold">{{ number_format($averageProgress ?? 0, 1) }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>فلترة البحث</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">حالة التقدم</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">الكل</option>
                                    <option value="not_started" {{ request('status') == 'not_started' ? 'selected' : '' }}>لم يبدأ</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد الدراسة</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">نطاق التقدم</label>
                                <select name="progress_range" class="form-select" onchange="this.form.submit()">
                                    <option value="">الكل</option>
                                    <option value="0-25" {{ request('progress_range') == '0-25' ? 'selected' : '' }}>0% - 25%</option>
                                    <option value="26-50" {{ request('progress_range') == '26-50' ? 'selected' : '' }}>26% - 50%</option>
                                    <option value="51-75" {{ request('progress_range') == '51-75' ? 'selected' : '' }}>51% - 75%</option>
                                    <option value="76-100" {{ request('progress_range') == '76-100' ? 'selected' : '' }}>76% - 100%</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ترتيب حسب</label>
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="progress_desc" {{ request('sort') == 'progress_desc' ? 'selected' : '' }}>التقدم (الأعلى أولاً)</option>
                                    <option value="progress_asc" {{ request('sort') == 'progress_asc' ? 'selected' : '' }}>التقدم (الأقل أولاً)</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>الاسم (أ-ي)</option>
                                    <option value="enrolled_desc" {{ request('sort') == 'enrolled_desc' ? 'selected' : '' }}>تاريخ التسجيل (الأحدث)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">بحث</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="اسم الطالب..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Progress Distribution Chart -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>توزيع التقدم</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="progressChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>ملخص سريع</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="fs-24 fw-bold text-primary">{{ $completedCount ?? 0 }}</div>
                                        <small class="text-muted">مكتمل</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="fs-24 fw-bold text-info">{{ $inProgressCount ?? 0 }}</div>
                                        <small class="text-muted">قيد الدراسة</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="fs-24 fw-bold text-warning">{{ $notStartedCount ?? 0 }}</div>
                                        <small class="text-muted">لم يبدأ</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="fs-24 fw-bold text-success">{{ number_format($averageProgress ?? 0, 1) }}%</div>
                                        <small class="text-muted">متوسط التقدم</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Progress Table -->
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">تقدم الطلاب التفصيلي</h6>
                    <span class="text-muted">{{ $enrollments->total() ?? 0 }} طالب</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>الطالب</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>التقدم</th>
                                    <th>الدروس المكتملة</th>
                                    <th>وقت الدراسة</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollments as $enrollment)
                                    <tr class="student-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($enrollment->student->avatar)
                                                    <img src="{{ asset('storage/' . $enrollment->student->avatar) }}"
                                                         alt="{{ $enrollment->student->name }}"
                                                         class="rounded-circle me-2"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                         style="width: 40px; height: 40px;">
                                                        {{ substr($enrollment->student->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $enrollment->student->name }}</div>
                                                    <small class="text-muted">{{ $enrollment->student->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($enrollment->enrolled_at)
                                                <div>{{ $enrollment->enrolled_at->format('Y/m/d') }}</div>
                                                <small class="text-muted">{{ $enrollment->enrolled_at->diffForHumans() }}</small>
                                            @else
                                                <div class="text-muted">-</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                <strong>{{ number_format($enrollment->progress_percentage ?? 0, 1) }}%</strong>
                                            </div>
                                            <div class="progress-bar-custom" style="width: 100px;">
                                                <div class="progress-fill" style="width: {{ $enrollment->progress_percentage ?? 0 }}%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $enrollment->completed_modules_count ?? 0 }}</span>
                                            <span class="text-muted">/ {{ $totalModules ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <span class="time-spent-badge">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $enrollment->total_time_spent ? gmdate('H:i', $enrollment->total_time_spent) : '00:00' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $progress = $enrollment->progress_percentage ?? 0;
                                                if ($progress == 100) {
                                                    $category = 'excellent';
                                                    $label = 'مكتمل';
                                                } elseif ($progress >= 75) {
                                                    $category = 'good';
                                                    $label = 'متقدم';
                                                } elseif ($progress >= 25) {
                                                    $category = 'average';
                                                    $label = 'متوسط';
                                                } else {
                                                    $category = 'poor';
                                                    $label = 'بدأ للتو';
                                                }
                                            @endphp
                                            <span class="progress-category progress-{{ $category }}">{{ $label }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.users.courses', $enrollment->student_id) }}"
                                                   class="btn btn-sm btn-light"
                                                   title="عرض تفاصيل الطالب"
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-light"
                                                        onclick="sendReminder({{ $enrollment->id }})"
                                                        title="إرسال تذكير">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-25"></i>
                                            <p class="text-muted">لا توجد تسجيلات</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($enrollments->hasPages())
                    <div class="card-footer">
                        {{ $enrollments->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php
    $progressData = $progressDistribution ?? [
        'not_started' => 0,
        'low'        => 0,
        'medium'     => 0,
        'high'       => 0,
        'completed'  => 0,
    ];
@endphp

<script>
    // Progress Distribution Chart
    const ctx = document.getElementById('progressChart').getContext('2d');
    const progressData = @json($progressData);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['لم يبدأ (0%)', 'منخفض (1-25%)', 'متوسط (26-75%)', 'مرتفع (76-99%)', 'مكتمل (100%)'],
            datasets: [{
                data: [
                    progressData.not_started,
                    progressData.low,
                    progressData.medium,
                    progressData.high,
                    progressData.completed
                ],
                backgroundColor: [
                    '#6c757d',
                    '#f8d7da',
                    '#fff3cd',
                    '#d1ecf1',
                    '#d4edda'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    position: 'bottom',
                    rtl: true,
                    labels: {
                        font: {
                            family: 'Cairo, sans-serif',
                            size: 12
                        },
                        padding: 10,
                        boxWidth: 12
                    }
                },
                tooltip: {
                    rtl: true,
                    bodyFont: {
                        family: 'Cairo, sans-serif'
                    }
                }
            }
        }
    });

    @php
        $exportExcelUrl = \Illuminate\Support\Facades\Route::has('courses.enrollments.export')
            ? route('courses.enrollments.export', [$course->id, 'format' => 'excel'])
            : null;

        $exportPdfUrl = \Illuminate\Support\Facades\Route::has('courses.enrollments.export')
            ? route('courses.enrollments.export', [$course->id, 'format' => 'pdf'])
            : null;
    @endphp

    // Export to Excel
    function exportToExcel() {
        @if($exportExcelUrl)
            window.location.href = '{{ $exportExcelUrl }}' +
                '?' + new URLSearchParams(new FormData(document.getElementById('filterForm')));
        @else
            alert('ميزة التصدير غير مفعّلة حالياً.');
        @endif
    }

    // Export to PDF
    function exportToPDF() {
        @if($exportPdfUrl)
            window.location.href = '{{ $exportPdfUrl }}' +
                '?' + new URLSearchParams(new FormData(document.getElementById('filterForm')));
        @else
            alert('ميزة التصدير غير مفعّلة حالياً.');
        @endif
    }

    // Send Reminder
    function sendReminder(enrollmentId) {
        if (confirm('هل تريد إرسال تذكير لهذا الطالب؟')) {
            fetch(`/admin/enrollments/${enrollmentId}/send-reminder`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم إرسال التذكير بنجاح');
                } else {
                    alert('حدث خطأ أثناء الإرسال');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء الإرسال');
            });
        }
    }
</script>
@stop
