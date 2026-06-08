@extends('admin.layouts.master')

@section('page-title')
    {{ $group->name }} - تفاصيل المجموعة
@stop

@section('css')
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            
            <!-- Alerts -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 mt-3" role="alert" style="font-size: 1.1rem; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3); border-left: 4px solid #28a745; background-color: #d4edda !important; color: #155724 !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fs-4" style="color: #28a745;"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1" style="color: #155724;">نجح!</strong>
                            <span style="color: #155724;">{{ session('success') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4 mt-3" role="alert" style="font-size: 1.1rem; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); border-left: 4px solid #dc3545; background-color: #f8d7da !important; color: #721c24 !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fs-4" style="color: #dc3545;"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1" style="color: #721c24;">خطأ!</strong>
                            <span style="color: #721c24;">{{ session('error') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-4 mt-3" role="alert" style="font-size: 1.1rem; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3); border-left: 4px solid #ffc107; background-color: #fff3cd !important; color: #856404 !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-3 fs-4" style="color: #ffc107;"></i>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1" style="color: #856404;">تحذير!</strong>
                            <span style="color: #856404;">{{ session('warning') }}</span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            <!-- Breadcrumb -->
            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('groups.all') }}">المجموعات</a></li>
                        @if($course)
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                        @endif
                        <li class="breadcrumb-item active">{{ $group->name }}</li>
                    </ol>
                </nav>
            </div>

            @include('admin.pages.groups.partials.show-hero', ['group' => $group, 'course' => $course])

            @include('admin.pages.groups.partials.show-stats', ['group' => $group, 'stats' => $stats])

            @include('admin.pages.groups.partials.show-visibility', ['group' => $group])

            <div class="row">
                <div class="col-lg-12">
                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <h6 class="group-show-members-card__title mb-0">
                                أعضاء المجموعة
                                <span class="group-show-members-card__count">{{ $stats['total_members'] ?? 0 }}</span>
                            </h6>
                            <div class="group-show-member-actions">
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                                    <i class="fe fe-user-plus me-1"></i>إضافة عضو
                                </button>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addBulkMembersModal">
                                    <i class="fe fe-users me-1"></i>إضافة سريعة
                                </button>
                                <a href="{{ route('groups.bulk-enroll-page', $group->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fe fe-filter me-1"></i>إضافة متقدمة
                                </a>
                            </div>
                        </div>

                        <div class="group-show-bulk-bar" id="bulkActionsBar" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-primary" id="selectedCount">0</span>
                                    <span class="text-muted">عضو محدد</span>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm" id="bulkRemoveBtn" disabled>
                                        <i class="fas fa-user-times me-2"></i>فك الارتباط (<span id="bulkRemoveCount">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm ms-2" id="clearSelectionBtn">
                                        <i class="fas fa-times me-2"></i>إلغاء التحديد
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form method="GET" action="{{ $course ? route('courses.groups.show', [$course->id, $group->id]) : route('groups.show', $group->id) }}" class="group-show-filters" id="groupMembersFilterForm">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label">البحث</label>
                                        <input type="text" name="search" class="form-control" id="groupMembersSearchInput"
                                               placeholder="ابحث بالاسم، الإيميل أو الهاتف..."
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">المجموعة الأخرى</label>
                                        <select name="other_group_id" class="form-select">
                                            <option value="">جميع المجموعات</option>
                                            @foreach($allGroups as $otherGroup)
                                                <option value="{{ $otherGroup->id }}" {{ request('other_group_id') == $otherGroup->id ? 'selected' : '' }}>
                                                    {{ $otherGroup->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">عدد المجموعات</label>
                                        <select name="groups_count" class="form-select">
                                            <option value="">جميع</option>
                                            <option value="0" {{ request('groups_count') == '0' ? 'selected' : '' }}>لا يوجد</option>
                                            <option value="1" {{ request('groups_count') == '1' ? 'selected' : '' }}>مجموعة واحدة</option>
                                            <option value="2" {{ request('groups_count') == '2' ? 'selected' : '' }}>مجموعتين أو أكثر</option>
                                            <option value="3" {{ request('groups_count') == '3' ? 'selected' : '' }}>3 مجموعات أو أكثر</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">حالة الاتصال</label>
                                        <select name="online_status" class="form-select">
                                            <option value="">الكل</option>
                                            <option value="online" {{ request('online_status') == 'online' ? 'selected' : '' }}>المتصلون فقط</option>
                                            <option value="offline" {{ request('online_status') == 'offline' ? 'selected' : '' }}>غير المتصلين فقط</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">تسجيل الدخول</label>
                                        <select name="login_status" class="form-select">
                                            <option value="">الكل</option>
                                            <option value="never" {{ request('login_status') === 'never' ? 'selected' : '' }}>لم يسجّل دخولاً بعد</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label">الترتيب حسب</label>
                                                <select name="sort" class="form-select">
                                                    <option value="joined_at" {{ request('sort', 'joined_at') == 'joined_at' ? 'selected' : '' }}>تاريخ الانضمام</option>
                                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>تاريخ العضوية في المجموعة</option>
                                                    <option value="last_login_at" {{ request('sort') == 'last_login_at' ? 'selected' : '' }}>آخر تسجيل دخول</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">الاتجاه</label>
                                                <select name="order" class="form-select">
                                                    <option value="desc" {{ request('order', 'desc') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                                                    <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                                                </select>
                                            </div>
                                            <div class="col-md-7 mt-md-4">
                                                <div class="d-flex flex-wrap gap-2">
                                                    <button type="submit" class="btn btn-primary" id="groupMembersSearchBtn">
                                                        <i class="fas fa-search me-1"></i>بحث
                                                    </button>
                                                    <a href="{{ $course ? route('courses.groups.show', [$course->id, $group->id]) : route('groups.show', $group->id) }}" class="btn btn-outline-secondary" title="إعادة تعيين" id="groupMembersResetBtn">
                                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div id="groupMembersFilterFeedback" class="small text-muted mb-2"></div>
                            <div id="groupMembersTableContainer">
                                @include('admin.pages.groups.partials.members-table', ['members' => $members, 'group' => $group, 'course' => $course, 'stats' => $stats ?? [], 'lastActivityByUserId' => $lastActivityByUserId ?? [], 'onlineUserIds' => $onlineUserIds ?? [], 'dueAmountsByStudentId' => $dueAmountsByStudentId ?? [], 'studentOutstandingInvoicesById' => $studentOutstandingInvoicesById ?? [], 'studentPaymentsById' => $studentPaymentsById ?? [], 'studentPaidTotalsById' => $studentPaidTotalsById ?? [], 'paymentMethods' => $paymentMethods ?? collect(), 'trainingCampsForModal' => $trainingCampsForModal ?? collect()])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('groups.add-member', $group->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة عضو جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اختر الطالب</label>
                            <select name="student_id" id="singleStudentSelect" class="form-select" required>
                                <option value="">-- اختر طالب --</option>
                                @foreach($availableStudents ?? [] as $student)
                                    @php
                                        $displayName = $student->name;
                                        if ($student->name_ar) {
                                            $displayName .= ' (' . $student->name_ar . ')';
                                        }
                                        $displayName .= ' - ' . $student->email;
                                    @endphp
                                    <option value="{{ $student->id }}">{{ $displayName }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-search me-1"></i>
                                ابدأ بالكتابة للبحث عن الطالب بالاسم (عربي/إنجليزي) أو البريد الإلكتروني
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الدور</label>
                            <select name="role" class="form-select" required>
                                <option value="member">عضو</option>
                                <option value="leader">قائد</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>إضافة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(($trainingCampsForModal ?? collect())->isNotEmpty())
    <div class="modal fade" id="attachTrainingCampModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-campground me-2"></i>
                        إضافة الطالب إلى معسكر تدريبي
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        الطالب: <strong id="attachCampStudentName">—</strong>
                    </p>
                    <input type="hidden" id="attachCampStudentId" value="">
                    <div class="mb-3">
                        <label class="form-label" for="attachCampSelect">اختر المعسكر</label>
                        <select class="form-select" id="attachCampSelect">
                            <option value="">— اختر معسكراً —</option>
                            @foreach($trainingCampsForModal as $tc)
                                <option value="{{ $tc->id }}">
                                    {{ $tc->name }}
                                    @if($tc->start_date)
                                        ({{ $tc->start_date->format('Y-m-d') }})
                                    @endif
                                    — ${{ number_format((float) $tc->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="attachCampDetails" class="border rounded p-3 bg-light bg-opacity-10 small d-none">
                        <div id="attachCampDetailsLoading" class="text-muted d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>جاري جلب تفاصيل المعسكر...
                        </div>
                        <div id="attachCampDetailsContent" class="d-none"></div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="attachCampNotes">ملاحظات (اختياري)</label>
                        <textarea class="form-control" id="attachCampNotes" rows="2" maxlength="1000" placeholder="ملاحظات التسجيل"></textarea>
                    </div>
                    <div class="alert alert-danger py-2 d-none" id="attachCampFormError" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="attachCampSubmitBtn" disabled>
                        <i class="fas fa-check me-1"></i>تأكيد التسجيل وإصدار الفاتورة
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Bulk Members Modal -->
    <div class="modal fade" id="addBulkMembersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('groups.add-bulk-members', $group->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">إضافة أعضاء جماعياً</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">اختر الطلاب</label>
                            <select name="student_ids[]" id="bulkStudentSelect" class="form-select" multiple required>
                                @foreach($availableStudents ?? [] as $student)
                                    @php
                                        $displayName = $student->name;
                                        if ($student->name_ar) {
                                            $displayName .= ' (' . $student->name_ar . ')';
                                        }
                                        $displayName .= ' - ' . $student->email;
                                    @endphp
                                    <option value="{{ $student->id }}">{{ $displayName }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-search me-1"></i>
                                ابدأ بالكتابة للبحث عن الطلاب بالاسم (عربي/إنجليزي) أو البريد الإلكتروني. يمكنك اختيار عدة طلاب.
                            </small>
                            <div id="bulkSelectedCount" class="mt-2 text-primary" style="display: none;">
                                <i class="fas fa-users me-1"></i>
                                <span id="bulkSelectedCountText">0</span> طالب محدد
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الدور الافتراضي</label>
                            <select name="default_role" class="form-select" required>
                                <option value="member" selected>عضو</option>
                                <option value="leader">قائد</option>
                            </select>
                            <small class="text-muted">سيتم تعيين هذا الدور لجميع الطلاب المحددين</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            سيتم إضافة جميع الطلاب المحددين بنفس الدور
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-users me-2"></i>إضافة الكل
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Record payment for member (from members table) -->
    <div class="modal fade" id="recordGroupMemberPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="recordGroupMemberPaymentForm">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-dollar-sign me-2"></i>
                            تسجيل دفعة — <span id="recordPaymentStudentName"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="recordPaymentStudentId" value="">
                        @if(($paymentMethods ?? collect())->isEmpty())
                            <div class="alert alert-warning mb-0">
                                لا توجد طرق دفع مفعّلة. أضف طريقة دفع من إعدادات النظام أولاً.
                            </div>
                        @else
                            <div id="recordPaymentTotalDueBanner" class="alert alert-info py-2 small mb-3" role="status">
                                <strong>إجمالي المستحق على الطالب:</strong>
                                $<span id="recordPaymentTotalDueValue">0.00</span>
                                <span id="recordPaymentTotalDueHint" class="d-block mt-2 mb-0 text-muted"></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="recordPaymentInvoiceId">الفاتورة</label>
                                <select class="form-select" id="recordPaymentInvoiceId" required></select>
                                <small class="text-muted">المبلغ الافتراضي = المتبقي على <strong>هذه</strong> الفاتورة فقط (ليس بالضرورة كامل الدين إن وُجدت فواتير أخرى).</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="recordPaymentAmount">المبلغ (لهذه الفاتورة)</label>
                                <input type="number" class="form-control" id="recordPaymentAmount" step="0.01" min="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="recordPaymentMethodId">طريقة الدفع</label>
                                <select class="form-select" id="recordPaymentMethodId" required>
                                    @foreach(($paymentMethods ?? collect()) as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="recordPaymentDate">تاريخ الدفع</label>
                                <input type="date" class="form-control" id="recordPaymentDate" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="recordPaymentTransactionId">رقم العملية (اختياري)</label>
                                <input type="text" class="form-control" id="recordPaymentTransactionId" autocomplete="off">
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="recordPaymentNotes">ملاحظات (اختياري)</label>
                                <textarea class="form-control" id="recordPaymentNotes" rows="2"></textarea>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        @if(($paymentMethods ?? collect())->isNotEmpty())
                            <button type="submit" class="btn btn-success" id="recordGroupMemberPaymentSubmit">
                                <i class="fas fa-check me-1"></i>تسجيل الدفعة
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.partials.impersonate-student')

@stop

@section('script')
<script>
    function animateGroupShowCountup(el, target, duration) {
        const start = performance.now();
        const from = 0;
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(from + (target - from) * eased);
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('[data-countup]').forEach(function(el) {
        const target = parseFloat(el.dataset.countup || '0');
        if (!isNaN(target)) {
            animateGroupShowCountup(el, target, 900);
        }
    });

    // Scroll to top to show alert message
    if (document.querySelector('.alert')) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Auto-hide alerts after 10 seconds (longer for bulk operations)
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 10000);

    function initGroupMembersSelection() {
        const selectAllCheckbox = document.getElementById('selectAllMembers');
        const memberCheckboxes = document.querySelectorAll('.member-checkbox');
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkRemoveBtn = document.getElementById('bulkRemoveBtn');
        const bulkRemoveCountSpan = document.getElementById('bulkRemoveCount');
        const clearSelectionBtn = document.getElementById('clearSelectionBtn');

        // Update bulk actions bar visibility and counts
        function updateBulkActions() {
            const selected = document.querySelectorAll('.member-checkbox:checked');
            const count = selected.length;
            
            if (!bulkActionsBar || !selectedCountSpan || !bulkRemoveCountSpan || !bulkRemoveBtn) {
                return;
            }

            if (count > 0) {
                bulkActionsBar.style.display = 'block';
                selectedCountSpan.textContent = count;
                bulkRemoveCountSpan.textContent = count;
                bulkRemoveBtn.disabled = false;
            } else {
                bulkActionsBar.style.display = 'none';
                bulkRemoveBtn.disabled = true;
            }

            // Update select all checkbox state
            if (selectAllCheckbox) {
                selectAllCheckbox.indeterminate = count > 0 && count < memberCheckboxes.length;
                selectAllCheckbox.checked = count === memberCheckboxes.length && count > 0;
            }
        }

        // Select all checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                memberCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });
        }

        // Individual checkboxes
        memberCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Clear selection
        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function() {
                memberCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
                updateBulkActions();
            });
        }

        // Bulk remove
        if (bulkRemoveBtn) {
            bulkRemoveBtn.addEventListener('click', function() {
                const selected = Array.from(document.querySelectorAll('.member-checkbox:checked'));
                const selectedIds = selected.map(cb => cb.value);
                const selectedNames = selected.map(cb => cb.getAttribute('data-member-name'));

                if (selectedIds.length === 0) {
                    alert('يرجى تحديد عضو واحد على الأقل');
                    return;
                }

                const confirmMessage = `هل أنت متأكد من إزالة ${selectedIds.length} عضو من المجموعة؟\n\nالأعضاء:\n${selectedNames.join('\n')}\n\nسيتم أيضاً إلغاء تسجيلهم من الكورسات المرتبطة بهذه المجموعة.`;

                if (confirm(confirmMessage)) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("groups.bulk-remove-members", $group->id) }}';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'member_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Initial update
        updateBulkActions();
    }

    function initGroupMembersAjaxFilters() {
        const form = document.getElementById('groupMembersFilterForm');
        const container = document.getElementById('groupMembersTableContainer');
        const feedback = document.getElementById('groupMembersFilterFeedback');
        const searchInput = document.getElementById('groupMembersSearchInput');
        const resetBtn = document.getElementById('groupMembersResetBtn');
        if (!form || !container) return;

        let activeController = null;
        let debounceTimer = null;

        const loadMembers = async (url = null) => {
            if (activeController) activeController.abort();
            activeController = new AbortController();

            const params = new URLSearchParams(new FormData(form));
            const targetUrl = url || `${form.action}?${params.toString()}`;
            if (feedback) feedback.textContent = 'جاري تحميل النتائج...';

            try {
                const response = await fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: activeController.signal
                });
                if (!response.ok) throw new Error('Failed request');
                const data = await response.json();
                container.innerHTML = data.table_html || '';
                if (feedback) feedback.textContent = '';
                initGroupMembersSelection();
            } catch (error) {
                if (error.name === 'AbortError') return;
                if (feedback) feedback.textContent = 'حدث خطأ أثناء تحميل البيانات';
            }
        };

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            loadMembers();
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadMembers(), 450);
            });
        }

        form.querySelectorAll('select, input[type="date"]').forEach((element) => {
            element.addEventListener('change', () => loadMembers());
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                loadMembers(form.action);
            });
        }

        container.addEventListener('click', function (e) {
            const link = e.target.closest('.pagination a');
            if (!link) return;
            e.preventDefault();
            loadMembers(link.href);
        });

        window.refreshGroupMembersTable = function () {
            loadMembers();
        };
    }

    initGroupMembersSelection();
    initGroupMembersAjaxFilters();

    const groupMemberPaymentBaseUrl = @json(url('/admin/groups/' . $group->id . '/members'));
    @if(($trainingCampsForModal ?? collect())->isNotEmpty())
    @php
        $campModalDataUrlTemplateJs = str_replace(
            '999999999',
            '__CAMP_ID__',
            route('training-camps.modal-data', ['camp' => 999999999])
        );
        $enrollCampUrlTemplateJs = str_replace(
            '999999999',
            '__USER_ID__',
            route('groups.members.training-camp-enrollment', ['group' => $group->id, 'user' => 999999999])
        );
    @endphp
    const campModalDataUrlTemplate = @json($campModalDataUrlTemplateJs);
    const enrollCampUrlTemplate = @json($enrollCampUrlTemplateJs);

    function attachCampEscapeHtml(text) {
        if (!text) return '';
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function openAttachCampModal(btn) {
        const modalEl = document.getElementById('attachTrainingCampModal');
        if (!modalEl) return;
        const studentId = btn.getAttribute('data-student-id') || '';
        const studentName = btn.getAttribute('data-student-name') || '';
        document.getElementById('attachCampStudentId').value = studentId;
        const nameEl = document.getElementById('attachCampStudentName');
        if (nameEl) nameEl.textContent = studentName || '—';
        const sel = document.getElementById('attachCampSelect');
        if (sel) sel.value = '';
        const notes = document.getElementById('attachCampNotes');
        if (notes) notes.value = '';
        const err = document.getElementById('attachCampFormError');
        if (err) {
            err.classList.add('d-none');
            err.textContent = '';
        }
        const details = document.getElementById('attachCampDetails');
        const loading = document.getElementById('attachCampDetailsLoading');
        const content = document.getElementById('attachCampDetailsContent');
        if (details) details.classList.add('d-none');
        if (loading) loading.classList.add('d-none');
        if (content) {
            content.classList.add('d-none');
            content.innerHTML = '';
        }
        const submitBtn = document.getElementById('attachCampSubmitBtn');
        if (submitBtn) submitBtn.disabled = true;
        let m = bootstrap.Modal.getInstance(modalEl);
        if (!m) m = new bootstrap.Modal(modalEl);
        m.show();
    }

    async function loadAttachCampDetails(campId) {
        const details = document.getElementById('attachCampDetails');
        const loading = document.getElementById('attachCampDetailsLoading');
        const content = document.getElementById('attachCampDetailsContent');
        const submitBtn = document.getElementById('attachCampSubmitBtn');
        if (!details || !loading || !content) return;
        details.classList.remove('d-none');
        loading.classList.remove('d-none');
        content.classList.add('d-none');
        content.innerHTML = '';
        if (submitBtn) submitBtn.disabled = true;
        const url = campModalDataUrlTemplate.replace('__CAMP_ID__', String(campId));
        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json().catch(function () { return {}; });
            loading.classList.add('d-none');
            if (!response.ok || !data.success || !data.camp) {
                content.innerHTML = '<p class="text-danger mb-0">تعذر تحميل تفاصيل المعسكر.</p>';
                content.classList.remove('d-none');
                return;
            }
            const c = data.camp;
            const descHtml = attachCampEscapeHtml(c.description || '').replace(/\n/g, '<br>');
            const cat = c.category_name ? attachCampEscapeHtml(c.category_name) : '—';
            const instructor = c.instructor_name ? attachCampEscapeHtml(c.instructor_name) : '—';
            const loc = c.location ? attachCampEscapeHtml(c.location) : '—';
            const maxP = c.max_participants != null ? attachCampEscapeHtml(String(c.max_participants)) : '—';
            const showUrlAttr = String(c.show_url || '#').replace(/"/g, '&quot;');
            content.innerHTML =
                '<dl class="row mb-0">' +
                '<dt class="col-sm-4">الاسم</dt><dd class="col-sm-8">' + attachCampEscapeHtml(c.name || '') + '</dd>' +
                '<dt class="col-sm-4">السعر</dt><dd class="col-sm-8"><strong>$' + Number(c.price).toFixed(2) + '</strong></dd>' +
                '<dt class="col-sm-4">تاريخ البداية / النهاية</dt><dd class="col-sm-8">' + attachCampEscapeHtml(c.start_date || '') + ' — ' + attachCampEscapeHtml(c.end_date || '') + '</dd>' +
                '<dt class="col-sm-4">المدة (أيام)</dt><dd class="col-sm-8">' + (c.duration_days != null ? attachCampEscapeHtml(String(c.duration_days)) : '—') + '</dd>' +
                '<dt class="col-sm-4">التصنيف</dt><dd class="col-sm-8">' + cat + '</dd>' +
                '<dt class="col-sm-4">المدرب</dt><dd class="col-sm-8">' + instructor + '</dd>' +
                '<dt class="col-sm-4">الموقع</dt><dd class="col-sm-8">' + loc + '</dd>' +
                '<dt class="col-sm-4">المشاركون</dt><dd class="col-sm-8">' + (c.current_participants != null ? c.current_participants : '0') + ' / ' + maxP + ' (تسجيلات: ' + (c.enrollments_count != null ? c.enrollments_count : '0') + ')</dd>' +
                '<dt class="col-sm-4">الحالة</dt><dd class="col-sm-8">' + (c.is_active ? 'نشط' : 'غير نشط') + '</dd>' +
                '<dt class="col-sm-4">الوصف</dt><dd class="col-sm-8">' + (descHtml || '<span class="text-muted">—</span>') + '</dd>' +
                '</dl>' +
                '<p class="mb-0 mt-3"><a href="' + showUrlAttr + '" target="_blank" rel="noopener">فتح صفحة المعسكر <i class="fas fa-external-link-alt fa-xs"></i></a></p>';
            content.classList.remove('d-none');
            if (submitBtn) submitBtn.disabled = false;
        } catch (e) {
            loading.classList.add('d-none');
            content.innerHTML = '<p class="text-danger mb-0">حدث خطأ في الاتصال.</p>';
            content.classList.remove('d-none');
        }
    }

    (function initAttachTrainingCampModal() {
        const sel = document.getElementById('attachCampSelect');
        if (!sel) return;
        sel.addEventListener('change', function () {
            const id = sel.value;
            if (!id) {
                const details = document.getElementById('attachCampDetails');
                const submitBtn = document.getElementById('attachCampSubmitBtn');
                if (details) details.classList.add('d-none');
                if (submitBtn) submitBtn.disabled = true;
                return;
            }
            loadAttachCampDetails(id);
        });
        const submitBtn = document.getElementById('attachCampSubmitBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', async function () {
                const studentId = document.getElementById('attachCampStudentId').value;
                const campId = document.getElementById('attachCampSelect').value;
                const notesEl = document.getElementById('attachCampNotes');
                const err = document.getElementById('attachCampFormError');
                if (!studentId || !campId) return;
                const url = enrollCampUrlTemplate.replace('__USER_ID__', String(studentId));
                submitBtn.disabled = true;
                const orig = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الحفظ...';
                if (err) {
                    err.classList.add('d-none');
                    err.textContent = '';
                }
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            camp_id: parseInt(campId, 10),
                            notes: notesEl && notesEl.value ? notesEl.value : null,
                        }),
                    });
                    const data = await response.json().catch(function () { return {}; });
                    if (response.ok && data.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(data.message || 'تم التسجيل');
                        } else {
                            alert(data.message || 'تم التسجيل');
                        }
                        const modalEl = document.getElementById('attachTrainingCampModal');
                        const m = bootstrap.Modal.getInstance(modalEl);
                        if (m) m.hide();
                        if (typeof window.refreshGroupMembersTable === 'function') {
                            window.refreshGroupMembersTable();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        let msg = data.message || 'فشل التسجيل';
                        if (data.errors) {
                            const first = Object.values(data.errors)[0];
                            if (Array.isArray(first) && first[0]) msg = first[0];
                        }
                        if (err) {
                            err.textContent = msg;
                            err.classList.remove('d-none');
                        } else if (typeof toastr !== 'undefined') {
                            toastr.error(msg);
                        } else {
                            alert(msg);
                        }
                    }
                } catch (e) {
                    if (err) {
                        err.textContent = 'حدث خطأ في الاتصال';
                        err.classList.remove('d-none');
                    }
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = orig;
                }
            });
        }
    })();
    @endif

    function openRecordGroupMemberPaymentModal(btn) {
        const studentId = btn.getAttribute('data-student-id');
        const studentName = btn.getAttribute('data-student-name') || '';
        let invoices = [];
        try {
            invoices = JSON.parse(btn.getAttribute('data-invoices') || '[]');
        } catch (err) {
            invoices = [];
        }
        const modalEl = document.getElementById('recordGroupMemberPaymentModal');
        const submitBtn = document.getElementById('recordGroupMemberPaymentSubmit');
        if (!modalEl) {
            return;
        }

        const nameEl = document.getElementById('recordPaymentStudentName');
        if (nameEl) {
            nameEl.textContent = studentName;
        }
        const sid = document.getElementById('recordPaymentStudentId');
        if (sid) {
            sid.value = studentId || '';
        }

        const totalDue = parseFloat(btn.getAttribute('data-total-due') || '0') || 0;
        const totalDueValEl = document.getElementById('recordPaymentTotalDueValue');
        const totalDueHintEl = document.getElementById('recordPaymentTotalDueHint');
        if (totalDueValEl) {
            totalDueValEl.textContent = totalDue.toFixed(2);
        }
        if (totalDueHintEl) {
            let sumInv = 0;
            invoices.forEach(function (inv) {
                sumInv += Number(inv.remaining_amount || 0);
            });
            if (invoices.length > 1) {
                totalDueHintEl.textContent =
                    'لدى الطالب ' +
                    invoices.length +
                    ' فاتورة بمستحقات. اختر فاتورة من القائمة؛ الحقل «المبلغ» يطبق على الفاتورة المختارة فقط. كرر التسجيل لفاتورة أخرى إن لزم.';
            } else if (invoices.length === 1) {
                totalDueHintEl.textContent =
                    'إن كان الرقم أعلاه أكبر من «متبقي» الفاتورة المعروضة، يوجد غالباً فواتير أخرى — راجع «تفاصيل الدفع» من الجدول.';
            } else {
                totalDueHintEl.textContent = '';
            }
            if (invoices.length > 0 && Math.abs(sumInv - totalDue) > 0.02) {
                totalDueHintEl.textContent +=
                    ' (تنبيه: مجموع المتبقي في القائمة $' +
                    sumInv.toFixed(2) +
                    ' يختلف عن الإجمالي المعروض؛ حدّث الصفحة.)';
            }
        }

        if (!submitBtn || invoices.length === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('تعذر تحميل قائمة الفواتير. حدّث الصفحة أو أعد المحاولة.');
            } else {
                alert('تعذر تحميل قائمة الفواتير. حدّث الصفحة أو أعد المحاولة.');
            }
            return;
        }

        invoices = invoices.slice().sort(function (a, b) {
            return Number(b.remaining_amount || 0) - Number(a.remaining_amount || 0);
        });

        const sel = document.getElementById('recordPaymentInvoiceId');
        sel.innerHTML = '';
        invoices.forEach(function (inv) {
            const opt = document.createElement('option');
            opt.value = inv.id;
            opt.textContent = (inv.invoice_number || '') + ' — متبقي: $' + Number(inv.remaining_amount || 0).toFixed(2);
            opt.dataset.remaining = String(inv.remaining_amount || 0);
            sel.appendChild(opt);
        });

        function syncAmountToInvoice() {
            const opt = sel.options[sel.selectedIndex];
            const rem = opt ? parseFloat(opt.dataset.remaining || '0') : 0;
            const amountInput = document.getElementById('recordPaymentAmount');
            if (!amountInput) {
                return;
            }
            amountInput.max = rem > 0 ? rem : '';
            amountInput.value = rem > 0 ? rem.toFixed(2) : '';
        }
        sel.onchange = syncAmountToInvoice;
        syncAmountToInvoice();

        const tx = document.getElementById('recordPaymentTransactionId');
        const nt = document.getElementById('recordPaymentNotes');
        if (tx) {
            tx.value = '';
        }
        if (nt) {
            nt.value = '';
        }

        let payModal = bootstrap.Modal.getInstance(modalEl);
        if (!payModal) {
            payModal = new bootstrap.Modal(modalEl);
        }
        payModal.show();
    }

    const groupMembersTableContainer = document.getElementById('groupMembersTableContainer');
    if (groupMembersTableContainer) {
        groupMembersTableContainer.addEventListener('click', function (e) {
            const copyBtn = e.target.closest('.js-copy-member-email');
            if (copyBtn && copyBtn.dataset.email) {
                e.preventDefault();
                const email = copyBtn.dataset.email;
                const done = function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('تم نسخ البريد');
                    } else {
                        alert('تم نسخ البريد');
                    }
                };
                const fail = function () {
                    alert('تعذر النسخ. انسخ يدوياً: ' + email);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(email).then(done).catch(fail);
                } else {
                    fail();
                }
                return;
            }

            const payBtn = e.target.closest('.js-open-record-payment');
            if (payBtn) {
                e.preventDefault();
                openRecordGroupMemberPaymentModal(payBtn);
            }

            @if(($trainingCampsForModal ?? collect())->isNotEmpty())
            const campBtn = e.target.closest('.js-open-attach-camp-modal');
            if (campBtn) {
                e.preventDefault();
                openAttachCampModal(campBtn);
            }
            @endif
        });
    }

    const recordGroupMemberPaymentForm = document.getElementById('recordGroupMemberPaymentForm');
    if (recordGroupMemberPaymentForm) {
        recordGroupMemberPaymentForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('recordGroupMemberPaymentSubmit');
            if (!submitBtn) {
                return;
            }
            const studentId = document.getElementById('recordPaymentStudentId').value;
            if (!studentId) {
                return;
            }
            const url = groupMemberPaymentBaseUrl + '/' + studentId + '/payments';
            const payload = {
                invoice_id: document.getElementById('recordPaymentInvoiceId').value,
                amount: parseFloat(document.getElementById('recordPaymentAmount').value),
                payment_method_id: document.getElementById('recordPaymentMethodId').value,
                payment_date: document.getElementById('recordPaymentDate').value,
                transaction_id: document.getElementById('recordPaymentTransactionId').value || null,
                notes: document.getElementById('recordPaymentNotes').value || null,
            };

            submitBtn.disabled = true;
            const origHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الحفظ...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json().catch(function () {
                    return {};
                });

                if (response.ok && data.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'تم تسجيل الدفعة');
                    } else {
                        alert(data.message || 'تم تسجيل الدفعة');
                    }
                    const modalEl = document.getElementById('recordGroupMemberPaymentModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                    if (typeof window.refreshGroupMembersTable === 'function') {
                        window.refreshGroupMembersTable();
                    } else {
                        window.location.reload();
                    }
                } else {
                    let msg = data.message || 'فشل تسجيل الدفعة';
                    if (data.errors) {
                        const first = Object.values(data.errors)[0];
                        if (Array.isArray(first) && first[0]) {
                            msg = first[0];
                        }
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(msg);
                    } else {
                        alert(msg);
                    }
                }
            } catch (err) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('حدث خطأ في الاتصال');
                } else {
                    alert('حدث خطأ في الاتصال');
                }
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origHtml;
            }
        });
    }

    // Initialize Choices.js for single student select
    let singleChoicesInstance = null;
    const singleMemberModal = document.getElementById('addMemberModal');
    if (singleMemberModal) {
        singleMemberModal.addEventListener('shown.bs.modal', function() {
            const singleStudentSelect = document.getElementById('singleStudentSelect');
            
            if (singleStudentSelect && !singleChoicesInstance) {
                const initSingleChoices = function() {
                    if (typeof Choices !== 'undefined' || typeof window.Choices !== 'undefined') {
                        const ChoicesClass = typeof Choices !== 'undefined' ? Choices : window.Choices;
                        
                        if (singleStudentSelect._choicesjs) {
                            singleStudentSelect._choicesjs.destroy();
                        }
                        
                        singleChoicesInstance = new ChoicesClass(singleStudentSelect, {
                            searchEnabled: true,
                            searchChoices: true,
                            placeholder: true,
                            placeholderValue: '-- اختر طالب --',
                            searchPlaceholderValue: 'ابحث بالاسم (عربي/إنجليزي) أو البريد الإلكتروني...',
                            itemSelectText: '',
                            shouldSort: false,
                            allowHTML: true,
                            fuseOptions: {
                                threshold: 0.4,
                                minMatchCharLength: 1,
                                includeScore: false
                            },
                        });
                    } else {
                        setTimeout(initSingleChoices, 100);
                    }
                };
                
                initSingleChoices();
            }
        });
    }

    // Initialize Choices.js for bulk student select
    let bulkChoicesInstance = null;
    
    // Initialize when modal is shown
    const bulkMembersModal = document.getElementById('addBulkMembersModal');
    if (bulkMembersModal) {
        bulkMembersModal.addEventListener('shown.bs.modal', function() {
            const bulkStudentSelect = document.getElementById('bulkStudentSelect');
            
            if (bulkStudentSelect && !bulkChoicesInstance) {
                // Wait for Choices.js to be available
                const initBulkChoices = function() {
                    if (typeof Choices !== 'undefined' || typeof window.Choices !== 'undefined') {
                        const ChoicesClass = typeof Choices !== 'undefined' ? Choices : window.Choices;
                        
                        // Destroy existing instance if any
                        if (bulkStudentSelect._choicesjs) {
                            bulkStudentSelect._choicesjs.destroy();
                        }
                        
                        bulkChoicesInstance = new ChoicesClass(bulkStudentSelect, {
                            removeItemButton: true,
                            searchEnabled: true,
                            searchChoices: true,
                            placeholder: true,
                            placeholderValue: 'اختر طالب أو أكثر',
                            searchPlaceholderValue: 'ابحث بالاسم (عربي/إنجليزي) أو البريد الإلكتروني...',
                            itemSelectText: '',
                            shouldSort: false,
                            allowHTML: true,
                            fuseOptions: {
                                threshold: 0.4,
                                minMatchCharLength: 1,
                                includeScore: false
                            },
                            classNames: {
                                containerOuter: 'choices',
                                containerInner: 'choices__inner',
                                input: 'choices__input',
                                inputCloned: 'choices__input--cloned',
                                list: 'choices__list',
                                listItems: 'choices__list--multiple',
                                listSingle: 'choices__list--single',
                                listDropdown: 'choices__list--dropdown',
                                item: 'choices__item',
                                itemSelectable: 'choices__item--selectable',
                                itemDisabled: 'choices__item--disabled',
                                itemChoice: 'choices__item--choice',
                                placeholder: 'choices__placeholder',
                                group: 'choices__group',
                                groupHeading: 'choices__heading',
                                button: 'choices__button',
                                activeState: 'is-active',
                                focusState: 'is-focused',
                                openState: 'is-open',
                                disabledState: 'is-disabled',
                                highlightedState: 'is-highlighted',
                                selectedState: 'is-selected',
                                flippedState: 'is-flipped',
                                loadingState: 'is-loading',
                                noResults: 'has-no-results',
                                noChoices: 'has-no-choices'
                            }
                        });

                        // Update selected count when choices change
                        bulkStudentSelect.addEventListener('change', function() {
                            updateBulkSelectedCount();
                        });

                        bulkStudentSelect.addEventListener('addItem', function() {
                            updateBulkSelectedCount();
                        });

                        bulkStudentSelect.addEventListener('removeItem', function() {
                            updateBulkSelectedCount();
                        });

                        // Initial count update
                        updateBulkSelectedCount();
                    } else {
                        // Retry after a short delay if Choices.js is not loaded yet
                        setTimeout(initBulkChoices, 100);
                    }
                };
                
                initBulkChoices();
            }
        });

        // Clean up when modal is hidden
        bulkMembersModal.addEventListener('hidden.bs.modal', function() {
            // Don't destroy, just keep it for next time
        });
    }

    // Update selected count display
    function updateBulkSelectedCount() {
        const selectedCountDiv = document.getElementById('bulkSelectedCount');
        const selectedCountText = document.getElementById('bulkSelectedCountText');
        const bulkStudentSelect = document.getElementById('bulkStudentSelect');
        
        if (bulkStudentSelect) {
            const selectedOptions = Array.from(bulkStudentSelect.selectedOptions);
            const count = selectedOptions.length;
            
            if (count > 0) {
                if (selectedCountDiv) selectedCountDiv.style.display = 'block';
                if (selectedCountText) selectedCountText.textContent = count;
            } else {
                if (selectedCountDiv) selectedCountDiv.style.display = 'none';
            }
        }
    }
</script>
@stop
