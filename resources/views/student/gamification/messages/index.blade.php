@extends('student.layouts.master')

@section('title', 'الرسائل')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">الرسائل</h4>
            <p class="fw-normal text-muted fs-14 mb-0">الرسائل الموجَّهة إليك من إدارة المنصة</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card student-gamification-notifications-page">
                <div class="card-header flex-wrap gap-3 d-flex justify-content-between align-items-center border-bottom">
                    <div class="card-title mb-0 d-flex align-items-center flex-wrap gap-2">
                        <span>الرسائل</span>
                        <span class="badge bg-primary-transparent text-primary" id="total-count">{{ $messages->total() }}</span>
                    </div>
                </div>
                <div class="card-body student-gamification-notif-list-wrap p-3 p-md-4">
                    @if($messages->count() > 0)
                        <div class="vstack gap-3" id="notifications-list">
                            @foreach($messages as $message)
                                <div class="student-gamification-notif-card card border shadow-sm {{ !$message->is_read ? 'is-unread' : '' }}"
                                     data-id="{{ $message->id }}"
                                     data-action-url="{{ e($message->action_url ?? '') }}"
                                     role="button"
                                     tabindex="0"
                                     onclick="viewNotificationFromCard(this)"
                                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();viewNotificationFromCard(this);}">
                                    <div class="card-body d-flex flex-column flex-sm-row align-items-stretch align-items-sm-start gap-3 py-3 px-3 px-sm-4">
                                        <div class="student-gamification-notif-icon flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 border">
                                            <span class="fs-22 d-inline-flex align-items-center justify-content-center student-gamification-notif-icon-inner">{!! $message->icon_html !!}</span>
                                        </div>
                                        <div class="flex-grow-1 min-w-0 text-start">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <h6 class="mb-0 fw-semibold student-gamification-notif-title">{{ $message->title }}</h6>
                                                @if(!$message->is_read)
                                                    <span class="badge bg-primary rounded-pill notif-new-badge" style="font-size: 10px;">جديد</span>
                                                @endif
                                            </div>
                                            <p class="mb-2 text-muted fs-13 lh-base student-gamification-notif-message">{{ $message->message }}</p>
                                            <div class="d-flex align-items-center text-muted fs-12">
                                                <i class="ri-time-line me-1 flex-shrink-0"></i>
                                                <span>{{ $message->time_ago }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 d-flex align-items-start justify-content-sm-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger student-gamification-notif-delete"
                                                    onclick="event.stopPropagation(); deleteNotification({{ $message->id }})"
                                                    title="حذف"
                                                    aria-label="حذف الرسالة">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 pt-4 mt-1 border-top student-gamification-notif-pagination-row">
                            <div class="text-muted fs-13 text-center text-sm-start mb-0">
                                عرض {{ $messages->firstItem() }} إلى {{ $messages->lastItem() }} من أصل {{ $messages->total() }}
                            </div>
                            <div class="student-gamification-notif-pagination">
                                {{ $messages->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 px-3 student-gamification-notif-empty">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 student-gamification-notif-empty-icon">
                                <i class="ri-mail-line fs-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">لا توجد رسائل</h5>
                            <p class="text-muted mb-0 fs-14">ليس لديك أي رسائل حالياً</p>
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
    if (!confirm('هل تريد حذف هذه الرسالة؟')) {
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

            const list = document.getElementById('notifications-list');
            if (list && list.children.length === 0) {
                window.location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ أثناء حذف الرسالة');
    });
}
</script>
@endpush
@endsection
