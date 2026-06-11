@extends('admin.layouts.master')

@section('page-title')
    هدايا الطلاب
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

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">هدايا الطلاب</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="ri ri-gift-line me-1"></i>
                            إدارة الهدايا
                        </span>
                        <h2 class="group-show-hero__title mb-2">هدايا الطلاب</h2>
                        <p class="group-show-hero__desc mb-0">
                            إنشاء الهدايا، استهداف الطلاب، المنح وإعادة المنح، ومتابعة المستلمين من لوحة واحدة.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('admin.gifts.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إنشاء هدية جديدة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                @include('admin.pages.student-gifts.partials.stats', ['stats' => $stats ?? []])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الهدايا</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالاسم أو فلتر حسب الحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.gifts.index') }}" method="GET" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-5 col-lg-6 col-md-6">
                                <label class="form-label" for="giftsSearchInput">بحث</label>
                                <input id="giftsSearchInput" type="text" name="search" class="form-control"
                                    placeholder="بحث باسم الهدية أو الوصف..." value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="giftsStatus">الحالة</label>
                                <select name="status" id="giftsStatus" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                    <option value="granted" {{ request('status') === 'granted' ? 'selected' : '' }}>ممنوحة</option>
                                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>ملغاة</option>
                                </select>
                            </div>
                            <div class="col-xl-4 col-lg-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('admin.gifts.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>مسح
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الهدايا
                        <span class="group-show-members-card__count">{{ $gifts->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap dashboard-table admin-users-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 48px;">#</th>
                                    <th scope="col">الهدية</th>
                                    <th scope="col">الاستهداف</th>
                                    <th scope="col">المستلمون</th>
                                    <th scope="col">الحالة</th>
                                    <th scope="col">تاريخ المنح</th>
                                    <th scope="col" style="width: 120px;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gifts as $gift)
                                    <tr class="admin-users-table__row">
                                        <td>{{ $loop->iteration + ($gifts->currentPage() - 1) * $gifts->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <div class="admin-users-table__avatar flex-shrink-0">
                                                    @if($gift->cover_url)
                                                        <img src="{{ $gift->cover_url }}" alt="{{ $gift->name }}">
                                                    @else
                                                        <span><i class="ri ri-gift-line"></i></span>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <a href="{{ route('admin.gifts.show', $gift) }}"
                                                       class="fw-semibold text-decoration-none d-block text-truncate admin-users-table__name">
                                                        {{ $gift->name }}
                                                    </a>
                                                    @if($gift->description)
                                                        <small class="text-muted d-block text-truncate">{{ Str::limit($gift->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-transparent">{{ $gift->target_type_label }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent">{{ $gift->recipients_count }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match($gift->status) {
                                                    'granted' => 'bg-success-transparent text-success',
                                                    'revoked' => 'bg-danger-transparent text-danger',
                                                    default => 'bg-secondary-transparent text-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $gift->status_label }}</span>
                                        </td>
                                        <td>
                                            @if($gift->granted_at)
                                                <span class="text-muted fs-12">{{ $gift->granted_at->format('Y-m-d H:i') }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <a class="btn btn-sm btn-info-light" href="{{ route('admin.gifts.show', $gift) }}" title="عرض">
                                                    <i class="fe fe-eye"></i>
                                                </a>
                                                @if($gift->isRevoked())
                                                    <form method="POST" action="{{ route('admin.gifts.regrant', $gift) }}" class="d-inline"
                                                          onsubmit="return confirm('إعادة تفعيل الهدية ومنحها للمستهدفين؟');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success-light" title="إعادة منح">
                                                            <i class="fe fe-refresh-cw"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a class="btn btn-sm btn-primary-light" href="{{ route('admin.gifts.edit', $gift) }}" title="تعديل">
                                                        <i class="fe fe-edit-2"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">لا توجد هدايا بعد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($gifts->hasPages())
                        <div class="mt-3">{{ $gifts->links() }}</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
<script>
(function() {
    function initGiftsCountup(container) {
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { initGiftsCountup(document); });
    } else {
        initGiftsCountup(document);
    }
})();
</script>
@stop
