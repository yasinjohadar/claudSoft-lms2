@extends('admin.layouts.master')

@section('page-title')
    تعديل إعدادات التخزين
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('backup-storage.index') }}">أماكن التخزين</a></li>
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-edit-2 me-1"></i>
                        تعديل التخزين
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $config->name }}</h2>
                    <p class="group-show-hero__desc mb-0">حدّث بيانات الاتصال أو الأولوية أو حالة التفعيل.</p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions group-show-actions--single">
                        <a href="{{ route('backup-storage.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع لأماكن التخزين</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h6 class="group-show-members-card__title mb-1">إعدادات الاتصال</h6>
            </div>
            <div class="card-body pt-3">
                        <form action="{{ route('backup-storage.update', $config->id) }}" method="POST" id="storage-form">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">اسم الإعداد <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $config->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="driver" class="form-label">نوع التخزين <span class="text-danger">*</span></label>
                                <select class="form-select" id="driver" name="driver" required>
                                    @foreach($drivers as $key => $label)
                                        <option value="{{ $key }}" {{ old('driver', $config->driver) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="config-fields">
                                <!-- سيتم ملؤها ديناميكياً -->
                            </div>

                            <!-- Test Connection Result -->
                            <div id="test-connection-result" class="mt-3" style="display: none;"></div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">الأولوية</label>
                                    <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', $config->priority) }}" min="0">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="max_backups" class="form-label">الحد الأقصى للنسخ</label>
                                    <input type="number" class="form-control" id="max_backups" name="max_backups" value="{{ old('max_backups', $config->max_backups) }}" min="1">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $config->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">نشط</label>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" id="test-connection-btn" class="btn btn-info">
                                    <i class="fe fe-zap me-1"></i> اختبار الاتصال
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> تحديث
                                </button>
                                <a href="{{ route('backup-storage.index') }}" class="btn btn-outline-secondary">
                                    إلغاء
                                </a>
                            </div>
                        </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var driverSelect = document.getElementById('driver');
    var configFields = document.getElementById('config-fields');
    var currentConfig = @json($config->getDecryptedConfig() ?? []);

    console.log('Edit page loaded, currentConfig:', currentConfig);

    var configTemplates = {
        'local': '<div class="mb-3"><label class="form-label">المسار (اختياري)</label><input type="text" class="form-control" name="config[path]" value="' + (currentConfig.path || 'backups') + '"></div>',
        's3': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[region]" value="${currentConfig.region || 'us-east-1'}" required placeholder="مثال: us-east-1, eu-west-1"></div>
            <div class="mb-3"><label class="form-label">Endpoint (لـ S3-compatible، اختياري)</label><input type="text" class="form-control" name="config[endpoint]" value="${currentConfig.endpoint || ''}" placeholder="https://s3.region.amazonaws.com"></div>
            <div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="config[use_path_style]" value="1" id="use_path_style" ${currentConfig.use_path_style ? 'checked' : ''}><label class="form-check-label" for="use_path_style">Use Path Style Endpoint</label></div></div>
        `,
        'digitalocean': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><select class="form-select" name="config[region]"><option value="nyc3" ${currentConfig.region == 'nyc3' ? 'selected' : ''}>NYC3</option><option value="nyc1" ${currentConfig.region == 'nyc1' ? 'selected' : ''}>NYC1</option><option value="sfo3" ${currentConfig.region == 'sfo3' ? 'selected' : ''}>SFO3</option><option value="sgp1" ${currentConfig.region == 'sgp1' ? 'selected' : ''}>SGP1</option><option value="sfo2" ${currentConfig.region == 'sfo2' ? 'selected' : ''}>SFO2</option><option value="ams3" ${currentConfig.region == 'ams3' ? 'selected' : ''}>AMS3</option></select></div>
        `,
        'wasabi': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><select class="form-select" name="config[region]"><option value="us-east-1" ${currentConfig.region == 'us-east-1' ? 'selected' : ''}>US East 1</option><option value="us-west-1" ${currentConfig.region == 'us-west-1' ? 'selected' : ''}>US West 1</option><option value="eu-central-1" ${currentConfig.region == 'eu-central-1' ? 'selected' : ''}>EU Central 1</option><option value="ap-northeast-1" ${currentConfig.region == 'ap-northeast-1' ? 'selected' : ''}>AP Northeast 1</option></select></div>
        `,
        'backblaze': `
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
            <div class="mb-3"><label class="form-label">Region</label><input type="text" class="form-control" name="config[region]" value="${currentConfig.region || 'us-west-000'}" placeholder="us-west-000"></div>
        `,
        'cloudflare_r2': `
            <div class="mb-3"><label class="form-label">Account ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[account_id]" value="${currentConfig.account_id || ''}" required placeholder="Account ID من Cloudflare"></div>
            <div class="mb-3"><label class="form-label">Access Key ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[access_key_id]" value="${currentConfig.access_key_id || ''}" required></div>
            <div class="mb-3"><label class="form-label">Secret Access Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[secret_access_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Bucket <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[bucket]" value="${currentConfig.bucket || ''}" required></div>
        `,
        'ftp': `
            <div class="mb-3"><label class="form-label">Protocol</label><select class="form-select" name="config[protocol]"><option value="ftp" ${currentConfig.protocol == 'ftp' ? 'selected' : ''}>FTP</option><option value="sftp" ${currentConfig.protocol == 'sftp' ? 'selected' : ''}>SFTP</option></select></div>
            <div class="mb-3"><label class="form-label">Host <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[host]" value="${currentConfig.host || ''}" required></div>
            <div class="mb-3"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[username]" value="${currentConfig.username || ''}" required></div>
            <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[password]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Port</label><input type="number" class="form-control" name="config[port]" value="${currentConfig.port || 21}" id="ftp_port"></div>
            <div class="mb-3"><label class="form-label">Root Path</label><input type="text" class="form-control" name="config[root]" value="${currentConfig.root || '/backups'}"></div>
            <div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="config[use_tls]" value="1" id="use_tls" ${currentConfig.use_tls ? 'checked' : ''}><label class="form-check-label" for="use_tls">Use TLS</label></div></div>
            <div class="mb-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="config[passive]" value="1" id="passive" ${currentConfig.passive !== false ? 'checked' : ''}><label class="form-check-label" for="passive">Passive Mode</label></div></div>
        `,
        'sftp': `
            <div class="mb-3"><label class="form-label">Host <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[host]" value="${currentConfig.host || ''}" required></div>
            <div class="mb-3"><label class="form-label">Username <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[username]" value="${currentConfig.username || ''}" required></div>
            <div class="mb-3"><label class="form-label">Password <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[password]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Port</label><input type="number" class="form-control" name="config[port]" value="${currentConfig.port || 22}"></div>
            <div class="mb-3"><label class="form-label">Root Path</label><input type="text" class="form-control" name="config[root]" value="${currentConfig.root || '/backups'}"></div>
        `,
        'azure': `
            <div class="mb-3"><label class="form-label">Account Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[account_name]" value="${currentConfig.account_name || ''}" required></div>
            <div class="mb-3"><label class="form-label">Account Key <span class="text-danger">*</span></label><input type="password" class="form-control" name="config[account_key]" placeholder="اتركه فارغاً للحفاظ على القيمة الحالية"></div>
            <div class="mb-3"><label class="form-label">Container <span class="text-danger">*</span></label><input type="text" class="form-control" name="config[container]" value="${currentConfig.container || ''}" required></div>
        `,
    };

    function updateConfigFields() {
        var driver = driverSelect.value;
        console.log('Updating config fields for driver:', driver);
        if (driver && configTemplates[driver]) {
            configFields.innerHTML = configTemplates[driver];
        } else {
            configFields.innerHTML = '<div class="alert alert-info mb-0">يرجى اختيار نوع التخزين</div>';
        }
    }

    driverSelect.addEventListener('change', updateConfigFields);

    // تشغيل عند التحميل
    updateConfigFields();

    // اختبار الاتصال
    var testBtn = document.getElementById('test-connection-btn');
    var testResultDiv = document.getElementById('test-connection-result');
    
    if (testBtn && testResultDiv) {
        testBtn.addEventListener('click', function() {
            var driver = driverSelect.value;
            if (!driver) {
                testResultDiv.innerHTML = `
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        يرجى اختيار نوع التخزين أولاً
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                testResultDiv.style.display = 'block';
                return;
            }

            // جمع بيانات الإعدادات
            var configData = {};
            var configInputs = configFields.querySelectorAll('input, select, textarea');
            configInputs.forEach(function(input) {
                if (input.name && input.name.startsWith('config[')) {
                    var key = input.name.replace('config[', '').replace(']', '');
                    if (input.type === 'checkbox') {
                        configData[key] = input.checked ? input.value : '';
                    } else {
                        // للحقول الفارغة (مثل password)، استخدم القيمة الحالية من currentConfig
                        if (input.value === '' && input.type === 'password' && currentConfig[key]) {
                            // لا نرسل كلمة المرور القديمة في الاختبار - نتركها فارغة
                            configData[key] = '';
                        } else {
                            configData[key] = input.value || '';
                        }
                    }
                }
            });

            // تعطيل الزر أثناء الاختبار
            testBtn.disabled = true;
            var originalText = testBtn.innerHTML;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الاختبار...';
            testResultDiv.style.display = 'none';

            // إرسال طلب AJAX
            fetch('{{ route("backup-storage.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    driver: driver,
                    config: configData
                })
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.text().then(function(text) {
                        throw new Error('HTTP ' + response.status + ': ' + text.substring(0, 100));
                    });
                }
                return response.json();
            })
            .then(function(data) {
                testBtn.disabled = false;
                testBtn.innerHTML = originalText;
                
                if (data.success) {
                    testResultDiv.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>✓ نجح الاختبار!</strong><br>
                            ${data.message || 'الاتصال ناجح'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                } else {
                    var errorMessage = data.message || 'فشل الاختبار';
                    // تقسيم الرسالة إلى أسطر إذا كانت طويلة
                    if (errorMessage.includes('\n')) {
                        errorMessage = errorMessage.split('\n').join('<br>');
                    }
                    // تنظيف الرسالة من أحرف خاصة قد تسبب مشاكل
                    errorMessage = errorMessage.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    testResultDiv.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>✗ فشل الاختبار!</strong><br>
                            ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                }
                testResultDiv.style.display = 'block';
            })
            .catch(function(error) {
                testBtn.disabled = false;
                testBtn.innerHTML = originalText;
                var errorMsg = error.message || 'حدث خطأ غير متوقع';
                // تنظيف رسالة الخطأ
                errorMsg = errorMsg.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                testResultDiv.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>خطأ!</strong><br>
                        حدث خطأ أثناء الاختبار: ${errorMsg}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                testResultDiv.style.display = 'block';
            });
        });
    }
});
</script>
@stop

