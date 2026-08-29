@extends('admin.layouts.master')

@section('page-title')
    دروس مجموعة: {{ $group->name }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('groups.all') }}">المجموعات</a></li>
                        <li class="breadcrumb-item active">دروس: {{ $group->name }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-lock me-1"></i>
                            إدارة الوصول
                        </span>
                        <h2 class="group-show-hero__title mb-2">دروس مجموعة: {{ $group->name }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            تفعيل أو إيقاف وصول هذه المجموعة لكل درس من كل كورساتها المرتبطة، من مكان واحد.
                            كل تبديل يُحفظ فوراً ويستخدم نفس آلية القيود المستخدَمة في صفحة الكورس.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            @php $firstCourse = $group->courses->first(); @endphp
                            <a href="{{ $firstCourse ? route('courses.groups.show', [$firstCourse->id, $group->id]) : route('groups.show', $group->id) }}"
                               class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للمجموعة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 dashboard-fade-in mb-4">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card admin-stats-card admin-stats-card--blue">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe fe-layers admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">إجمالي الدروس</p>
                                <h3 class="admin-stats-card__value mb-1" data-countup="{{ $totalLessons }}">0</h3>
                                <p class="admin-stats-card__sub mb-0">في كل كورسات المجموعة</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card admin-stats-card admin-stats-card--green">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe fe-check-circle admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">متاحة لهذه المجموعة</p>
                                <h3 class="admin-stats-card__value mb-1" id="lessons-allowed-count" data-countup="{{ $allowedCount }}">0</h3>
                                <p class="admin-stats-card__sub mb-0">يمكن لأعضائها الوصول إليها</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="card admin-stats-card admin-stats-card--red">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="admin-stats-card__icon-wrap">
                                <i class="fe fe-slash admin-stats-card__icon"></i>
                            </div>
                            <div class="admin-stats-card__content flex-fill min-w-0">
                                <p class="admin-stats-card__label mb-1">مستثناة عن هذه المجموعة</p>
                                <h3 class="admin-stats-card__value mb-1" id="lessons-excluded-count" data-countup="{{ $excludedCount }}">0</h3>
                                <p class="admin-stats-card__sub mb-0">لا يمكن لأعضائها الوصول إليها</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <h4 class="card-title mb-1">قائمة الدروس</h4>
                            <p class="fs-12 text-muted mb-0">التبديل يُحفظ مباشرة بدون الحاجة لزر حفظ.</p>
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="lessons-search" class="form-control" placeholder="ابحث باسم الدرس أو الكورس أو القسم...">
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @if($courses->isEmpty())
                        <div class="group-show-empty">
                            <div class="group-show-empty__icon">
                                <i class="fe fe-layers"></i>
                            </div>
                            <h4 class="group-show-empty__title">لا توجد كورسات مرتبطة بهذه المجموعة</h4>
                            <p class="text-muted mb-0">أضف كورساً للمجموعة أولاً لتظهر دروسه هنا.</p>
                        </div>
                    @else
                        @foreach($courses as $course)
                            <div class="group-lessons-course-block mb-4">
                                <h5 class="d-flex align-items-center gap-2 mb-3">
                                    <i class="fe fe-book-open text-primary"></i>
                                    {{ $course->title }}
                                </h5>

                                @forelse($course->sections as $section)
                                    @if($section->modules->isNotEmpty())
                                        @php
                                            $preset = $section->visualPresentation();
                                        @endphp
                                        <div class="group-lessons-section mb-3 ms-3">
                                            <h6 class="text-muted d-flex align-items-center gap-2 mb-2">
                                                <i class="fe {{ $preset['icon'] }}"></i>
                                                {{ $section->title }}
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-hover text-nowrap dashboard-table mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 60px;">#</th>
                                                            <th>الدرس</th>
                                                            <th style="width: 160px;">الحالة</th>
                                                            <th style="width: 120px;">متاح لهذه المجموعة</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($section->modules as $index => $module)
                                                            <tr class="js-lesson-row" data-search-text="{{ Str::lower($module->title.' '.$section->title.' '.$course->title) }}">
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $module->title }}</td>
                                                                <td>
                                                                    <span class="badge js-lesson-status-badge {{ $module->allowed_for_group ? ($module->is_restricted ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary') : 'bg-danger-transparent text-danger' }}">
                                                                        @if(! $module->allowed_for_group)
                                                                            مستثناة من هذا الدرس
                                                                        @elseif($module->is_restricted)
                                                                            مسموح ضمن مجموعات محددة
                                                                        @else
                                                                            مفتوح للجميع
                                                                        @endif
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <div class="form-check form-switch mb-0">
                                                                        <input type="checkbox"
                                                                               class="form-check-input js-lesson-access-toggle"
                                                                               role="switch"
                                                                               data-module-id="{{ $module->id }}"
                                                                               data-was-open="{{ $module->is_restricted ? '0' : '1' }}"
                                                                               {{ $module->allowed_for_group ? 'checked' : '' }}>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                @endforelse
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    'use strict';

    const toggleUrlBase = @json(url('/admin/groups/'.$group->id.'/lessons'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const map = {
            success: { class: 'alert-success', icon: 'fe-check-circle' },
            error: { class: 'alert-danger', icon: 'fe-alert-circle' },
        };
        const cfg = map[type] || map.error;

        alertContainer.innerHTML = `
            <div class="alert ${cfg.class} alert-dismissible fade show shadow-sm" role="alert">
                <i class="fe ${cfg.icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        setTimeout(function () {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(function () { alertContainer.innerHTML = ''; }, 300);
            }
        }, 4000);
    }

    function updateSummaryCounts() {
        const total = document.querySelectorAll('.js-lesson-access-toggle').length;
        const allowed = document.querySelectorAll('.js-lesson-access-toggle:checked').length;
        const allowedEl = document.getElementById('lessons-allowed-count');
        const excludedEl = document.getElementById('lessons-excluded-count');
        if (allowedEl) allowedEl.textContent = allowed;
        if (excludedEl) excludedEl.textContent = total - allowed;
    }

    function updateRowBadge(row, allowed, restricted) {
        const badge = row.querySelector('.js-lesson-status-badge');
        if (!badge) return;

        badge.classList.remove('bg-success-transparent', 'text-success', 'bg-secondary-transparent', 'text-secondary', 'bg-danger-transparent', 'text-danger');

        if (!allowed) {
            badge.classList.add('bg-danger-transparent', 'text-danger');
            badge.textContent = 'مستثناة من هذا الدرس';
        } else if (restricted) {
            badge.classList.add('bg-success-transparent', 'text-success');
            badge.textContent = 'مسموح ضمن مجموعات محددة';
        } else {
            badge.classList.add('bg-secondary-transparent', 'text-secondary');
            badge.textContent = 'مفتوح للجميع';
        }
    }

    document.querySelectorAll('.js-lesson-access-toggle').forEach(function (input) {
        input.addEventListener('change', function () {
            const row = input.closest('tr');
            const moduleId = input.getAttribute('data-module-id');
            const wasOpen = input.getAttribute('data-was-open') === '1';
            const wantAllowed = input.checked;

            if (!wantAllowed && wasOpen) {
                const confirmed = window.confirm(
                    'هذا الدرس مفتوح حالياً لكل المجموعات.\n' +
                    'إلغاء الإتاحة لهذه المجموعة سيحوّل الدرس تلقائياً إلى: مسموح لكل المجموعات الحالية في هذا الكورس ما عدا هذه المجموعة.\n' +
                    'هل تريد المتابعة؟'
                );
                if (!confirmed) {
                    input.checked = true;
                    return;
                }
            }

            input.disabled = true;

            fetch(toggleUrlBase + '/' + moduleId + '/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ allowed: wantAllowed }),
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('تعذر حفظ التغيير');
                    return r.json();
                })
                .then(function (data) {
                    if (!data.success) throw new Error(data.message || 'تعذر حفظ التغيير');
                    input.checked = data.allowed;
                    input.setAttribute('data-was-open', data.restricted ? '0' : '1');
                    updateRowBadge(row, data.allowed, data.restricted);
                    updateSummaryCounts();
                })
                .catch(function (err) {
                    input.checked = !wantAllowed;
                    showAlert('error', err.message || 'تعذر حفظ التغيير');
                })
                .finally(function () {
                    input.disabled = false;
                });
        });
    });

    const searchInput = document.getElementById('lessons-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = searchInput.value.trim().toLowerCase();
            document.querySelectorAll('.js-lesson-row').forEach(function (row) {
                const haystack = row.getAttribute('data-search-text') || '';
                row.style.display = haystack.indexOf(term) === -1 ? 'none' : '';
            });
        });
    }
})();
</script>
@stop
