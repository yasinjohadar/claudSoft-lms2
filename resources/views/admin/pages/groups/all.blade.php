@extends('admin.layouts.master')

@section('page-title')
    المجموعات
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
                        <li class="breadcrumb-item active">المجموعات</li>
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
                        <h2 class="group-show-hero__title mb-2">كافة المجموعات</h2>
                        <p class="group-show-hero__desc mb-0">عرض وإدارة جميع مجموعات الكورسات، الأعضاء، والحالة من مكان واحد.</p>
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

            @include('admin.pages.groups.partials.all-stats', [
                'totalGroups' => $totalGroups,
                'activeGroups' => $activeGroups,
                'totalMembers' => $totalMembers,
            ])

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية المجموعات</h4>
                    <p class="fs-12 text-muted mb-0">ابحث أو فلتر حسب الكورس والحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('groups.all') }}" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="ابحث عن مجموعة...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="is_active" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشطة</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشطة</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('groups.all') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة المجموعات
                        <span class="group-show-members-card__count">{{ $groups->total() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($groups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap dashboard-table mb-0">
                                <thead>
                                    <tr>
                                        <th>اسم المجموعة</th>
                                        <th>الكورس</th>
                                        <th>عدد الأعضاء</th>
                                        <th>منشئ المجموعة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groups as $group)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="avatar avatar-sm bg-primary-transparent flex-shrink-0">
                                                        <i class="fe fe-users"></i>
                                                    </span>
                                                    <div class="min-w-0">
                                                        @php $firstCourseForLink = $group->courses->first(); @endphp
                                                        @if($firstCourseForLink)
                                                            <a href="{{ route('courses.groups.show', [$firstCourseForLink->id, $group->id]) }}"
                                                               class="fw-semibold text-primary text-decoration-none">
                                                                {{ $group->name }}
                                                            </a>
                                                        @else
                                                            <a href="{{ route('groups.show', $group->id) }}"
                                                               class="fw-semibold text-primary text-decoration-none">
                                                                {{ $group->name }}
                                                            </a>
                                                        @endif
                                                        @if($group->description)
                                                            <small class="d-block text-muted text-truncate" style="max-width: 220px;">
                                                                {{ Str::limit($group->description, 50) }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($group->courses && $group->courses->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($group->courses as $course)
                                                            <a href="{{ route('courses.show', $course->id) }}" class="group-show-chip text-decoration-none">
                                                                {{ Str::limit($course->title, 28) }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                    @if($group->courses->count() > 1)
                                                        <small class="text-muted d-block mt-1">{{ $group->courses->count() }} كورسات</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">لا توجد كورسات</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info-transparent">
                                                    <i class="fe fe-users me-1"></i>{{ $group->members_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($group->createdBy)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="avatar avatar-xs bg-primary-transparent">
                                                            {{ substr($group->createdBy->name, 0, 1) }}
                                                        </span>
                                                        <span>{{ $group->createdBy->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $group->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                @if($group->is_active)
                                                    <span class="badge bg-success-transparent text-success">نشطة</span>
                                                @else
                                                    <span class="badge bg-secondary-transparent text-secondary">غير نشطة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @php $firstCourse = $group->courses->first(); @endphp
                                                    @if($firstCourse)
                                                        <a href="{{ route('courses.groups.show', [$firstCourse->id, $group->id]) }}"
                                                           class="btn btn-sm btn-info-light" title="عرض">
                                                            <i class="fe fe-eye"></i>
                                                        </a>
                                                        <a href="{{ route('courses.groups.edit', [$firstCourse->id, $group->id]) }}"
                                                           class="btn btn-sm btn-primary-light" title="تعديل">
                                                            <i class="fe fe-edit-2"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('groups.show', $group->id) }}"
                                                           class="btn btn-sm btn-info-light" title="عرض">
                                                            <i class="fe fe-eye"></i>
                                                        </a>
                                                        <a href="{{ route('groups.edit', $group->id) }}"
                                                           class="btn btn-sm btn-primary-light" title="تعديل">
                                                            <i class="fe fe-edit-2"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-danger-light" title="حذف"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal{{ $group->id }}">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $groups->links() }}
                        </div>
                    @else
                        <div class="group-show-empty">
                            <div class="group-show-empty__icon">
                                <i class="fe fe-layers"></i>
                            </div>
                            <h4 class="group-show-empty__title">لا توجد مجموعات</h4>
                            <p class="text-muted mb-3">ابدأ بإنشاء مجموعة جديدة لأحد الكورسات.</p>
                            <a href="{{ route('groups.select-course') }}" class="btn btn-primary">
                                <i class="fe fe-plus me-1"></i>إضافة مجموعة
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @foreach($groups as $group)
        <div class="modal fade" id="deleteModal{{ $group->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $group->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body text-center p-5">
                        <div class="avatar avatar-xl bg-danger-transparent mx-auto mb-3">
                            <i class="fe fe-trash-2 text-danger fs-24"></i>
                        </div>
                        <h5 class="mb-3" id="deleteModalLabel{{ $group->id }}">تأكيد حذف المجموعة</h5>
                        <p class="text-muted mb-2">
                            المجموعة: <strong class="text-danger">{{ $group->name }}</strong>
                        </p>
                        @if($group->members_count > 0)
                            <div class="alert alert-warning py-2 text-start" role="alert">
                                <i class="fe fe-alert-triangle me-1"></i>
                                تحتوي على <strong>{{ $group->members_count }}</strong> عضو/أعضاء
                            </div>
                        @endif
                        <p class="text-danger small mb-4">
                            <i class="fe fe-info me-1"></i>
                            لا يمكن التراجع عن هذا الإجراء
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </button>
                            <button type="button" class="btn btn-danger px-4" onclick="deleteGroup({{ $group->id }}, '{{ addslashes($group->name) }}')">
                                <i class="fe fe-trash-2 me-1"></i>نعم، احذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div id="alertContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>
@stop

@section('script')
<script>
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

    document.querySelectorAll('[data-countup]').forEach(function(el) {
        const target = parseFloat(el.dataset.countup || '0');
        if (!isNaN(target)) animateGroupsCountup(el, target, 900);
    });

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

        setTimeout(function() {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(function() { alertContainer.innerHTML = ''; }, 300);
            }
        }, type === 'info' ? 2000 : 5000);
    }

    function deleteGroup(groupId) {
        const modalElement = document.getElementById('deleteModal' + groupId);
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) modal.hide();

        showAlert('info', 'جاري حذف المجموعة...');

        fetch(`{{ url('/admin/groups') }}/${groupId}/delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showAlert('success', data.message || 'تم حذف المجموعة بنجاح');
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    showAlert('error', data.message || 'حدث خطأ أثناء حذف المجموعة');
                }
            })
            .catch(function(error) {
                showAlert('error', 'حدث خطأ أثناء حذف المجموعة: ' + error.message);
            });
    }
</script>
@stop
