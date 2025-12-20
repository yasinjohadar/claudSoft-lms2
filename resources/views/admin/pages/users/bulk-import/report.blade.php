@extends('admin.layouts.master')

@section('page-title')
    تقرير رفع المستخدمين
@stop

@section('css')
    <style>
        .stats-card {
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            opacity: 0.3;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .stats-card .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .stats-number {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        .stats-label {
            font-size: 1rem;
            color: #6c757d;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .success-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 16px 16px 0 0;
            position: relative;
            overflow: hidden;
        }
        .success-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .success-header i {
            position: relative;
            z-index: 1;
            animation: checkmark 0.6s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .success-header h2 {
            position: relative;
            z-index: 1;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .success-header p {
            position: relative;
            z-index: 1;
            opacity: 0.95;
            font-size: 1.1rem;
        }
        .table-section {
            margin-top: 30px;
        }
        .badge-field {
            margin: 2px;
        }
        .download-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }
        .bg-primary-transparent {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
            border-left: 4px solid #0d6efd;
        }
        .bg-primary-transparent .stats-number {
            color: #0d6efd;
        }
        .bg-primary-transparent .stats-icon {
            color: #0d6efd;
        }
        .bg-warning-transparent {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
            border-left: 4px solid #ffc107;
        }
        .bg-warning-transparent .stats-number {
            color: #ffc107;
        }
        .bg-warning-transparent .stats-icon {
            color: #ffc107;
        }
        .bg-info-transparent {
            background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, rgba(13, 202, 240, 0.05) 100%);
            border-left: 4px solid #0dcaf0;
        }
        .bg-info-transparent .stats-number {
            color: #0dcaf0;
        }
        .bg-info-transparent .stats-icon {
            color: #0dcaf0;
        }
        .bg-danger-transparent {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
            border-left: 4px solid #dc3545;
        }
        .bg-danger-transparent .stats-number {
            color: #dc3545;
        }
        .bg-danger-transparent .stats-icon {
            color: #dc3545;
        }
        .bg-success-transparent {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
            border-left: 4px solid #198754;
        }
        .bg-success-transparent .stats-number {
            color: #198754;
        }
        .bg-success-transparent .stats-icon {
            color: #198754;
        }
        .bg-secondary-transparent {
            background: linear-gradient(135deg, rgba(108, 117, 125, 0.1) 0%, rgba(108, 117, 125, 0.05) 100%);
            border-left: 4px solid #6c757d;
        }
        .bg-secondary-transparent .stats-number {
            color: #6c757d;
        }
        .bg-secondary-transparent .stats-icon {
            color: #6c757d;
        }
        .progress {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .progress-bar {
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@stop

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">تقرير رفع المستخدمين</h1>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.bulk-import.index') }}">رفع جماعي</a></li>
                            <li class="breadcrumb-item active" aria-current="page">التقرير</li>
                        </ol>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle me-2"></i>
                        <strong>{{ session('success') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle me-2"></i>
                        <strong>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Success Summary -->
                <div class="card">
                    <div class="success-header text-center">
                        <i class="fa fa-check-circle fa-3x mb-3"></i>
                        <h2>تم الانتهاء من عملية الرفع!</h2>
                        <p class="mb-0">تم معالجة {{ $session->total_rows }} صف من الملف</p>
                    </div>
                    <div class="card-body">
                        <!-- Statistics Cards -->
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="stats-card bg-primary-transparent">
                                    <i class="fas fa-user-plus stats-icon"></i>
                                    <div class="stats-number">{{ $session->new_users }}</div>
                                    <div class="stats-label">طلاب جدد</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card bg-warning-transparent">
                                    <i class="fas fa-sync-alt stats-icon"></i>
                                    <div class="stats-number">{{ $session->updated_users }}</div>
                                    <div class="stats-label">تم تحديثهم</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card bg-info-transparent">
                                    <i class="fas fa-book stats-icon"></i>
                                    <div class="stats-number">{{ $session->enrollments_created }}</div>
                                    <div class="stats-label">تسجيلات في كورسات</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card bg-danger-transparent">
                                    <i class="fas fa-exclamation-triangle stats-icon"></i>
                                    <div class="stats-number">{{ $session->failed_rows }}</div>
                                    <div class="stats-label">أخطاء</div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Stats -->
                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="stats-card bg-success-transparent">
                                    <i class="fas fa-users stats-icon"></i>
                                    <div class="stats-number">{{ $session->group_members_added }}</div>
                                    <div class="stats-label">إضافات للمجموعات</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card bg-secondary-transparent">
                                    <i class="fas fa-forward stats-icon"></i>
                                    <div class="stats-number">{{ $session->skipped_rows }}</div>
                                    <div class="stats-label">صفوف متخطاة</div>
                                </div>
                            </div>
                        </div>

                        <!-- Success Rate -->
                        <div class="progress mt-4" style="height: 30px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $session->getSuccessRate() }}%"
                                 aria-valuenow="{{ $session->getSuccessRate() }}" aria-valuemin="0" aria-valuemax="100">
                                معدل النجاح: {{ $session->getSuccessRate() }}%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Users Table -->
                @if($session->new_users > 0)
                    <div class="card table-section">
                        <div class="card-header bg-primary-transparent">
                            <h3 class="card-title">
                                <i class="fa fa-user-plus me-2"></i>
                                الطلاب الجدد ({{ $session->new_users }})
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>الاسم</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>الكورس</th>
                                            <th>المجموعة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($session->new_users_details as $index => $user)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <i class="fa fa-user text-success me-1"></i>
                                                    {{ $user['name'] }}
                                                </td>
                                                <td>{{ $user['email'] }}</td>
                                                <td>
                                                    @if(!empty($user['course']))
                                                        <span class="badge bg-info">{{ $user['course'] }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($user['group']))
                                                        <span class="badge bg-secondary">{{ $user['group'] }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Updated Users Table -->
                @if($session->updated_users > 0)
                    <div class="card table-section">
                        <div class="card-header bg-warning-transparent">
                            <h3 class="card-title">
                                <i class="fa fa-sync-alt me-2"></i>
                                الطلاب الذين تم تحديثهم ({{ $session->updated_users }})
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-warning">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>الاسم</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>البيانات المحدثة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($session->updated_users_details as $index => $user)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <i class="fa fa-user-edit text-warning me-1"></i>
                                                    {{ $user['name'] }}
                                                </td>
                                                <td>{{ $user['email'] }}</td>
                                                <td>
                                                    @foreach($user['updated_fields'] as $field)
                                                        <span class="badge bg-info badge-field">{{ $field }}</span>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Errors Table -->
                @if($session->failed_rows > 0)
                    <div class="card table-section">
                        <div class="card-header bg-danger-transparent">
                            <h3 class="card-title">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                الأخطاء ({{ $session->failed_rows }})
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fa fa-info-circle me-2"></i>
                                توجد {{ $session->failed_rows }} صفوف فشل معالجتها. يمكنك تحميل ملف الأخطاء لمراجعتها وتصحيحها.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-danger">
                                        <tr>
                                            <th width="80">الصف</th>
                                            <th>البريد</th>
                                            <th>الخطأ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($session->errors as $error)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-danger">{{ $error['row'] }}</span>
                                                </td>
                                                <td>{{ $error['email'] }}</td>
                                                <td>
                                                    <i class="fa fa-times-circle text-danger me-1"></i>
                                                    {{ $error['message'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Download & Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="download-section">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5><i class="fa fa-download me-2"></i>خيارات التحميل والإجراءات</h5>
                                    <p class="text-muted mb-0">قم بتحميل التقارير أو الانتقال إلى قائمة المستخدمين</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    @if($session->failed_rows > 0)
                                        <a href="{{ route('users.bulk-import.errors', $session->id) }}"
                                           class="btn btn-danger mb-2">
                                            <i class="fa fa-file-excel me-2"></i>تحميل ملف الأخطاء
                                        </a>
                                    @endif
                                    <a href="{{ route('users.index') }}" class="btn btn-primary mb-2">
                                        <i class="fa fa-users me-2"></i>عرض المستخدمين
                                    </a>
                                    <a href="{{ route('users.bulk-import.index') }}" class="btn btn-success mb-2">
                                        <i class="fa fa-plus me-2"></i>رفع ملف جديد
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">معلومات الجلسة</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>رقم الجلسة:</strong> #{{ $session->id }}</p>
                                <p><strong>اسم الملف:</strong> {{ $session->file_name }}</p>
                                <p><strong>الحالة:</strong>
                                    @if($session->isCompleted())
                                        <span class="badge bg-success">مكتمل</span>
                                    @elseif($session->isFailed())
                                        <span class="badge bg-danger">فشل</span>
                                    @elseif($session->isProcessing())
                                        <span class="badge bg-warning">قيد المعالجة</span>
                                    @else
                                        <span class="badge bg-secondary">معلق</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>رُفع بواسطة:</strong> {{ $session->uploadedBy->name }}</p>
                                <p><strong>تاريخ البدء:</strong> {{ $session->started_at ? $session->started_at->format('Y-m-d H:i') : '-' }}</p>
                                <p><strong>تاريخ الانتهاء:</strong> {{ $session->completed_at ? $session->completed_at->format('Y-m-d H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop

@section('script')
    <script>
        // Print report
        function printReport() {
            window.print();
        }
    </script>
@stop
