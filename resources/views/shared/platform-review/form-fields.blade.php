@php
    $isEdit = $isEdit ?? false;
    $review = $review ?? null;
    $submitLabel = $submitLabel ?? ($isEdit ? 'حفظ التعديلات' : 'إرسال التقييم');
    $submitBtnClass = $submitBtnClass ?? ($isEdit ? 'btn-warning' : 'btn-primary');
    $submitIcon = $submitIcon ?? ($isEdit ? 'fa-save' : 'fa-paper-plane');
    $ratingValue = old('rating', $review?->rating);
@endphp

<form action="{{ $formAction }}" method="POST" class="platform-review-form">
    @csrf
    @if(!empty($formMethod) && strtoupper($formMethod) !== 'POST')
        @method($formMethod)
    @endif

    @if($isEdit && $review?->is_active)
        <div class="platform-review-notice platform-review-notice--warning mb-4">
            <span class="platform-review-notice__icon"><i class="fas fa-exclamation-triangle"></i></span>
            <div>
                <strong>تنبيه:</strong>
                عند تعديل تقييمك المعتمد، سيُعاد إرساله للمراجعة قبل نشره مرة أخرى.
            </div>
        </div>
    @endif

    <div class="platform-review-field">
        <label class="platform-review-field__label">
            <span class="platform-review-field__label-icon platform-review-field__label-icon--gold"><i class="fas fa-star"></i></span>
            التقييم <span class="text-danger">*</span>
        </label>
        <div class="pr-rating-input" role="radiogroup" aria-label="تقييم المنصة">
            @foreach([5, 4, 3, 2, 1] as $star)
                <input type="radio" id="pr-star{{ $star }}" name="rating" value="{{ $star }}" required
                    {{ (string) $ratingValue === (string) $star ? 'checked' : '' }}>
                <label for="pr-star{{ $star }}" title="{{ $star }} نجوم">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                </label>
            @endforeach
        </div>
        <p class="pr-rating-input__caption" id="pr-rating-caption" aria-live="polite"></p>
        @error('rating')
            <div class="text-danger mt-1 small">{{ $message }}</div>
        @enderror
    </div>

    <div class="platform-review-field">
        <label for="student_position" class="platform-review-field__label">
            <span class="platform-review-field__label-icon"><i class="fas fa-briefcase"></i></span>
            المسمى الوظيفي أو التعليمي
            <span class="text-muted fw-normal">(اختياري)</span>
        </label>
        <input type="text"
            class="form-control @error('student_position') is-invalid @enderror"
            id="student_position"
            name="student_position"
            value="{{ old('student_position', $review?->student_position) }}"
            placeholder="مثال: طالب جامعي، مهندس برمجيات، مصمم جرافيك">
        <p class="platform-review-field__hint">يساعدنا على فهم خلفيتك المهنية أو التعليمية</p>
        @error('student_position')
            <div class="text-danger mt-1 small">{{ $message }}</div>
        @enderror
    </div>

    <div class="platform-review-field">
        <label for="review_text" class="platform-review-field__label">
            <span class="platform-review-field__label-icon"><i class="fas fa-comment-dots"></i></span>
            رأيك حول المنصة والكورسات <span class="text-danger">*</span>
        </label>
        <textarea
            class="form-control @error('review_text') is-invalid @enderror"
            id="review_text"
            name="review_text"
            rows="6"
            required
            minlength="10"
            maxlength="1000"
            placeholder="شاركنا تجربتك... ما الذي أعجبك؟ كيف ساعدتك الكورسات؟ ما المهارات التي اكتسبتها؟">{{ old('review_text', $review?->review_text) }}</textarea>
        <div class="d-flex justify-content-between align-items-start gap-2 mt-2">
            <p class="platform-review-field__hint mb-0">الحد الأدنى 10 أحرف، الحد الأقصى 1000 حرف</p>
            <span class="platform-review-field__counter"><span id="char-count">{{ strlen(old('review_text', $review?->review_text ?? '')) }}</span> / 1000</span>
        </div>
        @error('review_text')
            <div class="text-danger mt-1 small">{{ $message }}</div>
        @enderror
    </div>

    <div class="platform-review-field">
        <label for="suggestion" class="platform-review-field__label">
            <span class="platform-review-field__label-icon"><i class="fas fa-lightbulb"></i></span>
            اقتراحاتك لتطوير المنصة
            <span class="text-muted fw-normal">(اختياري)</span>
        </label>
        <textarea
            class="form-control @error('suggestion') is-invalid @enderror"
            id="suggestion"
            name="suggestion"
            rows="4"
            maxlength="500"
            placeholder="ما الذي تود إضافته أو تحسينه في المنصة؟">{{ old('suggestion', $review?->suggestion) }}</textarea>
        <div class="d-flex justify-content-between align-items-start gap-2 mt-2">
            <p class="platform-review-field__hint mb-0">اقتراحاتك تساعدنا على التطوير المستمر</p>
            <span class="platform-review-field__counter"><span id="suggestion-count">{{ strlen(old('suggestion', $review?->suggestion ?? '')) }}</span> / 500</span>
        </div>
        @error('suggestion')
            <div class="text-danger mt-1 small">{{ $message }}</div>
        @enderror
    </div>

    <div class="platform-review-notice">
        <span class="platform-review-notice__icon"><i class="fas fa-info-circle"></i></span>
        <div>
            <strong>ملاحظة:</strong>
            {{ $isEdit
                ? 'عند حفظ التعديلات، سيُراجع تقييمك من قبل فريق الإدارة قبل نشره.'
                : 'سيتم مراجعة تقييمك من قبل فريق الإدارة قبل نشره على المنصة.' }}
        </div>
    </div>

    <div class="platform-review-form-actions">
        <button type="submit" class="btn {{ $submitBtnClass }}">
            <i class="fas {{ $submitIcon }} me-2"></i>{{ $submitLabel }}
        </button>
        <a href="{{ route('student.platform-review.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>رجوع
        </a>
    </div>
</form>
