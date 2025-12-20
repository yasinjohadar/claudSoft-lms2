@extends('admin.layouts.master')

@section('page-title')
    تقارير الرفع الجماعي
@stop

@section('css')
    <style>
        .stats-card {
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }
        .stats-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .table-actions {
            white-space: nowrap;
        }
        .filter-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
    </style>
@stop

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">تقارير الرفع الجماعي</h1>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.bulk-import.index') }}">رفع جماعي</a></li>
                            <li class="breadcrumb-item active" aria-current="page">التقارير</li>
                        </ol>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stats-card bg-primary-transparent">
                            <i class="fas fa-file-upload stats-icon text-primary"></i>
                            <div class="stats-number text-primary">{{ $stats['total'] }}</div>
                            <div class="stats-label">إجمالي التقارير</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-success-transparent">
                            <i class="fas fa-check-circle stats-icon text-success"></i>
                            <div class="stats-number text-success">{{ $stats['completed'] }}</div>
                            <div class="stats-label">مكتملة</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-danger-transparent">
                            <i class="fas fa-times-circle stats-icon text-danger"></i>
                            <div class="stats-number text-danger">{{ $stats['failed'] }}</div>
                            <div class="stats-label">فاشلة</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-warning-transparent">
                            <i class="fas fa-spinner stats-icon text-warning"></i>
                            <div class="stats-number text-warning">{{ $stats['processing'] + $stats['pending'] }}</div>
                            <div class="stats-label">قيد المعالجة</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card filter-card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('users.bulk-import.reports') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control" 
                                       value="{{ request('search') }}" 
                                       placeholder="اسم الملف أو المستخدم">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق</option>
                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فاشل</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="date_from" class="form-control" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="date_to" class="form-control" 
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('users.bulk-import.reports') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reports Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list me-2"></i>
                            قائمة التقارير ({{ $sessions->total() }})
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th width="80">#</th>
                                        <th>اسم الملف</th>
                                        <th>رُفع بواسطة</th>
                                        <th width="120">إجمالي الصفوف</th>
                                        <th width="100">طلاب جدد</th>
                                        <th width="100">محدثون</th>
                                        <th width="100">أخطاء</th>
                                        <th width="100">معدل النجاح</th>
                                        <th width="120">الحالة</th>
                                        <th width="150">التاريخ</th>
                                        <th width="150" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sessions as $session)
                                        <tr>
                                            <td>{{ $session->id }}</td>
                                            <td>
                                                <i class="fas fa-file-excel text-success me-1"></i>
                                                <strong>{{ $session->file_name }}</strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-user text-info me-1"></i>
                                                {{ $session->uploadedBy->name ?? 'غير معروف' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $session->total_rows }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $session->new_users }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">{{ $session->updated_users }}</span>
                                            </td>
                                            <td>
                                                @if($session->failed_rows > 0)
                                                    <span class="badge bg-danger">{{ $session->failed_rows }}</span>
                                                @else
                                                    <span class="badge bg-success">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: {{ $session->getSuccessRate() }}%"
                                                         role="progressbar">
                                                        {{ $session->getSuccessRate() }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($session->isCompleted())
                                                    <span class="status-badge bg-success text-white">مكتمل</span>
                                                @elseif($session->isFailed())
                                                    <span class="status-badge bg-danger text-white">فاشل</span>
                                                @elseif($session->isProcessing())
                                                    <span class="status-badge bg-warning text-white">قيد المعالجة</span>
                                                @else
                                                    <span class="status-badge bg-secondary text-white">معلق</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $session->created_at->format('Y-m-d') }}<br>
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $session->created_at->format('H:i') }}
                                                </small>
                                            </td>
                                            <td class="table-actions text-center">
                                                <a href="{{ route('users.bulk-import.report', $session->id) }}" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="عرض التقرير">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($session->failed_rows > 0)
                                                    <a href="{{ route('users.bulk-import.errors', $session->id) }}" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="تحميل ملف الأخطاء">
                                                        <i class="fas fa-file-excel"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                                <p class="text-muted">لا توجد تقارير</p>
                                                <a href="{{ route('users.bulk-import.index') }}" class="btn btn-primary">
                                                    <i class="fas fa-upload me-1"></i>رفع ملف جديد
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($sessions->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $sessions->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop

@section('script')
@stop


