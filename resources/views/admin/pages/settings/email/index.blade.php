@extends('admin.layouts.master')

@section('page-title', 'إعدادات البريد الإلكتروني')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">إعدادات البريد الإلكتروني (SMTP)</h4>
            <p class="fw-normal text-muted fs-14 mb-0">إدارة إعدادات إرسال البريد الإلكتروني</p>
        </div>
        <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.settings.email.create') }}" class="btn btn-primary btn-wave">
                <i class="ri-add-line me-1"></i> إضافة إعدادات جديدة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-check-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Active Configuration Card -->
    @if($activeSettings)
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card border-success">
                <div class="card-header bg-success-transparent">
                    <div class="card-title text-success">
                        <i class="ri-mail-check-line me-2"></i>الإعدادات النشطة حالياً
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md bg-success-transparent me-3">
                                    <i class="ri-server-line fs-18"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">المزود</p>
                                    <h6 class="mb-0">{{ $providers[$activeSettings->provider]['name'] ?? 'مخصص' }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md bg-info-transparent me-3">
                                    <i class="ri-mail-line fs-18"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">البريد المرسل</p>
                                    <h6 class="mb-0">{{ $activeSettings->mail_from_address }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md bg-warning-transparent me-3">
                                    <i class="ri-shield-check-line fs-18"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">التشفير</p>
                                    <h6 class="mb-0">{{ strtoupper($activeSettings->mail_encryption) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md bg-primary-transparent me-3">
                                    <i class="ri-time-line fs-18"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">آخر اختبار</p>
                                    <h6 class="mb-0">
                                        @if($activeSettings->last_tested_at)
                                            {{ $activeSettings->last_tested_at->diffForHumans() }}
                                        @else
                                            لم يتم الاختبار
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-warning" role="alert">
        <i class="ri-error-warning-line me-2"></i>
        <strong>تنبيه:</strong> لا توجد إعدادات بريد إلكتروني نشطة. قم بإضافة وتفعيل إعدادات لإرسال البريد الإلكتروني.
    </div>
    @endif

    <!-- All Configurations -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">جميع الإعدادات المحفوظة</div>
                </div>
                <div class="card-body p-0">
                    @if($settings->count() > 0)
                        <div class="table-responsive">
                            <table class="table text-nowrap table-hover">
                                <thead>
                                    <tr>
                                        <th>المزود</th>
                                        <th>SMTP Host</th>
                                        <th>Port</th>
                                        <th>البريد</th>
                                        <th>التشفير</th>
                                        <th>الحالة</th>
                                        <th>آخر اختبار</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($settings as $setting)
                                        <tr class="{{ $setting->is_active ? 'table-success' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($setting->provider == 'gmail')
                                                        <i class="ri-google-fill text-danger fs-20 me-2"></i>
                                                    @elseif($setting->provider == 'outlook')
                                                        <i class="ri-microsoft-fill text-info fs-20 me-2"></i>
                                                    @else
                                                        <i class="ri-mail-settings-line fs-20 me-2"></i>
                                                    @endif
                                                    <strong>{{ $providers[$setting->provider]['name'] ?? 'مخصص' }}</strong>
                                                </div>
                                            </td>
                                            <td><code>{{ $setting->mail_host }}</code></td>
                                            <td><span class="badge bg-secondary">{{ $setting->mail_port }}</span></td>
                                            <td>{{ $setting->mail_from_address }}</td>
                                            <td><span class="badge bg-info-transparent">{{ strtoupper($setting->mail_encryption) }}</span></td>
                                            <td>
                                                @if($setting->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-secondary">غير نشط</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($setting->test_results)
                                                    @if($setting->test_results['status'] == 'success')
                                                        <span class="badge bg-success">
                                                            <i class="ri-check-line"></i> نجح
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="ri-close-line"></i> فشل
                                                        </span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ $setting->last_tested_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="text-muted">لم يتم الاختبار</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- Test Button -->
                                                    <button type="button" class="btn btn-sm btn-info-light"
                                                            onclick="testEmail({{ $setting->id }}, '{{ $setting->mail_from_address }}')">
                                                        <i class="ri-test-tube-line"></i>
                                                    </button>

                                                    <!-- Activate Button -->
                                                    @if(!$setting->is_active)
                                                        <form action="{{ route('admin.settings.email.activate', $setting->id) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success-light"
                                                                    onclick="return confirm('هل تريد تفعيل هذه الإعدادات؟')">
                                                                <i class="ri-check-double-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <!-- Edit Button -->
                                                    <a href="{{ route('admin.settings.email.edit', $setting->id) }}"
                                                       class="btn btn-sm btn-primary-light">
                                                        <i class="ri-edit-line"></i>
                                                    </a>

                                                    <!-- Delete Button -->
                                                    @if(!$setting->is_active)
                                                        <form action="{{ route('admin.settings.email.destroy', $setting->id) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger-light"
                                                                    onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-5">
                            <i class="ri-mail-settings-line fs-1 text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">لا توجد إعدادات بريد إلكتروني</h5>
                            <p class="text-muted">قم بإضافة إعدادات SMTP جديدة للبدء</p>
                            <a href="{{ route('admin.settings.email.create') }}" class="btn btn-primary mt-3">
                                <i class="ri-add-line me-1"></i> إضافة إعدادات
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<!-- End::app-content -->

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اختبار إعدادات البريد الإلكتروني</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    سيتم إرسال بريد اختباري إلى العنوان المحدد للتأكد من صحة الإعدادات
                </div>
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني للاختبار</label>
                    <input type="email" class="form-control" id="testEmailInput" placeholder="test@example.com" required>
                </div>
                <input type="hidden" id="testSettingId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="sendTestEmail()">
                    <i class="ri-send-plane-line me-1"></i> إرسال بريد اختبار
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
<script>
function testEmail(settingId, defaultEmail) {
    document.getElementById('testSettingId').value = settingId;
    document.getElementById('testEmailInput').value = defaultEmail;

    const modal = new bootstrap.Modal(document.getElementById('testEmailModal'));
    modal.show();
}

async function sendTestEmail() {
    const settingId = document.getElementById('testSettingId').value;
    const testEmail = document.getElementById('testEmailInput').value;

    if (!testEmail) {
        alert('الرجاء إدخال بريد إلكتروني صحيح');
        return;
    }

    try {
        const response = await fetch(`/admin/settings/email/${settingId}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ test_email: testEmail })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ ' + result.message);
            bootstrap.Modal.getInstance(document.getElementById('testEmailModal')).hide();
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ حدث خطأ أثناء إرسال البريد الاختباري');
    }
}
</script>
@stop
