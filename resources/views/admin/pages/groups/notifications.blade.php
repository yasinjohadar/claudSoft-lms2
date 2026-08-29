@extends('admin.layouts.master')

@section('page-title')
    إشعارات مجموعة: {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('groups.all') }}">المجموعات</a></li>
                        <li class="breadcrumb-item active">إشعارات: {{ $group->name }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-bell me-1"></i>
                            إشعارات المجموعة
                        </span>
                        <h2 class="group-show-hero__title mb-2">إشعارات مجموعة: {{ $group->name }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            أرسل إشعاراً مخصصاً لكل أعضاء هذه المجموعة، وتابع من قرأه لاحقاً من القائمة أسفل الصفحة.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            @php $firstCourse = $group->courses->first(); @endphp
                            <a href="{{ $firstCourse ? route('courses.groups.show', [$firstCourse->id, $group->id]) : route('groups.show', $group->id) }}"
                               class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للمجموعة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">إشعار جديد</h4>
                    <p class="fs-12 text-muted mb-0">يصل لكل أعضاء المجموعة الحاليين ({{ $group->students()->count() }} عضواً).</p>
                </div>
                <div class="card-body pt-3">
                    <form id="group-notification-form">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="notif-title">العنوان</label>
                                <input type="text" id="notif-title" name="title" class="form-control" maxlength="255" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="notif-type">النوع</label>
                                <select id="notif-type" name="type" class="form-select">
                                    <option value="info">معلومة (أزرق)</option>
                                    <option value="success">نجاح (أخضر)</option>
                                    <option value="warning">تنبيه (برتقالي)</option>
                                    <option value="error">خطأ (أحمر)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notif-message">نص الرسالة</label>
                                <textarea id="notif-message" name="message" class="form-control" rows="3" maxlength="2000" required></textarea>
                                <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                    <small class="text-muted">إدراج متغيّر:</small>
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-insert-var" data-var="@{{student_name}}">اسم الطالب</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-insert-var" data-var="@{{group_name}}">اسم المجموعة</button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="notif-action-url">رابط عند الضغط (اختياري)</label>
                                <input type="url" id="notif-action-url" name="action_url" class="form-control" maxlength="500" placeholder="https://...">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" id="group-notification-submit" class="btn btn-primary w-100">
                                    <i class="fe fe-send me-1"></i>إرسال للمجموعة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">الإشعارات المُرسَلة سابقاً</h4>
                    <p class="fs-12 text-muted mb-0">{{ $notifications->total() }} إشعار.</p>
                </div>
                <div class="card-body pt-3">
                    <div id="group-notifications-list">
                        @include('admin.pages.groups.partials.notifications-list', ['notifications' => $notifications, 'group' => $group])
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="notificationRecipientsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">من قرأ هذا الإشعار</h5>
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
                    <h5 class="mb-3">تأكيد حذف الإشعار</h5>
                    <p class="text-danger small mb-4">
                        <i class="fe fe-info me-1"></i>
                        سيُحذف من صندوق كل الطلاب المستلمين، ولا يمكن التراجع عن هذا الإجراء.
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

    const groupId = {{ (int) $group->id }};
    const storeUrl = @json(route('groups.notifications.store', $group->id));
    const baseUrl = @json(url('/admin/groups/'.$group->id.'/notifications'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let pendingDeleteId = null;

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

    document.querySelectorAll('.js-insert-var').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const textarea = document.getElementById('notif-message');
            const value = btn.getAttribute('data-var');
            const start = textarea.selectionStart ?? textarea.value.length;
            const end = textarea.selectionEnd ?? textarea.value.length;
            textarea.value = textarea.value.slice(0, start) + value + textarea.value.slice(end);
            textarea.focus();
            const cursor = start + value.length;
            textarea.setSelectionRange(cursor, cursor);
        });
    });

    const form = document.getElementById('group-notification-form');
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = document.getElementById('group-notification-submit');
        submitBtn.disabled = true;

        const payload = {
            title: document.getElementById('notif-title').value,
            message: document.getElementById('notif-message').value,
            type: document.getElementById('notif-type').value,
            action_url: document.getElementById('notif-action-url').value || null,
        };

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function ({ ok, data }) {
                if (!ok || !data.success) {
                    throw new Error(data.message || 'تعذر إرسال الإشعار');
                }
                showAlert('success', data.message);
                form.reset();
                reloadList();
            })
            .catch(function (err) {
                showAlert('error', err.message || 'تعذر إرسال الإشعار');
            })
            .finally(function () {
                submitBtn.disabled = false;
            });
    });

    function reloadList() {
        fetch(baseUrl, {
            method: 'GET',
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const fresh = doc.getElementById('group-notifications-list');
                const current = document.getElementById('group-notifications-list');
                if (fresh && current) {
                    current.innerHTML = fresh.innerHTML;
                }
            })
            .catch(function () {});
    }

    const listContainer = document.getElementById('group-notifications-list');
    listContainer.addEventListener('click', function (e) {
        const viewBtn = e.target.closest('.js-view-recipients');
        if (viewBtn) {
            const notificationId = viewBtn.getAttribute('data-notification-id');
            const modalEl = document.getElementById('notificationRecipientsModal');
            const body = document.getElementById('notificationRecipientsBody');
            body.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();

            fetch(baseUrl + '/' + notificationId, {
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
            pendingDeleteId = deleteBtn.getAttribute('data-notification-id');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteNotificationModal')).show();
        }
    });

    document.getElementById('confirm-delete-notification-btn').addEventListener('click', function () {
        if (!pendingDeleteId) return;
        const id = pendingDeleteId;
        pendingDeleteId = null;
        bootstrap.Modal.getInstance(document.getElementById('deleteNotificationModal'))?.hide();

        fetch(baseUrl + '/' + id, {
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
                reloadList();
            })
            .catch(function (err) {
                showAlert('error', err.message || 'حدث خطأ أثناء حذف الإشعار');
            });
    });
})();
</script>
@stop
