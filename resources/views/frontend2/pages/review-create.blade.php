@extends('frontend2.layouts.master')

@php
    $pageTitle = 'إضافة تقييمك | ' . config('app.name');
    $pageDescription = 'شاركنا رأيك وتقييمك حول المنصة والكورسات والدورات التدريبية.';
    $canonicalUrl = route('frontend.reviews.create');
    $ogImage = asset('frontend2/assets/images/logo.png');
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@push('head')
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="ar_SA">
    <meta name="robots" content="noindex, follow">
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            { "@@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "{{ route('frontend.home') }}" },
            { "@@type": "ListItem", "position": 2, "name": "آراء الطلاب", "item": "{{ route('frontend.reviews.index') }}" },
            { "@@type": "ListItem", "position": 3, "name": "إضافة تقييم", "item": "{{ $canonicalUrl }}" }
        ]
    }
    </script>
@endpush

@section('content')

    <section class="page-banner page-banner-testimonials">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-star-half-stroke"></i></div>
                <h1 class="page-banner-title">أضف <span>تقييمك</span></h1>
                <p class="page-banner-desc">رأيك يهمنا ويساعدنا على تحسين المنصة وتقديم تجربة أفضل</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <a href="{{ route('frontend.reviews.index') }}">آراء الطلاب</a>
                    <span class="page-banner-sep">/</span>
                    <span>إضافة تقييم</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding review-create-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show animate-on-scroll" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show animate-on-scroll" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show animate-on-scroll" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                    </div>
                    @endif

                    <div class="glass-panel contact-form-wrapper review-create-form animate-on-scroll">
                        <h2 class="review-create-form__title"><i class="fas fa-pen-nib me-2"></i>شاركنا تجربتك</h2>
                        <p class="review-create-form__lead">قيّم تجربتك مع المنصة واكتب رأيك بصدق — التقييم يُراجع قبل النشر.</p>

                        <form action="{{ route('frontend.reviews.store') }}" method="POST" id="reviewCreateForm">
                            @csrf

                            <fieldset class="review-create-fieldset mb-4">
                                <legend class="form-label review-create-legend">
                                    <i class="fas fa-star text-warning me-1"></i> التقييم <span class="text-danger">*</span>
                                </legend>
                                <div class="f2-rating-input" role="radiogroup" aria-label="التقييم من 1 إلى 5">
                                    @php
                                        $starTitles = [
                                            5 => 'ممتاز — 5 نجوم',
                                            4 => 'جيد جداً — 4 نجوم',
                                            3 => 'جيد — 3 نجوم',
                                            2 => 'مقبول — نجمتان',
                                            1 => 'ضعيف — نجمة واحدة',
                                        ];
                                    @endphp
                                    @foreach([5, 4, 3, 2, 1] as $s)
                                        <input type="radio" name="rating" id="review-rating-{{ $s }}" value="{{ $s }}"
                                            {{ (string) old('rating') === (string) $s ? 'checked' : '' }}
                                            @if($s === 5) required @endif>
                                        <label for="review-rating-{{ $s }}" title="{{ $starTitles[$s] }}">
                                            <i class="fas fa-star" aria-hidden="true"></i>
                                            <span class="visually-hidden">{{ $s }} من 5</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('rating')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                                <p class="review-create-hint mb-0 mt-2">اضغط على النجوم لاختيار التقييم (من 1 إلى 5).</p>
                            </fieldset>

                            <div class="mb-4">
                                <label for="student_position" class="form-label fw-semibold">
                                    <i class="fas fa-briefcase me-1 text-primary"></i> المسمى الوظيفي أو التعليمي
                                    <span class="text-muted fw-normal small">(اختياري)</span>
                                </label>
                                <input type="text" class="form-control @error('student_position') is-invalid @enderror"
                                    id="student_position" name="student_position" value="{{ old('student_position') }}"
                                    placeholder="مثال: طالب جامعي، مهندس برمجيات، مصمم جرافيك">
                                <small class="text-muted d-block mt-1">يظهر بجانب اسمك عند نشر التقييم إن رغبت.</small>
                                @error('student_position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="review_text" class="form-label fw-semibold">
                                    <i class="fas fa-comment-dots me-1 text-primary"></i> رأيك حول المنصة والكورسات
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('review_text') is-invalid @enderror" id="review_text"
                                    name="review_text" rows="6" required minlength="10" maxlength="1000"
                                    placeholder="ما الذي أعجبك؟ كيف ساعدتك الكورسات؟">{{ old('review_text') }}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-1">
                                    <small class="text-muted">من 10 إلى 1000 حرف</small>
                                    <small class="review-create-charcount"><span id="review-char-count">0</span> / 1000</small>
                                </div>
                                @error('review_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="suggestion" class="form-label fw-semibold">
                                    <i class="fas fa-lightbulb me-1 text-primary"></i> اقتراحات لتطوير المنصة
                                    <span class="text-muted fw-normal small">(اختياري)</span>
                                </label>
                                <textarea class="form-control @error('suggestion') is-invalid @enderror" id="suggestion"
                                    name="suggestion" rows="4" maxlength="500"
                                    placeholder="ما الذي تود إضافته أو تحسينه؟">{{ old('suggestion') }}</textarea>
                                <small class="text-muted d-block mt-1">حتى 500 حرف</small>
                                @error('suggestion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info review-create-note mb-4" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>ملاحظة:</strong> يتم مراجعة تقييمك من الإدارة قبل نشره على الموقع.
                            </div>

                            <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-between">
                                <button type="submit" class="btn-primary-custom px-4" style="min-width: 200px; justify-content: center;">
                                    <i class="fas fa-paper-plane"></i> إرسال التقييم
                                </button>
                                <a href="{{ route('frontend.reviews.index') }}" class="btn btn-outline-secondary btn-lg px-4 align-self-center">
                                    <i class="fas fa-arrow-right ms-1"></i> العودة لآراء الطلاب
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ta = document.getElementById('review_text');
    var cc = document.getElementById('review-char-count');
    if (!ta || !cc) return;
    function sync() {
        var n = ta.value.length;
        cc.textContent = n;
        cc.style.color = n >= 1000 ? 'var(--bs-danger, #dc3545)' : (n >= 800 ? '#e8b923' : '');
    }
    ta.addEventListener('input', sync);
    sync();
});
</script>
@endpush
