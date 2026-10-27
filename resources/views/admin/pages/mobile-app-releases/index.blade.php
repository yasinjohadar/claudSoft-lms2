@extends('admin.layouts.master')

@section('page-title')
    تحديثات تطبيق الجوال
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تحديثات تطبيق الجوال</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">تحديثات تطبيق الجوال</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.mobile-app-releases.create') }}" class="btn btn-primary btn-wave">
                        <i class="fas fa-rocket me-2"></i>نشر إصدار جديد
                    </a>
                </div>
            </div>

            <!-- How it works -->
            <div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
                <i class="fas fa-info-circle fs-18 mt-1"></i>
                <div class="small">
                    <strong>كيف يعمل؟</strong>
                    عند فتح التطبيق (أو العودة إليه) يقارن رقم البناء المثبّت مع أحدث إصدار <strong>نشط</strong> لمنصّته هنا.
                    إذا كان هناك إصدار أحدث تظهر للطالب نافذة <strong>تحديث اختياري</strong> يمكنه تأجيلها،
                    أو شاشة <strong>تحديث إجباري</strong> تمنعه من المتابعة حتى يحدّث.
                    زر <i class="fas fa-paper-plane"></i> يرسل إشعاراً فورياً للطلاب يفتح نافذة التحديث مباشرة.
                </div>
            </div>

            <!-- Latest per platform -->
            <div class="row">
                @foreach (\App\Models\MobileAppRelease::PLATFORMS as $key => $label)
                    @php $current = $latest[$key] ?? null; @endphp
                    <div class="col-md-6">
                        <div class="card custom-card">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg {{ $key === 'android' ? 'bg-success-transparent' : 'bg-dark-transparent' }}">
                                    <i class="fab {{ $key === 'android' ? 'fa-android text-success' : 'fa-apple' }} fs-24"></i>
                                </div>
                                <div class="flex-fill">
                                    <div class="text-muted small mb-1">أحدث إصدار نشط — {{ $label }}</div>
                                    @if ($current)
                                        <h4 class="fw-semibold mb-1" dir="ltr" style="text-align: right;">
                                            v{{ $current->version_name }}
                                            <span class="fs-13 text-muted fw-normal">(build {{ $current->build_number }})</span>
                                        </h4>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if ($current->is_forced)
                                                <span class="badge bg-danger-transparent"><i class="fas fa-lock me-1"></i>إجباري</span>
                                            @else
                                                <span class="badge bg-info-transparent">اختياري</span>
                                            @endif
                                            <span class="badge bg-light text-dark">{{ $current->distribution === 'store' ? 'المتجر' : 'رابط مباشر' }}</span>
                                            <span class="badge bg-light text-dark">{{ $current->created_at->diffForHumans() }}</span>
                                        </div>
                                    @else
                                        <div class="text-muted">لا يوجد إصدار منشور بعد</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="card-title">سجل الإصدارات</div>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.mobile-app-releases.index') }}" class="btn btn-sm {{ ! $platform ? 'btn-primary' : 'btn-outline-primary' }}">الكل</a>
                                @foreach (\App\Models\MobileAppRelease::PLATFORMS as $key => $label)
                                    <a href="{{ route('admin.mobile-app-releases.index', ['platform' => $key]) }}" class="btn btn-sm {{ $platform === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>المنصة</th>
                                            <th>الإصدار</th>
                                            <th>النوع</th>
                                            <th>التوزيع</th>
                                            <th>الحالة</th>
                                            <th>الإشعار</th>
                                            <th>تاريخ النشر</th>
                                            <th style="min-width: 150px;">العمليات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($releases as $release)
                                            <tr>
                                                <td>{{ $loop->iteration + ($releases->currentPage() - 1) * $releases->perPage() }}</td>
                                                <td>
                                                    <i class="fab {{ $release->platform === 'android' ? 'fa-android text-success' : 'fa-apple' }} me-1"></i>
                                                    {{ $release->platform_label }}
                                                </td>
                                                <td dir="ltr" style="text-align: right;">
                                                    <strong>v{{ $release->version_name }}</strong>
                                                    <span class="text-muted small">({{ $release->build_number }})</span>
                                                    @if ($release->release_notes)
                                                        <i class="fas fa-sticky-note text-muted ms-1" data-bs-toggle="tooltip" title="{{ Str::limit($release->release_notes, 300) }}"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($release->is_forced)
                                                        <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>إجباري</span>
                                                    @else
                                                        <span class="badge bg-info">اختياري</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ $release->download_url }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                                        <i class="fas {{ $release->distribution === 'store' ? 'fa-store' : 'fa-download' }} me-1"></i>
                                                        {{ $release->distribution === 'store' ? 'المتجر' : 'رابط مباشر' }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm toggle-active {{ $release->is_active ? 'btn-success-light' : 'btn-danger-light' }}"
                                                            data-id="{{ $release->id }}" title="تغيير الحالة">
                                                        {{ $release->is_active ? 'نشط' : 'موقوف' }}
                                                    </button>
                                                </td>
                                                <td>
                                                    @if ($release->notified_at)
                                                        <span class="text-success small" title="{{ $release->notified_at->format('Y-m-d H:i') }}">
                                                            <i class="fas fa-check-double me-1"></i>{{ $release->notified_count }} طالب
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">لم يُرسل</span>
                                                    @endif
                                                </td>
                                                <td>{{ $release->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <div class="btn-list">
                                                        <button type="button" class="btn btn-sm btn-primary btn-wave" title="إشعار الطلاب بالتحديث"
                                                                onclick="notifyRelease({{ $release->id }}, '{{ e($release->platform_label.' v'.$release->version_name) }}')">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                        <a href="{{ route('admin.mobile-app-releases.edit', $release) }}" class="btn btn-sm btn-info btn-wave" title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger btn-wave" title="حذف"
                                                                onclick="deleteRelease({{ $release->id }}, '{{ e($release->platform_label.' v'.$release->version_name) }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">لا توجد إصدارات منشورة بعد</p>
                                                        <a href="{{ route('admin.mobile-app-releases.create') }}" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-rocket me-1"></i>نشر أول إصدار
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($releases->hasPages())
                                <div class="d-flex justify-content-center mt-4">{{ $releases->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notify Modal -->
    <div class="modal fade" id="notifyReleaseModal" tabindex="-1" aria-labelledby="notifyReleaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-3">
                        <i class="fas fa-paper-plane fs-24 text-primary"></i>
                    </div>
                    <h5 class="mb-2" id="notifyReleaseModalLabel">إشعار الطلاب بالتحديث</h5>
                    <p class="text-muted mb-3" id="notifyReleaseMessage"></p>
                    <div class="form-check form-switch d-inline-flex align-items-center gap-2 text-start">
                        <input class="form-check-input" type="checkbox" id="notifyAppUsersOnly" checked>
                        <label class="form-check-label small" for="notifyAppUsersOnly">فقط الطلاب الذين سجّلوا الدخول من التطبيق</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>إلغاء</button>
                    <button type="button" class="btn btn-primary" id="confirmNotifyRelease"><i class="fas fa-paper-plane me-2"></i>إرسال</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteReleaseModal" tabindex="-1" aria-labelledby="deleteReleaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                        <i class="fas fa-trash-alt fs-24 text-danger"></i>
                    </div>
                    <h5 class="mb-2" id="deleteReleaseModalLabel">حذف الإصدار</h5>
                    <p class="text-muted mb-0" id="deleteReleaseMessage">هل أنت متأكد من حذف هذا الإصدار؟</p>
                    <p class="text-danger small mt-2 mb-0">لن يمكن التراجع عن هذا الإجراء.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteRelease"><i class="fas fa-trash me-2"></i>حذف</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-xl bg-success-transparent mx-auto mb-3" id="alertIconContainer">
                        <i class="fas fa-check-circle fs-24 text-success" id="alertIcon"></i>
                    </div>
                    <h5 class="mb-2" id="alertModalLabel">نجح</h5>
                    <p class="text-muted mb-0" id="alertMessage">تمت العملية بنجاح</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="fas fa-check me-2"></i>حسناً</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    const releasesUrl = `{{ url('/admin/mobile-app-releases') }}`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    let currentReleaseId = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showAlert(type, message) {
        const modal = new bootstrap.Modal(document.getElementById('alertModal'));
        const ok = type === 'success';
        document.getElementById('alertIconContainer').className = `avatar avatar-xl ${ok ? 'bg-success-transparent' : 'bg-danger-transparent'} mx-auto mb-3`;
        document.getElementById('alertIcon').className = `fas ${ok ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger'} fs-24`;
        document.getElementById('alertModalLabel').textContent = ok ? 'نجح' : 'خطأ';
        document.getElementById('alertMessage').textContent = message;
        modal.show();
    }

    function hideModal(id) {
        const instance = bootstrap.Modal.getInstance(document.getElementById(id));
        if (instance) instance.hide();
    }

    async function postJson(url, method = 'POST', body = null) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: body ? JSON.stringify(body) : null
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
            throw new Error(data.message || `HTTP ${response.status}`);
        }
        return data;
    }

    function notifyRelease(id, label) {
        currentReleaseId = id;
        document.getElementById('notifyReleaseMessage').innerHTML =
            `سيصل للطلاب إشعار فوري بإصدار <strong>${escapeHtml(label)}</strong>، وعند الضغط عليه تفتح نافذة التحديث في التطبيق.`;
        new bootstrap.Modal(document.getElementById('notifyReleaseModal')).show();
    }

    function deleteRelease(id, label) {
        currentReleaseId = id;
        document.getElementById('deleteReleaseMessage').innerHTML = `هل أنت متأكد من حذف الإصدار<br><strong>${escapeHtml(label)}</strong>؟`;
        new bootstrap.Modal(document.getElementById('deleteReleaseModal')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

        document.querySelectorAll('.toggle-active').forEach(button => {
            button.addEventListener('click', async function () {
                button.disabled = true;
                try {
                    const data = await postJson(`${releasesUrl}/${button.dataset.id}/toggle-active`);
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1000);
                } catch (e) {
                    showAlert('error', e.message || 'حدث خطأ أثناء تحديث الحالة');
                    button.disabled = false;
                }
            });
        });

        document.getElementById('confirmNotifyRelease').addEventListener('click', async function () {
            if (!currentReleaseId) return;
            const button = this;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جارٍ الإرسال...';
            try {
                const data = await postJson(`${releasesUrl}/${currentReleaseId}/notify`, 'POST', {
                    app_users_only: document.getElementById('notifyAppUsersOnly').checked
                });
                hideModal('notifyReleaseModal');
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } catch (e) {
                hideModal('notifyReleaseModal');
                showAlert('error', e.message || 'تعذّر إرسال الإشعار');
            } finally {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-paper-plane me-2"></i>إرسال';
            }
        });

        document.getElementById('confirmDeleteRelease').addEventListener('click', async function () {
            if (!currentReleaseId) return;
            hideModal('deleteReleaseModal');
            try {
                const data = await postJson(`${releasesUrl}/${currentReleaseId}`, 'DELETE');
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } catch (e) {
                showAlert('error', e.message || 'حدث خطأ أثناء الحذف');
            }
        });
    });
</script>
@stop
