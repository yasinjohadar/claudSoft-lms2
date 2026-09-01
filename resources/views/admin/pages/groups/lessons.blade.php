@extends('admin.layouts.master')

@section('page-title')
    دروس مجموعة: {{ $group->name }}
@stop

@section('styles')
<style>
    .group-lessons-course-block {
        background: var(--custom-white, #fff);
        border: 1px solid var(--default-border, #eef1f6);
        border-radius: 16px;
        padding: 1.25rem 1.25rem 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }

    [data-theme-mode="dark"] .group-lessons-course-block {
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.22);
    }

    .group-lessons-course-block + .group-lessons-course-block {
        margin-top: 1.5rem;
    }

    .group-lessons-course-block .admin-course-sections-accordion {
        gap: 1rem;
    }

    .group-lessons-course-block .admin-course-section-item .accordion-body {
        padding: 1.15rem 1.25rem 1.35rem;
    }

    .group-lessons-course-block .js-lesson-row td {
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }
</style>
@endsection

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
                            <div class="group-lessons-course-block">
                                <h5 class="d-flex align-items-center gap-2 mb-3">
                                    <i class="fe fe-book-open text-primary"></i>
                                    {{ $course->title }}
                                </h5>

                                @php
                                    $hasVisibleSections = $course->sections->contains(fn ($s) => $s->modules->isNotEmpty());
                                @endphp

                                @if($hasVisibleSections)
                                    <div class="accordion admin-course-sections-accordion" id="groupLessonsAccordion-{{ $course->id }}">
                                        @foreach($course->sections as $section)
                                            @continue($section->modules->isEmpty())
                                            @php $preset = $section->visualPresentation(); @endphp
                                            <div class="accordion-item admin-course-section-item">
                                                <h2 class="accordion-header d-flex align-items-stretch" id="group-lessons-heading-{{ $section->id }}">
                                                    <button class="accordion-button collapsed flex-grow-1 admin-course-section-item__toggle" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#group-lessons-section-{{ $section->id }}"
                                                            aria-expanded="false" aria-controls="group-lessons-section-{{ $section->id }}">
                                                        <div class="d-flex align-items-center w-100 justify-content-between gap-3 me-2">
                                                            <span class="d-flex align-items-center gap-2 flex-wrap">
                                                                <i class="fe {{ $preset['icon'] }}"></i>
                                                                <span class="fw-semibold">{{ $section->title }}</span>
                                                                <span class="badge bg-danger-transparent text-danger js-section-restricted-badge {{ $section->allowed_for_group ? 'd-none' : '' }}">مقيّد بالكامل</span>
                                                            </span>
                                                            <span class="d-flex align-items-center gap-3" onclick="event.stopPropagation()">
                                                                <span class="text-muted fs-12">{{ $section->modules->count() }} {{ $section->modules->count() == 1 ? 'درس' : 'دروس' }}</span>
                                                                <span class="form-check form-switch mb-0" title="إتاحة القسم كاملاً لهذه المجموعة">
                                                                    <input type="checkbox"
                                                                           class="form-check-input js-section-access-toggle"
                                                                           role="switch"
                                                                           data-section-id="{{ $section->id }}"
                                                                           data-was-open="{{ $section->is_restricted ? '0' : '1' }}"
                                                                           {{ $section->allowed_for_group ? 'checked' : '' }}>
                                                                </span>
                                                            </span>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="group-lessons-section-{{ $section->id }}" class="accordion-collapse collapse"
                                                     aria-labelledby="group-lessons-heading-{{ $section->id }}" data-bs-parent="#groupLessonsAccordion-{{ $course->id }}">
                                                    <div class="accordion-body">
                                                        <p class="text-danger fs-12 mb-3 js-section-restricted-note {{ $section->allowed_for_group ? 'd-none' : '' }}">
                                                            <i class="fe fe-alert-triangle me-1"></i>
                                                            القسم مقيّد بالكامل لهذه المجموعة — تبديل الدروس أدناه لن يغيّر ذلك حتى يُعاد تفعيل القسم.
                                                        </p>
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
                                                                                <span class="badge js-lesson-status-badge {{ ($section->allowed_for_group && $module->allowed_for_group) ? ($module->is_restricted ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-secondary') : 'bg-danger-transparent text-danger' }}">
                                                                                    @if(! $section->allowed_for_group)
                                                                                        مستثناة (القسم مقيّد)
                                                                                    @elseif(! $module->allowed_for_group)
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
                                                                                           {{ $module->allowed_for_group ? 'checked' : '' }}
                                                                                           {{ ! $section->allowed_for_group ? 'disabled' : '' }}>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
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
        // Disabled rows belong to a section that's restricted for this group — they
        // don't count as allowed regardless of their own checked state.
        const allowed = document.querySelectorAll('.js-lesson-access-toggle:checked:not(:disabled)').length;
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

    // Re-derives a lesson row's disabled/badge state after its SECTION's access changed,
    // without touching the lesson's own saved checked/data-was-open state.
    function applySectionStateToRow(row, sectionAllowed) {
        const toggle = row.querySelector('.js-lesson-access-toggle');
        const badge = row.querySelector('.js-lesson-status-badge');
        if (!toggle || !badge) return;

        toggle.disabled = !sectionAllowed;

        badge.classList.remove('bg-success-transparent', 'text-success', 'bg-secondary-transparent', 'text-secondary', 'bg-danger-transparent', 'text-danger');

        if (!sectionAllowed) {
            badge.classList.add('bg-danger-transparent', 'text-danger');
            badge.textContent = 'مستثناة (القسم مقيّد)';
            return;
        }

        const moduleAllowed = toggle.checked;
        const moduleRestricted = toggle.getAttribute('data-was-open') === '0';
        if (!moduleAllowed) {
            badge.classList.add('bg-danger-transparent', 'text-danger');
            badge.textContent = 'مستثناة من هذا الدرس';
        } else if (moduleRestricted) {
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

    document.querySelectorAll('.js-section-access-toggle').forEach(function (input) {
        input.addEventListener('change', function () {
            const sectionId = input.getAttribute('data-section-id');
            const wasOpen = input.getAttribute('data-was-open') === '1';
            const wantAllowed = input.checked;

            if (!wantAllowed && wasOpen) {
                const confirmed = window.confirm(
                    'هذا القسم مفتوح حالياً لكل المجموعات، وكل الدروس داخله معه.\n' +
                    'إلغاء الإتاحة لهذه المجموعة سيحوّل القسم تلقائياً إلى: مسموح لكل المجموعات الحالية في هذا الكورس ما عدا هذه المجموعة — وسيُخفي كل دروسه عن هذه المجموعة.\n' +
                    'هل تريد المتابعة؟'
                );
                if (!confirmed) {
                    input.checked = true;
                    return;
                }
            }

            input.disabled = true;

            fetch(toggleUrlBase + '/sections/' + sectionId + '/toggle', {
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

                    const accordionItem = input.closest('.admin-course-section-item');
                    if (accordionItem) {
                        const badge = accordionItem.querySelector('.js-section-restricted-badge');
                        if (badge) badge.classList.toggle('d-none', data.allowed);

                        const note = accordionItem.querySelector('.js-section-restricted-note');
                        if (note) note.classList.toggle('d-none', data.allowed);

                        accordionItem.querySelectorAll('.js-lesson-row').forEach(function (row) {
                            applySectionStateToRow(row, data.allowed);
                        });
                    }

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
