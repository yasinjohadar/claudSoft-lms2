@extends('admin.layouts.master')

@section('page-title')
    اختر نوع السؤال للتصدير
@stop

@section('styles')
    @include('admin.pages.question-bank.partials.import-ui-styles')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb qb-import-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                    <li class="breadcrumb-item active">تصدير {{ $format === 'excel' ? 'Excel' : 'JSON' }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in qb-import-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-{{ $format === 'excel' ? 'file-text' : 'code' }} me-1"></i>
                        تصدير {{ $format === 'excel' ? 'Excel' : 'JSON' }}
                    </span>
                    <h2 class="group-show-hero__title mb-2">اختر نوع السؤال</h2>
                    <p class="group-show-hero__desc mb-0">
                        سيتم تصدير الأسئلة بصيغة {{ $format === 'excel' ? 'Excel' : 'JSON' }} المتوافقة مع شاشة الاستيراد لنفس النوع.
                        @if(!empty($filterQuery))
                            <span class="d-block mt-1 text-muted small">يُطبَّق نفس فلتر بنك الأسئلة الحالي على التصدير.</span>
                        @endif
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        @if($format === 'excel')
                            <a href="{{ route('question-bank.export.excel', $filterQuery) }}" class="group-show-action group-show-action--success">
                                <span class="group-show-action__icon"><i class="fe fe-layers"></i></span>
                                <span class="group-show-action__text">تصدير متعدد الأنواع</span>
                            </a>
                        @endif
                        <a href="{{ route('question-bank.index', $filterQuery) }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in qb-import-animate">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">أنواع الأسئلة</h4>
                <p class="fs-12 text-muted mb-0">انقر على النوع لتنزيل ملف التصدير.</p>
            </div>
            <div class="card-body pt-3">
                <div class="row g-3">
                    @php
                        $feIcons = [
                            'multiple_choice_single' => 'fe-circle',
                            'multiple_choice_multiple' => 'fe-check-square',
                            'true_false' => 'fe-check-circle',
                            'short_answer' => 'fe-type',
                            'essay' => 'fe-edit',
                            'matching' => 'fe-shuffle',
                            'ordering' => 'fe-list',
                            'fill_blanks' => 'fe-edit-2',
                            'numerical' => 'fe-hash',
                            'calculated' => 'fe-percent',
                        ];
                    @endphp
                    @foreach($questionTypes as $type)
                        <div class="col-md-4 col-lg-3">
                            <a href="{{ route('question-bank.export.type.download', array_merge(['format' => $format, 'type' => $type->name], $filterQuery)) }}"
                               class="qb-type-pick-card">
                                <div class="qb-type-pick-card__icon">
                                    <i class="fe {{ $feIcons[$type->name] ?? 'fe-help-circle' }}"></i>
                                </div>
                                <h6 class="mb-1 fw-semibold">{{ $type->display_name }}</h6>
                                <p class="text-muted small mb-0">تنزيل {{ $format === 'excel' ? 'Excel' : 'JSON' }}</p>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@stop
