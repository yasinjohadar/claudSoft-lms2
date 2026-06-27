@extends('admin.layouts.master')

@section('page-title')
    تقييم مشاريع الفرق
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}?v={{ filemtime(public_path('assets/css/project-challenge.css')) }}">
@endpush

@section('content')
    @php
        $statusLabels = [
            'submitted' => 'مُرسَل',
            'under_review' => 'قيد المراجعة',
        ];
    @endphp

    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="my-3 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.project-challenges.index') }}">تحديات المشاريع</a></li>
                        <li class="breadcrumb-item active">تقييم التسليمات</li>
                    </ol>
                </nav>
            </div>

            <div class="pc-form-hero dashboard-fade-in">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <i class="fe fe-check-square fa-lg"></i>
                            <span class="pc-form-hero__badge">Project Grading</span>
                        </div>
                        <h1 class="pc-form-hero__title">تسليمات بانتظار التقييم</h1>
                        <p class="pc-form-hero__desc">
                            راجع روابط التسليم، قيّم أعمال الفرق، وحدّث التقدم أو اطلب إعادة التسليم.
                        </p>
                    </div>
                    <a href="{{ route('admin.project-challenges.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fe fe-layers me-1"></i> تحديات المشاريع
                    </a>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-inbox', 'label' => 'إجمالي المعلّقة', 'value' => $stats['total'], 'sub' => 'بانتظار قرارك'],
                    ['variant' => 'orange', 'icon' => 'fe-send', 'label' => 'مُرسَلة', 'value' => $stats['submitted'], 'sub' => 'جديدة'],
                    ['variant' => 'cyan', 'icon' => 'fe-eye', 'label' => 'قيد المراجعة', 'value' => $stats['under_review'], 'sub' => 'تحت الفحص'],
                ];
            @endphp

            <div class="row g-3 dashboard-fade-in mb-4">
                @foreach ($kpiCards as $index => $card)
                    <div class="col-lg-4 col-md-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                        <div class="card pc-kpi pc-kpi--{{ $card['variant'] }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="pc-kpi__icon-wrap">
                                    <i class="fe {{ $card['icon'] }} pc-kpi__icon"></i>
                                </div>
                                <div class="flex-fill min-w-0">
                                    <p class="pc-kpi__label mb-1">{{ $card['label'] }}</p>
                                    <h3 class="pc-kpi__value mb-1">{{ $card['value'] }}</h3>
                                    <p class="pc-kpi__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($submissions->isEmpty())
                <div class="pc-index-empty dashboard-fade-in">
                    <div class="pc-index-empty__icon"><i class="fe fe-check-circle"></i></div>
                    <h3 class="h5 fw-bold mb-2">لا توجد تسليمات بانتظار التقييم</h3>
                    <p class="text-muted mb-3 mx-auto" style="max-width:420px">
                        عندما يسلّم الطلاب مراحل المشاريع ستظهر هنا للمراجعة والتقييم.
                    </p>
                    <a href="{{ route('admin.project-challenges.index') }}" class="btn btn-primary rounded-pill px-4">
                        <i class="fe fe-layers me-1"></i> عرض التحديات
                    </a>
                </div>
            @else
                <div class="pc-form-panel dashboard-fade-in mb-3">
                    <div class="pc-form-panel__head">
                        <span class="pc-form-panel__icon"><i class="fe fe-list"></i></span>
                        <div>
                            <h2 class="pc-form-panel__title">قائمة التسليمات</h2>
                            <p class="pc-form-panel__sub">{{ $submissions->total() }} تسليم بانتظار التقييم</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>التحدي</th>
                                    <th>المرحلة</th>
                                    <th>الفريق</th>
                                    <th>المُسلِّم</th>
                                    <th>تاريخ التسليم</th>
                                    <th>الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td class="fw-semibold">{{ $submission->team->challenge->title ?? '—' }}</td>
                                        <td>{{ $submission->stage->title ?? '—' }}</td>
                                        <td>{{ $submission->team->name ?? '—' }}</td>
                                        <td>{{ $submission->submitter->name ?? $submission->submitter->email ?? '—' }}</td>
                                        <td>{{ $submission->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td>
                                            <span class="pc-stage-status pc-stage-status--submitted">
                                                {{ $statusLabels[$submission->status] ?? $submission->status }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.project-grading.show', $submission->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="fe fe-check-square me-1"></i>تقييم
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($submissions->hasPages())
                        <div class="pt-3 border-top mt-2">{{ $submissions->links() }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@stop
