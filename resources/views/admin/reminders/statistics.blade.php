@extends('admin.layouts.master')

@section('page-title')
    إحصائيات التذكيرات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إحصائيات التذكيرات</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.reminders.index') }}">التذكيرات</a></li>
                        <li class="breadcrumb-item active">الإحصائيات</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('admin.reminders.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-2"></i>العودة للقائمة
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card custom-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalReminders }}</h3>
                            <p class="mb-0">إجمالي التذكيرات</p>
                        </div>
                        <i class="ri-notification-line fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $sentReminders }}</h3>
                            <p class="mb-0">التذكيرات المرسلة</p>
                        </div>
                        <i class="ri-send-plane-fill fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $pendingReminders }}</h3>
                            <p class="mb-0">قيد الانتظار</p>
                        </div>
                        <i class="ri-time-line fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalRecipients }}</h3>
                            <p class="mb-0">إجمالي المستلمين</p>
                        </div>
                        <i class="ri-group-line fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="row">
            <!-- By Type Chart -->
            <div class="col-md-6 mb-4">
                <div class="card custom-card">
                <div class="card-header">
                    <h5 class="mb-0">التذكيرات حسب النوع</h5>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="250"></canvas>
                </div>
                </div>
            </div>

            <!-- By Priority Chart -->
            <div class="col-md-6 mb-4">
                <div class="card custom-card">
                <div class="card-header">
                    <h5 class="mb-0">التذكيرات حسب الأولوية</h5>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" height="250"></canvas>
                </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-12">
                <div class="card custom-card">
                <div class="card-header">
                    <h5 class="mb-0">آخر التذكيرات المرسلة</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>العنوان</th>
                                    <th>النوع</th>
                                    <th>المستلمون</th>
                                    <th>تاريخ الإرسال</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentReminders as $reminder)
                                    @php
                                        $types = \App\Models\GroupReminder::getReminderTypes();
                                        $typeInfo = $types[$reminder->reminder_type] ?? $types['announcement'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.reminders.show', $reminder) }}">
                                                {{ $reminder->title }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $typeInfo['color'] }}">
                                                {{ $typeInfo['icon'] }} {{ $typeInfo['name'] }}
                                            </span>
                                        </td>
                                        <td>{{ $reminder->recipients_count }}</td>
                                        <td>{{ $reminder->sent_at ? $reminder->sent_at->diffForHumans() : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Type Chart
const typeCtx = document.getElementById('typeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: @json(array_column($byType, 'name')),
        datasets: [{
            data: @json(array_column($byType, 'count')),
            backgroundColor: [
                '#3b82f6',
                '#ef4444',
                '#06b6d4',
                '#f59e0b',
                '#10b981',
                '#6b7280'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Priority Chart
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
new Chart(priorityCtx, {
    type: 'bar',
    data: {
        labels: @json(array_column($byPriority, 'name')),
        datasets: [{
            label: 'عدد التذكيرات',
            data: @json(array_column($byPriority, 'count')),
            backgroundColor: [
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#1f2937'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endsection
