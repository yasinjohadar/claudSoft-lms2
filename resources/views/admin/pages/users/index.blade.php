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
                                    <form action="{{ route('users.index') }}" method="GET"
                                        class="d-flex align-items-center gap-2">
                                        {{-- حقل البحث --}}
                                        <input style="width: 300px" type="text" name="query" class="form-control"
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
                                </div>
                            </div>
                        </div>


                        <div class="card-body">
                            <p class="text-muted">
                            <div class="">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle table-nowrap mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="width: 40px;">#</th>
                                                <th scope="col" style="min-width: 150px;">اسم المستخدم</th>
                                                <th scope="col" style="min-width: 200px;">البريد</th>
                                                <th scope="col" style="min-width: 120px;">الهاتف</th>
                                                <th scope="col" style="min-width: 130px;">اخر دخول</th>
                                                <th scope="col" style="min-width: 150px;">الأدوار</th>
                                                <th scope="col" style="min-width: 110px;">الحالة</th>
                                                <th scope="col" style="min-width: 120px;">الحالة النشطة</th>
                                                <th scope="col" style="min-width: 200px;">العمليات</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @forelse ($users as $user)
                                                @php
                                                    $userSessions = $sessions->get($user->id);
                                                    $lastSession = $userSessions ? $userSessions->first() : null;
                                                @endphp
                                                <tr>
                                                    <th scope="row">{{ $loop->iteration }}</th>

                                                    <td>
                                                        <a href="{{ route('users.show', $user->id) }}"
                                                            class="text-decoration-none">
                                                            {{ $user->name }}
                                                        </a>
                                                    </td>

                                                    <td>
                                                        @if ($user->email)
                                                            <a href="mailto:{{ $user->email }}"
                                                                class="text-primary text-decoration-none"
                                                                title="إرسال بريد إلكتروني">
                                                                {{ $user->email }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if ($user->phone)
                                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}"
                                                                target="_blank"
                                                                class="text-success text-decoration-none me-1"
                                                                title="فتح WhatsApp">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                            {{ $user->phone }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if ($lastSession)
                                                            {{ \Carbon\Carbon::createFromTimestamp($lastSession->last_activity)->diffForHumans() }}
                                                        @else
                                                            لا توجد جلسات
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @foreach ($user->getRoleNames() as $role)
                                                            <span class="badge bg-primary me-1">{{ $role }}</span>
                                                        @endforeach
                                                    </td>

                                                    <td>
                                                        @if ($user->is_connected)
                                                            <span class="badge bg-success">متصل</span>
                                                        @else
                                                            <span class="badge bg-secondary">غير متصل</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <button class="btn btn-sm {{ $user->is_active ? 'btn-success' : 'btn-secondary' }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#toggleStatus{{ $user->id }}"
                                                                title="تغيير الحالة">
                                                            <i class="fas fa-power-off me-1"></i>
                                                            {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                                                        </button>
                                                    </td>

                                                    <td>
                                                        @if($user->hasRole('student'))
                                                            <a class="btn btn-primary btn-sm me-1"
                                                                href="{{ route('admin.users.courses', $user->id) }}"
                                                                title="عرض الكورسات">
                                                                <i class="fas fa-book"></i>
                                                            </a>
                                                            @if($user->is_active)
                                                                <button type="button" 
                                                                        class="btn btn-success btn-sm me-1 impersonate-btn" 
                                                                        data-user-id="{{ $user->id }}"
                                                                        data-user-name="{{ $user->name }}"
                                                                        title="الدخول كطالب في تبويب جديد">
                                                                    <i class="fas fa-user-secret"></i>
                                                                </button>
                                                            @endif
                                                            @if($user->hasRole('student'))
                                                                <a href="{{ route('users.student-details', $user->id) }}" 
                                                                   class="btn btn-info btn-sm me-1"
                                                                   title="عرض تفاصيل الطالب والمجموعات">
                                                                    <i class="fas fa-users"></i>
                                                                </a>
                                                            @endif
                                                        @endif
                                                        <a class="btn btn-info btn-sm me-1"
                                                            href="{{ route('users.edit', $user->id) }}"
                                                            title="تعديل المستخدم">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <a class="btn btn-danger btn-sm me-1" data-bs-toggle="modal"
                                                            data-bs-target="#delete{{ $user->id }}"
                                                            title="حذف المستخدم">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-warning btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#change_password{{ $user->id }}"
                                                            title="تعديل كلمة السر">
                                                            <i class="fa-solid fa-key"></i>
                                                        </a>
                                                    </td>
                                                </tr>

                                                @include('admin.pages.users.delete')
                                                @include('admin.pages.users.change_password')
                                                @include('admin.pages.users.toggle_status')
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-danger fw-bold">لا توجد
                                                        بيانات متاحة
                                                    </td>
                                                </tr>
                                            @endforelse

                                        </tbody>
                                    </table>

                                    <div class="mt-3">
                                        {{ $users->withQueryString()->links() }}
                                    </div>
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



@stop

@section('scripts')
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
        currentImpersonateUrl = url;
        document.getElementById('impersonateUrl').value = url;
        document.getElementById('impersonateUserName').textContent = userName;
        
        // نسخ الرابط تلقائياً
        copyToClipboard(url).then(function() {
            // إظهار الـ Modal
            const modal = new bootstrap.Modal(document.getElementById('impersonateModal'));
            modal.show();
        });
    }
    
    function handleImpersonateClick(e) {
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
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            if (data.success && data.url) {
                // إظهار Modal مع الرابط
                showImpersonateModal(data.url, userName);
            } else {
                alert('حدث خطأ: ' + (data.message || 'فشل في إنشاء رابط الدخول'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
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
        // أزرار الدخول كطالب
        document.querySelectorAll('.impersonate-btn').forEach(function(btn) {
            btn.addEventListener('click', handleImpersonateClick);
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
    }
    
    // محاولة التهيئة عند تحميل الصفحة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initImpersonateButtons);
    } else {
        initImpersonateButtons();
    }
})();
</script>
@stop
