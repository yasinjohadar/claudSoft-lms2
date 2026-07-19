@php
    $typeName = $question->questionType->name ?? '';
    $options = $question->options ? $question->options->sortBy('option_order')->values() : collect();
    $metadata = $question->metadata ?? [];
@endphp

<div class="qb-preview-question">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        @if($question->questionType)
            <span class="qb-type-chip">{{ $question->questionType->display_name }}</span>
        @endif
        @include('admin.pages.question-bank.partials.programming-language-chips', [
            'languages' => $question->programmingLanguages ?? collect(),
        ])
        <span class="badge bg-secondary-transparent">{{ $question->default_grade ?? 0 }} نقطة</span>
        @if($question->difficulty_level)
            <span class="badge bg-info-transparent">{{ $question->difficulty_level }}</span>
        @endif
    </div>

    <div class="qb-show-question-text mb-3">
        {!! mixed_bidi_html($question->question_text) !!}
    </div>

    @if($question->question_image)
        <div class="mb-3">
            <img src="{{ asset('storage/' . $question->question_image) }}" alt="صورة السؤال"
                 class="img-fluid rounded" style="max-width: 100%;">
        </div>
    @endif

    <div class="qb-preview-answer-area">
        @switch($typeName)
            @case('multiple_choice_single')
                @foreach($options as $option)
                    <label class="form-check quiz-option-hit d-flex align-items-start gap-2 w-100 mb-2 p-3 border rounded">
                        <input class="form-check-input flex-shrink-0" type="radio" disabled
                               {{ $option->is_correct ? 'checked' : '' }}>
                        <span class="flex-grow-1">{!! mixed_bidi_html($option->option_text) !!}</span>
                        @if($option->is_correct)
                            <i class="fe fe-check-circle text-success"></i>
                        @endif
                    </label>
                @endforeach
                @break

            @case('multiple_choice_multiple')
                @foreach($options as $option)
                    <label class="form-check quiz-option-hit d-flex align-items-start gap-2 w-100 mb-2 p-3 border rounded">
                        <input class="form-check-input flex-shrink-0" type="checkbox" disabled
                               {{ $option->is_correct ? 'checked' : '' }}>
                        <span class="flex-grow-1">{!! mixed_bidi_html($option->option_text) !!}</span>
                        @if($option->is_correct)
                            <i class="fe fe-check-circle text-success"></i>
                        @endif
                    </label>
                @endforeach
                @break

            @case('true_false')
                @foreach($options as $option)
                    <label class="form-check quiz-option-hit d-flex align-items-start gap-2 w-100 mb-2 p-3 border rounded">
                        <input class="form-check-input flex-shrink-0" type="radio" disabled
                               {{ $option->is_correct ? 'checked' : '' }}>
                        <span class="flex-grow-1 fs-5">{!! mixed_bidi_html($option->option_text) !!}</span>
                    </label>
                @endforeach
                @break

            @case('short_answer')
            @case('essay')
                <textarea class="form-control" rows="{{ $typeName === 'essay' ? 5 : 2 }}" disabled
                          placeholder="حقل إجابة الطالب (معاينة فقط)"></textarea>
                @if($options->where('is_correct', true)->isNotEmpty())
                    <div class="alert alert-success mt-3 mb-0">
                        <strong>إجابات مقبولة:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($options->where('is_correct', true) as $option)
                                <li>{!! mixed_bidi_html($option->option_text) !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @break

            @case('numerical')
            @case('calculated')
                <input type="number" class="form-control" disabled placeholder="إدخال رقمي">
                @php $correctValue = $metadata['correct_answer'] ?? $metadata['answer'] ?? null; @endphp
                @if($correctValue !== null)
                    <div class="alert alert-success mt-3 mb-0">
                        <strong>الإجابة الصحيحة:</strong> {{ $correctValue }}
                        @if(!empty($metadata['tolerance']))
                            <span class="text-muted">(هامش: {{ $metadata['tolerance'] }})</span>
                        @endif
                    </div>
                @endif
                @break

            @case('fill_blanks')
                @php
                    $dropdownPool = $options
                        ->pluck('option_text')
                        ->map(fn ($t) => trim((string) $t))
                        ->filter()
                        ->unique()
                        ->values();
                    $correctByBlank = $options
                        ->where('is_correct', true)
                        ->sortBy(['option_order', 'id'])
                        ->groupBy(fn ($o) => (int) $o->option_order)
                        ->sortKeys()
                        ->filter(fn ($alts, $order) => (int) $order >= 1);
                @endphp
                <div class="alert alert-info mb-3">
                    <i class="fe fe-info me-1"></i>معاينة نوع «ملء الفراغات» — قائمة منسدلة مشتركة
                </div>
                @if($dropdownPool->isNotEmpty())
                    <p class="fw-semibold mb-2">خيارات القائمة</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($dropdownPool as $opt)
                            <span class="badge bg-primary-transparent">{{ $opt }}</span>
                        @endforeach
                    </div>
                @endif
                @if($correctByBlank->isNotEmpty())
                    <p class="fw-semibold mb-2">الإجابة الصحيحة لكل فراغ</p>
                    <div class="d-flex flex-column gap-2">
                        @foreach($correctByBlank as $blankOrder => $alts)
                            <div class="p-3 border rounded d-flex justify-content-between gap-2">
                                <span>فراغ {{ $blankOrder }}</span>
                                <span class="fw-semibold text-success">{!! mixed_bidi_html($alts->first()->option_text) !!}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @break

            @case('matching')
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="fw-semibold mb-2">المطابقات</p>
                        @foreach($options->where('option_order', '<=', $options->count() / 2) as $prompt)
                            <div class="p-2 border rounded mb-2">{!! mixed_bidi_html($prompt->option_text) !!}</div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <p class="fw-semibold mb-2">الخيارات</p>
                        @foreach($options->where('is_correct', true) as $match)
                            <div class="p-2 border rounded mb-2">
                                {!! mixed_bidi_html($match->feedback ?? $match->option_text) !!}
                            </div>
                        @endforeach
                    </div>
                </div>
                @break

            @case('ordering')
                <div class="alert alert-info mb-3">
                    <i class="fe fe-move me-1"></i>معاينة نوع «ترتيب» — الترتيب الصحيح حسب option_order
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($options as $index => $option)
                        <div class="qb-show-option qb-show-option--correct d-flex align-items-center gap-2">
                            <span class="badge bg-primary">{{ $index + 1 }}</span>
                            <i class="fe fe-menu text-muted"></i>
                            <span class="fw-semibold">{!! mixed_bidi_html($option->option_text) !!}</span>
                        </div>
                    @endforeach
                </div>
                @break

            @case('drag_drop')
                <div class="alert alert-info mb-3">
                    <i class="fe fe-layers me-1"></i>معاينة نوع «سحب وإفلات»
                </div>
                @foreach($options as $option)
                    <div class="p-3 border rounded mb-2">
                        <div class="fw-semibold mb-1">{!! mixed_bidi_html($option->option_text) !!}</div>
                        @if($option->feedback)
                            <small class="text-muted">الهدف: {!! mixed_bidi_html($option->feedback) !!}</small>
                        @endif
                    </div>
                @endforeach
                @break

            @default
                @if($options->isNotEmpty())
                    <div class="d-flex flex-column gap-2">
                        @foreach($options as $index => $option)
                            <div class="qb-show-option {{ $option->is_correct ? 'qb-show-option--correct' : '' }}">
                                <span class="badge bg-secondary-transparent me-2">{{ $index + 1 }}</span>
                                {!! mixed_bidi_html($option->option_text) !!}
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">لا توجد خيارات لعرضها لهذا النوع.</p>
                @endif
        @endswitch
    </div>

    @if($question->explanation)
        <div class="alert alert-info mt-4 mb-0">
            <h6 class="fw-semibold mb-2"><i class="fe fe-info me-1"></i>شرح الإجابة</h6>
            {!! mixed_bidi_html($question->explanation) !!}
        </div>
    @endif
</div>

<style>
    .qb-preview-question .qb-preview-answer-area .form-check,
    .qb-preview-question .qb-preview-answer-area .form-control {
        pointer-events: none;
        opacity: 0.95;
    }
</style>
