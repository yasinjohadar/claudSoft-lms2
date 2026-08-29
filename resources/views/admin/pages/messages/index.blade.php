@extends('admin.layouts.master')

@section('page-title')
    الرسائل
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الرسائل</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-mail me-1"></i>
                            التواصل مع الطلاب
                        </span>
                        <h2 class="group-show-hero__title mb-2">الرسائل</h2>
                        <p class="group-show-hero__desc mb-0">
                            أرشيف موحّد لكل الرسائل التي أرسلتها لمجموعات الطلاب من مكان واحد، بدل فتح كل مجموعة على حدة.
                        </p>
                    </div>
                </div>
            </div>

            <div class="row g-3 dashboard-fade-in mb-4">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card admin-stats-card admin-stats-card--blue">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe fe-mail admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">إجمالي الرسائل</p>
                                <h3 class="admin-stats-card__value mb-1" data-countup="{{ $totalMessages }}">0</h3>
                                <p class="admin-stats-card__sub mb-0">مرسَلة لجميع المجموعات</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card admin-stats-card admin-stats-card--cyan">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe fe-users admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">إجمالي المستلمين</p>
                                <h3 class="admin-stats-card__value mb-1" data-countup="{{ $totalRecipients }}">0</h3>
                                <p class="admin-stats-card__sub mb-0">عبر كل الرسائل</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card admin-stats-card admin-stats-card--green">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe fe-check-circle admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">نسبة القراءة العامة</p>
                                <h3 class="admin-stats-card__value mb-1" data-countup="{{ $readRate }}" data-countup-suffix="%" data-countup-decimals="1">0</h3>
                                <p class="admin-stats-card__sub mb-0">من كل النسخ المُرسَلة</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">إرسال رسالة جديدة</h4>
                    <p class="fs-12 text-muted mb-0">اختر مجموعة لفتح نموذج تأليف الرسالة الخاص بها.</p>
                </div>
                <div class="card-body pt-3">
                    <form id="goToGroupForm" class="row g-3 align-items-end" onsubmit="return false;">
                        <div class="col-md-8">
                            <label class="form-label" for="messageGroupSelect">المجموعة</label>
                            <select id="messageGroupSelect" class="form-select">
                                <option value="">اختر مجموعة...</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100" id="goToGroupBtn" disabled>
                                <i class="fe fe-edit-3 me-1"></i>فتح نموذج الإرسال
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">كل الرسائل المُرسَلة</h4>
                    <p class="fs-12 text-muted mb-0">{{ $notifications->total() }} رسالة.</p>
                </div>
                <div class="card-body pt-3">
                    @if($notifications->isEmpty())
                        <div class="group-show-empty">
                            <div class="group-show-empty__icon">
                                <i class="fe fe-mail"></i>
                            </div>
                            <h4 class="group-show-empty__title">لا توجد رسائل مُرسَلة بعد</h4>
                            <p class="text-muted mb-0">استخدم النموذج أعلاه للبدء بإرسال أول رسالة.</p>
                        </div>
                    @else
                        @php
                            $typeStyles = [
                                'success' => ['badge' => 'bg-success-transparent text-success', 'label' => 'نجاح'],
                                'info' => ['badge' => 'bg-info-transparent text-info', 'label' => 'معلومة'],
                                'warning' => ['badge' => 'bg-warning-transparent text-warning', 'label' => 'تنبيه'],
                                'error' => ['badge' => 'bg-danger-transparent text-danger', 'label' => 'خطأ'],
                            ];
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap dashboard-table mb-0">
                                <thead>
                                    <tr>
                                        <th>المجموعة</th>
                                        <th>العنوان</th>
                                        <th>النوع</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>القراءة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                        @php $style = $typeStyles[$notification->type] ?? $typeStyles['info']; @endphp
                                        <tr>
                                            <td>
                                                @if($notification->group)
                                                    <a href="{{ route('groups.notifications', $notification->group_id) }}" class="text-primary text-decoration-none">
                                                        {{ $notification->group->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $notification->title }}</div>
                                                <small class="d-block text-muted text-truncate" style="max-width: 280px;">
                                                    {{ Str::limit($notification->message, 60) }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $style['badge'] }}">{{ $style['label'] }}</span>
                                            </td>
                                            <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-secondary js-view-recipients"
                                                        data-group-id="{{ $notification->group_id }}"
                                                        data-notification-id="{{ $notification->id }}">
                                                    <i class="fe fe-eye me-1"></i>
                                                    قرأه {{ $notification->read_count }} من {{ $notification->recipients_count }}
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger-light js-delete-notification"
                                                        data-group-id="{{ $notification->group_id }}"
                                                        data-notification-id="{{ $notification->id }}" title="حذف">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex justify-content-center">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="notificationRecipientsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">من قرأ هذه الرسالة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="notificationRecipientsBody">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteNotificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                        <i class="fe fe-trash-2 text-danger fs-24"></i>
                    </div>
                    <h5 class="mb-3">تأكيد حذف الرسالة</h5>
                    <p class="text-danger small mb-4">
                        <i class="fe fe-info me-1"></i>
                        ستُحذف من صندوق كل الطلاب المستلمين، ولا يمكن التراجع عن هذا الإجراء.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            <i class="fe fe-x me-1"></i>إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirm-delete-notification-btn">
                            <i class="fe fe-trash-2 me-1"></i>نعم، احذف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const groupsBaseUrl = @json(url('/admin/groups'));
    let pendingDelete = null;

    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const map = {
            success: { class: 'alert-success', icon: 'fe-check-circle' },
            error: { class: 'alert-danger', icon: 'fe-alert-circle' },
        };
        const cfg = map[type] || map.error;

        alertContainer.innerHTML = `
            <div class="alert ${cfg.class} alert-dismissible fade show shadow-sm" role="alert">
                <i class="fe ${cfg.icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        setTimeout(function () {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(function () { alertContainer.innerHTML = ''; }, 300);
            }
        }, 4500);
    }

    const groupSelect = document.getElementById('messageGroupSelect');
    const goToGroupBtn = document.getElementById('goToGroupBtn');
    groupSelect?.addEventListener('change', function () {
        goToGroupBtn.disabled = !groupSelect.value;
    });
    document.getElementById('goToGroupForm')?.addEventListener('submit', function () {
        if (!groupSelect.value) return;
        window.location.href = groupsBaseUrl + '/' + groupSelect.value + '/notifications';
    });

    document.addEventListener('click', function (e) {
        const viewBtn = e.target.closest('.js-view-recipients');
        if (viewBtn) {
            const groupId = viewBtn.getAttribute('data-group-id');
            const notificationId = viewBtn.getAttribute('data-notification-id');
            const modalEl = document.getElementById('notificationRecipientsModal');
            const body = document.getElementById('notificationRecipientsBody');
            body.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();

            fetch(groupsBaseUrl + '/' + groupId + '/notifications/' + notificationId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) throw new Error(data.message || 'تعذر التحميل');
                    if (!data.recipients.length) {
                        body.innerHTML = '<p class="text-muted text-center mb-0">لا يوجد مستلمون.</p>';
                        return;
                    }
                    const rows = data.recipients.map(function (r) {
                        const badge = r.is_read
                            ? '<span class="badge bg-success-transparent text-success">قرأ' + (r.read_at ? ' — ' + r.read_at : '') + '</span>'
                            : '<span class="badge bg-secondary-transparent text-secondary">لم يقرأ بعد</span>';
                        return '<li class="d-flex justify-content-between align-items-center py-2 border-bottom">' +
                            '<span>' + r.student_name + '</span>' + badge + '</li>';
                    }).join('');
                    body.innerHTML = '<ul class="list-unstyled mb-0">' + rows + '</ul>';
                })
                .catch(function (err) {
                    body.innerHTML = '<p class="text-danger text-center mb-0">' + (err.message || 'تعذر التحميل') + '</p>';
                });
            return;
        }

        const deleteBtn = e.target.closest('.js-delete-notification');
        if (deleteBtn) {
            pendingDelete = {
                groupId: deleteBtn.getAttribute('data-group-id'),
                notificationId: deleteBtn.getAttribute('data-notification-id'),
                row: deleteBtn.closest('tr'),
            };
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteNotificationModal')).show();
        }
    });

    document.getElementById('confirm-delete-notification-btn').addEventListener('click', function () {
        if (!pendingDelete) return;
        const { groupId, notificationId, row } = pendingDelete;
        pendingDelete = null;
        bootstrap.Modal.getInstance(document.getElementById('deleteNotificationModal'))?.hide();

        fetch(groupsBaseUrl + '/' + groupId + '/notifications/' + notificationId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'فشل الحذف');
                showAlert('success', data.message);
                row?.remove();
            })
            .catch(function (err) {
                showAlert('error', err.message || 'حدث خطأ أثناء حذف الرسالة');
            });
    });
})();
</script>
@stop
