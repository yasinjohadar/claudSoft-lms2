@extends('admin.layouts.master')

@section('page-title')
    اختر نوع السؤال
@stop

@section('css')
<style>
    .question-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
        height: 100%;
    }
    .question-type-card:hover {
        border-color: #4f46e5;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.15);
    }
    .question-type-card .icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
    }
    .question-type-card h5 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    .question-type-card p {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 0;
    }
    .type-multiple-single .icon-wrapper { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
    .type-multiple-multiple .icon-wrapper { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .type-true-false .icon-wrapper { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .type-short-answer .icon-wrapper { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .type-essay .icon-wrapper { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
    .type-matching .icon-wrapper { background: rgba(6, 182, 212, 0.1); color: #06b6d4; }
    .type-ordering .icon-wrapper { background: rgba(236, 72, 153, 0.1); color: #ec4899; }
    .type-fill-blank .icon-wrapper { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
    .type-numerical .icon-wrapper { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">اختر نوع السؤال</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item active">اختر نوع السؤال</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            اختر نوع السؤال الذي تريد إنشاءه. كل نوع له واجهة مخصصة لتسهيل عملية الإدخال.
        </div>

        <!-- Question Types Grid -->
        <div class="row g-4">
            @foreach($questionTypes as $type)
                <div class="col-md-4 col-lg-3">
                    <a href="{{ route('question-bank.create.type', $type->name) }}" class="text-decoration-none">
                        <div class="card question-type-card type-{{ str_replace('_', '-', $type->name) }}">
                            <div class="card-body text-center p-4">
                                <div class="icon-wrapper">
                                    @php
                                        $icons = [
                                            'multiple_choice_single' => 'fa-dot-circle',
                                            'multiple_choice_multiple' => 'fa-check-double',
                                            'true_false' => 'fa-check-circle',
                                            'short_answer' => 'fa-font',
                                            'essay' => 'fa-pen-fancy',
                                            'matching' => 'fa-arrows-alt-h',
                                            'ordering' => 'fa-sort-numeric-down',
                                            'fill_blank' => 'fa-pen-square',
                                            'fill_blanks' => 'fa-pen-square',
                                            'numerical' => 'fa-calculator',
                                            'calculated' => 'fa-square-root-alt',
                                            'drag_drop' => 'fa-hand-pointer',
                                        ];
                                        $iconClass = $type->icon ?? ($icons[$type->name] ?? 'fa-question');
                                        // Add 'fas' prefix if not present
                                        if (!str_starts_with($iconClass, 'fas ') && !str_starts_with($iconClass, 'far ') && !str_starts_with($iconClass, 'fab ')) {
                                            $iconClass = 'fas ' . $iconClass;
                                        }
                                    @endphp
                                    <i class="{{ $iconClass }}"></i>
                                </div>
                                <h5 class="text-dark">{{ $type->display_name }}</h5>
                                <p>
                                    @switch($type->name)
                                        @case('multiple_choice_single')
                                            اختيار إجابة واحدة صحيحة من عدة خيارات
                                            @break
                                        @case('multiple_choice_multiple')
                                            اختيار أكثر من إجابة صحيحة
                                            @break
                                        @case('true_false')
                                            تحديد إذا كانت العبارة صحيحة أم خاطئة
                                            @break
                                        @case('short_answer')
                                            إجابة نصية قصيرة
                                            @break
                                        @case('essay')
                                            إجابة مقالية طويلة
                                            @break
                                        @case('matching')
                                            مطابقة العناصر ببعضها
                                            @break
                                        @case('ordering')
                                            ترتيب العناصر بالتسلسل الصحيح
                                            @break
                                        @case('fill_blank')
                                            ملء الفراغات في النص
                                            @break
                                        @case('numerical')
                                            إجابة رقمية مع هامش خطأ
                                            @break
                                        @case('calculated')
                                            سؤال محسوب بمعادلات
                                            @break
                                        @case('drag_drop')
                                            سحب العناصر وإفلاتها في أماكنها الصحيحة
                                            @break
                                        @default
                                            {{ $type->description ?? '' }}
                                    @endswitch
                                </p>
                                @if($type->requires_manual_grading)
                                    <span class="badge bg-warning-transparent mt-2">
                                        <i class="fas fa-user-edit me-1"></i>تصحيح يدوي
                                    </span>
                                @else
                                    <span class="badge bg-success-transparent mt-2">
                                        <i class="fas fa-robot me-1"></i>تصحيح تلقائي
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="{{ route('question-bank.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-right me-2"></i>العودة لبنك الأسئلة
            </a>
        </div>

    </div>
</div>
@stop
