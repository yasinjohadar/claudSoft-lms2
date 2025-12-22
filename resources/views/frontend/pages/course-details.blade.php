@extends('frontend.layouts.master')

@section('title', $course->meta_title ?? $course->title)
@section('meta_description', $course->meta_description ?? $course->description)

@section('seo_meta')
    @include('frontend.components.seo-meta', ['course' => $course])
@endsection

@section('content')

<!-- Course Hero Section -->
<section class="course-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="#">الكورسات</a></li>
                        <li class="breadcrumb-item"><a href="#">{{ $course->category->name }}</a></li>
                        <li class="breadcrumb-item active">{{ $course->title }}</li>
                    </ol>
                </nav>

                <h1 class="course-title">{{ $course->title }}</h1>
                <p class="course-subtitle">{{ $course->subtitle }}</p>

                <div class="course-meta d-flex flex-wrap gap-4 align-items-center">
                    <div class="meta-item">
                        <i class="fa-solid fa-star text-warning"></i>
                        <span class="fw-bold">{{ $course->rating }}</span>
                        <span class="text-muted">({{ $course->reviews_count }} تقييم)</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>{{ number_format($course->students_count) }} طالب</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-clock"></i>
                        <span>{{ $course->duration }} ساعة</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-play-circle"></i>
                        <span>{{ $course->lessons_count }} درس</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-signal"></i>
                        <span>{{ __('المستوى: ' . $course->level) }}</span>
                    </div>
                </div>

                <div class="instructor-info mt-4">
                    <span class="text-muted">المدرب:</span>
                    <strong>{{ $course->instructor->name }}</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Course Content -->
<section class="course-content py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">

                <!-- Course Image/Video -->
                <div class="course-preview mb-4">
                    @if($course->preview_video)
                        <div class="video-container">
                            <video controls poster="{{ $course->thumbnail_url }}" class="w-100">
                                <source src="{{ asset($course->preview_video) }}" type="video/mp4">
                                المتصفح الخاص بك لا يدعم تشغيل الفيديو
                            </video>
                        </div>
                    @else
                        <img src="{{ $course->thumbnail_url }}" 
                             alt="{{ $course->title }} - {{ $course->subtitle ?? '' }}"
                             title="{{ $course->title }}"
                             class="img-fluid rounded"
                             loading="eager"
                             width="1200"
                             height="675">
                    @endif
                </div>

                <!-- Course Description -->
                <div class="course-section mb-4">
                    <h3 class="section-title">نظرة عامة على الكورس</h3>
                    <div class="section-content">
                        <p>{{ $course->description }}</p>
                    </div>
                </div>

                <!-- What You'll Learn -->
                @if($course->what_you_learn && is_array($course->what_you_learn) && count($course->what_you_learn) > 0)
                <div class="course-section mb-4">
                    <h3 class="section-title">ماذا ستتعلم</h3>
                    <div class="section-content">
                        <div class="row">
                            @foreach($course->what_you_learn as $item)
                            <div class="col-md-6 mb-3">
                                <div class="learn-item">
                                    <i class="fa-solid fa-check-circle text-success"></i>
                                    <span>{{ $item }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Requirements -->
                @if($course->requirements)
                <div class="course-section mb-4">
                    <h3 class="section-title">المتطلبات</h3>
                    <div class="section-content">
                        <div class="requirement-item">
                            <i class="fa-solid fa-info-circle text-primary"></i>
                            <span>{{ $course->requirements }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Course Curriculum (Sections & Lessons) -->
                @if($course->sections && $course->sections->count() > 0)
                <div class="course-section mb-4">
                    <h3 class="section-title">محتوى الكورس</h3>
                    <div class="section-content">
                        <div class="curriculum-stats mb-3">
                            <span><i class="fa-solid fa-folder-open text-primary"></i> {{ $course->sections->count() }} محور</span>
                            <span class="mx-3">•</span>
                            <span><i class="fa-solid fa-play-circle text-success"></i> {{ $course->lessons_count }} درس</span>
                            <span class="mx-3">•</span>
                            <span><i class="fa-solid fa-clock text-warning"></i> {{ $course->duration }} ساعة</span>
                        </div>

                        <div class="accordion curriculum-accordion" id="curriculumAccordion">
                            @foreach($course->sections as $sectionIndex => $section)
                            <div class="accordion-item mb-2">
                                <h2 class="accordion-header" id="heading{{ $sectionIndex }}">
                                    <button class="accordion-button {{ $sectionIndex > 0 ? 'collapsed' : '' }}"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $sectionIndex }}"
                                            aria-expanded="{{ $sectionIndex == 0 ? 'true' : 'false' }}"
                                            aria-controls="collapse{{ $sectionIndex }}">
                                        <div class="section-header-content">
                                            <div class="section-title-wrapper">
                                                <i class="fa-solid fa-folder-open me-2"></i>
                                                <strong>{{ $section->title }}</strong>
                                            </div>
                                            <div class="section-meta">
                                                <span class="lessons-count">{{ $section->lessons_count }} دروس</span>
                                                @if($section->duration > 0)
                                                <span class="ms-2">• {{ round($section->duration / 60, 1) }} ساعة</span>
                                                @endif
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $sectionIndex }}"
                                     class="accordion-collapse collapse {{ $sectionIndex == 0 ? 'show' : '' }}"
                                     aria-labelledby="heading{{ $sectionIndex }}"
                                     data-bs-parent="#curriculumAccordion">
                                    <div class="accordion-body">
                                        @if($section->description)
                                        <p class="section-description">{{ $section->description }}</p>
                                        @endif

                                        @if($section->lessons && $section->lessons->count() > 0)
                                        <ul class="lessons-list">
                                            @foreach($section->lessons as $lesson)
                                            <li class="lesson-item">
                                                <div class="lesson-info">
                                                    <div class="lesson-icon-title">
                                                        @switch($lesson->type)
                                                            @case('video')
                                                                <i class="fa-solid fa-play-circle text-primary"></i>
                                                                @break
                                                            @case('text')
                                                                <i class="fa-solid fa-file-alt text-info"></i>
                                                                @break
                                                            @case('file')
                                                                <i class="fa-solid fa-file-download text-success"></i>
                                                                @break
                                                            @case('quiz')
                                                                <i class="fa-solid fa-question-circle text-warning"></i>
                                                                @break
                                                            @case('live')
                                                                <i class="fa-solid fa-video text-danger"></i>
                                                                @break
                                                            @default
                                                                <i class="fa-solid fa-circle text-secondary"></i>
                                                        @endswitch
                                                        <span class="lesson-title">{{ $lesson->title }}</span>
                                                    </div>
                                                    <div class="lesson-details">
                                                        @if($lesson->duration)
                                                        <span class="lesson-duration">
                                                            <i class="fa-regular fa-clock"></i>
                                                            {{ $lesson->duration }} د
                                                        </span>
                                                        @endif
                                                        @if($lesson->is_free)
                                                        <span class="badge bg-success ms-2">معاينة مجانية</span>
                                                        @else
                                                        <span class="lesson-lock">
                                                            <i class="fa-solid fa-lock"></i>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @else
                                        <p class="text-muted text-center py-2">لا توجد دروس في هذا المحور حالياً</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Course Features -->
                <div class="course-section mb-4">
                    <h3 class="section-title">مميزات الكورس</h3>
                    <div class="section-content">
                        <div class="row">
                            @if($course->certificate)
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fa-solid fa-certificate text-warning"></i>
                                    <span>شهادة إتمام معتمدة</span>
                                </div>
                            </div>
                            @endif
                            @if($course->lifetime_access)
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fa-solid fa-infinity text-primary"></i>
                                    <span>وصول مدى الحياة</span>
                                </div>
                            </div>
                            @endif
                            @if($course->downloadable_resources)
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fa-solid fa-download text-success"></i>
                                    <span>موارد قابلة للتحميل</span>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <div class="feature-item">
                                    <i class="fa-solid fa-mobile-screen text-info"></i>
                                    <span>متوفر على الجوال والكمبيوتر</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Reviews -->
                @if($reviews && $reviews->count() > 0)
                <div class="course-section mb-4">
                    <h3 class="section-title">آراء الطلاب</h3>
                    <div class="section-content">

                        <!-- Overall Rating Summary -->
                        <div class="rating-summary mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center">
                                    <div class="overall-rating">
                                        <div class="rating-number">{{ number_format($course->rating, 1) }}</div>
                                        <div class="rating-stars mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($course->rating))
                                                    <i class="fa-solid fa-star text-warning"></i>
                                                @elseif($i - 0.5 <= $course->rating)
                                                    <i class="fa-solid fa-star-half-stroke text-warning"></i>
                                                @else
                                                    <i class="fa-regular fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <div class="rating-count text-muted">{{ $course->reviews_count }} تقييم</div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="rating-bars">
                                        @php
                                            $ratingDistribution = $reviews->groupBy('rating')->map->count();
                                            $totalReviews = $reviews->count();
                                        @endphp
                                        @for($star = 5; $star >= 1; $star--)
                                            @php
                                                $count = $ratingDistribution->get($star, 0);
                                                $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                                            @endphp
                                            <div class="rating-bar-item">
                                                <span class="star-label">{{ $star }} نجوم</span>
                                                <div class="progress flex-grow-1 mx-3">
                                                    <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <span class="rating-percentage">{{ number_format($percentage, 0) }}%</span>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div class="reviews-list">
                            @foreach($reviews as $review)
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-avatar">
                                            @if($review->student_image)
                                                <img src="{{ asset('storage/' . $review->student_image) }}" 
                                                     alt="{{ $review->student_name }} - طالب"
                                                     title="{{ $review->student_name }}"
                                                     loading="lazy"
                                                     width="50"
                                                     height="50">
                                            @else
                                                <div class="avatar-placeholder">{{ substr($review->student_name, 0, 1) }}</div>
                                            @endif
                                        </div>
                                        <div class="reviewer-details">
                                            <h6 class="reviewer-name">{{ $review->student_name }}</h6>
                                            @if($review->student_position)
                                            <p class="reviewer-position">{{ $review->student_position }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fa-solid fa-star text-warning"></i>
                                            @else
                                                <i class="fa-regular fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                        <span class="review-date ms-2 text-muted">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>{{ $review->review_text }}</p>
                                </div>
                                @if($review->is_featured)
                                <div class="featured-badge-review">
                                    <i class="fa-solid fa-badge-check"></i> تقييم مميز
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                    </div>
                </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="course-sidebar sticky-top">

                    <!-- Price Card -->
                    <div class="price-card mb-4">
                        @if($course->is_free)
                            <h2 class="price free-price">مجاني</h2>
                        @else
                            <div class="price-wrapper">
                                @if($course->has_discount)
                                    <h2 class="price">{{ $course->discount_price }} {{ $course->currency }}</h2>
                                    <p class="original-price">{{ $course->price }} {{ $course->currency }}</p>
                                    <span class="discount-badge">خصم {{ $course->discount_percentage }}%</span>
                                @else
                                    <h2 class="price">{{ $course->price }} {{ $course->currency }}</h2>
                                @endif
                            </div>
                        @endif

                        <button class="btn btn-enroll w-100 mb-3">
                            <i class="fa-solid fa-graduation-cap"></i>
                            سجل الآن
                        </button>

                        <button class="btn btn-wishlist w-100 mb-3">
                            <i class="fa-regular fa-heart"></i>
                            أضف للمفضلة
                        </button>

                        <div class="course-includes">
                            <h5 class="mb-3">يتضمن هذا الكورس:</h5>
                            <ul class="includes-list">
                                <li><i class="fa-solid fa-video"></i> {{ $course->lessons_count }} درس فيديو</li>
                                <li><i class="fa-solid fa-clock"></i> {{ $course->duration }} ساعة من المحتوى</li>
                                @if($course->certificate)
                                <li><i class="fa-solid fa-certificate"></i> شهادة إتمام</li>
                                @endif
                                @if($course->lifetime_access)
                                <li><i class="fa-solid fa-infinity"></i> وصول مدى الحياة</li>
                                @endif
                                @if($course->downloadable_resources)
                                <li><i class="fa-solid fa-download"></i> موارد قابلة للتحميل</li>
                                @endif
                                <li><i class="fa-solid fa-mobile"></i> الوصول عبر الجوال</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Share Course -->
                    <div class="share-card">
                        <h5 class="mb-3">شارك هذا الكورس</h5>
                        <div class="social-share d-flex gap-2">
                            @if(isset($socialLinks) && $socialLinks->count() > 0)
                                @foreach($socialLinks as $social)
                                    @if($social['platform'] == 'copy')
                                        <a href="#" 
                                           class="share-btn copy" 
                                           onclick="copyCourseLink(event); return false;" 
                                           title="{{ $social['label'] ?? 'نسخ الرابط' }}">
                                            <i class="fa-solid fa-link"></i>
                                        </a>
                                    @else
                                        <a href="{{ $social['url'] ?? '#' }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           class="share-btn {{ $social['platform'] }}" 
                                           title="{{ $social['label'] ?? '' }}">
                                            <i class="fa-brands {{ $social['icon'] ?? 'fa-link' }}"></i>
                                        </a>
                                    @endif
                                @endforeach
                            @else
                                {{-- Fallback if no social links --}}
                                <a href="#" class="share-btn facebook"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="#" class="share-btn twitter"><i class="fa-brands fa-twitter"></i></a>
                                <a href="#" class="share-btn whatsapp"><i class="fa-brands fa-whatsapp"></i></a>
                                <a href="#" class="share-btn telegram"><i class="fa-brands fa-telegram"></i></a>
                                <a href="#" class="share-btn copy" onclick="copyCourseLink(event); return false;"><i class="fa-solid fa-link"></i></a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Courses -->
@if($relatedCourses->count() > 0)
<section class="related-courses py-5 bg-light">
    <div class="container">
        <h3 class="section-title mb-4">كورسات ذات صلة</h3>
        <div class="row">
            @foreach($relatedCourses as $relatedCourse)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="course-card">
                    <div class="course-img">
                        <img src="{{ $relatedCourse->thumbnail_url }}" 
                             alt="{{ $relatedCourse->title }} - {{ $relatedCourse->subtitle ?? '' }}"
                             title="{{ $relatedCourse->title }}"
                             loading="lazy"
                             width="400"
                             height="225">
                        @if($relatedCourse->is_featured)
                        <span class="featured-badge">مميز</span>
                        @endif
                    </div>
                    <div class="course-body">
                        <span class="course-category">{{ $relatedCourse->category->name }}</span>
                        <h5 class="course-card-title">{{ Str::limit($relatedCourse->title, 50) }}</h5>
                        <div class="course-rating mb-2">
                            <i class="fa-solid fa-star text-warning"></i>
                            <span>{{ $relatedCourse->rating }}</span>
                            <span class="text-muted">({{ $relatedCourse->reviews_count }})</span>
                        </div>
                        <div class="course-footer d-flex justify-content-between align-items-center">
                            @if($relatedCourse->is_free)
                                <span class="price-tag free">مجاني</span>
                            @else
                                <span class="price-tag">{{ $relatedCourse->final_price }} {{ $relatedCourse->currency }}</span>
                            @endif
                            <a href="{{ route('frontend.courses.show', $relatedCourse->slug) }}" class="btn btn-sm btn-view">عرض</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<style>
/* Course Hero Section */
.course-hero {
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 80px 0 40px;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 20px;
}

.breadcrumb-item a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #ffffff;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: rgba(255, 255, 255, 0.6);
}

.course-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.course-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 25px;
}

.course-meta {
    margin-top: 20px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.95);
}

.meta-item i {
    font-size: 18px;
}

.instructor-info {
    padding: 15px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

/* Course Content */
.course-content {
    margin-top: -40px;
}

.course-preview {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.course-preview img,
.video-container {
    border-radius: 10px;
}

.course-section {
    background: #ffffff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--main-Color);
}

.section-content {
    color: #555;
    line-height: 1.8;
}

.learn-item,
.feature-item,
.requirement-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 15px;
}

.learn-item i,
.feature-item i,
.requirement-item i {
    font-size: 20px;
    margin-top: 2px;
}

/* Sidebar */
.course-sidebar {
    top: 100px;
}

.price-card,
.share-card {
    background: #ffffff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.price-wrapper {
    text-align: center;
    margin-bottom: 20px;
}

.price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.price.free-price {
    color: #38c172;
    text-align: center;
    margin-bottom: 20px;
}

.original-price {
    font-size: 1.2rem;
    color: #999;
    text-decoration: line-through;
    margin: 10px 0;
}

.discount-badge {
    display: inline-block;
    background: #e74c3c;
    color: #ffffff;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.btn-enroll {
    background: var(--secondary-Color);
    color: #ffffff;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 15px;
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-enroll:hover {
    background: var(--secondary-Color);
    opacity: 0.9;
    transform: translateY(-2px);
}

.btn-wishlist {
    background: #ffffff;
    color: var(--secondary-Color);
    font-weight: 600;
    padding: 12px;
    border: 2px solid var(--secondary-Color);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-wishlist:hover {
    background: var(--secondary-Color);
    color: #ffffff;
}

.course-includes h5 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.includes-list {
    list-style: none;
    padding: 0;
}

.includes-list li {
    padding: 10px 0;
    color: #555;
    display: flex;
    align-items: center;
    gap: 10px;
}

.includes-list li i {
    color: var(--main-Color);
    font-size: 18px;
}

/* Share Buttons */
.social-share {
    justify-content: center;
}

.share-btn {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #ffffff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.share-btn.facebook {
    background: #3b5998;
}

.share-btn.twitter {
    background: #1da1f2;
}

.share-btn.whatsapp {
    background: #25d366;
}

.share-btn.telegram {
    background: #0088cc;
}

.share-btn.copy {
    background: #95a5a6;
}

.share-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* Curriculum Section */
.curriculum-stats {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 14px;
    color: #555;
}

.curriculum-stats i {
    margin-left: 5px;
}

.curriculum-accordion .accordion-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.curriculum-accordion .accordion-button {
    background: #f8f9fa;
    color: #2c3e50;
    font-size: 15px;
    padding: 15px 20px;
    border: none;
}

.curriculum-accordion .accordion-button:not(.collapsed) {
    background: var(--main-Color);
    color: #ffffff;
    box-shadow: none;
}

.curriculum-accordion .accordion-button:focus {
    box-shadow: none;
    border: none;
}

.curriculum-accordion .accordion-button::after {
    margin-right: auto;
    margin-left: 15px;
}

.section-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    margin-left: 30px;
}

.section-title-wrapper {
    display: flex;
    align-items: center;
    font-weight: 600;
}

.section-meta {
    font-size: 13px;
    opacity: 0.9;
}

.section-description {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 15px;
    font-size: 14px;
    color: #666;
}

.lessons-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.lesson-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.lesson-item:last-child {
    border-bottom: none;
}

.lesson-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.lesson-icon-title {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.lesson-icon-title i {
    font-size: 16px;
}

.lesson-title {
    color: #2c3e50;
    font-size: 14px;
}

.lesson-details {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #777;
}

.lesson-duration {
    display: flex;
    align-items: center;
    gap: 5px;
}

.lesson-lock {
    color: #95a5a6;
}

/* Reviews Section */
.rating-summary {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 10px;
}

.overall-rating {
    padding: 20px;
}

.rating-number {
    font-size: 4rem;
    font-weight: 700;
    color: var(--main-Color);
    line-height: 1;
}

.rating-stars {
    font-size: 1.5rem;
}

.rating-count {
    font-size: 14px;
}

.rating-bars {
    padding: 10px 0;
}

.rating-bar-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    font-size: 14px;
}

.star-label {
    min-width: 70px;
    color: #555;
}

.rating-bar-item .progress {
    height: 8px;
    background: #e9ecef;
}

.rating-percentage {
    min-width: 45px;
    text-align: left;
    color: #777;
    font-size: 13px;
}

.reviews-list {
    margin-top: 30px;
}

.review-item {
    padding: 25px 0;
    border-bottom: 1px solid #e9ecef;
}

.review-item:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.reviewer-info {
    display: flex;
    gap: 15px;
    align-items: center;
}

.reviewer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.reviewer-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--main-Color);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
}

.reviewer-details {
    flex: 1;
}

.reviewer-name {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.reviewer-position {
    margin: 0;
    font-size: 13px;
    color: #777;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
}

.review-rating i {
    font-size: 16px;
}

.review-date {
    font-size: 12px;
}

.review-content p {
    color: #555;
    line-height: 1.8;
    margin: 0;
}

.featured-badge-review {
    display: inline-block;
    background: var(--secondary-Color);
    color: #ffffff;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 10px;
}

.featured-badge-review i {
    margin-left: 5px;
}

/* Related Courses */
.related-courses {
    background: #f8f9fa;
}

.course-card {
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.course-card .course-img {
    position: relative;
    overflow: hidden;
    height: 180px;
}

.course-card .course-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.course-card:hover .course-img img {
    transform: scale(1.1);
}

.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #f39c12;
    color: #ffffff;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.course-body {
    padding: 20px;
}

.course-category {
    display: inline-block;
    background: #ecf0f1;
    color: #7f8c8d;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    margin-bottom: 10px;
}

.course-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 10px;
    min-height: 48px;
}

.course-rating {
    font-size: 14px;
}

.price-tag {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--main-Color);
}

.price-tag.free {
    color: #38c172;
}

.btn-view {
    background: var(--main-Color);
    color: #ffffff;
    border: none;
    padding: 8px 20px;
    border-radius: 5px;
}

.btn-view:hover {
    background: var(--secondary-Color);
    color: #ffffff;
}

/* Responsive */
@media (max-width: 992px) {
    .course-title {
        font-size: 2rem;
    }

    .course-sidebar {
        margin-top: 30px;
        position: relative !important;
        top: auto !important;
    }
}

@media (max-width: 768px) {
    .course-hero {
        padding: 60px 0 30px;
    }

    .course-title {
        font-size: 1.5rem;
    }

    .course-subtitle {
        font-size: 1rem;
    }

    .course-meta {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px !important;
    }

    .section-title {
        font-size: 1.2rem;
    }

    .curriculum-stats {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .section-header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .lesson-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .lesson-details {
        width: 100%;
        justify-content: space-between;
    }

    .rating-summary {
        padding: 20px;
    }

    .rating-number {
        font-size: 3rem;
    }

    .review-header {
        flex-direction: column;
        gap: 15px;
    }

    .review-rating {
        align-self: flex-start;
    }
}
</style>

@section('script')
<script>
function copyCourseLink(event) {
    event.preventDefault();
    const courseUrl = window.location.href;
    
    // Copy to clipboard
    navigator.clipboard.writeText(courseUrl).then(function() {
        // Show success message
        const btn = event.currentTarget;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        btn.style.background = '#28a745';
        
        setTimeout(function() {
            btn.innerHTML = originalHTML;
            btn.style.background = '';
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('فشل نسخ الرابط');
    });
}
</script>
@endsection
