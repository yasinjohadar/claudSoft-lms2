@extends('frontend2.layouts.master')

@section('title', $course->meta_title ?? $course->title)
@section('meta_description', $course->meta_description ?? Str::limit(strip_tags($course->description ?? ''), 160))

@push('head')
    @include('frontend.components.seo-meta', ['course' => $course])
@endpush

@section('content')

@php
    $bannerDesc = $course->subtitle
        ? $course->subtitle
        : Str::limit(strip_tags($course->description ?? ''), 160);
@endphp

    <!-- Page Banner (موحّد مع صفحة الكورسات) -->
    <section class="page-banner page-banner-courses">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-graduation-cap"></i></div>
                <h1 class="page-banner-title">{{ $course->title }}</h1>
                <p class="page-banner-desc">{{ $bannerDesc }}</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <a href="{{ route('frontend.courses.index') }}">الكورسات</a>
                    @if($course->category)
                    <span class="page-banner-sep">/</span>
                    <a href="{{ route('frontend.courses.index', ['category' => $course->category_id]) }}">{{ $course->category->name }}</a>
                    @endif
                    <span class="page-banner-sep">/</span>
                    <span>{{ Str::limit($course->title, 50) }}</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

<!-- Course Content -->
<section class="course-content section-padding" style="padding-top: 30px;">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">

                <div class="article-meta-line d-flex flex-wrap gap-3 mb-3 small animate-on-scroll">
                    <span><i class="fas fa-star text-warning me-1"></i><strong>{{ $course->rating }}</strong> <span class="opacity-75">({{ $course->reviews_count }} تقييم)</span></span>
                    <span><i class="fas fa-user-graduate me-1"></i>{{ number_format($course->students_count) }} طالب</span>
                    <span><i class="fas fa-clock me-1"></i>{{ $course->duration }} ساعة</span>
                    <span><i class="fas fa-play-circle me-1"></i>{{ $course->lessons_count }} درس</span>
                    <span><i class="fas fa-signal me-1"></i>{{ __('المستوى: ' . $course->level) }}</span>
                    @if($course->instructor)
                    <span><i class="fas fa-chalkboard-teacher me-1"></i>المدرب: {{ $course->instructor->name }}</span>
                    @endif
                </div>

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

                        @auth
                            @php
                                $user = auth()->user();
                            @endphp
                            @if(method_exists($user, 'hasRole') && $user->hasRole('admin'))
                                <a href="{{ route('admin.frontend-courses.edit', $course->id) }}" 
                                   class="btn btn-outline-warning w-100 mb-3">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>
                                    تعديل الكورس (لوحة التحكم)
                                </a>
                            @endif
                        @endauth

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
                        @php
                            $courseUrl = urlencode(request()->url());
                            $courseTitle = urlencode($course->title);
                        @endphp
                        <div class="social-share d-flex gap-2">
                            @if(!empty($socialLinks))
                                @foreach($socialLinks as $social)
                                    @if(($social['enabled'] ?? true) && !empty($social['url']))
                                        @php
                                            $platform = strtolower($social['platform'] ?? '');
                                            $shareUrl = '#';
                                            $iconClass = $social['icon'] ?? 'fa-link';
                                            $btnClass = 'copy';
                                            
                                            // تحديد رابط المشاركة حسب المنصة
                                            if (str_contains($platform, 'facebook')) {
                                                $shareUrl = "https://www.facebook.com/sharer/sharer.php?u={$courseUrl}";
                                                $btnClass = 'facebook';
                                            } elseif (str_contains($platform, 'twitter') || str_contains($platform, 'x')) {
                                                $shareUrl = "https://twitter.com/intent/tweet?url={$courseUrl}&text={$courseTitle}";
                                                $btnClass = 'twitter';
                                            } elseif (str_contains($platform, 'whatsapp')) {
                                                $shareUrl = "https://wa.me/?text={$courseTitle}%20{$courseUrl}";
                                                $btnClass = 'whatsapp';
                                            } elseif (str_contains($platform, 'telegram')) {
                                                $shareUrl = "https://t.me/share/url?url={$courseUrl}&text={$courseTitle}";
                                                $btnClass = 'telegram';
                                            } elseif (str_contains($platform, 'linkedin')) {
                                                $shareUrl = "https://www.linkedin.com/shareArticle?mini=true&url={$courseUrl}&title={$courseTitle}";
                                                $btnClass = 'linkedin';
                                            }
                                        @endphp
                                        <a href="{{ $shareUrl }}" 
                                           class="share-btn {{ $btnClass }}" 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           title="{{ $social['label'] ?? 'شارك' }}">
                                            <i class="fa-brands {{ $iconClass }}"></i>
                                        </a>
                                    @endif
                                @endforeach
                            @else
                                {{-- روابط مشاركة افتراضية في حال عدم وجود إعدادات --}}
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $courseUrl }}" class="share-btn facebook" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="https://twitter.com/intent/tweet?url={{ $courseUrl }}&text={{ $courseTitle }}" class="share-btn twitter" target="_blank"><i class="fa-brands fa-twitter"></i></a>
                                <a href="https://wa.me/?text={{ $courseTitle }}%20{{ $courseUrl }}" class="share-btn whatsapp" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
                                <a href="https://t.me/share/url?url={{ $courseUrl }}&text={{ $courseTitle }}" class="share-btn telegram" target="_blank"><i class="fa-brands fa-telegram"></i></a>
                            @endif
                            {{-- زر نسخ الرابط --}}
                            <button type="button" class="share-btn copy" onclick="copyToClipboard('{{ request()->url() }}')" title="نسخ الرابط">
                                <i class="fa-solid fa-link"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Courses (نفس بطاقات صفحة الكورسات) -->
@if($relatedCourses->count() > 0)
<section class="section-padding related-courses-section" style="background: var(--clr-bg);">
    <div class="container">
        <div class="section-header animate-on-scroll">
            <span class="section-badge">الكورسات</span>
            <h2>كورسات ذات صلة</h2>
            <p>دورات أخرى قد تناسب اهتمامك في نفس المجال</p>
        </div>
        <div class="row g-4">
            @foreach($relatedCourses as $relatedCourse)
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('frontend.courses.show', $relatedCourse->slug) }}" class="glass-panel course-card animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}" style="text-decoration: none; color: inherit; display: block; height: 100%;">
                    <div class="course-img-wrapper">
                        <img src="{{ $relatedCourse->thumbnail ? course_image_url($relatedCourse->thumbnail) : asset('frontend2/assets/images/course-webdev.svg') }}" alt="{{ $relatedCourse->title }}" width="400" height="200" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                        @if($relatedCourse->is_free)<span class="course-badge" style="top: auto; right: auto; bottom: 10px; left: 10px; background: var(--clr-secondary);">مجاني</span>@endif
                        @if($relatedCourse->hasDiscount())<span class="course-badge" style="top: 10px; left: 10px; right: auto; background: #e74c3c;">خصم {{ $relatedCourse->discount_percentage }}%</span>@endif
                    </div>
                    <div class="course-body">
                        @if($relatedCourse->category)
                        <span class="d-inline-block small mb-2" style="color: var(--clr-primary);">
                            <i class="fa {{ $relatedCourse->category->icon ?? 'fa-folder' }} me-1"></i>{{ $relatedCourse->category->name }}
                        </span>
                        @endif
                        <h5 class="course-card-title">{{ Str::limit($relatedCourse->title, 55) }}</h5>
                        <p class="course-card-subtitle">{{ Str::limit(strip_tags($relatedCourse->subtitle ?? $relatedCourse->description ?? ''), 80) }}</p>
                        <div class="course-meta d-flex flex-wrap gap-2 small" style="color: var(--clr-text-muted);">
                            @if($relatedCourse->rating > 0)<span><i class="fas fa-star text-warning"></i> {{ $relatedCourse->rating }}</span>@endif
                            <span><i class="fas fa-user-graduate"></i> {{ number_format($relatedCourse->students_count ?? 0) }}</span>
                            <span><i class="fas fa-clock"></i> {{ $relatedCourse->duration ? number_format((float) $relatedCourse->duration, 1) . ' ساعة' : '—' }}</span>
                        </div>
                    </div>
                    <div class="course-footer">
                        @if($relatedCourse->is_free)
                            <span class="price" style="color: var(--clr-secondary); font-weight: 700;">مجاني</span>
                        @else
                            @if($relatedCourse->hasDiscount())
                                <span class="text-decoration-line-through small me-1" style="color: var(--clr-text-muted);">{{ number_format((float) $relatedCourse->price, 0) }} {{ $relatedCourse->currency ?? 'ر.س' }}</span>
                                <span class="price" style="color: var(--clr-primary); font-weight: 700;">{{ number_format((float) $relatedCourse->discount_price, 0) }} {{ $relatedCourse->currency ?? 'ر.س' }}</span>
                            @else
                                <span class="price" style="color: var(--clr-primary); font-weight: 700;">{{ number_format((float) $relatedCourse->price, 0) }} {{ $relatedCourse->currency ?? 'ر.س' }}</span>
                            @endif
                        @endif
                        <span class="view-link" style="color: var(--clr-primary); font-weight: 600;">عرض <i class="fas fa-arrow-left me-1"></i></span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<style>
/* ========== Light Mode (Default) ========== */
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
    border-bottom: 2px solid var(--clr-primary);
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
    background: var(--clr-primary);
    color: #ffffff;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 15px;
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-enroll:hover {
    background: var(--clr-primary-dark);
    transform: translateY(-2px);
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
    color: var(--clr-primary);
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

.share-btn.facebook { background: #3b5998; }
.share-btn.twitter { background: #1da1f2; }
.share-btn.whatsapp { background: #25d366; }
.share-btn.telegram { background: #0088cc; }
.share-btn.linkedin { background: #0077b5; }
.share-btn.copy { background: #95a5a6; border: none; cursor: pointer; }

.share-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.share-btn.copied { background: #27ae60 !important; }

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

.curriculum-stats i { margin-left: 5px; }

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
    background: var(--clr-primary);
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

.lesson-item:last-child { border-bottom: none; }

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

.lesson-icon-title i { font-size: 16px; }

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

.lesson-lock { color: #95a5a6; }

/* Reviews Section */
.rating-summary {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 10px;
}

.overall-rating { padding: 20px; }

.rating-number {
    font-size: 4rem;
    font-weight: 700;
    color: var(--clr-primary);
    line-height: 1;
}

.rating-stars { font-size: 1.5rem; }
.rating-count { font-size: 14px; }

.rating-bars { padding: 10px 0; }

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

.reviews-list { margin-top: 30px; }

.review-item {
    padding: 25px 0;
    border-bottom: 1px solid #e9ecef;
}

.review-item:last-child { border-bottom: none; }

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
    background: var(--clr-primary);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
}

.reviewer-details { flex: 1; }

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

.review-rating i { font-size: 16px; }
.review-date { font-size: 12px; }

.review-content p {
    color: #555;
    line-height: 1.8;
    margin: 0;
}

.featured-badge-review {
    display: inline-block;
    background: var(--clr-primary);
    color: #ffffff;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 10px;
}

.featured-badge-review i { margin-left: 5px; }

/* ========== Dark Mode Overrides ========== */
[data-theme="dark"] .course-section,
[data-theme="dark"] .price-card,
[data-theme="dark"] .share-card {
    background: #111627;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.4);
}

[data-theme="dark"] .section-title {
    color: #e2e8f0;
    border-bottom-color: var(--clr-primary);
}

[data-theme="dark"] .section-content,
[data-theme="dark"] .includes-list li,
[data-theme="dark"] .review-content p {
    color: #cbd5e1;
}

[data-theme="dark"] .curriculum-stats,
[data-theme="dark"] .section-description,
[data-theme="dark"] .rating-summary {
    background: #0f1322;
    color: #94a3b8;
}

[data-theme="dark"] .curriculum-accordion .accordion-item {
    border-color: rgba(255, 255, 255, 0.1);
    background: #0d111f;
}

[data-theme="dark"] .curriculum-accordion .accordion-button {
    background: #151a2e;
    color: #e2e8f0;
}

[data-theme="dark"] .curriculum-accordion .accordion-button:not(.collapsed) {
    background: var(--clr-primary);
    color: #ffffff;
}

[data-theme="dark"] .lesson-item { border-bottom-color: rgba(255, 255, 255, 0.06); }
[data-theme="dark"] .lesson-title { color: #e2e8f0; }
[data-theme="dark"] .lesson-details { color: #94a3b8; }
[data-theme="dark"] .lesson-lock { color: #64748b; }

[data-theme="dark"] .price { color: #f8fafc; }
[data-theme="dark"] .course-includes h5 {
    color: #e2e8f0;
    border-top-color: rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .rating-bar-item .progress {
    background: #1e293b;
}

[data-theme="dark"] .review-item { border-bottom-color: rgba(255, 255, 255, 0.08); }
[data-theme="dark"] .reviewer-name { color: #f1f5f9; }
[data-theme="dark"] .reviewer-position { color: #94a3b8; }

[data-theme="dark"] .star-label { color: #cbd5e1; }
[data-theme="dark"] .rating-percentage { color: #94a3b8; }

[data-theme="dark"] .article-meta-line span { color: #cbd5e1 !important; }
[data-theme="dark"] .article-meta-line i { opacity: 0.8; }

[data-theme="dark"] .btn-outline-warning {
    border-color: #eab308;
    color: #eab308;
}
[data-theme="dark"] .btn-outline-warning:hover {
    background: #eab308;
    color: #0f172a;
}

/* Hover Effects */
[data-theme="dark"] .price-card:hover,
[data-theme="dark"] .share-card:hover {
    border-color: rgba(255, 255, 255, 0.15);
    transition: all 0.3s ease;
}

[data-theme="dark"] .lesson-item:hover {
    background: rgba(255, 255, 255, 0.02);
}

/* Responsive */
@media (max-width: 992px) {
    .course-sidebar {
        margin-top: 30px;
        position: relative !important;
        top: auto !important;
    }
}

@media (max-width: 768px) {
    .section-title { font-size: 1.2rem; }
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
    .rating-summary { padding: 20px; }
    .rating-number { font-size: 3rem; }
    .review-header {
        flex-direction: column;
        gap: 15px;
    }
    .review-rating { align-self: flex-start; }
}
</style>

@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // تغيير مظهر الزر مؤقتاً
        const copyBtn = document.querySelector('.share-btn.copy');
        if (copyBtn) {
            const originalIcon = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fa-solid fa-check"></i>';
            copyBtn.classList.add('copied');
            
            setTimeout(function() {
                copyBtn.innerHTML = originalIcon;
                copyBtn.classList.remove('copied');
            }, 2000);
        }
    }).catch(function(err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('تم نسخ الرابط!');
    });
}
</script>
@endpush

