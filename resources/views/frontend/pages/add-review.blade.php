@extends('frontend.layouts.master')

@section('title', 'إضافة تقييمك')
@section('meta_description', 'شاركنا رأيك وتقييمك حول المنصة والكورسات والدورات التدريبية المقدمة')

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">إضافة تقييمك ورأيك</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.reviews.index') }}">آراء الطلاب</a></li>
                        <li class="breadcrumb-item active">إضافة تقييم</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Review Form Section -->
<section class="add-review-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="review-form-card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-star-half-stroke"></i> شاركنا تجربتك</h3>
                        <p class="text-muted mb-0">رأيك يهمنا ويساعدنا على تحسين المنصة وتقديم محتوى أفضل</p>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('frontend.reviews.store') }}" method="POST">
                            @csrf

                            <!-- Rating -->
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fa-solid fa-star text-warning"></i>
                                    التقييم
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="rating-input-page d-flex gap-2 justify-content-center">
                                    <input type="radio" id="star5" name="rating" value="5" required>
                                    <label for="star5" title="ممتاز - 5 نجوم">
                                        <i class="fa-solid fa-star"></i>
                                    </label>

                                    <input type="radio" id="star4" name="rating" value="4">
                                    <label for="star4" title="جيد جداً - 4 نجوم">
                                        <i class="fa-solid fa-star"></i>
                                    </label>

                                    <input type="radio" id="star3" name="rating" value="3">
                                    <label for="star3" title="جيد - 3 نجوم">
                                        <i class="fa-solid fa-star"></i>
                                    </label>

                                    <input type="radio" id="star2" name="rating" value="2">
                                    <label for="star2" title="مقبول - نجمتين">
                                        <i class="fa-solid fa-star"></i>
                                    </label>

                                    <input type="radio" id="star1" name="rating" value="1">
                                    <label for="star1" title="ضعيف - نجمة واحدة">
                                        <i class="fa-solid fa-star"></i>
                                    </label>
                                </div>
                                <small class="form-text text-muted d-block text-center mt-2">
                                    اضغط على النجوم لتقييم تجربتك مع المنصة
                                </small>
                            </div>

                            <!-- Student Position -->
                            <div class="form-group mb-4">
                                <label for="student_position" class="form-label fw-bold">
                                    <i class="fa-solid fa-briefcase"></i>
                                    المسمى الوظيفي أو التعليمي
                                    <span class="text-muted">(اختياري)</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control form-control-lg"
                                    id="student_position"
                                    name="student_position"
                                    value="{{ old('student_position') }}"
                                    placeholder="مثال: طالب جامعي، مهندس برمجيات، مصمم جرافيك، إلخ">
                                <small class="form-text text-muted">
                                    يساعدنا على فهم خلفيتك المهنية أو التعليمية
                                </small>
                            </div>

                            <!-- Review Text -->
                            <div class="form-group mb-4">
                                <label for="review_text" class="form-label fw-bold">
                                    <i class="fa-solid fa-comment-dots"></i>
                                    رأيك حول المنصة والكورسات
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    class="form-control form-control-lg"
                                    id="review_text"
                                    name="review_text"
                                    rows="6"
                                    required
                                    minlength="10"
                                    maxlength="1000"
                                    placeholder="شاركنا تجربتك مع المنصة... ما الذي أعجبك؟ كيف ساعدتك الكورسات؟ ما هي المهارات التي اكتسبتها؟">{{ old('review_text') }}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="form-text text-muted">
                                        الحد الأدنى 10 أحرف، الحد الأقصى 1000 حرف
                                    </small>
                                    <small class="form-text text-muted char-counter">
                                        <span id="char-count">0</span> / 1000
                                    </small>
                                </div>
                            </div>

                            <!-- Suggestion -->
                            <div class="form-group mb-4">
                                <label for="suggestion" class="form-label fw-bold">
                                    <i class="fa-solid fa-lightbulb"></i>
                                    اقتراحاتك لتطوير المنصة
                                    <span class="text-muted">(اختياري)</span>
                                </label>
                                <textarea
                                    class="form-control form-control-lg"
                                    id="suggestion"
                                    name="suggestion"
                                    rows="4"
                                    maxlength="500"
                                    placeholder="نرحب باقتراحاتك لتحسين المنصة وتطويرها... ما الذي تود إضافته؟ كيف يمكننا تحسين تجربتك؟">{{ old('suggestion') }}</textarea>
                                <small class="form-text text-muted">
                                    الحد الأقصى 500 حرف - اقتراحاتك تساعدنا على التطوير المستمر
                                </small>
                            </div>

                            <!-- Info Note -->
                            <div class="alert alert-info" role="alert">
                                <i class="fa-solid fa-info-circle"></i>
                                <strong>ملاحظة:</strong> سيتم مراجعة تقييمك من قبل فريق الإدارة قبل نشره على المنصة.
                            </div>

                            <!-- Form Buttons -->
                            <div class="form-buttons d-flex gap-3 justify-content-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fa-solid fa-paper-plane"></i> إرسال التقييم
                                </button>
                                <a href="{{ route('frontend.reviews.index') }}" class="btn btn-outline-secondary btn-lg px-5">
                                    <i class="fa-solid fa-arrow-right"></i> رجوع
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<style>
/* Page Header */
.page-header {
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 80px 0 40px;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.page-header .breadcrumb {
    background: transparent;
}

.page-header .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.page-header .breadcrumb-item.active {
    color: #ffffff;
}

.page-header .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: rgba(255, 255, 255, 0.6);
}

/* Review Form Section */
.add-review-section {
    background-color: #f8f9fa;
    min-height: 70vh;
}

.review-form-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.review-form-card .card-header {
    background: var(--secondary-Color);
    color: white;
    padding: 30px;
    text-align: center;
}

.review-form-card .card-header h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.review-form-card .card-body {
    padding: 40px;
}

/* Form Groups */
.form-group {
    margin-bottom: 25px;
}

.form-label {
    color: #2c3e50;
    font-size: 1.1rem;
    margin-bottom: 10px;
    display: block;
}

.form-label i {
    margin-left: 8px;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    padding: 12px 18px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--main-Color);
    box-shadow: 0 0 0 0.2rem rgba(5, 86, 162, 0.15);
}

.form-control-lg {
    padding: 15px 20px;
    font-size: 1.05rem;
}

/* Rating Input */
.rating-input-page {
    direction: rtl;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    margin: 15px 0;
}

.rating-input-page input[type="radio"] {
    display: none;
}

.rating-input-page label {
    cursor: pointer;
    font-size: 3rem;
    color: #ddd;
    transition: all 0.2s ease;
    display: inline-block;
}

.rating-input-page label:hover {
    transform: scale(1.2);
}

.rating-input-page label i {
    transition: color 0.2s ease;
}

.rating-input-page label:hover i,
.rating-input-page label:hover ~ label i,
.rating-input-page input[type="radio"]:checked ~ label i {
    color: #ffc107;
}

/* Character Counter */
.char-counter {
    font-weight: 600;
    color: #6c757d;
}

/* Form Buttons */
.form-buttons {
    margin-top: 30px;
}

.btn-lg {
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 10px;
}

.btn-primary {
    background: var(--main-Color);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: var(--secondary-Color);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

/* Alert Info */
.alert-info {
    background-color: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-right: 4px solid #0056b3;
    color: #004085;
    border-radius: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }

    .review-form-card .card-header {
        padding: 20px;
    }

    .review-form-card .card-header h3 {
        font-size: 1.5rem;
    }

    .review-form-card .card-body {
        padding: 25px 20px;
    }

    .rating-input-page label {
        font-size: 2.5rem;
    }

    .form-buttons {
        flex-direction: column;
    }

    .form-buttons .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.5rem;
    }

    .rating-input-page label {
        font-size: 2rem;
    }

    .btn-lg {
        padding: 12px 30px;
        font-size: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for review text
    const reviewText = document.getElementById('review_text');
    const charCount = document.getElementById('char-count');

    if (reviewText && charCount) {
        reviewText.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;

            if (count >= 1000) {
                charCount.style.color = '#dc3545';
            } else if (count >= 800) {
                charCount.style.color = '#ffc107';
            } else {
                charCount.style.color = '#6c757d';
            }
        });

        // Initial count
        charCount.textContent = reviewText.value.length;
    }
});
</script>

@endsection
