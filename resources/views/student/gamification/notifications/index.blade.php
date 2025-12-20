@extends('student.layouts.master')

@section('title', 'الإشعارات')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">الإشعارات</h4>
            <p class="fw-normal text-muted fs-14 mb-0">جميع الإشعارات والتحديثات</p>
        </div>
        <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-primary btn-wave" onclick="markAllAsRead()">
                <i class="ri-check-double-line me-1"></i> تحديد الكل كمقروء
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        الإشعارات
                        <span class="badge bg-primary-transparent ms-2" id="total-count">{{ $notifications->total() }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <!-- Filter Buttons -->
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="filter" id="filter-all" value="all" {{ !request('filter') || request('filter') == 'all' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm" for="filter-all" onclick="filterNotifications('all')">الكل</label>

                            <input type="radio" class="btn-check" name="filter" id="filter-unread" value="unread" {{ request('filter') == 'unread' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm" for="filter-unread" onclick="filterNotifications('unread')">غير المقروءة</label>
                        </div>

                        <!-- Type Filter -->
                        <select class="form-select form-select-sm" style="width: 200px;" onchange="filterByType(this.value)">
                            <option value="">جميع الأنواع</option>
                            <option value="certificate_issued" {{ request('type') == 'certificate_issued' ? 'selected' : '' }}>الشهادات</option>
                            <option value="course_completed" {{ request('type') == 'course_completed' ? 'selected' : '' }}>الكورسات</option>
                            <option value="quiz_passed" {{ request('type') == 'quiz_passed' ? 'selected' : '' }}>الاختبارات</option>
                            <option value="badge_earned" {{ request('type') == 'badge_earned' ? 'selected' : '' }}>الشارات</option>
                            <option value="achievement_unlocked" {{ request('type') == 'achievement_unlocked' ? 'selected' : '' }}>الإنجازات</option>
                            <option value="level_up" {{ request('type') == 'level_up' ? 'selected' : '' }}>المستويات</option>
                            <option value="invoice_created" {{ request('type') == 'invoice_created' ? 'selected' : '' }}>الفواتير</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table text-nowrap table-hover">
                                <tbody id="notifications-tbody">
                                    @foreach($notifications as $notification)
                                        <tr class="notification-item {{ !$notification->is_read ? 'table-primary' : '' }}"
                                            data-id="{{ $notification->id }}"
                                            style="cursor: pointer;"
                                            onclick="viewNotification({{ $notification->id }}, '{{ $notification->action_url ?? '#' }}')">
                                            <td style="width: 50px;">
                                                <div class="fs-20">
                                                    {!! $notification->icon_html !!}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 fw-semibold">
                                                            {{ $notification->title }}
                                                            @if(!$notification->is_read)
                                                                <span class="badge bg-primary rounded-pill ms-2" style="font-size: 10px;">جديد</span>
                                                            @endif
                                                        </h6>
                                                        <p class="mb-0 text-muted fs-13">{{ $notification->message }}</p>
                                                        <small class="text-muted">
                                                            <i class="ri-time-line me-1"></i>{{ $notification->time_ago }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="width: 100px;" class="text-end">
                                                <button type="button" class="btn btn-sm btn-icon btn-danger-light"
                                                        onclick="event.stopPropagation(); deleteNotification({{ $notification->id }})"
                                                        title="حذف">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    عرض {{ $notifications->firstItem() }} إلى {{ $notifications->lastItem() }} من أصل {{ $notifications->total() }}
                                </div>
                                <div>
                                    {{ $notifications->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="ri-notification-off-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد إشعارات</h5>
                            <p class="text-muted">ليس لديك أي إشعارات حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<!-- End::app-content -->

@push('scripts')
<script>
function filterNotifications(filter) {
    const url = new URL(window.location.href);
    if (filter === 'all') {
        url.searchParams.delete('filter');
    } else {
        url.searchParams.set('filter', filter);
    }
    window.location.href = url.toString();
}

function filterByType(type) {
    const url = new URL(window.location.href);
    if (type === '') {
        url.searchParams.delete('type');
    } else {
        url.searchParams.set('type', type);
    }
    window.location.href = url.toString();
}

function viewNotification(notificationId, actionUrl) {
    // Mark as read
    fetch(`/student/gamification/notifications/${notificationId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const row = document.querySelector(`tr[data-id="${notificationId}"]`);
            if (row) {
                row.classList.remove('table-primary');
                const badge = row.querySelector('.badge.bg-primary');
                if (badge) badge.remove();
            }

            // Redirect if there's an action URL
            if (actionUrl && actionUrl !== '#') {
                window.location.href = actionUrl;
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (!confirm('هل تريد حذف هذا الإشعار؟')) {
        return;
    }

    fetch(`/student/gamification/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove row from table
            const row = document.querySelector(`tr[data-id="${notificationId}"]`);
            if (row) {
                row.remove();
            }

            // Show success message
            alert(data.message);

            // Reload if no more notifications
            const tbody = document.getElementById('notifications-tbody');
            if (tbody.children.length === 0) {
                window.location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء حذف الإشعار');
    });
}

function markAllAsRead() {
    if (!confirm('هل تريد تحديد جميع الإشعارات كمقروءة؟')) {
        return;
    }

    fetch('/student/gamification/notifications/mark-all-as-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove all unread styling
            document.querySelectorAll('.table-primary').forEach(row => {
                row.classList.remove('table-primary');
            });
            document.querySelectorAll('.badge.bg-primary').forEach(badge => {
                if (badge.textContent === 'جديد') {
                    badge.remove();
                }
            });

            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء تحديث الإشعارات');
    });
}
</script>
@endpush

@push('styles')
<style>
.notification-item:hover {
    background-color: #f8f9fa;
}
.table-primary {
    background-color: #e7f1ff !important;
}
</style>
@endpush
@endsection
