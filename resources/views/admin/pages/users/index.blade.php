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

    @include('admin.partials.impersonate-student')

@stop

@section('scripts')
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
