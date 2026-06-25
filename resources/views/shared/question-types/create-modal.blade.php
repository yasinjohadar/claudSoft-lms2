@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/question-type-select.css') }}?v={{ @filemtime(public_path('assets/css/question-type-select.css')) ?: '1' }}">
@endpush

@php
    $modalId = $modalId ?? 'createQuestionModal';
    $context = $context ?? 'question-bank';
    $contextParams = $contextParams ?? [];
@endphp

<div class="modal fade qt-select-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fe fe-plus-circle"></i>
                    إنشاء سؤال جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <p class="qt-select-modal__hint mb-0">اختر نوع السؤال الذي تريد إنشاءه — كل نوع له واجهة إدخال مخصصة.</p>

                <div class="qt-select-grid mt-3">
                    @foreach($questionTypes as $type)
                        <a href="{{ $type->createUrl($context, $contextParams) }}"
                           class="qt-select-card qt-select-card--{{ $type->typeSlug() }}">
                            <span class="qt-select-card__icon">
                                <i class="{{ $type->featherIconClass() }}"></i>
                            </span>
                            <span class="qt-select-card__title">{{ $type->display_name }}</span>
                            <span class="qt-select-card__desc">{{ $type->selectionSummary() }}</span>
                            @if($type->requires_manual_grading)
                                <span class="qt-select-card__badge qt-select-card__badge--manual">تصحيح يدوي</span>
                            @else
                                <span class="qt-select-card__badge qt-select-card__badge--auto">تصحيح تلقائي</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
