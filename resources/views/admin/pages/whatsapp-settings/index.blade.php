@extends('admin.layouts.master')

@section('page-title', 'إعدادات WhatsApp')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="page-title fw-semibold fs-18 mb-0">إعدادات WhatsApp</h4>
                <p class="fw-normal text-muted fs-14 mb-0">إدارة إعدادات تكامل WhatsApp</p>
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $savedProvider = $settings['whatsapp_provider'] ?? 'evolution';
            $activeProvider = $savedProvider === 'meta' ? 'evolution' : $savedProvider;

            /*
            | مصدر الحقيقة للتبويبات. مفتاح 'fields' يربط كل حقل بتبويبه، وهو ما
            | يسمح بفتح التبويب الصحيح تلقائياً عند فشل التحقق. أي حقل جديد يُضاف
            | إلى partial لا بد أن يُسجَّل هنا أيضاً وإلا لن يُكشف خطؤه.
            */
            $tabs = [
                'general' => [
                    'label' => 'عام',
                    'icon' => 'ri-settings-3-line',
                    'fields' => ['whatsapp_enabled', 'whatsapp_provider', 'study_report_delivery'],
                ],
                'provider' => [
                    'label' => 'المزود',
                    'icon' => 'ri-plug-line',
                    'fields' => [
                        'custom_api_url', 'custom_api_method', 'custom_api_key', 'custom_api_headers',
                        'custom_api_preflight_enabled', 'custom_api_preflight_url',
                        'whatsapp_web_service_url', 'whatsapp_web_api_token',
                    ],
                ],
                'webhook' => [
                    'label' => 'Webhook',
                    'icon' => 'ri-webhook-line',
                    'fields' => [
                        'webhook_path', 'default_from', 'strict_signature',
                        'api_version', 'phone_number_id', 'waba_id', 'access_token', 'verify_token', 'app_secret',
                    ],
                ],
                'auto-reply' => [
                    'label' => 'الرد التلقائي',
                    'icon' => 'ri-reply-line',
                    'fields' => [
                        'auto_reply', 'auto_reply_message', 'auto_reply_use_ai', 'auto_reply_ai_model_id',
                        'auto_reply_ai_system_prompt', 'auto_reply_evolution_instance', 'auto_reply_faq_context',
                        'auto_reply_initial_delay_min', 'auto_reply_initial_delay_max', 'auto_reply_typing_duration',
                        'auto_reply_max_chunks', 'auto_reply_chunk_max_chars', 'auto_reply_contact_cooldown',
                        'auto_reply_debounce_seconds', 'auto_reply_test_phone',
                    ],
                ],
                'delays' => [
                    'label' => 'الفواصل الزمنية',
                    'icon' => 'ri-time-line',
                    'fields' => [
                        'delay_between_messages', 'delay_between_broadcasts', 'max_messages_per_minute',
                        'random_delay_enabled', 'min_delay', 'max_delay',
                    ],
                ],
                'queue' => [
                    'label' => 'عامل الطابور',
                    'icon' => 'ri-play-circle-line',
                    'fields' => [],
                ],
                'advanced' => [
                    'label' => 'متقدم',
                    'icon' => 'ri-settings-4-line',
                    'fields' => ['timeout'],
                ],
                'messages-log' => [
                    'label' => 'سجل الرسائل',
                    'icon' => 'ri-file-list-3-line',
                    'link' => 'admin.whatsapp-messages.index',
                ],
            ];

            // خريطة الأخطاء لكل تبويب + أول تبويب فيه خطأ بالترتيب البصري
            $tabHasError = [];
            $errorTab = null;
            foreach ($tabs as $key => $tab) {
                $has = false;
                foreach ($tab['fields'] ?? [] as $field) {
                    if ($errors->has($field)) { $has = true; break; }
                }
                $tabHasError[$key] = $has;
                if ($has && $errorTab === null) { $errorTab = $key; }
            }

            $activeTab = $errorTab ?: (session('active_tab') ?: old('active_tab', 'general'));
            if (! array_key_exists($activeTab, $tabs) || isset($tabs[$activeTab]['link'])) {
                $activeTab = 'general';
            }
        @endphp

        <!-- Settings Form -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        @include('admin.pages.whatsapp-settings.partials.tabs-nav')
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.whatsapp-settings.update') }}" method="POST" id="whatsapp-settings-form">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="active_tab" id="active_tab" value="{{ $activeTab }}">

                            {{--
                                ⚠ تبويبات Bootstrap تُخفي بـ display:none لا بـ disabled، فكل الحقول
                                في كل التبويبات تُرسَل مع هذا النموذج الواحد — وهذا مطلوب: نموذج واحد
                                يحفظ كل الإعدادات، وزر «اختبار الاتصال» يرسل new FormData(form) كاملاً.
                                لا تضف disabled ولا تستبدل tab-pane بـ d-none، ولا تضع required على
                                حقل قد يكون داخل تبويب مخفي (المتصفح يرفض الإرسال بصمت).
                            --}}
                            <div class="tab-content pt-3">
                                <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="tab-general" role="tabpanel">
                                    @include('admin.pages.whatsapp-settings.partials.tab-general')
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'provider' ? 'show active' : '' }}" id="tab-provider" role="tabpanel">
                                    @include('admin.pages.whatsapp-settings.partials.tab-provider')
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'webhook' ? 'show active' : '' }}" id="tab-webhook" role="tabpanel">
                                    @include('admin.pages.whatsapp-settings.partials.tab-webhook')
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'auto-reply' ? 'show active' : '' }}" id="tab-auto-reply" role="tabpanel">
                                    @include('admin.pages.whatsapp-settings.partials.tab-auto-reply')
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'delays' ? 'show active' : '' }}" id="tab-delays" role="tabpanel">
                                    @include('admin.pages.whatsapp-settings.partials.delay-settings')
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'queue' ? 'show active' : '' }}" id="tab-queue" role="tabpanel">
                                    @include('admin.pages.whatsapp-settings.partials.tab-queue-worker')
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'advanced' ? 'show active' : '' }}" id="tab-advanced" role="tabpanel">
                                    @include('admin.pages.whatsapp-settings.partials.tab-advanced')
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary" id="test-connection-btn">
                                    <i class="ri-plug-line me-1"></i>اختبار الاتصال
                                </button>
                                <button type="submit" class="btn btn-primary btn-wave">
                                    <i class="ri-save-line me-1"></i>حفظ الإعدادات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End::app-content -->


<!-- Test Connection Modal -->
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnectionModalLabel">اختبار الاتصال</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div id="test-connection-result"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="close-test-modal-btn">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
/* ==== تبويبات الصفحة ==== */
(function () {
    // إن فتح الخادم تبويباً بسببِ خطأ تحقق، لا نسمح للـ hash بإخفائه
    const HAS_ERROR_TAB = @json($errorTab !== null);

    document.addEventListener('DOMContentLoaded', function () {
        const nav = document.getElementById('whatsappSettingsTabs');
        const activeInput = document.getElementById('active_tab');
        if (!nav) return;

        // حفظ التبويب النشط ليبقى مفتوحاً بعد الحفظ، وتحديث الـ hash بلا قفزة تمرير
        nav.addEventListener('shown.bs.tab', function (e) {
            const key = e.target?.dataset?.tabKey;
            if (!key) return;
            if (activeInput) activeInput.value = key;
            try {
                history.replaceState(null, '', '#tab-' + key);
            } catch (_) { /* بعض المتصفحات تمنع replaceState */ }
        });

        /*
         * موجّه الـ hash: يقبل أي مرساة داخل الصفحة لا #tab-* فقط، فيفتح التبويب
         * الحاوي لها. هذا ما يُبقي الروابط القديمة مثل #delay-settings تعمل
         * (تستخدمها صفحات whatsapp-web-settings و whatsapp-messages/send و
         * evolution-api/groups/compare) بعد نقل المحتوى داخل تبويبات.
         */
        function openTabFromHash() {
            if (HAS_ERROR_TAB || !location.hash || location.hash.length < 2) return;

            let el = null;
            try {
                el = document.querySelector(location.hash);
            } catch (_) {
                return; // hash غير صالح كمُحدِّد CSS
            }
            if (!el) return;

            const pane = el.closest('.tab-pane');
            if (!pane) return;

            const btn = document.querySelector('[data-bs-target="#' + pane.id + '"]');
            if (btn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(btn).show();
            }
            if (el !== pane) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        openTabFromHash();
        window.addEventListener('hashchange', openTabFromHash);
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('whatsapp_provider');
    const customApiSettings = document.getElementById('custom-api-settings');
    const whatsappWebSettings = document.getElementById('whatsapp-web-settings');
    const evolutionHint = document.getElementById('evolution-hint');
    const testConnectionBtn = document.getElementById('test-connection-btn');
    
    // Safely initialize modal
    let testConnectionModal = null;
    const modalElement = document.getElementById('testConnectionModal');
    if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        testConnectionModal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        
        // Ensure close buttons work - both btn-close and footer button
        const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"], #close-test-modal-btn');
        closeButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (testConnectionModal) {
                    testConnectionModal.hide();
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    // Fallback: create modal instance if not exists
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
            });
        });
        
        // Close modal when clicking outside (backdrop)
        modalElement.addEventListener('click', function(e) {
            if (e.target === modalElement) {
                if (testConnectionModal) {
                    testConnectionModal.hide();
                }
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalElement.classList.contains('show')) {
                if (testConnectionModal) {
                    testConnectionModal.hide();
                }
            }
        });
    } else {
        console.error('Bootstrap Modal not available or modal element not found');
    }

    // Toggle provider settings
    function toggleProviderSettings() {
        if (!providerSelect) {
            console.error('Provider select not found');
            return;
        }

        const provider = providerSelect.value;

        if (customApiSettings) customApiSettings.style.display = 'none';
        if (whatsappWebSettings) whatsappWebSettings.style.display = 'none';
        if (evolutionHint) evolutionHint.style.display = 'none';

        // ملاحظة: لا نضع required على #custom_api_url — الحقل داخل تبويب قد يكون
        // مخفياً (display:none)، والمتصفح يرفض إرسال النموذج بخطأ في الكونسول فقط
        // ("not focusable") فيبدو زر الحفظ معطّلاً. التحقق يتم في الخادم عبر
        // required_if:whatsapp_provider,custom_api ويُفتح تبويب المزود تلقائياً.

        if (provider === 'custom_api') {
            if (customApiSettings) customApiSettings.style.display = 'block';
        } else if (provider === 'whatsapp_web') {
            if (whatsappWebSettings) whatsappWebSettings.style.display = 'block';
        } else if (provider === 'evolution') {
            if (evolutionHint) evolutionHint.style.display = 'block';
        }

        // شارة اسم المزود على تبويب «المزود» + العنوان داخله
        const badge = document.getElementById('provider-tab-badge');
        const label = providerSelect.options[providerSelect.selectedIndex]?.text || '';
        if (badge) badge.textContent = label;
        const currentLabel = document.getElementById('provider-current-label');
        if (currentLabel) currentLabel.textContent = label;
    }

    // Initial call after DOM is fully loaded
    if (providerSelect && customApiSettings && whatsappWebSettings) {
        // Add event listener for change
        providerSelect.addEventListener('change', function() {
            toggleProviderSettings();
        });
        
        // Call immediately to set initial state based on selected value
        toggleProviderSettings();
        
        // Also call after a small delay as backup
        setTimeout(function() {
            toggleProviderSettings();
        }, 50);
    } else {
        console.error('Required elements not found:', {
            providerSelect: !!providerSelect,
            customApiSettings: !!customApiSettings,
            whatsappWebSettings: !!whatsappWebSettings,
            evolutionHint: !!evolutionHint,
        });
    }

    // Test connection - prevent multiple clicks
    let isTesting = false;
    if (testConnectionBtn && !testConnectionBtn.hasAttribute('data-listener-added')) {
        testConnectionBtn.setAttribute('data-listener-added', 'true');
        testConnectionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Prevent multiple simultaneous requests
            if (isTesting) {
                console.log('Test already in progress, ignoring click');
                return;
            }
            
            isTesting = true;
            testConnectionBtn.disabled = true;
            console.log('Test connection button clicked');
            
            const form = document.getElementById('whatsapp-settings-form');
            if (!form) {
                console.error('Form not found');
                alert('خطأ: لم يتم العثور على النموذج');
                isTesting = false;
                testConnectionBtn.disabled = false;
                return;
            }
            
            const formData = new FormData(form);
            
            // Show loading
            const resultDiv = document.getElementById('test-connection-result');
            if (resultDiv) {
                resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري الاختبار...</span></div><p class="mt-2">جاري اختبار الاتصال...</p></div>';
            }
            
            // Show modal
            if (testConnectionModal) {
                testConnectionModal.show();
            }

            fetch('{{ route("admin.whatsapp-settings.test-connection") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (resultDiv) {
                    if (data.success) {
                        resultDiv.innerHTML = '<div class="alert alert-success"><i class="ri-check-line me-2"></i>' + (data.message || 'تم الاتصال بنجاح!') + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>' + (data.message || 'فشل الاتصال') + '</div>';
                    }
                }
                isTesting = false;
                testConnectionBtn.disabled = false;
            })
            .catch(error => {
                console.error('Fetch error:', error);
                if (resultDiv) {
                    resultDiv.innerHTML = '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>حدث خطأ أثناء الاختبار: ' + error.message + '</div>';
                }
                isTesting = false;
                testConnectionBtn.disabled = false;
            });
        });
        console.log('Test connection button event listener attached');
    } else {
        console.error('Test connection button not found');
    }
});

    // Queue Worker: تشغيل / إيقاف / تحديث الحالة
    (function() {
        const statusBadge = document.getElementById('queue-worker-status-badge');
        const startBtn = document.getElementById('queue-worker-start-btn');
        const stopBtn = document.getElementById('queue-worker-stop-btn');
        const refreshBtn = document.getElementById('queue-worker-refresh-btn');
        const messageEl = document.getElementById('queue-worker-message');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        function setRunning(running, pid) {
            if (statusBadge) {
                statusBadge.textContent = running ? 'يعمل' : 'متوقف';
                statusBadge.className = 'badge fs-6 ' + (running ? 'bg-success' : 'bg-secondary');
            }
            if (startBtn) startBtn.disabled = !!running;
            if (stopBtn) stopBtn.disabled = !running;
        }

        function showMessage(msg, isError) {
            if (!messageEl) return;
            messageEl.textContent = msg || '';
            messageEl.className = 'mt-2 small ' + (isError ? 'text-danger' : 'text-muted');
        }

        function fetchStatus() {
            fetch('{{ route("admin.whatsapp-settings.queue-worker.status") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                setRunning(data.running, data.pid);
                showMessage(data.message || '');
            })
            .catch(function() { showMessage('فشل جلب الحالة.', true); });
        }

        if (startBtn) {
            startBtn.addEventListener('click', function() {
                startBtn.disabled = true;
                showMessage('جاري التشغيل...');
                fetch('{{ route("admin.whatsapp-settings.queue-worker.start") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(r => r.json())
                .then(function(data) {
                    showMessage(data.message || '', !data.success);
                    if (data.success) setRunning(true, data.pid);
                    else startBtn.disabled = false;
                    if (stopBtn) stopBtn.disabled = !data.success;
                })
                .catch(function() {
                    showMessage('حدث خطأ أثناء التشغيل.', true);
                    startBtn.disabled = false;
                });
            });
        }
        if (stopBtn) {
            stopBtn.addEventListener('click', function() {
                stopBtn.disabled = true;
                showMessage('جاري الإيقاف...');
                fetch('{{ route("admin.whatsapp-settings.queue-worker.stop") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(r => r.json())
                .then(function(data) {
                    showMessage(data.message || '');
                    setRunning(false);
                    if (startBtn) startBtn.disabled = false;
                })
                .catch(function() {
                    showMessage('حدث خطأ أثناء الإيقاف.', true);
                    stopBtn.disabled = false;
                });
            });
        }
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                refreshBtn.disabled = true;
                fetchStatus();
                setTimeout(function() { refreshBtn.disabled = false; }, 500);
            });
        }
    })();

/* ==== الرد التلقائي: إظهار حقول الذكاء الاصطناعي + أداة الفحص ==== */
// دالة عامة عمداً: مربوطة عبر onchange في partials/tab-auto-reply.blade.php
function toggleAutoReplyAiFields(useAi) {
    var el = document.getElementById('auto_reply_ai_fields');
    var labelExtra = document.getElementById('auto_reply_message_label_extra');
    if (el) el.style.display = useAi ? 'flex' : 'none';
    if (labelExtra) labelExtra.textContent = useAi ? '(تُستخدم عند عدم تفعيل الذكاء الاصطناعي أو عند فشل التوليد)' : '';
}

document.addEventListener('DOMContentLoaded', function() {
    var cb = document.getElementById('auto_reply_use_ai');
    if (cb) toggleAutoReplyAiFields(cb.checked);

    var previewBtn = document.getElementById('btn_auto_reply_preview');
    var testSendBtn = document.getElementById('btn_auto_reply_test_send');
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            var question = document.getElementById('auto_reply_test_question')?.value?.trim();
            var statusEl = document.getElementById('auto_reply_test_status');
            if (!question) {
                if (statusEl) statusEl.textContent = 'أدخل سؤالاً تجريبياً.';
                return;
            }
            previewBtn.disabled = true;
            if (statusEl) statusEl.textContent = 'جاري توليد المعاينة...';
            fetch('{{ route('admin.whatsapp-settings.auto-reply.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question: question }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                previewBtn.disabled = false;
                if (!data.success) {
                    if (statusEl) statusEl.textContent = data.message || 'فشلت المعاينة';
                    return;
                }
                var box = document.getElementById('auto_reply_preview_result');
                var replyEl = document.getElementById('auto_reply_preview_reply');
                var chunksEl = document.getElementById('auto_reply_preview_chunks');
                if (box) box.classList.remove('d-none');
                if (replyEl) replyEl.textContent = data.reply || '';
                if (chunksEl) {
                    chunksEl.innerHTML = '';
                    (data.chunks || []).forEach(function(c, i) {
                        var li = document.createElement('li');
                        li.textContent = (i + 1) + '. ' + c;
                        chunksEl.appendChild(li);
                    });
                }
                if (statusEl) statusEl.textContent = 'تمت المعاينة.';
            })
            .catch(function() {
                previewBtn.disabled = false;
                if (statusEl) statusEl.textContent = 'خطأ في الاتصال.';
            });
        });
    }

    if (testSendBtn) {
        testSendBtn.addEventListener('click', function() {
            var question = document.getElementById('auto_reply_test_question')?.value?.trim();
            var phone = document.querySelector('[name="auto_reply_test_phone"]')?.value?.trim();
            var statusEl = document.getElementById('auto_reply_test_status');
            if (!question) {
                if (statusEl) statusEl.textContent = 'أدخل سؤالاً تجريبياً.';
                return;
            }
            testSendBtn.disabled = true;
            if (statusEl) statusEl.textContent = 'جاري الإرسال التجريبي (قد يستغرق وقتاً)...';
            fetch('{{ route('admin.whatsapp-settings.auto-reply.test-send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question: question, test_phone: phone }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                testSendBtn.disabled = false;
                if (statusEl) statusEl.textContent = data.message || (data.success ? 'تم.' : 'فشل.');
            })
            .catch(function() {
                testSendBtn.disabled = false;
                if (statusEl) statusEl.textContent = 'خطأ في الاتصال.';
            });
        });
    }
});
</script>
@endsection

