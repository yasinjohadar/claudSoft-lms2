@extends('admin.layouts.master')

@section('page-title')
    قائمة المستخدمون
@stop



@section('css')
@stop

@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">كافة المستخدمين</h5>

                </div>


            </div>
            <!-- Page Header Close -->



            <!-- Start::row-1 -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex gap-3">
                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">إنشاء مستخدم جديد</a>

                            <div class="flex-shrink-0">
                                <div class="form-check form-switch form-switch-right form-switch-md">
                                    <form id="usersFilterForm" action="{{ route('users.index') }}" method="GET"
                                        class="d-flex align-items-center gap-2">
                                        {{-- حقل البحث --}}
                                        <input id="usersSearchInput" style="width: 300px" type="text" name="query" class="form-control"
                                            placeholder="بحث بالاسم أو الإيميل أو الهاتف" value="{{ request('query') }}">

                                        {{-- فلتر الحالة النشطة --}}
                                        <select name="is_active" class="form-select">
                                            <option value="">كل الحالات النشطة</option>
                                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                        </select>

                                        <select name="status" class="form-select">
                                            <option value="">كل الحالات</option>
                                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>فعال
                                            </option>
                                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>معلق
                                            </option>
                                            <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>محظور
                                                مؤقتاً
                                            </option>
                                            <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>محظور
                                                نهائياً
                                            </option>
                                        </select>

                                        <button type="submit" class="btn btn-secondary">بحث</button>
                                        <a href="{{ route('users.index') }}" class="btn btn-danger">مسح </a>
                                    </form>
                                    <small id="usersSearchFeedback" class="text-muted d-block mt-1"></small>
                                </div>
                            </div>
                        </div>


                        <div class="card-body">
                            <p class="text-muted">
                            <div class="">
                                <div id="usersTableContainer">
                                    @include('admin.pages.users._users_table', ['users' => $users, 'sessions' => $sessions])
                                </div>
                            </div>



                        </div><!-- end card-body -->
                    </div><!-- end card -->
                </div>
            </div>
            <!--End::row-1 -->


        </div>
    </div>
    <!-- End::app-content -->

    <!-- Modal للدخول كطالب -->
<div class="modal fade" id="impersonateModal" tabindex="-1" aria-labelledby="impersonateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="impersonateModalLabel">
                    <i class="fas fa-user-secret me-2"></i>
                    الدخول كـ <span id="impersonateUserName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-success mb-4">
                    <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                    <h5 class="mb-1">تم نسخ الرابط تلقائياً! ✓</h5>
                </div>
                
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-keyboard me-2"></i>
                            الخطوات:
                        </h6>
                        <div class="d-flex flex-column gap-2 text-start">
                            <div class="step">
                                <span class="badge bg-primary rounded-circle me-2">1</span>
                                اضغط <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>N</kbd> لفتح نافذة مخفية
                            </div>
                            <div class="step">
                                <span class="badge bg-primary rounded-circle me-2">2</span>
                                اضغط <kbd>Ctrl</kbd> + <kbd>V</kbd> للصق الرابط
                            </div>
                            <div class="step">
                                <span class="badge bg-primary rounded-circle me-2">3</span>
                                اضغط <kbd>Enter</kbd>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small text-muted">الرابط (تم نسخه):</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm" id="impersonateUrl" readonly dir="ltr" style="font-size: 11px;">
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="copyUrlBtn" title="نسخ مرة أخرى">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted">صالح لمدة 60 دقيقة</small>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    إغلاق
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('scripts')
<script>
(function() {
    'use strict';
    
    let currentImpersonateUrl = '';
    
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
        } catch (err) {
            console.error('Failed to copy:', err);
        }
        
        document.body.removeChild(textArea);
        return Promise.resolve();
    }
    
    function showImpersonateModal(url, userName) {
        console.log('showImpersonateModal called with:', { url, userName });
        try {
            const modalElement = document.getElementById('impersonateModal');
            console.log('Modal element:', modalElement);
            if (!modalElement) {
                console.error('Modal element not found');
                alert('خطأ: لم يتم العثور على المودال');
                return;
            }
            
            // التحقق من وجود Bootstrap
            console.log('Bootstrap type:', typeof bootstrap);
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap is not loaded');
                alert('خطأ: Bootstrap غير محمل');
                return;
            }
            
            currentImpersonateUrl = url;
            const urlInput = document.getElementById('impersonateUrl');
            const userNameSpan = document.getElementById('impersonateUserName');
            
            console.log('URL input:', urlInput);
            console.log('User name span:', userNameSpan);
            
            if (urlInput) {
                urlInput.value = url;
            }
            if (userNameSpan) {
                userNameSpan.textContent = userName;
            }
            
            // نسخ الرابط تلقائياً
            copyToClipboard(url).then(function() {
                console.log('Clipboard copy successful, showing modal...');
            }).catch(function(err) {
                console.error('Error copying to clipboard:', err);
            });
            
            // إظهار الـ Modal بعد نسخ الرابط (أو حتى لو فشل النسخ)
            setTimeout(function() {
                // إزالة أي modal instances قديمة
                const existingModal = bootstrap.Modal.getInstance(modalElement);
                if (existingModal) {
                    existingModal.dispose();
                }
                
                // إنشاء modal جديد
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
                console.log('Bootstrap Modal instance created:', modal);
                
                // إضافة event listener لإزالة backdrop عند الإغلاق
                modalElement.addEventListener('hidden.bs.modal', function() {
                    // إزالة backdrop إذا بقي
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(function(backdrop) {
                        backdrop.remove();
                    });
                    
                    // إزالة modal-open class من body
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, { once: true });
                
                console.log('Calling modal.show()...');
                modal.show();
                console.log('modal.show() called');
                
                // التأكد من أن المودال مرئي
                setTimeout(function() {
                    if (modalElement.classList.contains('show')) {
                        console.log('Modal is visible');
                    } else {
                        console.error('Modal is not visible after show()');
                        // محاولة إظهاره يدوياً
                        modalElement.style.display = 'block';
                        modalElement.classList.add('show');
                        document.body.classList.add('modal-open');
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
                }, 100);
            }, 50);
        } catch (error) {
            console.error('Error showing modal:', error);
            alert('حدث خطأ أثناء فتح المودال: ' + error.message);
        }
    }
    
    function handleImpersonateClick(e) {
        console.log('Impersonate button clicked');
        e.preventDefault();
        e.stopPropagation();
        
        const btn = e.currentTarget;
        const userId = btn.getAttribute('data-user-id');
        const userName = btn.getAttribute('data-user-name');
        
        if (!userId) {
            alert('خطأ: لم يتم العثور على معرف المستخدم');
            return;
        }

        // إظهار loading
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        // الحصول على CSRF token
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;
        
        if (!csrfToken) {
            alert('خطأ: لم يتم العثور على CSRF token');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            return;
        }
        
        console.log('Sending fetch request for userId:', userId);
        
        // إرسال طلب لإنشاء token
        fetch('{{ url("/admin/impersonate") }}/' + userId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response received:', response.status, response.statusText);
            if (!response.ok) {
                return response.json().then(err => {
                    console.error('Response error:', err);
                    return Promise.reject(err);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            if (data.success && data.url) {
                console.log('Calling showImpersonateModal with URL:', data.url);
                // إظهار Modal مع الرابط
                showImpersonateModal(data.url, userName);
            } else {
                console.error('Invalid response data:', data);
                alert('حدث خطأ: ' + (data.message || 'فشل في إنشاء رابط الدخول'));
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            
            let errorMessage = 'حدث خطأ أثناء إنشاء رابط الدخول';
            
            if (error.message) {
                errorMessage = error.message;
            }
            
            alert(errorMessage);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
    }
    
    function initImpersonateButtons() {
        console.log('Initializing impersonate buttons...');
        const buttons = document.querySelectorAll('.impersonate-btn');
        console.log('Found buttons:', buttons.length);
        
        // أزرار الدخول كطالب
        buttons.forEach(function(btn) {
            // إزالة event listeners القديمة لتجنب التكرار
            btn.removeEventListener('click', handleImpersonateClick);
            btn.addEventListener('click', handleImpersonateClick);
            console.log('Added event listener to button:', btn);
        });
        
        // زر نسخ الرابط
        const copyBtn = document.getElementById('copyUrlBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                copyToClipboard(currentImpersonateUrl).then(function() {
                    const originalHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check text-success"></i>';
                    setTimeout(function() {
                        copyBtn.innerHTML = originalHTML;
                    }, 2000);
                });
            });
        }
        
        // إضافة event listener عام للمودال لإزالة backdrop عند الإغلاق
        const modalElement = document.getElementById('impersonateModal');
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function() {
                // إزالة backdrop إذا بقي
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(backdrop) {
                    backdrop.remove();
                });
                
                // إزالة modal-open class من body
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        }
    }
    
    // محاولة التهيئة عند تحميل الصفحة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initImpersonateButtons);
    } else {
        initImpersonateButtons();
    }
    
    // نسخ الإيميل
    function initCopyEmailButtons() {
        document.querySelectorAll('.copy-email-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const email = btn.getAttribute('data-email');
                copyToClipboard(email).then(function() {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check text-success"></i>';
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                    }, 1500);
                });
            });
        });
    }
    
    // تهيئة أزرار النسخ عند تحميل الصفحة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCopyEmailButtons);
    } else {
        initCopyEmailButtons();
    }

    function debounce(fn, delay) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function initUsersAjaxSearch() {
        const form = document.getElementById('usersFilterForm');
        const tableContainer = document.getElementById('usersTableContainer');
        const searchInput = document.getElementById('usersSearchInput');
        const feedback = document.getElementById('usersSearchFeedback');

        if (!form || !tableContainer) {
            return;
        }

        const getQueryString = function() {
            const formData = new FormData(form);
            const query = (formData.get('query') || '').toString().trim();
            formData.set('query', query);
            return new URLSearchParams(formData).toString();
        };

        let currentController = null;

        const fetchAndRender = function(url) {
            if (currentController) {
                currentController.abort();
            }

            currentController = new AbortController();

            if (feedback) {
                feedback.textContent = 'جاري البحث...';
            }

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: currentController.signal,
                credentials: 'same-origin',
            })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('فشل جلب النتائج');
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (!data || typeof data.table_html !== 'string') {
                        throw new Error('صيغة استجابة غير متوقعة');
                    }

                    tableContainer.innerHTML = data.table_html;
                    initImpersonateButtons();
                    initCopyEmailButtons();

                    if (feedback) {
                        feedback.textContent = 'تم تحديث النتائج بنجاح';
                    }
                })
                .catch(function(error) {
                    if (error.name === 'AbortError') {
                        return;
                    }
                    if (feedback) {
                        feedback.textContent = 'تعذر تحميل النتائج، حاول مرة أخرى.';
                    }
                    console.error(error);
                });
        };

        const triggerSearch = function() {
            const queryString = getQueryString();
            const baseUrl = form.getAttribute('action');
            const url = queryString ? (baseUrl + '?' + queryString) : baseUrl;
            fetchAndRender(url);
        };

        const debouncedSearch = debounce(triggerSearch, 350);

        if (searchInput) {
            searchInput.addEventListener('input', debouncedSearch);
        }

        form.querySelectorAll('select[name="is_active"], select[name="status"]').forEach(function(selectElement) {
            selectElement.addEventListener('change', triggerSearch);
        });

        tableContainer.addEventListener('click', function(event) {
            const paginationLink = event.target.closest('.pagination a');
            if (!paginationLink) {
                return;
            }

            event.preventDefault();
            fetchAndRender(paginationLink.href);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUsersAjaxSearch);
    } else {
        initUsersAjaxSearch();
    }
})();
</script>
@stop
