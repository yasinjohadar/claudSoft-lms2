@extends('admin.layouts.master')

@section('page-title', 'تعديل إعدادات البريد الإلكتروني')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
        <div>
            <h4 class="page-title fw-semibold fs-18 mb-0">تعديل إعدادات البريد الإلكتروني</h4>
            <p class="fw-normal text-muted fs-14 mb-0">تحديث تكوين SMTP</p>
        </div>
        <div class="ms-md-auto d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.settings.email.index') }}" class="btn btn-secondary btn-wave">
                <i class="ri-arrow-right-line me-1"></i> رجوع
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <form action="{{ route('admin.settings.email.update', $emailSetting->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">اختر المزود</div>
                    </div>
                    <div class="card-body">
                        <!-- Provider Selection -->
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">مزود البريد <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="provider" id="provider" class="form-select @error('provider') is-invalid @enderror" required>
                                    <option value="">-- اختر المزود --</option>
                                    @foreach($providers as $key => $provider)
                                        <option value="{{ $key }}" {{ old('provider', $emailSetting->provider) == $key ? 'selected' : '' }}>
                                            {{ $provider['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">إعدادات SMTP</div>
                    </div>
                    <div class="card-body">
                        <!-- SMTP Host -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">SMTP Host <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_host" id="mail_host"
                                       class="form-control @error('mail_host') is-invalid @enderror"
                                       value="{{ old('mail_host', $emailSetting->mail_host) }}"
                                       placeholder="smtp.gmail.com" required>
                                @error('mail_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- SMTP Port -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Port <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="number" name="mail_port" id="mail_port"
                                       class="form-control @error('mail_port') is-invalid @enderror"
                                       value="{{ old('mail_port', $emailSetting->mail_port) }}"
                                       placeholder="587" required>
                                @error('mail_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Encryption -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">التشفير <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select name="mail_encryption" id="mail_encryption" class="form-select @error('mail_encryption') is-invalid @enderror" required>
                                    <option value="tls" {{ old('mail_encryption', $emailSetting->mail_encryption) == 'tls' ? 'selected' : '' }}>TLS (موصى به)</option>
                                    <option value="ssl" {{ old('mail_encryption', $emailSetting->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ old('mail_encryption', $emailSetting->mail_encryption) == 'none' ? 'selected' : '' }}>بدون تشفير</option>
                                </select>
                                @error('mail_encryption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">اسم المستخدم/البريد <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_username" id="mail_username"
                                       class="form-control @error('mail_username') is-invalid @enderror"
                                       value="{{ old('mail_username', $emailSetting->mail_username) }}"
                                       placeholder="your-email@gmail.com" required>
                                @error('mail_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">كلمة المرور</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="password" name="mail_password" id="mail_password"
                                           class="form-control @error('mail_password') is-invalid @enderror"
                                           placeholder="اتركه فارغاً للاحتفاظ بكلمة المرور الحالية">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="ri-eye-line" id="toggleIcon"></i>
                                    </button>
                                </div>
                                @error('mail_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">اترك الحقل فارغاً للاحتفاظ بكلمة المرور الحالية</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">إعدادات البريد المرسل</div>
                    </div>
                    <div class="card-body">
                        <!-- From Address -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">البريد المرسل <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="email" name="mail_from_address" id="mail_from_address"
                                       class="form-control @error('mail_from_address') is-invalid @enderror"
                                       value="{{ old('mail_from_address', $emailSetting->mail_from_address) }}"
                                       placeholder="noreply@example.com" required>
                                @error('mail_from_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- From Name -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">اسم المرسل <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" name="mail_from_name" id="mail_from_name"
                                       class="form-control @error('mail_from_name') is-invalid @enderror"
                                       value="{{ old('mail_from_name', $emailSetting->mail_from_name) }}"
                                       placeholder="نظام إدارة التعلم" required>
                                @error('mail_from_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-info me-2" onclick="testConnectionBeforeSave()">
                            <i class="ri-test-tube-line me-1"></i> اختبار الاتصال
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> حفظ التغييرات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<!-- End::app-content -->
@stop

@section('script')
<script>
// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('mail_password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('ri-eye-line');
        toggleIcon.classList.add('ri-eye-off-line');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('ri-eye-off-line');
        toggleIcon.classList.add('ri-eye-line');
    }
}

// Auto-fill settings based on provider selection
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('provider');
    if (providerSelect) {
        providerSelect.addEventListener('change', async function() {
            const provider = this.value;

            if (!provider || provider === 'custom') {
                return;
            }

            try {
                const response = await fetch(`/admin/settings/email/provider/${provider}`);
                const data = await response.json();

                document.getElementById('mail_host').value = data.mail_host || '';
                document.getElementById('mail_port').value = data.mail_port || 587;
                document.getElementById('mail_encryption').value = data.mail_encryption || 'tls';
            } catch (error) {
                console.error('Error loading provider preset:', error);
            }
        });
    }
});

// Test connection before saving
async function testConnectionBeforeSave() {
    // Get form values
    const mailHost = document.getElementById('mail_host').value;
    const mailPort = document.getElementById('mail_port').value;
    const mailUsername = document.getElementById('mail_username').value;
    const mailPassword = document.getElementById('mail_password').value;
    const mailEncryption = document.getElementById('mail_encryption').value;
    const mailFromAddress = document.getElementById('mail_from_address').value;

    // Validate required fields
    if (!mailHost || !mailPort || !mailUsername || !mailFromAddress) {
        alert('يرجى ملء جميع الحقول المطلوبة قبل الاختبار');
        return;
    }

    // If password is empty, we need to use the existing one from the server
    // For edit page, we'll need to send a flag to use existing password
    if (!mailPassword) {
        // Use existing setting's password - we'll need to test with the saved setting
        const settingId = {{ $emailSetting->id }};
        const testEmail = prompt('أدخل البريد الإلكتروني لإرسال بريد اختباري إليه:', mailFromAddress);
        if (!testEmail) {
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(testEmail)) {
            alert('يرجى إدخال بريد إلكتروني صحيح');
            return;
        }

        // Show loading
        const testBtn = event.target;
        const originalText = testBtn.innerHTML;
        testBtn.disabled = true;
        testBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> جاري الاختبار...';

        try {
            // Test with existing saved setting
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
            } else {
                alert('❌ ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('❌ حدث خطأ أثناء اختبار الاتصال: ' + error.message);
        } finally {
            testBtn.disabled = false;
            testBtn.innerHTML = originalText;
        }
        return;
    }

    // If password is provided, test with new settings
    const testEmail = prompt('أدخل البريد الإلكتروني لإرسال بريد اختباري إليه:', mailFromAddress);
    if (!testEmail) {
        return;
    }

    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(testEmail)) {
        alert('يرجى إدخال بريد إلكتروني صحيح');
        return;
    }

    // Show loading
    const testBtn = event.target;
    const originalText = testBtn.innerHTML;
    testBtn.disabled = true;
    testBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> جاري الاختبار...';

    try {
        const response = await fetch('{{ route("admin.settings.email.test-temp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                mail_host: mailHost,
                mail_port: mailPort,
                mail_username: mailUsername,
                mail_password: mailPassword,
                mail_encryption: mailEncryption,
                mail_from_address: mailFromAddress,
                mail_from_name: document.getElementById('mail_from_name').value || 'Test',
                test_email: testEmail
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ ' + result.message);
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ حدث خطأ أثناء اختبار الاتصال: ' + error.message);
    } finally {
        testBtn.disabled = false;
        testBtn.innerHTML = originalText;
    }
}
</script>
@stop
