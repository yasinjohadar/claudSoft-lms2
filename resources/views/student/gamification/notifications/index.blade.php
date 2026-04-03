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
            <div class="card custom-card student-gamification-notifications-page">
                <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center border-bottom">
                    <div class="card-title mb-0 d-flex align-items-center flex-wrap gap-2">
                        <span>الإشعارات</span>
                        <span class="badge bg-primary-transparent text-primary" id="total-count">{{ $notifications->total() }}</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="filter" id="filter-all" value="all" {{ !request('filter') || request('filter') == 'all' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm" for="filter-all" onclick="filterNotifications('all')">الكل</label>

                            <input type="radio" class="btn-check" name="filter" id="filter-unread" value="unread" {{ request('filter') == 'unread' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm" for="filter-unread" onclick="filterNotifications('unread')">غير المقروءة</label>
                        </div>

                        <select class="form-select form-select-sm student-gamification-notif-type-select" aria-label="تصفية حسب النوع" onchange="filterByType(this.value)">
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
                <div class="card-body student-gamification-notif-list-wrap p-3 p-md-4">
                    @if($notifications->count() > 0)
                        <div class="vstack gap-3" id="notifications-list">
                            @foreach($notifications as $notification)
                                <div class="student-gamification-notif-card card border shadow-sm {{ !$notification->is_read ? 'is-unread' : '' }}"
                                     data-id="{{ $notification->id }}"
                                     data-action-url="{{ e($notification->action_url ?? '') }}"
                                     role="button"
                                     tabindex="0"
                                     onclick="viewNotificationFromCard(this)"
                                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();viewNotificationFromCard(this);}">
                                    <div class="card-body d-flex flex-column flex-sm-row align-items-stretch align-items-sm-start gap-3 py-3 px-3 px-sm-4">
                                        <div class="student-gamification-notif-icon flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 border">
                                            <span class="fs-22 d-inline-flex align-items-center justify-content-center student-gamification-notif-icon-inner">{!! $notification->icon_html !!}</span>
                                        </div>
                                        <div class="flex-grow-1 min-w-0 text-start">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <h6 class="mb-0 fw-semibold student-gamification-notif-title">{{ $notification->title }}</h6>
                                                @if(!$notification->is_read)
                                                    <span class="badge bg-primary rounded-pill notif-new-badge" style="font-size: 10px;">جديد</span>
                                                @endif
                                            </div>
                                            <p class="mb-2 text-muted fs-13 lh-base student-gamification-notif-message">{{ $notification->message }}</p>
                                            <div class="d-flex align-items-center text-muted fs-12">
                                                <i class="ri-time-line me-1 flex-shrink-0"></i>
                                                <span>{{ $notification->time_ago }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 d-flex align-items-start justify-content-sm-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger student-gamification-notif-delete"
                                                    onclick="event.stopPropagation(); deleteNotification({{ $notification->id }})"
                                                    title="حذف"
                                                    aria-label="حذف الإشعار">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 pt-4 mt-1 border-top student-gamification-notif-pagination-row">
                            <div class="text-muted fs-13 text-center text-sm-start mb-0">
                                عرض {{ $notifications->firstItem() }} إلى {{ $notifications->lastItem() }} من أصل {{ $notifications->total() }}
                            </div>
                            <div class="student-gamification-notif-pagination">
                                {{ $notifications->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 px-3 student-gamification-notif-empty">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 student-gamification-notif-empty-icon">
                                <i class="ri-notification-off-line fs-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">لا توجد إشعارات</h5>
                            <p class="text-muted mb-0 fs-14">ليس لديك أي إشعارات حالياً</p>
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

function notifCardSelector(notificationId) {
    return `.student-gamification-notif-card[data-id="${notificationId}"]`;
}

function viewNotificationFromCard(el) {
    const id = parseInt(el.getAttribute('data-id'), 10);
    const raw = el.getAttribute('data-action-url') || '';
    const actionUrl = raw.trim() === '' ? '#' : raw;
    viewNotification(id, actionUrl);
}

function viewNotification(notificationId, actionUrl) {
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
            const card = document.querySelector(notifCardSelector(notificationId));
            if (card) {
                card.classList.remove('is-unread');
                const badge = card.querySelector('.notif-new-badge');
                if (badge) badge.remove();
            }

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
            const card = document.querySelector(notifCardSelector(notificationId));
            if (card) {
                card.remove();
            }

            alert(data.message);

            const list = document.getElementById('notifications-list');
            if (list && list.children.length === 0) {
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
            document.querySelectorAll('.student-gamification-notif-card.is-unread').forEach(card => {
                card.classList.remove('is-unread');
            });
            document.querySelectorAll('.notif-new-badge').forEach(badge => badge.remove());

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
@endsection
