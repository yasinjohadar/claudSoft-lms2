@extends('admin.layouts.master')

@section('page-title')
    {{ $gift->name }}
@stop

@section('content')
@php
    $statusClass = match($gift->status) {
        'granted' => 'bg-success-transparent text-success',
        'revoked' => 'bg-danger-transparent text-danger',
        default => 'bg-secondary-transparent text-secondary',
    };
@endphp
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.gifts.index') }}">هدايا الطلاب</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($gift->name, 40) }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-7">
                    <span class="group-show-hero__eyebrow">
                        <i class="ri ri-gift-line me-1"></i>
                        تفاصيل الهدية
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $gift->name }}</h2>
                    <p class="group-show-hero__desc mb-2">{{ $gift->description ?: 'لا يوجد وصف لهذه الهدية.' }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge {{ $statusClass }}">{{ $gift->status_label }}</span>
                        <span class="badge bg-primary-transparent text-primary">
                            <i class="fe fe-target me-1"></i>{{ $gift->target_type_label }}
                        </span>
                        <span class="badge bg-light text-muted">
                            <i class="fe fe-users me-1"></i>{{ $gift->recipients_count ?? $recipients->total() }} مستلم
                        </span>
                        @if($gift->granted_at)
                            <span class="badge bg-light text-muted">
                                <i class="fe fe-clock me-1"></i>{{ $gift->granted_at->format('Y-m-d H:i') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="group-show-actions">
                        <a href="{{ route('admin.gifts.index') }}" class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">عودة للقائمة</span>
                        </a>
                        @if(!$gift->isRevoked())
                            <a href="{{ route('admin.gifts.edit', $gift) }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                                <span class="group-show-action__text">تعديل</span>
                            </a>
                        @endif
                        @if(!$gift->isRevoked() && $gift->isDraft())
                            <form method="POST" action="{{ route('admin.gifts.grant', $gift) }}" class="d-inline"
                                  onsubmit="return confirm('تأكيد منح الهدية للمستهدفين؟');">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--success w-100">
                                    <span class="group-show-action__icon"><i class="fe fe-send"></i></span>
                                    <span class="group-show-action__text">منح الآن</span>
                                </button>
                            </form>
                        @endif
                        @if($gift->isRevoked())
                            <form method="POST" action="{{ route('admin.gifts.regrant', $gift) }}" class="d-inline"
                                  onsubmit="return confirm('إعادة تفعيل الهدية ومنحها للمستهدفين؟');">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--success w-100">
                                    <span class="group-show-action__icon"><i class="fe fe-refresh-cw"></i></span>
                                    <span class="group-show-action__text">إعادة منح</span>
                                </button>
                            </form>
                        @endif
                        @if($gift->isGranted())
                            <form method="POST" action="{{ route('admin.gifts.regrant', $gift) }}" class="d-inline"
                                  onsubmit="return confirm('إعادة حل المستهدفين وإضافة الجدد فقط؟');">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--warning w-100">
                                    <span class="group-show-action__icon"><i class="fe fe-refresh-cw"></i></span>
                                    <span class="group-show-action__text">إعادة منح</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.gifts.revoke', $gift) }}" class="d-inline"
                                  onsubmit="return confirm('إلغاء الهدية؟ لن يصل الطلاب للمحتوى.');">
                                @csrf
                                <button type="submit" class="group-show-action group-show-action--danger w-100">
                                    <span class="group-show-action__icon"><i class="fe fe-slash"></i></span>
                                    <span class="group-show-action__text">إلغاء الهدية</span>
                                </button>
                            </form>
                        @endif
                        @if($gift->isDraft())
                            <form method="POST" action="{{ route('admin.gifts.destroy', $gift) }}" class="d-inline"
                                  onsubmit="return confirm('حذف الهدية؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="group-show-action group-show-action--danger w-100">
                                    <span class="group-show-action__icon"><i class="fe fe-trash-2"></i></span>
                                    <span class="group-show-action__text">حذف</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-1">معلومات الهدية</h4>
                        <p class="fs-12 text-muted mb-0">الغلاف والمحتوى والتواريخ.</p>
                    </div>
                    <div class="card-body pt-3 text-center">
                        @if($gift->cover_url)
                            <img src="{{ $gift->cover_url }}" alt="" class="img-fluid rounded mb-3" style="max-height:180px;object-fit:cover">
                        @else
                            <div class="admin-users-table__avatar mx-auto mb-3" style="width:80px;height:80px;font-size:1.5rem">
                                <span><i class="ri ri-gift-line"></i></span>
                            </div>
                        @endif
                        <ul class="list-unstyled text-start small mb-0">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">المحتوى</span>
                                <strong>{{ $gift->isUploadMode() ? 'ملفات مرفوعة' : 'روابط خارجية' }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">تاريخ المنح</span>
                                <strong>{{ $gift->granted_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted">آخر إعادة منح</span>
                                <strong>{{ $gift->last_regranted_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card custom-card group-show-members-card dashboard-fade-in">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                        <div>
                            <h6 class="group-show-members-card__title mb-0">
                                المستلمون
                                <span class="group-show-members-card__count">{{ $recipients->total() }}</span>
                            </h6>
                            <p class="fs-12 text-muted mb-0 mt-1">متابعة من حصل على الهدية وتفاعله معها.</p>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive">
                            <table class="table table-hover text-nowrap dashboard-table admin-users-table mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">الطالب</th>
                                        <th scope="col">تاريخ المنح</th>
                                        <th scope="col">معاينة</th>
                                        <th scope="col">تحميل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recipients as $recipient)
                                        <tr class="admin-users-table__row">
                                            <td>
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="admin-users-table__avatar flex-shrink-0">
                                                        <span>{{ mb_strtoupper(mb_substr($recipient->student?->name ?? '?', 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <span class="fw-semibold d-block text-truncate">{{ $recipient->student?->name }}</span>
                                                        <small class="text-muted d-block text-truncate">{{ $recipient->student?->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted fs-12">{{ $recipient->granted_at?->format('Y-m-d H:i') ?? '—' }}</span>
                                            </td>
                                            <td>
                                                @if($recipient->previewed_at)
                                                    <span class="badge bg-success-transparent text-success">نعم</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($recipient->downloaded_at)
                                                    <span class="badge bg-success-transparent text-success">نعم</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">لم يُمنح أحد بعد.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($recipients->hasPages())
                            <div class="mt-3">{{ $recipients->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
