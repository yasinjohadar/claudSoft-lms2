@extends('admin.layouts.master')

@section('page-title')
    إعدادات Google
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إعدادات Google</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">إعدادات Google</li>
                        </ol>
                    </nav>
                </div>
            </div>

@section('css')
    <style>
        .form-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 2px solid #e9ecef;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .form-section:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
            transform: translateY(-3px);
        }

        .form-section:hover::before {
            opacity: 1;
        }

        .form-section-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .bg-primary-transparent {
            background: rgba(102, 126, 234, 0.1);
        }

        .bg-info-transparent {
            background: rgba(13, 202, 240, 0.1);
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 0.5rem;
        }

        .info-box i {
            color: #0dcaf0;
            margin-left: 0.5rem;
        }
    </style>
@stop

            <div class="row">
                <div class="col-12">
                    <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fab fa-google me-2"></i>
                            إعدادات Google
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>حدث خطأ:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.google-settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Google Tag Manager -->
                            <div class="form-section">
                                <div class="form-section-header">
                                    <div class="form-section-title">
                                        <div class="form-section-icon bg-primary-transparent text-primary">
                                            <i class="fas fa-tags"></i>
                                        </div>
                                        Google Tag Manager (GTM)
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">
                                            Container ID
                                            <span class="text-muted">(مثال: GTM-XXXXXXX)</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" 
                                                   name="gtm_container_id" 
                                                   id="gtm_container_id"
                                                   class="form-control @error('gtm_container_id') is-invalid @enderror" 
                                                   value="{{ old('gtm_container_id', $settings->gtm_container_id) }}"
                                                   placeholder="GTM-XXXXXXX">
                                            <button type="button" 
                                                    class="btn btn-outline-primary" 
                                                    id="testGtmBtn"
                                                    onclick="testGtmConnection()"
                                                    title="اختبار اتصال GTM"
                                                    {{ !$settings->gtm_container_id ? 'disabled' : '' }}>
                                                <i class="fas fa-vial me-1"></i>
                                                اختبار الاتصال
                                            </button>
                                        </div>
                                        @error('gtm_container_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="info-box">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>معلومات مهمة:</strong> أدخل Container ID من Google Tag Manager. <strong>GTM يتتبع تلقائياً جميع صفحات الواجهة الأمامية</strong> بدون أي إعدادات إضافية. من خلال GTM يمكنك إدارة Google Analytics وجميع التاغات الأخرى.
                                        </div>
                                        <div id="gtmTestResult" class="mt-2"></div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="gtm_enabled" 
                                                   id="gtm_enabled"
                                                   value="1"
                                                   {{ old('gtm_enabled', $settings->gtm_enabled) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gtm_enabled">
                                                تفعيل Google Tag Manager
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Google Search Console -->
                            <div class="form-section">
                                <div class="form-section-header">
                                    <div class="form-section-title">
                                        <div class="form-section-icon bg-info-transparent text-info">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        Google Search Console
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">
                                            كود التحقق
                                        </label>
                                        <input type="text" 
                                               name="search_console_verification" 
                                               class="form-control @error('search_console_verification') is-invalid @enderror" 
                                               value="{{ old('search_console_verification', $settings->search_console_verification) }}"
                                               placeholder="أدخل كود التحقق من Google Search Console">
                                        @error('search_console_verification')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="info-box">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>معلومات:</strong> كود التحقق من Google Search Console. يمكنك الحصول عليه من <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a>.
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="search_console_enabled" 
                                                   id="search_console_enabled"
                                                   value="1"
                                                   {{ old('search_console_enabled', $settings->search_console_enabled) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="search_console_enabled">
                                                تفعيل Google Search Console
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    حفظ التغييرات
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@stop

@section('scripts')
<script>
// Enable/disable test button based on Container ID input
document.addEventListener('DOMContentLoaded', function() {
    const gtmInput = document.getElementById('gtm_container_id');
    const testBtn = document.getElementById('testGtmBtn');
    
    if (gtmInput && testBtn) {
        gtmInput.addEventListener('input', function() {
            const containerId = this.value.trim();
            const gtmPattern = /^GTM-[A-Z0-9]+$/;
            
            if (containerId && gtmPattern.test(containerId)) {
                testBtn.disabled = false;
            } else {
                testBtn.disabled = true;
            }
        });
    }
});

function testGtmConnection() {
    const containerId = document.getElementById('gtm_container_id').value.trim();
    const gtmEnabled = document.getElementById('gtm_enabled').checked;
    const testBtn = document.getElementById('testGtmBtn');
    const resultDiv = document.getElementById('gtmTestResult');
    
    // Validate Container ID format
    const gtmPattern = /^GTM-[A-Z0-9]+$/;
    if (!containerId || !gtmPattern.test(containerId)) {
        resultDiv.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-exclamation-triangle me-2"></i>' +
            'يرجى إدخال Container ID صحيح (مثال: GTM-XXXXXXX)' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        return;
    }
    
    // Check if GTM is enabled
    if (!gtmEnabled) {
        resultDiv.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-exclamation-triangle me-2"></i>' +
            '<strong>تحذير:</strong> يجب تفعيل Google Tag Manager أولاً قبل الاختبار.' +
            '<br><br>قم بتفعيل الخيار "تفعيل Google Tag Manager" ثم احفظ الإعدادات قبل الاختبار.' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        return;
    }
    
    // Show loading state
    const originalHtml = testBtn.innerHTML;
    testBtn.disabled = true;
    testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الاختبار...';
    resultDiv.innerHTML = '';
    
    // Check if settings are saved (compare with saved value)
    const savedContainerId = '{{ $settings->gtm_container_id }}';
    const savedGtmEnabled = {{ $settings->gtm_enabled ? 'true' : 'false' }};
    
    if (containerId !== savedContainerId || gtmEnabled !== savedGtmEnabled) {
        resultDiv.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-exclamation-triangle me-2"></i>' +
            '<strong>تنبيه مهم:</strong> يجب حفظ الإعدادات أولاً قبل الاختبار!' +
            '<br><br>الإعدادات الحالية لم يتم حفظها بعد. احفظ الإعدادات ثم اضغط على زر الاختبار مرة أخرى.' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        testBtn.disabled = false;
        testBtn.innerHTML = originalHtml;
        return;
    }
    
    // Test GTM connection by opening test page
    const testUrl = '{{ route("frontend.home") }}';
    const testWindow = window.open(testUrl, '_blank', 'width=1200,height=800');
    
    if (testWindow) {
        resultDiv.innerHTML = '<div class="alert alert-info alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-info-circle me-2"></i>' +
            '<strong>تم فتح صفحة الاختبار:</strong> تم فتح الواجهة الأمامية في نافذة جديدة.' +
            '<br><br><strong>خطوات التحقق من GTM:</strong>' +
            '<ol class="mb-0 mt-2">' +
            '<li>افتح Developer Tools (اضغط F12 أو انقر بزر الماوس الأيمن → Inspect)</li>' +
            '<li>اذهب إلى تبويب <strong>Console</strong> وتحقق من عدم وجود أخطاء</li>' +
            '<li>اذهب إلى تبويب <strong>Network</strong> وابحث عن: <code>gtm.js?id=' + containerId + '</code></li>' +
            '<li>في Console، اكتب: <code>console.log(window.dataLayer)</code> وتحقق من وجود dataLayer</li>' +
            '<li>يمكنك أيضاً استخدام <a href="https://tagassistant.google.com/" target="_blank">Google Tag Assistant</a> extension</li>' +
            '</ol>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
    } else {
        resultDiv.innerHTML = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
            '<i class="fas fa-exclamation-triangle me-2"></i>' +
            '<strong>تحذير:</strong> تم منع فتح النافذة الجديدة. يرجى السماح بالنوافذ المنبثقة ثم حاول مرة أخرى.' +
            '<br><br><strong>للتحقق يدوياً:</strong>' +
            '<ol class="mb-0 mt-2">' +
            '<li>احفظ الإعدادات أولاً</li>' +
            '<li>افتح الواجهة الأمامية في نافذة جديدة</li>' +
            '<li>افتح Developer Tools (F12)</li>' +
            '<li>تحقق من Console و Network tabs</li>' +
            '</ol>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
    }
    
    testBtn.disabled = false;
    testBtn.innerHTML = originalHtml;
}
</script>
@stop

