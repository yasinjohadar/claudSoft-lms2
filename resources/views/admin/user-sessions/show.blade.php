@extends('admin.layouts.master')

@section('page-title')
    تفاصيل الجلسة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تفاصيل الجلسة</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.user-sessions.index') }}">جلسات المستخدمين</a></li>
                        <li class="breadcrumb-item active">تفاصيل الجلسة</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.user-sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>
        </div>

        <!-- Session Info Card -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>معلومات الجلسة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المستخدم:</strong>
                                <div class="d-flex align-items-center mt-2">
                                    @if($session->user)
                                        @if($session->user->avatar)
                                            <img src="{{ asset('storage/' . $session->user->avatar) }}" 
                                                 alt="{{ $session->user->name }}" 
                                                 class="avatar avatar-md rounded-circle me-2">
                                        @else
                                            <div class="avatar avatar-md rounded-circle bg-primary-transparent me-2">
                                                <span class="fw-bold">{{ substr($session->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $session->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $session->user->email }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <strong>معرف الجلسة:</strong>
                                <p class="mb-0 mt-2">
                                    <code>{{ $session->session_uuid ?? $session->id }}</code>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>تاريخ البدء:</strong>
                                <p class="mb-0 mt-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $session->started_at->format('Y-m-d H:i:s') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>تاريخ الانتهاء:</strong>
                                <p class="mb-0 mt-2">
                                    @if($session->ended_at)
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $session->ended_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">لا يزال نشطاً</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>المدة:</strong>
                                <p class="mb-0 mt-2">
                                    <span class="badge bg-info-transparent text-info fs-14">
                                        {{ $session->duration_formatted }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>الحالة:</strong>
                                <p class="mb-0 mt-2">
                                    @if($session->status == 'active')
                                        <span class="badge bg-success">نشطة</span>
                                    @elseif($session->status == 'completed')
                                        <span class="badge bg-info">مكتملة</span>
                                    @elseif($session->status == 'disconnected')
                                        <span class="badge bg-warning">منفصلة</span>
                                    @else
                                        <span class="badge bg-secondary">انتهت</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($session->session_name || $session->session_description)
                            <div class="row mb-3">
                                <div class="col-12">
                                    @if($session->session_name)
                                        <strong>اسم الجلسة:</strong>
                                        <p class="mb-2">{{ $session->session_name }}</p>
                                    @endif
                                    @if($session->session_description)
                                        <strong>الوصف:</strong>
                                        <p class="mb-0">{{ $session->session_description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Device & Connection Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-laptop me-2"></i>معلومات الجهاز والاتصال
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>عنوان IP:</strong>
                                <p class="mb-0 mt-1"><code>{{ $session->ip_address ?? '-' }}</code></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>نوع الجهاز:</strong>
                                <p class="mb-0 mt-1">{{ ucfirst($session->device_type ?? '-') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>المتصفح:</strong>
                                <p class="mb-0 mt-1">
                                    {{ $session->browser ?? '-' }}
                                    @if($session->browser_version)
                                        <span class="text-muted">({{ $session->browser_version }})</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>المنصة:</strong>
                                <p class="mb-0 mt-1">
                                    {{ $session->platform ?? '-' }}
                                    @if($session->platform_version)
                                        <span class="text-muted">({{ $session->platform_version }})</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>دقة الشاشة:</strong>
                                <p class="mb-0 mt-1">{{ $session->screen_resolution ?? '-' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>نوع الاتصال:</strong>
                                <p class="mb-0 mt-1">
                                    @if($session->connection_type)
                                        <span class="badge bg-primary-transparent text-primary">
                                            {{ ucfirst($session->connection_type) }}
                                        </span>
                                        @if($session->bandwidth_mbps)
                                            <span class="text-muted">({{ $session->bandwidth_mbps }} Mbps)</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            @if($session->user_agent)
                                <div class="col-12">
                                    <strong>User Agent:</strong>
                                    <p class="mb-0 mt-1">
                                        <small class="text-muted">{{ $session->user_agent }}</small>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>إحصائيات الأنشطة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>إجمالي الأنشطة:</strong>
                            <h4 class="mb-0">{{ $activityStats['total'] }}</h4>
                        </div>
                        <div class="mb-3">
                            <strong>مشاهدات الصفحات:</strong>
                            <h4 class="mb-0">{{ $activityStats['page_views'] }}</h4>
                        </div>
                        <div class="mb-3">
                            <strong>الصفحات الفريدة:</strong>
                            <h4 class="mb-0">{{ $activityStats['unique_pages'] }}</h4>
                        </div>
                        <hr>
                        <strong>توزيع الأنشطة:</strong>
                        <div class="mt-2">
                            @foreach($activityStats['by_type'] as $type => $count)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>{{ $type }}</span>
                                    <span class="badge bg-primary-transparent text-primary">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities Timeline -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>الأنشطة
                </h5>
            </div>
            <div class="card-body">
                @if($session->activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الوقت</th>
                                    <th>نوع النشاط</th>
                                    <th>الصفحة/الرابط</th>
                                    <th>التفاصيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($session->activities as $activity)
                                    <tr>
                                        <td>
                                            <small>{{ $activity->occurred_at->format('Y-m-d H:i:s') }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $badgeColors = [
                                                    'session_start' => 'success',
                                                    'session_end' => 'danger',
                                                    'page_view' => 'info',
                                                    'action' => 'primary',
                                                    'disconnect' => 'warning',
                                                    'reconnect' => 'success',
                                                    'idle_start' => 'secondary',
                                                    'idle_end' => 'info',
                                                    'focus_lost' => 'warning',
                                                    'focus_gained' => 'success',
                                                ];
                                                $color = $badgeColors[$activity->activity_type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}-transparent text-{{ $color }}">
                                                {{ $activity->activity_type }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($activity->page_url)
                                                <a href="{{ $activity->page_url }}" target="_blank" class="text-primary">
                                                    {{ Str::limit($activity->page_url, 50) }}
                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->activity_details && is_array($activity->activity_details))
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#activityDetailsModal{{ $activity->id }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Activity Details Modal -->
                                    @if($activity->activity_details && is_array($activity->activity_details))
                                        <div class="modal fade" id="activityDetailsModal{{ $activity->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تفاصيل النشاط</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <pre class="bg-light p-3 rounded">{{ json_encode($activity->activity_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد أنشطة مسجلة</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
