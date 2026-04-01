{{-- مودال + سكربت الدخول كطالب (مشاركة بين صفحة المستخدمين وصفحة المجموعات) --}}
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

<script>
(function() {
    'use strict';

    if (window.__adminImpersonateStudentInit) {
        return;
    }
    window.__adminImpersonateStudentInit = true;

    var currentImpersonateUrl = '';
    var impersonateBaseUrl = @json(url('/admin/impersonate'));

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        var textArea = document.createElement('textarea');
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
        try {
            var modalElement = document.getElementById('impersonateModal');
            if (!modalElement) {
                alert('خطأ: لم يتم العثور على المودال');
                return;
            }
            if (typeof bootstrap === 'undefined') {
                alert('خطأ: Bootstrap غير محمل');
                return;
            }

            currentImpersonateUrl = url;
            var urlInput = document.getElementById('impersonateUrl');
            var userNameSpan = document.getElementById('impersonateUserName');

            if (urlInput) {
                urlInput.value = url;
            }
            if (userNameSpan) {
                userNameSpan.textContent = userName;
            }

            copyToClipboard(url).catch(function() {});

            setTimeout(function() {
                var existingModal = bootstrap.Modal.getInstance(modalElement);
                if (existingModal) {
                    existingModal.dispose();
                }

                var modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });

                modalElement.addEventListener('hidden.bs.modal', function() {
                    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                        backdrop.remove();
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, { once: true });

                modal.show();

                setTimeout(function() {
                    if (!modalElement.classList.contains('show')) {
                        modalElement.style.display = 'block';
                        modalElement.classList.add('show');
                        document.body.classList.add('modal-open');
                        var backdrop = document.createElement('div');
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

    function handleImpersonateClick(e, btn) {
        e.preventDefault();
        e.stopPropagation();

        var userId = btn.getAttribute('data-user-id');
        var userName = btn.getAttribute('data-user-name') || '';

        if (!userId) {
            alert('خطأ: لم يتم العثور على معرف المستخدم');
            return;
        }

        var originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;

        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

        if (!csrfToken) {
            alert('خطأ: لم يتم العثور على CSRF token');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            return;
        }

        fetch(impersonateBaseUrl + '/' + userId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function(response) {
                if (!response.ok) {
                    return response.json().then(function(err) {
                        return Promise.reject(err);
                    });
                }
                return response.json();
            })
            .then(function(data) {
                btn.innerHTML = originalHTML;
                btn.disabled = false;

                if (data.success && data.url) {
                    showImpersonateModal(data.url, userName);
                } else {
                    alert('حدث خطأ: ' + (data.message || 'فشل في إنشاء رابط الدخول'));
                }
            })
            .catch(function(error) {
                var errorMessage = 'حدث خطأ أثناء إنشاء رابط الدخول';
                if (error && error.message) {
                    errorMessage = error.message;
                }
                alert(errorMessage);
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
    }

    document.addEventListener('click', function(e) {
        var copyBtn = e.target.closest('#copyUrlBtn');
        if (copyBtn && document.getElementById('impersonateModal')) {
            e.preventDefault();
            copyToClipboard(currentImpersonateUrl).then(function() {
                var originalHTML = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check text-success"></i>';
                setTimeout(function() {
                    copyBtn.innerHTML = originalHTML;
                }, 2000);
            });
            return;
        }

        var impersonateBtn = e.target.closest('.impersonate-btn');
        if (impersonateBtn) {
            handleImpersonateClick(e, impersonateBtn);
        }
    });

    var modalEl = document.getElementById('impersonateModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                backdrop.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
})();
</script>
