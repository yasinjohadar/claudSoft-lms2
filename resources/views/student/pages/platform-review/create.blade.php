@extends('student.layouts.master')

@section('page-title')
    إضافة تقييم للمنصة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة تقييم للمنصة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('student.platform-review.index') }}">تقييمي للمنصة</a></li>
                            <li class="breadcrumb-item active">إضافة تقييم</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8 offset-xl-2">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-star me-2"></i>شاركنا تجربتك
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('student.platform-review.store') }}" method="POST">
                                @csrf

                                <!-- Rating -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-star text-warning me-2"></i>التقييم
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="rating-input-page d-flex gap-2 justify-content-start">
                                        <input type="radio" id="star5" name="rating" value="5" required {{ old('rating') == '5' ? 'checked' : '' }}>
                                        <label for="star5" title="ممتاز - 5 نجوم">
                                            <i class="fa-solid fa-star"></i>
                                        </label>

                                        <input type="radio" id="star4" name="rating" value="4" {{ old('rating') == '4' ? 'checked' : '' }}>
                                        <label for="star4" title="جيد جداً - 4 نجوم">
                                            <i class="fa-solid fa-star"></i>
                                        </label>

                                        <input type="radio" id="star3" name="rating" value="3" {{ old('rating') == '3' ? 'checked' : '' }}>
                                        <label for="star3" title="جيد - 3 نجوم">
                                            <i class="fa-solid fa-star"></i>
                                        </label>

                                        <input type="radio" id="star2" name="rating" value="2" {{ old('rating') == '2' ? 'checked' : '' }}>
                                        <label for="star2" title="مقبول - نجمتين">
                                            <i class="fa-solid fa-star"></i>
                                        </label>

                                        <input type="radio" id="star1" name="rating" value="1" {{ old('rating') == '1' ? 'checked' : '' }}>
                                        <label for="star1" title="ضعيف - نجمة واحدة">
                                            <i class="fa-solid fa-star"></i>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted d-block mt-2">
                                        اضغط على النجوم لتقييم تجربتك مع المنصة
                                    </small>
                                    @error('rating')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Student Position -->
                                <div class="mb-4">
                                    <label for="student_position" class="form-label fw-semibold">
                                        <i class="fas fa-briefcase me-2"></i>المسمى الوظيفي أو التعليمي
                                        <span class="text-muted">(اختياري)</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control @error('student_position') is-invalid @enderror"
                                        id="student_position"
                                        name="student_position"
                                        value="{{ old('student_position') }}"
                                        placeholder="مثال: طالب جامعي، مهندس برمجيات، مصمم جرافيك، إلخ">
                                    <small class="form-text text-muted">
                                        يساعدنا على فهم خلفيتك المهنية أو التعليمية
                                    </small>
                                    @error('student_position')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Review Text -->
                                <div class="mb-4">
                                    <label for="review_text" class="form-label fw-semibold">
                                        <i class="fas fa-comment-dots me-2"></i>رأيك حول المنصة والكورسات
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea
                                        class="form-control @error('review_text') is-invalid @enderror"
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
                                    @error('review_text')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Suggestion -->
                                <div class="mb-4">
                                    <label for="suggestion" class="form-label fw-semibold">
                                        <i class="fas fa-lightbulb me-2"></i>اقتراحاتك لتطوير المنصة
                                        <span class="text-muted">(اختياري)</span>
                                    </label>
                                    <textarea
                                        class="form-control @error('suggestion') is-invalid @enderror"
                                        id="suggestion"
                                        name="suggestion"
                                        rows="4"
                                        maxlength="500"
                                        placeholder="نرحب باقتراحاتك لتحسين المنصة وتطويرها... ما الذي تود إضافته؟ كيف يمكننا تحسين تجربتك؟">{{ old('suggestion') }}</textarea>
                                    <small class="form-text text-muted">
                                        الحد الأقصى 500 حرف - اقتراحاتك تساعدنا على التطوير المستمر
                                    </small>
                                    @error('suggestion')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Info Note -->
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>ملاحظة:</strong> سيتم مراجعة تقييمك من قبل فريق الإدارة قبل نشره على المنصة.
                                </div>

                                <!-- Form Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>إرسال التقييم
                                    </button>
                                    <a href="{{ route('student.platform-review.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-right me-2"></i>رجوع
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        /* Rating Stars */
        .rating-input-page {
            display: flex;
            gap: 5px;
            direction: rtl;
        }

        .rating-input-page input[type="radio"] {
            display: none;
        }

        .rating-input-page label {
            cursor: pointer;
            font-size: 30px;
            color: #ddd;
            transition: color 0.2s;
        }

        .rating-input-page input[type="radio"]:checked ~ label,
        .rating-input-page label:hover,
        .rating-input-page label:hover ~ label {
            color: #ffc107;
        }

        .rating-input-page input[type="radio"]:checked ~ label {
            color: #ffc107;
        }
    </style>

    <script>
        // Character counter
        document.addEventListener('DOMContentLoaded', function() {
            const reviewText = document.getElementById('review_text');
            const charCount = document.getElementById('char-count');
            
            if (reviewText && charCount) {
                charCount.textContent = reviewText.value.length;
                
                reviewText.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }
        });
    </script>
@endsection

