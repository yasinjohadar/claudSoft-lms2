@extends('admin.layouts.master')

@section('page-title')
    ملف الطالب - {{ $user->name }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="my-4 page-header-breadcrumb">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                    <li class="breadcrumb-item active">ملف الطالب</li>
                </ol>
            </nav>
        </div>

        @include('admin.components.alerts')

        @php
            $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
            $displayPhone = $user->full_phone
                ?? (($user->country_code && $user->phone) ? $user->country_code . $user->phone : null)
                ?? $user->phone;
            $whatsappLink = $user->whatsapp_url
                ?? ($displayPhone ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $displayPhone) : null);
            $roleLabel = $user->roles->pluck('name')->join('، ') ?: 'مستخدم';
            $kpiCards = [
                ['variant' => 'blue', 'icon' => 'fe-book-open', 'label' => 'إجمالي الكورسات', 'value' => $courseStats['total_enrollments'], 'sub' => 'كل التسجيلات'],
                ['variant' => 'green', 'icon' => 'fe-play-circle', 'label' => 'كورسات نشطة', 'value' => $courseStats['active_enrollments'], 'sub' => 'قيد التعلم'],
                ['variant' => 'cyan', 'icon' => 'fe-check-circle', 'label' => 'كورسات مكتملة', 'value' => $courseStats['completed_enrollments'], 'sub' => 'منتهية'],
                ['variant' => 'orange', 'icon' => 'fe-trending-up', 'label' => 'متوسط التقدم', 'value' => number_format($courseStats['average_progress'], 1) . '%', 'sub' => 'عبر كل الكورسات', 'countup' => false],
            ];
        @endphp

        {{-- Hero --}}
        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3">
                        <div class="admin-users-table__avatar flex-shrink-0" style="width: 80px; height: 80px; font-size: 1.75rem;">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                            @elseif($user->photo)
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}">
                            @else
                                <span>{{ $initial }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <span class="group-show-hero__eyebrow">
                                <i class="fe fe-user me-1"></i>
                                ملف الطالب
                            </span>
                            <h2 class="group-show-hero__title mb-1">{{ $user->name }}</h2>
                            @if($user->name_ar)
                                <p class="group-show-hero__desc mb-2">{{ $user->name_ar }}</p>
                            @endif
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="group-show-chip group-show-chip--sm">{{ $roleLabel }}</span>
                                @if($user->is_active)
                                    <span class="group-show-chip group-show-chip--sm text-success"><i class="fe fe-check-circle me-1"></i>نشط</span>
                                @else
                                    <span class="group-show-chip group-show-chip--sm text-danger"><i class="fe fe-slash me-1"></i>موقوف</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('users.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع للمستخدمين</span>
                        </a>
                        @if($user->hasRole('student'))
                            <a href="{{ route('admin.users.courses', $user->id) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-book-open"></i></span>
                                <span class="group-show-action__text">كورسات الطالب</span>
                            </a>
                            <a href="{{ route('users.student-details', $user->id) }}" class="group-show-action group-show-action--warning">
                                <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                                <span class="group-show-action__text">المجموعات والتفاصيل</span>
                            </a>
                            <x-admin.impersonate-trigger :user="$user" variant="group-action">
                                <span class="group-show-action__icon"><i class="fe fe-log-in"></i></span>
                                <span class="group-show-action__text">الدخول كطالب</span>
                            </x-admin.impersonate-trigger>
                        @endif
                        @if($user->email)
                            <button type="button"
                                    class="group-show-action group-show-action--info js-open-send-email border-0 bg-transparent w-100 text-start"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-user-email="{{ $user->email }}"
                                    data-preview-url="{{ route('users.send-email.preview', $user) }}"
                                    data-send-url="{{ route('users.send-email.send', $user) }}">
                                <span class="group-show-action__icon"><i class="fe fe-mail"></i></span>
                                <span class="group-show-action__text">إرسال بريد</span>
                            </button>
                        @endif
                        @php
                            $profileWhatsappPhone = $user->full_phone ?: trim(($user->country_code ?? '').($user->phone ?? ''));
                        @endphp
                        @if($profileWhatsappPhone !== '')
                            <button type="button"
                                    class="group-show-action group-show-action--success js-open-send-whatsapp border-0 bg-transparent w-100 text-start"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-user-phone="{{ $profileWhatsappPhone }}"
                                    data-preview-url="{{ route('users.send-whatsapp.preview', $user) }}"
                                    data-send-url="{{ route('users.send-whatsapp.send', $user) }}">
                                <span class="group-show-action__icon"><i class="ri-whatsapp-line"></i></span>
                                <span class="group-show-action__text">إرسال واتساب</span>
                            </button>
                        @endif
                        <a href="{{ route('users.edit', $user->id) }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                            <span class="group-show-action__text">تعديل البيانات</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI --}}
        <div class="row g-3 dashboard-fade-in mb-4">
            @foreach ($kpiCards as $index => $card)
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                    <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                @if($card['countup'] ?? true)
                                    <h3 class="admin-stats-card__value mb-1" data-countup="{{ is_numeric($card['value']) ? $card['value'] : 0 }}">0</h3>
                                @else
                                    <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1">{{ $card['value'] }}</h3>
                                @endif
                                <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Contact info strip --}}
        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-body py-3">
                <div class="row g-3 admin-profile-contact-strip">
                    <div class="col-md-6 col-xl">
                        <div class="admin-profile-contact-item">
                            <span class="admin-profile-sidebar-field__icon"><i class="fe fe-mail"></i></span>
                            <div class="min-w-0 flex-fill">
                                <small class="text-muted d-block">البريد الإلكتروني</small>
                                @if($user->email)
                                    <div class="d-flex align-items-center gap-1 min-w-0">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary py-0 px-1 copy-email-btn flex-shrink-0"
                                                data-email="{{ $user->email }}"
                                                title="نسخ البريد">
                                            <i class="fe fe-copy"></i>
                                        </button>
                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none text-truncate" title="إرسال بريد">
                                            {{ $user->email }}
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($displayPhone)
                        <div class="col-md-6 col-xl">
                            <div class="admin-profile-contact-item">
                                <span class="admin-profile-sidebar-field__icon"><i class="fe fe-phone"></i></span>
                                <div class="min-w-0">
                                    <small class="text-muted d-block">رقم الجوال</small>
                                    @if($whatsappLink)
                                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer"
                                           class="text-success text-decoration-none d-inline-flex align-items-center gap-1"
                                           title="فتح WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                            <span>{{ $displayPhone }}</span>
                                        </a>
                                    @else
                                        <span>{{ $displayPhone }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($user->nationality)
                        <div class="col-md-6 col-xl">
                            <div class="admin-profile-contact-item">
                                <span class="admin-profile-sidebar-field__icon"><i class="fe fe-flag"></i></span>
                                <div>
                                    <small class="text-muted d-block">الجنسية</small>
                                    <span>{{ $user->nationality->name }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6 col-xl">
                        <div class="admin-profile-contact-item">
                            <span class="admin-profile-sidebar-field__icon"><i class="fe fe-calendar"></i></span>
                            <div>
                                <small class="text-muted d-block">تاريخ التسجيل</small>
                                <span>{{ $user->created_at?->format('Y-m-d') ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs full width --}}
        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-body pt-3 px-2 px-md-3">
                <ul class="nav admin-profile-tabs admin-profile-tabs--full" id="studentProfileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-overview-btn" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab">
                                    <i class="fe fe-user me-1"></i>بيانات الطالب
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-courses" type="button" role="tab">
                                    <i class="fe fe-book-open me-1"></i>الكورسات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-quizzes" type="button" role="tab">
                                    <i class="fe fe-clipboard me-1"></i>الاختبارات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-billing" type="button" role="tab">
                                    <i class="fe fe-credit-card me-1"></i>الفواتير
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-certificates" type="button" role="tab">
                                    <i class="fe fe-award me-1"></i>الشهادات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-groups" type="button" role="tab">
                                    <i class="fe fe-layers me-1"></i>المجموعات
                                    @if($groups->count() > 0)
                                        <span class="badge bg-primary ms-1" id="profile-groups-badge">{{ $groups->count() }}</span>
                                    @else
                                        <span class="badge bg-primary ms-1 d-none" id="profile-groups-badge">0</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bootcamps" type="button" role="tab">
                                    <i class="fe fe-zap me-1"></i>المعسكرات
                                    @if(($campStats['total'] ?? 0) > 0)
                                        <span class="badge bg-primary ms-1" id="profile-camps-badge">{{ $campStats['total'] }}</span>
                                    @else
                                        <span class="badge bg-primary ms-1 d-none" id="profile-camps-badge">0</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sessions" type="button" role="tab">
                                    <i class="fe fe-clock me-1"></i>الجلسات
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-devices" type="button" role="tab">
                                    <i class="fe fe-smartphone me-1"></i>الأجهزة
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-admin-notes" type="button" role="tab">
                                    <i class="fe fe-file-text me-1"></i>ملاحظات
                                    @if(isset($adminNotes) && $adminNotes->isNotEmpty())
                                        <span class="badge bg-primary ms-1">{{ $adminNotes->count() }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>

                <div class="tab-content admin-profile-tab-content pt-3 px-1 px-md-2" id="studentProfileTabContent">
                    @include('admin.pages.users.partials.profile-tab-panes')
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Record payment modal --}}
<div class="modal fade" id="profileRecordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="profileRecordPaymentForm">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fe fe-dollar-sign me-2"></i>
                        تسجيل دفعة — {{ $user->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(($paymentMethods ?? collect())->isEmpty())
                        <div class="alert alert-warning mb-0">
                            لا توجد طرق دفع مفعّلة. أضف طريقة دفع من إعدادات النظام أولاً.
                        </div>
                    @elseif(($payableInvoices ?? collect())->isEmpty())
                        <div class="alert alert-info mb-0">
                            لا توجد فواتير مستحقة لهذا الطالب.
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-3" role="status">
                            <strong>المتبقي على الفاتورة المختارة:</strong>
                            <span id="profilePaymentRemainingValue">0.00</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="profilePaymentInvoiceId">الفاتورة</label>
                            <select class="form-select" id="profilePaymentInvoiceId" required>
                                <option value="">اختر فاتورة</option>
                                @foreach($payableInvoices as $payableInvoice)
                                    <option value="{{ $payableInvoice->id }}"
                                            data-remaining="{{ $payableInvoice->remaining_amount }}"
                                            data-invoice-number="{{ $payableInvoice->invoice_number }}">
                                        {{ $payableInvoice->invoice_number }} — المتبقي: {{ number_format($payableInvoice->remaining_amount, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="profilePaymentAmount">المبلغ</label>
                            <input type="number" class="form-control" id="profilePaymentAmount" step="0.01" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="profilePaymentMethodId">طريقة الدفع</label>
                            <select class="form-select" id="profilePaymentMethodId" required>
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="profilePaymentDate">تاريخ الدفع</label>
                            <input type="date" class="form-control" id="profilePaymentDate" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="profilePaymentTransactionId">رقم العملية (اختياري)</label>
                            <input type="text" class="form-control" id="profilePaymentTransactionId" autocomplete="off">
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="profilePaymentNotes">ملاحظات (اختياري)</label>
                            <textarea class="form-control" id="profilePaymentNotes" rows="2"></textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    @if(($paymentMethods ?? collect())->isNotEmpty() && ($payableInvoices ?? collect())->isNotEmpty())
                        <button type="submit" class="btn btn-success" id="profileRecordPaymentSubmit">
                            <span class="profile-action-btn__label"><i class="fe fe-check me-1"></i>تسجيل الدفعة</span>
                            <span class="profile-action-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الحفظ...</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.pages.users.partials.send-email-modal')
@include('admin.pages.users.partials.send-whatsapp-modal')
@endsection

@push('scripts')
@include('admin.pages.users.partials.send-email-scripts')
@include('admin.pages.users.partials.send-whatsapp-scripts')
<script>
    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var raw = el.getAttribute('data-countup');
        var target = parseInt(raw, 10);
        if (!target) {
            el.textContent = raw || '0';
            return;
        }
        var current = 0;
        var step = Math.max(1, Math.ceil(target / 20));
        var timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString('ar-EG');
        }, 30);
    });

    document.querySelectorAll('.admin-profile-quick-link[data-bs-toggle="tab"]').forEach(function (link) {
        link.addEventListener('click', function () {
            var target = link.getAttribute('href');
            if (!target) return;
            var btn = document.querySelector('[data-bs-target="' + target + '"]');
            if (btn) btn.click();
        });
    });

    (function () {
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

        document.querySelectorAll('.copy-email-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var email = btn.getAttribute('data-email');
                if (!email) return;
                copyToClipboard(email).then(function () {
                    var originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fe fe-check text-success"></i>';
                    setTimeout(function () {
                        btn.innerHTML = originalHTML;
                    }, 1500);
                });
            });
        });

        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var token = csrfToken ? csrfToken.getAttribute('content') : '';

        function showFeedback(el, message, type) {
            if (!el) return;
            el.textContent = message;
            el.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
            el.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
        }

        function hideFeedback(el) {
            if (!el) return;
            el.classList.add('d-none');
            el.textContent = '';
        }

        function setButtonLoading(btn, loading) {
            if (!btn) return;
            var label = btn.querySelector('.profile-action-btn__label');
            var spinner = btn.querySelector('.profile-action-btn__spinner');
            btn.disabled = loading;
            if (label) label.classList.toggle('d-none', loading);
            if (spinner) spinner.classList.toggle('d-none', !loading);
        }

        function formatValidationErrors(data) {
            if (data.errors) {
                return Object.values(data.errors).flat().join(' ');
            }
            return data.message || 'حدث خطأ غير متوقع';
        }

        function postProfileAction(url, formData, feedbackEl, onSuccess) {
            hideFeedback(feedbackEl);
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                credentials: 'same-origin',
                body: formData,
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    }).catch(function () {
                        return { ok: response.ok, status: response.status, data: {} };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.data.success) {
                        showFeedback(feedbackEl, result.data.message, 'success');
                        if (typeof onSuccess === 'function') {
                            onSuccess(result.data);
                        }
                        return;
                    }
                    showFeedback(feedbackEl, formatValidationErrors(result.data), 'error');
                })
                .catch(function () {
                    showFeedback(feedbackEl, 'تعذر إتمام الطلب. حاول مرة أخرى.', 'error');
                });
        }

        function updateTabBadge(badgeEl, count) {
            if (!badgeEl) return;
            badgeEl.textContent = count;
            badgeEl.classList.toggle('d-none', count <= 0);
        }

        function removeSelectOption(selectEl, value) {
            if (!selectEl) return;
            var option = selectEl.querySelector('option[value="' + value + '"]');
            if (option) option.remove();
            selectEl.value = '';
            if (selectEl.options.length <= 1 && selectEl.closest('#profile-add-group-form, #profile-add-camp-form')) {
                var formWrap = selectEl.closest('#profile-add-group-form, #profile-add-camp-form');
                if (formWrap) formWrap.classList.add('d-none');
            }
        }

        function addGroupSelectOption(selectEl, group) {
            if (!selectEl || !group) return;
            if (selectEl.querySelector('option[value="' + group.id + '"]')) return;

            var option = document.createElement('option');
            option.value = String(group.id);
            var label = group.name || '';
            if (group.courses_count > 0) {
                label += ' (' + group.courses_count + ' كورس)';
            }
            option.textContent = label;
            selectEl.appendChild(option);

            var formWrap = document.getElementById('profile-add-group-form');
            if (formWrap) formWrap.classList.remove('d-none');
        }

        function renumberProfileGroupRows() {
            var tbody = document.getElementById('profile-groups-tbody');
            if (!tbody) return;
            tbody.querySelectorAll('.profile-group-row').forEach(function (row, index) {
                var cell = row.querySelector('td:first-child');
                if (cell) cell.textContent = String(index + 1);
            });
        }

        var addGroupBtn = document.getElementById('profile-add-group-btn');
        if (addGroupBtn) {
            addGroupBtn.addEventListener('click', function () {
                var groupSelect = document.getElementById('profile_group_id');
                var roleSelect = document.getElementById('profile_group_role');
                var feedbackEl = document.getElementById('profile-groups-feedback');

                if (!groupSelect || !groupSelect.value) {
                    showFeedback(feedbackEl, 'يرجى اختيار مجموعة.', 'error');
                    return;
                }

                var formData = new FormData();
                formData.append('group_id', groupSelect.value);
                formData.append('role', roleSelect ? roleSelect.value : 'member');

                setButtonLoading(addGroupBtn, true);
                postProfileAction('{{ route('users.add-to-group', $user->id) }}', formData, feedbackEl, function (data) {
                    var tbody = document.getElementById('profile-groups-tbody');
                    var tableWrap = document.getElementById('profile-groups-table-wrap');
                    var emptyEl = document.getElementById('profile-groups-empty');

                    if (tbody && data.row_html) {
                        tbody.insertAdjacentHTML('beforeend', data.row_html);
                    }
                    if (tableWrap) tableWrap.classList.remove('d-none');
                    if (emptyEl) emptyEl.classList.add('d-none');

                    if (data.stats && typeof data.stats.total === 'number') {
                        updateTabBadge(document.getElementById('profile-groups-badge'), data.stats.total);
                    }

                    if (data.group_id) {
                        removeSelectOption(groupSelect, String(data.group_id));
                    }
                }).finally(function () {
                    setButtonLoading(addGroupBtn, false);
                });
            });
        }

        var groupsTbody = document.getElementById('profile-groups-tbody');
        if (groupsTbody) {
            groupsTbody.addEventListener('click', function (event) {
                var btn = event.target.closest('.profile-remove-group-btn');
                if (!btn) return;

                var groupId = btn.getAttribute('data-group-id');
                var groupName = btn.getAttribute('data-group-name') || 'هذه المجموعة';
                var row = btn.closest('.profile-group-row');
                var feedbackEl = document.getElementById('profile-groups-feedback');
                var groupSelect = document.getElementById('profile_group_id');

                if (!groupId || !row) return;

                if (!window.confirm('هل أنت متأكد من إلغاء انضمام الطالب إلى المجموعة «' + groupName + '»؟')) {
                    return;
                }

                var formData = new FormData();
                formData.append('group_id', groupId);

                btn.disabled = true;
                hideFeedback(feedbackEl);

                postProfileAction('{{ route('users.remove-from-group', $user->id) }}', formData, feedbackEl, function (data) {
                    row.remove();
                    renumberProfileGroupRows();

                    var tbody = document.getElementById('profile-groups-tbody');
                    var tableWrap = document.getElementById('profile-groups-table-wrap');
                    var emptyEl = document.getElementById('profile-groups-empty');
                    var hasRows = tbody && tbody.querySelectorAll('.profile-group-row').length > 0;

                    if (tableWrap) tableWrap.classList.toggle('d-none', !hasRows);
                    if (emptyEl) emptyEl.classList.toggle('d-none', hasRows);

                    if (data.stats && typeof data.stats.total === 'number') {
                        updateTabBadge(document.getElementById('profile-groups-badge'), data.stats.total);
                    }

                    if (data.group && groupSelect) {
                        addGroupSelectOption(groupSelect, data.group);
                    }
                }).finally(function () {
                    btn.disabled = false;
                });
            });
        }

        function syncProfileCampPrice() {
            var campSelect = document.getElementById('profile_camp_id');
            var priceInput = document.getElementById('profile_camp_price');
            if (!campSelect || !priceInput || !campSelect.selectedOptions.length) {
                return;
            }
            var price = campSelect.selectedOptions[0].getAttribute('data-price');
            if (price !== null && price !== '' && (!priceInput.dataset.userEdited || priceInput.value === '')) {
                priceInput.value = parseFloat(price).toFixed(2);
            }
        }

        var profileCampSelect = document.getElementById('profile_camp_id');
        if (profileCampSelect) {
            profileCampSelect.addEventListener('change', function () {
                var priceInput = document.getElementById('profile_camp_price');
                if (priceInput) {
                    priceInput.dataset.userEdited = '';
                }
                syncProfileCampPrice();
            });
        }

        var profileCampPriceInput = document.getElementById('profile_camp_price');
        if (profileCampPriceInput) {
            profileCampPriceInput.addEventListener('input', function () {
                profileCampPriceInput.dataset.userEdited = '1';
            });
        }

        var addCampBtn = document.getElementById('profile-add-camp-btn');
        if (addCampBtn) {
            addCampBtn.addEventListener('click', function () {
                var campSelect = document.getElementById('profile_camp_id');
                var statusSelect = document.getElementById('profile_camp_status');
                var paymentSelect = document.getElementById('profile_camp_payment_status');
                var priceInput = document.getElementById('profile_camp_price');
                var notesInput = document.getElementById('profile_camp_notes');
                var feedbackEl = document.getElementById('profile-camps-feedback');

                if (!campSelect || !campSelect.value) {
                    showFeedback(feedbackEl, 'يرجى اختيار معسكر.', 'error');
                    return;
                }

                var formData = new FormData();
                formData.append('camp_id', campSelect.value);
                formData.append('status', statusSelect ? statusSelect.value : 'pending');
                formData.append('payment_status', paymentSelect ? paymentSelect.value : 'unpaid');
                if (priceInput && priceInput.value !== '') {
                    formData.append('price', priceInput.value);
                }
                if (notesInput && notesInput.value.trim()) {
                    formData.append('notes', notesInput.value.trim());
                }

                setButtonLoading(addCampBtn, true);
                postProfileAction('{{ route('users.add-to-camp', $user->id) }}', formData, feedbackEl, function (data) {
                    var tbody = document.getElementById('profile-camps-tbody');
                    var tableWrap = document.getElementById('profile-camps-table-wrap');
                    var emptyEl = document.getElementById('profile-camps-empty');
                    var statsWrap = document.getElementById('profile-camps-stats');

                    if (tbody && data.row_html) {
                        tbody.insertAdjacentHTML('beforeend', data.row_html);
                        var newRow = tbody.lastElementChild;
                        if (typeof initProfileCampRow === 'function') {
                            initProfileCampRow(newRow);
                        }
                    }
                    if (tableWrap) tableWrap.classList.remove('d-none');
                    if (emptyEl) emptyEl.classList.add('d-none');
                    if (statsWrap) statsWrap.classList.remove('d-none');

                    if (data.camp_stats) {
                        var totalEl = document.getElementById('profile-camps-stat-total');
                        var approvedEl = document.getElementById('profile-camps-stat-approved');
                        var pendingEl = document.getElementById('profile-camps-stat-pending');
                        if (totalEl) totalEl.textContent = data.camp_stats.total;
                        if (approvedEl) approvedEl.textContent = data.camp_stats.approved;
                        if (pendingEl) pendingEl.textContent = data.camp_stats.pending;
                        updateTabBadge(document.getElementById('profile-camps-badge'), data.camp_stats.total);
                    }

                    if (data.camp_id) {
                        removeSelectOption(campSelect, String(data.camp_id));
                    }

                    if (priceInput) {
                        priceInput.value = '';
                        priceInput.dataset.userEdited = '';
                    }
                    if (notesInput) notesInput.value = '';
                }).finally(function () {
                    setButtonLoading(addCampBtn, false);
                });
            });
        }

        var campStatusValues = ['pending', 'approved', 'rejected', 'cancelled'];
        var campPaymentValues = ['unpaid', 'paid', 'refunded'];

        function applyCampSelectStyle(selectEl, values) {
            if (!selectEl) return;
            var picker = selectEl.closest('.profile-camp-status-picker');
            values.forEach(function (value) {
                selectEl.classList.remove('is-' + value);
                if (picker) picker.classList.remove('is-' + value);
            });
            selectEl.classList.add('is-' + selectEl.value);
            if (picker) picker.classList.add('is-' + selectEl.value);
        }

        function setCampPickerLoading(selectEl, loading) {
            var picker = selectEl ? selectEl.closest('.profile-camp-status-picker') : null;
            if (picker) picker.classList.toggle('is-loading', loading);
        }

        function updateProfileCampStats(stats) {
            if (!stats) return;
            var totalEl = document.getElementById('profile-camps-stat-total');
            var approvedEl = document.getElementById('profile-camps-stat-approved');
            var pendingEl = document.getElementById('profile-camps-stat-pending');
            if (totalEl) totalEl.textContent = stats.total;
            if (approvedEl) approvedEl.textContent = stats.approved;
            if (pendingEl) pendingEl.textContent = stats.pending;
        }

        function initProfileCampRow(row) {
            if (!row) return;
            row.querySelectorAll('.profile-camp-status-select').forEach(function (el) {
                applyCampSelectStyle(el, campStatusValues);
            });
            row.querySelectorAll('.profile-camp-payment-select').forEach(function (el) {
                applyCampSelectStyle(el, campPaymentValues);
            });
        }

        function renumberProfileCampRows() {
            var tbody = document.getElementById('profile-camps-tbody');
            if (!tbody) return;
            tbody.querySelectorAll('.profile-camp-row').forEach(function (row, index) {
                var cell = row.querySelector('td:first-child');
                if (cell) cell.textContent = String(index + 1);
            });
        }

        function addCampSelectOption(selectEl, camp) {
            if (!selectEl || !camp) return;
            if (selectEl.querySelector('option[value="' + camp.id + '"]')) return;

            var option = document.createElement('option');
            option.value = String(camp.id);
            option.setAttribute('data-price', camp.price != null ? String(camp.price) : '0');
            option.textContent = camp.name + ' — ' + parseFloat(camp.price || 0).toFixed(2);
            selectEl.appendChild(option);

            var formWrap = document.getElementById('profile-add-camp-form');
            if (formWrap) formWrap.classList.remove('d-none');
        }

        var campsTbody = document.getElementById('profile-camps-tbody');
        if (campsTbody) {
            campsTbody.querySelectorAll('.profile-camp-row').forEach(initProfileCampRow);

            campsTbody.addEventListener('focusin', function (event) {
                var selectEl = event.target;
                if (selectEl.classList.contains('profile-camp-field-select')) {
                    selectEl.dataset.previousValue = selectEl.value;
                }
            });

            campsTbody.addEventListener('change', function (event) {
                var selectEl = event.target;
                if (!selectEl.classList.contains('profile-camp-field-select')) return;

                var row = selectEl.closest('.profile-camp-row');
                if (!row) return;

                var fieldName = selectEl.getAttribute('name');
                var previousValue = selectEl.dataset.previousValue;
                var updateUrl = row.getAttribute('data-update-url');
                var feedbackEl = document.getElementById('profile-camps-feedback');

                if (!fieldName || !updateUrl) return;

                var formData = new FormData();
                formData.append(fieldName, selectEl.value);

                selectEl.disabled = true;
                setCampPickerLoading(selectEl, true);
                hideFeedback(feedbackEl);

                fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    credentials: 'same-origin',
                    body: formData,
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        }).catch(function () {
                            return { ok: response.ok, data: {} };
                        });
                    })
                    .then(function (result) {
                        if (result.ok && result.data.success) {
                            selectEl.dataset.previousValue = selectEl.value;
                            applyCampSelectStyle(
                                selectEl,
                                selectEl.classList.contains('profile-camp-status-select') ? campStatusValues : campPaymentValues
                            );
                            updateProfileCampStats(result.data.camp_stats);
                            applyProfileBillingSideEffects(result.data);
                            showFeedback(feedbackEl, result.data.message, 'success');
                            return;
                        }

                        selectEl.value = previousValue;
                        applyCampSelectStyle(
                            selectEl,
                            selectEl.classList.contains('profile-camp-status-select') ? campStatusValues : campPaymentValues
                        );
                        showFeedback(feedbackEl, formatValidationErrors(result.data), 'error');
                    })
                    .catch(function () {
                        selectEl.value = previousValue;
                        applyCampSelectStyle(
                            selectEl,
                            selectEl.classList.contains('profile-camp-status-select') ? campStatusValues : campPaymentValues
                        );
                        showFeedback(feedbackEl, 'تعذر تحديث الحالة. حاول مرة أخرى.', 'error');
                    })
                    .finally(function () {
                        selectEl.disabled = false;
                        setCampPickerLoading(selectEl, false);
                    });
            });

            campsTbody.addEventListener('click', function (event) {
                var btn = event.target.closest('.profile-remove-camp-btn');
                if (!btn) return;

                var row = btn.closest('.profile-camp-row');
                var removeUrl = btn.getAttribute('data-remove-url');
                var campName = btn.getAttribute('data-camp-name') || 'هذا المعسكر';
                var feedbackEl = document.getElementById('profile-camps-feedback');
                var campSelect = document.getElementById('profile_camp_id');

                if (!row || !removeUrl) return;

                if (!window.confirm('هل أنت متأكد من إلغاء تسجيل الطالب في المعسكر «' + campName + '»؟')) {
                    return;
                }

                btn.disabled = true;
                hideFeedback(feedbackEl);

                fetch(removeUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    credentials: 'same-origin',
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        }).catch(function () {
                            return { ok: response.ok, data: {} };
                        });
                    })
                    .then(function (result) {
                        if (result.ok && result.data.success) {
                            row.remove();
                            renumberProfileCampRows();

                            var tbody = document.getElementById('profile-camps-tbody');
                            var tableWrap = document.getElementById('profile-camps-table-wrap');
                            var emptyEl = document.getElementById('profile-camps-empty');
                            var statsWrap = document.getElementById('profile-camps-stats');
                            var hasRows = tbody && tbody.querySelectorAll('.profile-camp-row').length > 0;

                            if (tableWrap) tableWrap.classList.toggle('d-none', !hasRows);
                            if (emptyEl) emptyEl.classList.toggle('d-none', hasRows);
                            if (statsWrap) statsWrap.classList.toggle('d-none', !hasRows);

                            updateProfileCampStats(result.data.camp_stats);
                            updateTabBadge(document.getElementById('profile-camps-badge'), result.data.camp_stats.total);

                            if (result.data.camp && campSelect) {
                                addCampSelectOption(campSelect, result.data.camp);
                            }

                            applyProfileBillingSideEffects(result.data);
                            showFeedback(feedbackEl, result.data.message, 'success');
                            return;
                        }

                        showFeedback(feedbackEl, formatValidationErrors(result.data), 'error');
                    })
                    .catch(function () {
                        showFeedback(feedbackEl, 'تعذر إلغاء التسجيل. حاول مرة أخرى.', 'error');
                    })
                    .finally(function () {
                        btn.disabled = false;
                    });
            });
        }

        window.initProfileCampRow = initProfileCampRow;

        function updateBillingStats(stats) {
            if (!stats) return;
            var el;
            el = document.getElementById('profile-billing-stat-total-invoices');
            if (el) el.textContent = stats.total_invoices;
            el = document.getElementById('profile-billing-stat-total-amount');
            if (el) el.textContent = Number(stats.total_amount).toFixed(2);
            el = document.getElementById('profile-billing-stat-total-paid');
            if (el) el.textContent = Number(stats.total_paid).toFixed(2);
            el = document.getElementById('profile-billing-stat-remaining');
            if (el) el.textContent = Number(stats.remaining_amount).toFixed(2);
        }

        function markProfileInvoicesCancelled(invoiceIds) {
            if (!invoiceIds || !invoiceIds.length) return;

            invoiceIds.forEach(function (invoiceId) {
                var row = document.getElementById('profile-invoice-row-' + invoiceId);
                if (!row) return;

                var remainingCell = row.querySelector('td:nth-child(5)');
                if (remainingCell) remainingCell.textContent = '0.00';

                var statusChip = row.querySelector('.group-show-chip');
                if (statusChip) {
                    statusChip.textContent = 'ملغاة';
                    statusChip.classList.remove('text-success', 'text-warning', 'text-info');
                    statusChip.classList.add('text-danger');
                }

                var actionsCell = row.querySelector('td:last-child');
                if (actionsCell) {
                    actionsCell.innerHTML = '<span class="text-muted">—</span>';
                }

                var paymentSelect = document.getElementById('profilePaymentInvoiceId');
                if (paymentSelect) {
                    var option = paymentSelect.querySelector('option[value="' + invoiceId + '"]');
                    if (option) option.remove();
                }
            });
        }

        function applyProfileBillingSideEffects(data) {
            if (data.billing_stats) {
                updateBillingStats(data.billing_stats);
            }
            if (data.cancelled_invoice_ids) {
                markProfileInvoicesCancelled(data.cancelled_invoice_ids);
            }
        }

        function syncProfilePaymentRemaining() {
            var select = document.getElementById('profilePaymentInvoiceId');
            var amountInput = document.getElementById('profilePaymentAmount');
            var remainingEl = document.getElementById('profilePaymentRemainingValue');
            if (!select || !select.selectedOptions.length) return;
            var remaining = parseFloat(select.selectedOptions[0].getAttribute('data-remaining') || '0') || 0;
            if (remainingEl) remainingEl.textContent = remaining.toFixed(2);
            if (amountInput && (!amountInput.dataset.userEdited || amountInput.value === '')) {
                amountInput.value = remaining > 0 ? remaining.toFixed(2) : '';
            }
        }

        function openProfileRecordPaymentModal(trigger) {
            var modalEl = document.getElementById('profileRecordPaymentModal');
            if (!modalEl || typeof window.bootstrap === 'undefined') return;

            var invoiceSelect = document.getElementById('profilePaymentInvoiceId');
            var amountInput = document.getElementById('profilePaymentAmount');
            var titleEl = modalEl.querySelector('.modal-title');
            if (!invoiceSelect) return;

            if (trigger && trigger.getAttribute('data-invoice-id')) {
                invoiceSelect.value = String(trigger.getAttribute('data-invoice-id'));
            }

            if (amountInput) {
                amountInput.dataset.userEdited = '';
                if (trigger && trigger.getAttribute('data-remaining')) {
                    amountInput.value = parseFloat(trigger.getAttribute('data-remaining')).toFixed(2);
                }
            }

            if (titleEl) {
                var campName = trigger && trigger.getAttribute('data-camp-name');
                titleEl.innerHTML = campName
                    ? '<i class="fe fe-dollar-sign me-2"></i>تسجيل دفعة — {{ $user->name }} — ' + campName
                    : '<i class="fe fe-dollar-sign me-2"></i>تسجيل دفعة — {{ $user->name }}';
            }

            syncProfilePaymentRemaining();

            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function refreshProfileCampRow(enrollmentId, rowHtml) {
            if (!enrollmentId || !rowHtml) return;
            var existingRow = document.querySelector('.profile-camp-row[data-enrollment-id="' + enrollmentId + '"]');
            if (!existingRow) return;
            existingRow.outerHTML = rowHtml;
            initProfileCampRow(document.querySelector('.profile-camp-row[data-enrollment-id="' + enrollmentId + '"]'));
        }

        document.addEventListener('click', function (e) {
            var payBtn = e.target.closest('.js-profile-record-payment');
            if (payBtn) {
                e.preventDefault();
                openProfileRecordPaymentModal(payBtn);
            }
        });

        var profilePaymentInvoiceSelect = document.getElementById('profilePaymentInvoiceId');
        if (profilePaymentInvoiceSelect) {
            profilePaymentInvoiceSelect.addEventListener('change', function () {
                var amountInput = document.getElementById('profilePaymentAmount');
                if (amountInput) amountInput.dataset.userEdited = '';
                syncProfilePaymentRemaining();
            });
        }

        var profilePaymentAmount = document.getElementById('profilePaymentAmount');
        if (profilePaymentAmount) {
            profilePaymentAmount.addEventListener('input', function () {
                profilePaymentAmount.dataset.userEdited = '1';
            });
        }

        var profileRecordPaymentForm = document.getElementById('profileRecordPaymentForm');
        if (profileRecordPaymentForm) {
            profileRecordPaymentForm.addEventListener('submit', function (e) {
                e.preventDefault();

                var submitBtn = document.getElementById('profileRecordPaymentSubmit');
                var feedbackEl = document.getElementById('profile-billing-feedback');
                var invoiceSelect = document.getElementById('profilePaymentInvoiceId');
                var amountInput = document.getElementById('profilePaymentAmount');

                if (!invoiceSelect || !invoiceSelect.value) {
                    showFeedback(feedbackEl, 'يرجى اختيار فاتورة.', 'error');
                    return;
                }

                var formData = new FormData();
                formData.append('invoice_id', invoiceSelect.value);
                formData.append('amount', amountInput ? amountInput.value : '');
                formData.append('payment_method_id', document.getElementById('profilePaymentMethodId').value);
                formData.append('payment_date', document.getElementById('profilePaymentDate').value);

                var transactionId = document.getElementById('profilePaymentTransactionId');
                if (transactionId && transactionId.value.trim()) {
                    formData.append('transaction_id', transactionId.value.trim());
                }
                var notes = document.getElementById('profilePaymentNotes');
                if (notes && notes.value.trim()) {
                    formData.append('notes', notes.value.trim());
                }

                setButtonLoading(submitBtn, true);
                postProfileAction('{{ route('users.record-payment', $user->id) }}', formData, feedbackEl, function (data) {
                    updateBillingStats(data.billing_stats);

                    if (data.invoice_id && data.invoice_row_html) {
                        var existingRow = document.getElementById('profile-invoice-row-' + data.invoice_id);
                        if (existingRow) {
                            existingRow.outerHTML = data.invoice_row_html;
                        }
                    }

                    if (data.payment_row_html) {
                        var paymentsTbody = document.getElementById('profile-payments-tbody');
                        var paymentsEmpty = document.getElementById('profile-payments-empty');
                        var paymentsWrap = document.getElementById('profile-payments-table-wrap');
                        if (paymentsTbody) {
                            paymentsTbody.insertAdjacentHTML('afterbegin', data.payment_row_html);
                        }
                        if (paymentsEmpty) paymentsEmpty.classList.add('d-none');
                        if (paymentsWrap) paymentsWrap.classList.remove('d-none');
                    }

                    if (data.camp_enrollment_id && data.camp_row_html) {
                        refreshProfileCampRow(data.camp_enrollment_id, data.camp_row_html);
                    }

                    var modalEl = document.getElementById('profileRecordPaymentModal');
                    if (modalEl) {
                        var modal = window.bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }

                    if (invoiceSelect && data.invoice_id) {
                        var paidOption = invoiceSelect.querySelector('option[value="' + data.invoice_id + '"]');
                        if (paidOption && parseFloat(paidOption.getAttribute('data-remaining') || '0') <= parseFloat(amountInput ? amountInput.value : '0')) {
                            paidOption.remove();
                        } else if (paidOption && data.billing_stats) {
                            var newRemaining = parseFloat(paidOption.getAttribute('data-remaining') || '0') - parseFloat(amountInput ? amountInput.value : '0');
                            if (newRemaining > 0) {
                                paidOption.setAttribute('data-remaining', newRemaining.toFixed(2));
                                paidOption.textContent = paidOption.getAttribute('data-invoice-number') + ' — المتبقي: ' + newRemaining.toFixed(2);
                            } else {
                                paidOption.remove();
                            }
                        }
                        if (invoiceSelect.options.length <= 1) {
                            var openBtn = document.getElementById('profile-open-payment-modal-btn');
                            if (openBtn) openBtn.classList.add('d-none');
                        }
                    }

                    if (amountInput) amountInput.value = '';
                    if (transactionId) transactionId.value = '';
                    if (notes) notes.value = '';
                }).finally(function () {
                    setButtonLoading(submitBtn, false);
                });
            });
        }
    })();
</script>
@endpush
