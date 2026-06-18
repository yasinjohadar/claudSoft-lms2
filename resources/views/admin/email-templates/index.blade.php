@extends('admin.layouts.master')

@section('page-title')
    قوالب البريد الإلكتروني
@stop

@section('content')
    <div class="main-content app-content admin-email-templates-page">
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
                        <li class="breadcrumb-item active">قوالب البريد</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-file-text me-1"></i>
                            قوالب البريد
                        </span>
                        <h2 class="group-show-hero__title mb-2">قوالب البريد الإلكتروني</h2>
                        <p class="group-show-hero__desc mb-0">
                            إدارة قوالب الترحيب والتأكيد والرسائل المخصصة المستخدمة في النظام.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('admin.email-templates.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة قالب جديد</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-layers', 'label' => 'إجمالي القوالب', 'value' => $stats['total'] ?? 0, 'sub' => 'كل القوالب'],
                    ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'نشطة', 'value' => $stats['active'] ?? 0, 'sub' => 'قوالب مفعّلة'],
                    ['variant' => 'orange', 'icon' => 'fe-slash', 'label' => 'معطّلة', 'value' => $stats['inactive'] ?? 0, 'sub' => 'غير مفعّلة'],
                    ['variant' => 'cyan', 'icon' => 'fe-user-plus', 'label' => 'ترحيب بالتسجيل', 'value' => $stats['registration_welcome'] ?? 0, 'sub' => 'قوالب ترحيب'],
                ];
            @endphp

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
                                    <h3 class="admin-stats-card__value mb-1">{{ $card['value'] }}</h3>
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية القوالب</h4>
                    <p class="fs-12 text-muted mb-0">ابحث بالاسم أو الموضوع، أو فلتر حسب النوع والحالة.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('admin.email-templates.index') }}" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-5 col-md-6">
                                <label class="form-label" for="templatesSearchInput">بحث</label>
                                <input id="templatesSearchInput" type="text" name="search" class="form-control"
                                       value="{{ request('search') }}"
                                       placeholder="ابحث بالاسم أو الموضوع...">
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="templatesType">النوع</label>
                                <select name="type" id="templatesType" class="form-select">
                                    <option value="">جميع الأنواع</option>
                                    <option value="registration_welcome" {{ request('type') == 'registration_welcome' ? 'selected' : '' }}>ترحيب بالتسجيل</option>
                                    <option value="enrollment_confirmation" {{ request('type') == 'enrollment_confirmation' ? 'selected' : '' }}>تأكيد التسجيل</option>
                                    <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>مخصص</option>
                                </select>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6">
                                <label class="form-label" for="templatesIsActive">الحالة</label>
                                <select name="is_active" id="templatesIsActive" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>معطل</option>
                                </select>
                            </div>
                            <div class="col-xl-4 col-lg-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary btn-sm">
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
                        قائمة القوالب
                        <span class="group-show-members-card__count">{{ $templates->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @if ($templates->isEmpty())
                        <div class="group-show-empty py-5">
                            <i class="fe fe-inbox group-show-empty__icon" style="width:56px;height:56px;font-size:1.35rem;"></i>
                            <p class="group-show-empty__title">لا توجد قوالب</p>
                            <p class="group-show-empty__desc mb-3">ابدأ بإنشاء قالب بريد جديد للاستخدام في النظام.</p>
                            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary btn-sm rounded-pill">
                                <i class="fe fe-plus me-1"></i>إضافة قالب جديد
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 admin-email-templates-page__table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>الموضوع</th>
                                        <th>النوع</th>
                                        <th>الحالة</th>
                                        <th>التاريخ</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($templates as $template)
                                        <tr>
                                            <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $template->name_ar ?: $template->name }}</div>
                                                @if ($template->name_ar && $template->name !== $template->name_ar)
                                                    <small class="text-muted">{{ $template->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ Str::limit($template->subject, 50) }}</span>
                                            </td>
                                            <td>
                                                @if ($template->type === 'registration_welcome')
                                                    <span class="group-show-chip group-show-chip--sm text-info">ترحيب بالتسجيل</span>
                                                @elseif ($template->type === 'enrollment_confirmation')
                                                    <span class="group-show-chip group-show-chip--sm text-success">تأكيد التسجيل</span>
                                                @else
                                                    <span class="group-show-chip group-show-chip--sm text-secondary">مخصص</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($template->is_active)
                                                    <span class="group-show-chip group-show-chip--sm text-success">
                                                        <i class="fe fe-check-circle me-1"></i>نشط
                                                    </span>
                                                @else
                                                    <span class="group-show-chip group-show-chip--sm text-danger">
                                                        <i class="fe fe-slash me-1"></i>معطل
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $template->created_at->format('Y-m-d H:i') }}</span>
                                            </td>
                                            <td>
                                                <div class="admin-email-templates-page__actions d-flex flex-wrap gap-1">
                                                    <a href="{{ route('admin.email-templates.show', $template) }}"
                                                       class="btn btn-sm btn-outline-primary" title="عرض">
                                                        <i class="fe fe-eye me-1"></i>عرض
                                                    </a>
                                                    <a href="{{ route('admin.email-templates.preview', $template) }}"
                                                       class="btn btn-sm btn-outline-info" title="معاينة" target="_blank">
                                                        <i class="fe fe-maximize-2 me-1"></i>معاينة
                                                    </a>
                                                    <a href="{{ route('admin.email-templates.edit', $template) }}"
                                                       class="btn btn-sm btn-outline-secondary" title="تعديل">
                                                        <i class="fe fe-edit-2 me-1"></i>تعديل
                                                    </a>
                                                    <form action="{{ route('admin.email-templates.duplicate', $template) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من نسخ هذا القالب؟');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="نسخ">
                                                            <i class="fe fe-copy me-1"></i>نسخ
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.email-templates.destroy', $template) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                            <i class="fe fe-trash-2 me-1"></i>حذف
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($templates->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $templates->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop
