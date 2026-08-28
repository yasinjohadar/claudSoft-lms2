@extends('admin.layouts.master')

@section('page-title')
    المجموعات المجانية
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('groups.all') }}">المجموعات</a></li>
                        <li class="breadcrumb-item active">المجموعات المجانية</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-layers me-1"></i>
                            إدارة المجموعات
                        </span>
                        <h2 class="group-show-hero__title mb-2">المجموعات المجانية</h2>
                        <p class="group-show-hero__desc mb-0">عرض وإدارة المجموعات العادية (غير المدفوعة) فقط، أعضاؤها، وحالتها من مكان واحد.</p>
                    </div>
                    <div class="col-lg-5">
                        <div class="group-show-actions">
                            <a href="{{ route('groups.select-course') }}" target="_blank"
                               class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-external-link"></i></span>
                                <span class="group-show-action__text">إضافة مجموعة (نافذة جديدة)</span>
                            </a>
                            <a href="{{ route('groups.select-course') }}"
                               class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة مجموعة (نفس النافذة)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.pages.groups.partials.free-stats', [
                'totalGroups' => $totalGroups,
                'activeGroups' => $activeGroups,
                'totalMembers' => $totalMembers,
                'otherTypeGroups' => $otherTypeGroups ?? 0,
            ])

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية المجموعات المجانية</h4>
                    <p class="fs-12 text-muted mb-0">الفلاتر والفرز يعملان مباشرة بدون إعادة تحميل الصفحة.</p>
                </div>
                <div class="card-body pt-3">
                    <form id="groups-filters-form" class="group-show-filters mb-0" onsubmit="return false;">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="filter-search">البحث</label>
                                <input type="text" name="search" id="filter-search" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن مجموعة...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="filter-course">الكورس</label>
                                <select name="course_id" id="filter-course" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="filter-active">الحالة</label>
                                <select name="is_active" id="filter-active" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشطة</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشطة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" id="groups-filter-apply" class="btn btn-primary">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <button type="button" id="groups-filter-reset" class="btn btn-outline-secondary">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in position-relative" id="groups-table-card">
                <div id="groups-table-loading" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 align-items-center justify-content-center" style="z-index: 5; display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                </div>
                <div id="groups-table-container">
                    @include('admin.pages.groups.partials.all-groups-table', ['groups' => $groups])
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                        <i class="fe fe-trash-2 text-danger fs-24"></i>
                    </div>
                    <h5 class="mb-3">تأكيد حذف المجموعة</h5>
                    <p class="text-muted mb-2">
                        المجموعة: <strong class="text-danger" id="delete-group-name">—</strong>
                    </p>
                    <div id="delete-group-members-alert" class="alert alert-warning py-2 text-start d-none" role="alert">
                        <i class="fe fe-alert-triangle me-1"></i>
                        تحتوي على <strong id="delete-group-members-count">0</strong> عضو/أعضاء
                    </div>
                    <p class="text-danger small mb-4">
                        <i class="fe fe-info me-1"></i>
                        لا يمكن التراجع عن هذا الإجراء
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                            <i class="fe fe-x me-1"></i>إلغاء
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirm-delete-group-btn">
                            <i class="fe fe-trash-2 me-1"></i>نعم، احذف
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>
@stop

@section('script')
<script>
(function () {
    'use strict';

    const baseUrl = @json(route('groups.free'));
    const deleteUrlBase = @json(url('/admin/groups'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let currentPage = 1;
    let pendingDeleteId = null;
    let filterController = null;

    function animateGroupsCountup(el, target, duration) {
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        const target = parseFloat(el.dataset.countup || '0');
        if (!isNaN(target)) animateGroupsCountup(el, target, 900);
    });

    function debounce(fn, delay) {
        let timer = null;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(null, args); }, delay);
        };
    }

    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const map = {
            success: { class: 'alert-success', icon: 'fe-check-circle' },
            error: { class: 'alert-danger', icon: 'fe-alert-circle' },
            info: { class: 'alert-info', icon: 'fe-info' },
        };
        const cfg = map[type] || map.info;

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
        }, type === 'info' ? 2000 : 5000);
    }

    function getFilters() {
        const sortSelect = document.getElementById('filter-sort');
        let sort = 'created_at';
        let order = 'desc';
        if (sortSelect && sortSelect.value) {
            const parts = sortSelect.value.split(':');
            sort = parts[0] || 'created_at';
            order = parts[1] || 'desc';
        }

        return {
            search: document.getElementById('filter-search')?.value || '',
            course_id: document.getElementById('filter-course')?.value || '',
            is_active: document.getElementById('filter-active')?.value || '',
            sort: sort,
            order: order,
            page: currentPage,
        };
    }

    function setLoading(isLoading) {
        const loader = document.getElementById('groups-table-loading');
        if (!loader) return;
        if (isLoading) {
            loader.classList.remove('d-none');
            loader.style.display = 'flex';
        } else {
            loader.classList.add('d-none');
            loader.style.display = 'none';
        }
    }

    function loadGroups(page) {
        if (page) currentPage = page;

        if (filterController) filterController.abort();
        filterController = new AbortController();

        const params = new URLSearchParams();
        const filters = getFilters();
        Object.keys(filters).forEach(function (key) {
            if (filters[key] !== '' && filters[key] !== null && filters[key] !== undefined) {
                params.set(key, filters[key]);
            }
        });

        setLoading(true);

        fetch(baseUrl + '?' + params.toString(), {
            method: 'GET',
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: filterController.signal,
        })
            .then(function (r) {
                if (!r.ok) throw new Error('تعذر تحميل المجموعات');
                return r.text();
            })
            .then(function (html) {
                const container = document.getElementById('groups-table-container');
                if (container) container.innerHTML = html;
                const url = baseUrl + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', url);
            })
            .catch(function (err) {
                if (err.name === 'AbortError') return;
                showAlert('error', err.message || 'تعذر تحميل المجموعات');
            })
            .finally(function () {
                setLoading(false);
            });
    }

    function bindFilterEvents() {
        const debouncedSearch = debounce(function () {
            currentPage = 1;
            loadGroups(1);
        }, 350);

        document.getElementById('filter-search')?.addEventListener('input', debouncedSearch);
        document.getElementById('filter-course')?.addEventListener('change', function () {
            currentPage = 1;
            loadGroups(1);
        });
        document.getElementById('filter-active')?.addEventListener('change', function () {
            currentPage = 1;
            loadGroups(1);
        });
        document.getElementById('groups-filter-apply')?.addEventListener('click', function () {
            currentPage = 1;
            loadGroups(1);
        });
        document.getElementById('groups-filter-reset')?.addEventListener('click', function () {
            const search = document.getElementById('filter-search');
            const course = document.getElementById('filter-course');
            const active = document.getElementById('filter-active');
            const sort = document.getElementById('filter-sort');
            if (search) search.value = '';
            if (course) course.value = '';
            if (active) active.value = '';
            if (sort) sort.value = 'created_at:desc';
            currentPage = 1;
            loadGroups(1);
        });
    }

    const container = document.getElementById('groups-table-container');
    if (container) {
        container.addEventListener('change', function (e) {
            if (e.target && e.target.id === 'filter-sort') {
                currentPage = 1;
                loadGroups(1);
            }
        });

        container.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#groups-pagination a');
            if (pageLink) {
                e.preventDefault();
                const match = pageLink.href.match(/[?&]page=(\d+)/);
                loadGroups(match ? parseInt(match[1], 10) : 1);
                return;
            }

            const deleteBtn = e.target.closest('.js-delete-group');
            if (deleteBtn) {
                pendingDeleteId = deleteBtn.getAttribute('data-group-id');
                const name = deleteBtn.getAttribute('data-group-name') || '—';
                const members = parseInt(deleteBtn.getAttribute('data-members-count') || '0', 10);
                document.getElementById('delete-group-name').textContent = name;
                const alertEl = document.getElementById('delete-group-members-alert');
                if (members > 0) {
                    document.getElementById('delete-group-members-count').textContent = String(members);
                    alertEl.classList.remove('d-none');
                } else {
                    alertEl.classList.add('d-none');
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteGroupModal')).show();
            }
        });
    }

    document.getElementById('confirm-delete-group-btn')?.addEventListener('click', function () {
        if (!pendingDeleteId) return;
        const groupId = pendingDeleteId;
        pendingDeleteId = null;
        bootstrap.Modal.getInstance(document.getElementById('deleteGroupModal'))?.hide();
        showAlert('info', 'جاري حذف المجموعة...');

        fetch(deleteUrlBase + '/' + groupId + '/delete', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'فشل الحذف');
                showAlert('success', data.message || 'تم حذف المجموعة بنجاح');
                loadGroups(currentPage);
            })
            .catch(function (err) {
                showAlert('error', err.message || 'حدث خطأ أثناء حذف المجموعة');
            });
    });

    bindFilterEvents();
})();
</script>
@stop
