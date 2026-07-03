@extends('admin.layouts.master')

@section('page-title')
    قائمة المستخدمون
@stop



@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-triangle me-2"></i>
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">المستخدمون</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-users me-1"></i>
                            إدارة المستخدمين
                        </span>
                        <h2 class="group-show-hero__title mb-2">كافة المستخدمين</h2>
                        <p class="group-show-hero__desc mb-0">
                            إدارة الحسابات، اكتمال البروفايل، التفعيل، والجلسات من لوحة واحدة أنيقة.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('users.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-user-plus"></i></span>
                                <span class="group-show-action__text">إنشاء مستخدم جديد</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="usersStatsContainer" class="mb-4">
                @include('admin.pages.users.partials.stats', ['stats' => $stats ?? []])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية المستخدمين</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالاسم العربي أو الإنجليزي أو البريد أو الهاتف، أو فلتر حسب الحالة أو الدور أو اكتمال البروفايل.</p>
                </div>
                <div class="card-body pt-3">
                    <form id="usersFilterForm" action="{{ route('users.index') }}" method="GET" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-5 col-md-6">
                                <label class="form-label" for="usersSearchInput">بحث</label>
                                <input id="usersSearchInput" type="text" name="query" class="form-control"
                                    placeholder="البريد كاملاً، الهاتف بالأرقام، أو الاسم..." value="{{ request('query') }}">
                                <div class="form-text">البريد: مطابقة دقيقة — الهاتف: بالأرقام — الاسم: عربي أو إنجليزي</div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="usersIsActive">الحالة النشطة</label>
                                <select name="is_active" id="usersIsActive" class="form-select">
                                    <option value="">كل الحالات النشطة</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="usersStatus">الحالة</label>
                                <select name="status" id="usersStatus" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>فعال</option>
                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>معلق</option>
                                    <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>محظور مؤقتاً</option>
                                    <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>محظور نهائياً</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="usersRole">الدور</label>
                                <select name="role" id="usersRole" class="form-select">
                                    <option value="">كل الأدوار</option>
                                    @foreach($roles ?? [] as $role)
                                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="usersAccountTier">نوع الحساب</label>
                                <select name="account_tier" id="usersAccountTier" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="gold" @selected(request('account_tier') === 'gold')>ذهبي</option>
                                    <option value="silver" @selected(request('account_tier') === 'silver')>فضي</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="usersProfileCompletion">اكتمال البروفايل</label>
                                <select name="profile_completion" id="usersProfileCompletion" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="complete" @selected(request('profile_completion') === 'complete')>مكتمل (100%)</option>
                                    <option value="incomplete" @selected(request('profile_completion') === 'incomplete')>غير مكتمل</option>
                                    <option value="low" @selected(request('profile_completion') === 'low')>أقل من 50%</option>
                                    <option value="medium" @selected(request('profile_completion') === 'medium')>من 50% إلى 99%</option>
                                </select>
                            </div>
                            <div class="col-xl-4 col-lg-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm" id="usersResetBtn">
                                        <i class="fe fe-rotate-cw me-1"></i>مسح
                                    </a>
                                    <small id="usersSearchFeedback" class="text-muted ms-1"></small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة المستخدمين
                        <span class="group-show-members-card__count" id="usersTableCount">{{ $users->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div id="usersTableContainer">
                        @include('admin.pages.users._users_table', ['users' => $users, 'sessions' => $sessions, 'tierByUserId' => $tierByUserId ?? []])
                    </div>
                </div>
            </div>

            <div id="usersModalsContainer">
                @include('admin.pages.users._users_modals', ['users' => $users])
            </div>

        </div>
    </div>

    @include('admin.pages.users.partials.change-password-modal')
    @include('admin.pages.users.partials.send-email-modal')
    @include('admin.pages.users.partials.send-whatsapp-modal')

    <div class="modal fade" id="adminUserNotesModal" tabindex="-1" aria-labelledby="adminUserNotesModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adminUserNotesModalTitle">ملاحظات إدارية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body" id="adminUserNotesModalBody">
                    <p class="text-muted mb-0">جاري التحميل...</p>
                </div>
            </div>
        </div>
    </div>

@stop

@section('scripts')
@include('admin.pages.users.partials.send-email-scripts')
@include('admin.pages.users.partials.send-whatsapp-scripts')
<script>
(function() {
    'use strict';

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }

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

    // نسخ الإيميل
    function initCopyEmailButtons() {
        document.querySelectorAll('.copy-email-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const email = btn.getAttribute('data-email');
                copyToClipboard(email).then(function() {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fe fe-check text-success"></i>';
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

    function initUsersCountup(container) {
        const root = container || document;
        root.querySelectorAll('[data-countup]').forEach(function(el) {
            const target = parseFloat(el.dataset.countup || '0');
            const duration = 800;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    }

    function initUsersAjaxSearch() {
        const form = document.getElementById('usersFilterForm');
        const tableContainer = document.getElementById('usersTableContainer');
        const modalsContainer = document.getElementById('usersModalsContainer');
        const statsContainer = document.getElementById('usersStatsContainer');
        const countBadge = document.getElementById('usersTableCount');
        const searchInput = document.getElementById('usersSearchInput');
        const feedback = document.getElementById('usersSearchFeedback');
        const resetBtn = document.getElementById('usersResetBtn');

        if (!form || !tableContainer) {
            return;
        }

        initUsersCountup(document);

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
                    if (modalsContainer && typeof data.modals_html === 'string') {
                        modalsContainer.innerHTML = data.modals_html;
                    }
                    if (statsContainer && typeof data.stats_html === 'string') {
                        statsContainer.innerHTML = data.stats_html;
                        initUsersCountup(statsContainer);
                    }
                    if (countBadge && typeof data.count === 'number') {
                        countBadge.textContent = data.count;
                    }
                    initCopyEmailButtons();

                    if (feedback) {
                        feedback.textContent = 'تم تحديث النتائج';
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

        form.querySelectorAll('select[name="is_active"], select[name="status"], select[name="role"], select[name="account_tier"], select[name="profile_completion"]').forEach(function(selectElement) {
            selectElement.addEventListener('change', triggerSearch);
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                form.reset();
                triggerSearch();
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            triggerSearch();
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

    const PASSWORD_CHARSET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*-_+=';

    function generateStrongPassword(length) {
        const size = length || 16;
        const values = window.crypto.getRandomValues(new Uint32Array(size));
        return Array.from(values, function(v) {
            return PASSWORD_CHARSET[v % PASSWORD_CHARSET.length];
        }).join('');
    }

    function getPasswordStrength(password) {
        if (!password) {
            return { label: '—', className: 'bg-secondary' };
        }

        let score = 0;
        if (password.length >= 12) score++;
        if (password.length >= 16) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        if (score >= 4) {
            return { label: 'قوية', className: 'bg-success' };
        }
        if (score >= 2) {
            return { label: 'متوسطة', className: 'bg-warning text-dark' };
        }
        return { label: 'ضعيفة', className: 'bg-danger' };
    }

    function updatePasswordStrengthBadge(password) {
        const badge = document.getElementById('changePasswordStrengthBadge');
        if (!badge) {
            return;
        }
        const strength = getPasswordStrength(password);
        badge.textContent = strength.label;
        badge.className = 'badge align-self-center ' + strength.className;
    }

    function clearChangePasswordErrors() {
        ['changePasswordInput', 'changePasswordConfirmInput'].forEach(function(id) {
            const input = document.getElementById(id);
            if (input) {
                input.classList.remove('is-invalid');
            }
        });
        ['changePasswordInputError', 'changePasswordConfirmInputError'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = '';
            }
        });
        const alertEl = document.getElementById('changePasswordAlert');
        if (alertEl) {
            alertEl.className = 'alert d-none mb-3';
            alertEl.textContent = '';
        }
    }

    function showChangePasswordAlert(message, type) {
        const alertEl = document.getElementById('changePasswordAlert');
        if (!alertEl) {
            return;
        }
        alertEl.className = 'alert alert-' + type + ' mb-3';
        alertEl.textContent = message;
    }

    function setChangePasswordFieldError(field, message) {
        const inputMap = {
            password: 'changePasswordInput',
            password_confirmation: 'changePasswordConfirmInput',
        };
        const errorMap = {
            password: 'changePasswordInputError',
            password_confirmation: 'changePasswordConfirmInputError',
        };
        const inputId = inputMap[field];
        const errorId = errorMap[field];
        if (!inputId || !errorId) {
            return;
        }
        const input = document.getElementById(inputId);
        const errorEl = document.getElementById(errorId);
        if (input) {
            input.classList.add('is-invalid');
        }
        if (errorEl) {
            errorEl.textContent = message;
        }
    }

    function resetChangePasswordForm() {
        const form = document.getElementById('changePasswordForm');
        const passwordInput = document.getElementById('changePasswordInput');
        const confirmInput = document.getElementById('changePasswordConfirmInput');
        if (form) {
            form.reset();
        }
        if (passwordInput) {
            passwordInput.type = 'password';
        }
        if (confirmInput) {
            confirmInput.type = 'password';
        }
        updatePasswordStrengthBadge('');
        clearChangePasswordErrors();
    }

    function openChangePasswordModal(btn) {
        const modalEl = document.getElementById('changePasswordModal');
        const form = document.getElementById('changePasswordForm');
        const userNameEl = document.getElementById('changePasswordUserName');
        const userIdEl = document.getElementById('changePasswordUserId');
        if (!modalEl || !form || !btn) {
            return;
        }

        const updateUrl = btn.getAttribute('data-update-url');
        const userName = btn.getAttribute('data-user-name') || '—';
        const userId = btn.getAttribute('data-user-id') || '';

        form.dataset.updateUrl = updateUrl || '';
        if (userIdEl) {
            userIdEl.value = userId;
        }
        if (userNameEl) {
            userNameEl.textContent = userName;
        }

        resetChangePasswordForm();

        const submitBtn = document.getElementById('changePasswordSubmitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>تعديل كلمة المرور';
        }

        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function initChangePasswordModal() {
        const form = document.getElementById('changePasswordForm');
        const passwordInput = document.getElementById('changePasswordInput');
        const confirmInput = document.getElementById('changePasswordConfirmInput');
        const generateBtn = document.getElementById('changePasswordGenerateBtn');
        const copyBtn = document.getElementById('changePasswordCopyBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]');

        if (!form) {
            return;
        }

        document.addEventListener('click', function(e) {
            const openBtn = e.target.closest('.js-open-change-password');
            if (openBtn) {
                e.preventDefault();
                openChangePasswordModal(openBtn);
                return;
            }

            const toggleBtn = e.target.closest('.js-toggle-password');
            if (toggleBtn) {
                const targetId = toggleBtn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = toggleBtn.querySelector('i');
                if (!input || !icon) {
                    return;
                }
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }
        });

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                updatePasswordStrengthBadge(passwordInput.value);
            });
        }

        if (generateBtn && passwordInput && confirmInput) {
            generateBtn.addEventListener('click', function() {
                const generated = generateStrongPassword(16);
                passwordInput.value = generated;
                confirmInput.value = generated;
                passwordInput.type = 'text';
                confirmInput.type = 'text';
                updatePasswordStrengthBadge(generated);
                clearChangePasswordErrors();
            });
        }

        if (copyBtn && passwordInput) {
            copyBtn.addEventListener('click', function() {
                const value = passwordInput.value;
                if (!value) {
                    showChangePasswordAlert('لا توجد كلمة مرور للنسخ. ولّد كلمة مرور أولاً.', 'warning');
                    return;
                }
                copyToClipboard(value).then(function() {
                    const originalHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check me-1"></i>تم النسخ';
                    setTimeout(function() {
                        copyBtn.innerHTML = originalHTML;
                    }, 1500);
                });
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearChangePasswordErrors();

            const updateUrl = form.dataset.updateUrl;
            const submitBtn = document.getElementById('changePasswordSubmitBtn');
            if (!updateUrl) {
                showChangePasswordAlert('تعذر تحديد المستخدم. أعد فتح النافذة.', 'danger');
                return;
            }

            const formData = new FormData(form);
            formData.append('_method', 'PUT');
            if (csrfToken) {
                formData.append('_token', csrfToken.getAttribute('content'));
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الحفظ...';
            }

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: formData,
            })
                .then(function(response) {
                    return response.json().then(function(data) {
                        return { ok: response.ok, status: response.status, data: data };
                    }).catch(function() {
                        return { ok: response.ok, status: response.status, data: {} };
                    });
                })
                .then(function(result) {
                    if (result.ok) {
                        showChangePasswordAlert(result.data.message || 'تم تحديث كلمة المرور بنجاح', 'success');
                        setTimeout(function() {
                            const modalEl = document.getElementById('changePasswordModal');
                            if (modalEl) {
                                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                            }
                            resetChangePasswordForm();
                        }, 1500);
                        return;
                    }

                    if (result.status === 422 && result.data && result.data.errors) {
                        Object.keys(result.data.errors).forEach(function(field) {
                            const messages = result.data.errors[field];
                            if (messages && messages.length) {
                                setChangePasswordFieldError(field, messages[0]);
                            }
                        });
                        showChangePasswordAlert('يرجى تصحيح الأخطاء أدناه.', 'danger');
                        return;
                    }

                    showChangePasswordAlert(
                        (result.data && result.data.message) ? result.data.message : 'تعذر تحديث كلمة المرور.',
                        'danger'
                    );
                })
                .catch(function() {
                    showChangePasswordAlert('حدث خطأ في الاتصال. حاول مرة أخرى.', 'danger');
                })
                .finally(function() {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>تعديل كلمة المرور';
                    }
                });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChangePasswordModal);
    } else {
        initChangePasswordModal();
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-open-admin-notes');
        if (!btn) {
            return;
        }
        e.preventDefault();
        const url = btn.getAttribute('data-notes-url');
        const name = btn.getAttribute('data-user-name') || '';
        const modalEl = document.getElementById('adminUserNotesModal');
        if (!modalEl || !url) {
            return;
        }
        const titleEl = document.getElementById('adminUserNotesModalTitle');
        const bodyEl = document.getElementById('adminUserNotesModalBody');
        if (titleEl) {
            titleEl.textContent = name ? ('ملاحظات إدارية — ' + name) : 'ملاحظات إدارية';
        }
        if (bodyEl) {
            bodyEl.innerHTML = '<p class="text-muted mb-0">جاري التحميل...</p>';
        }
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('bad response');
                }
                return response.json();
            })
            .then(function(data) {
                if (bodyEl && data && typeof data.html === 'string') {
                    bodyEl.innerHTML = data.html;
                }
            })
            .catch(function() {
                if (bodyEl) {
                    bodyEl.innerHTML = '<p class="text-danger mb-0">تعذر تحميل الملاحظات.</p>';
                }
            });
    });
})();
</script>
@stop
