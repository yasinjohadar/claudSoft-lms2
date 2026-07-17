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

    {{-- Hero --}}
    <section class="cdp-hero">
        <div class="cdp-hero__glow" aria-hidden="true"></div>
        <div class="container cdp-hero__inner">
            <nav class="cdp-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('frontend.home') }}">الرئيسية</a>
                <span>/</span>
                <a href="{{ route('frontend.courses.index') }}">الكورسات</a>
                @if($course->category)
                    <span>/</span>
                    <a href="{{ route('frontend.courses.index', ['category' => $course->category_id]) }}">{{ $course->category->name }}</a>
                @endif
                <span>/</span>
                <span>{{ Str::limit($course->title, 40) }}</span>
            </nav>

            <div class="cdp-hero__grid">
                <div class="cdp-hero__main">
                    <div class="cdp-hero__badges">
                        @if($course->category)
                            <span class="cdp-tag"><i class="fa {{ $course->category->icon ?? 'fa-folder' }}"></i> {{ $course->category->name }}</span>
                        @endif
                        @if($course->is_free)
                            <span class="cdp-tag cdp-tag--free">مجاني</span>
                        @elseif($course->has_discount ?? false)
                            <span class="cdp-tag cdp-tag--sale">خصم {{ $course->discount_percentage }}%</span>
                        @endif
                    </div>

                    <h1 class="cdp-hero__title">{{ $course->title }}</h1>
                    @if($bannerDesc)
                        <p class="cdp-hero__desc">{{ $bannerDesc }}</p>
                    @endif

                    <div class="cdp-meta">
                        <span class="cdp-meta__item"><i class="fas fa-clock"></i> {{ $course->duration }} ساعة</span>
                        <span class="cdp-meta__item"><i class="fas fa-play-circle"></i> {{ $course->lessons_count }} درس</span>
                        <span class="cdp-meta__item"><i class="fas fa-signal"></i> {{ __('المستوى: ' . $course->level) }}</span>
                        @if($course->instructor)
                            <span class="cdp-meta__item"><i class="fas fa-chalkboard-teacher"></i> المدرب: {{ $course->instructor->name }}</span>
                        @endif
                    </div>
                </div>

                <div class="cdp-hero__media">
                    @if($course->preview_video)
                        <div class="cdp-preview cdp-preview--video">
                            <video controls poster="{{ $course->thumbnail_url }}" class="cdp-preview__video">
                                <source src="{{ asset($course->preview_video) }}" type="video/mp4">
                                المتصفح الخاص بك لا يدعم تشغيل الفيديو
                            </video>
                        </div>
                    @else
                        <div class="cdp-preview">
                            <img
                                src="{{ $course->thumbnail_url }}"
                                alt="{{ $course->title }}"
                                class="cdp-preview__img"
                                width="1200"
                                height="675"
                                loading="eager"
                                decoding="async"
                            >
                            <span class="cdp-preview__badge" aria-hidden="true"><i class="fas fa-play"></i></span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Body --}}
    <section class="cdp-body section-padding">
        <div class="container">
            <div class="cdp-layout">
                <div class="cdp-main">

                    <article class="cdp-card">
                        <h2 class="cdp-card__title"><i class="fas fa-align-right"></i> نظرة عامة على الكورس</h2>
                        <div class="cdp-card__content cdp-prose">
                            <p>{{ $course->description }}</p>
                        </div>
                    </article>

                    @if($course->what_you_learn && is_array($course->what_you_learn) && count($course->what_you_learn) > 0)
                    <article class="cdp-card">
                        <h2 class="cdp-card__title"><i class="fas fa-check-double"></i> ماذا ستتعلم</h2>
                        <div class="cdp-learn">
                            @foreach($course->what_you_learn as $item)
                                <div class="cdp-learn__item">
                                    <i class="fas fa-check"></i>
                                    <span>{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                    @endif

                    @if($course->requirements)
                    <article class="cdp-card">
                        <h2 class="cdp-card__title"><i class="fas fa-list-check"></i> المتطلبات</h2>
                        <div class="cdp-req">
                            <i class="fas fa-info-circle"></i>
                            <span>{{ $course->requirements }}</span>
                        </div>
                    </article>
                    @endif

                    @if($course->sections && $course->sections->count() > 0)
                    <article class="cdp-card">
                        <h2 class="cdp-card__title"><i class="fas fa-book-open"></i> محتوى الكورس</h2>
                        <div class="cdp-curr-stats">
                            <span><i class="fas fa-folder-open"></i> {{ $course->sections->count() }} محور</span>
                            <span><i class="fas fa-play-circle"></i> {{ $course->lessons_count }} درس</span>
                            <span><i class="fas fa-clock"></i> {{ $course->duration }} ساعة</span>
                        </div>

                        <div class="accordion cdp-accordion" id="curriculumAccordion">
                            @foreach($course->sections as $sectionIndex => $section)
                            <div class="accordion-item cdp-acc-item">
                                <h3 class="accordion-header" id="heading{{ $sectionIndex }}">
                                    <button
                                        class="accordion-button cdp-acc-btn {{ $sectionIndex > 0 ? 'collapsed' : '' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ $sectionIndex }}"
                                        aria-expanded="{{ $sectionIndex == 0 ? 'true' : 'false' }}"
                                        aria-controls="collapse{{ $sectionIndex }}"
                                    >
                                        <span class="cdp-acc-btn__main">
                                            <span class="cdp-acc-num">{{ $sectionIndex + 1 }}</span>
                                            <strong>{{ $section->title }}</strong>
                                        </span>
                                        <span class="cdp-acc-btn__meta">
                                            {{ $section->lessons_count }} دروس
                                            @if($section->duration > 0)
                                                · {{ round($section->duration / 60, 1) }} ساعة
                                            @endif
                                        </span>
                                    </button>
                                </h3>
                                <div
                                    id="collapse{{ $sectionIndex }}"
                                    class="accordion-collapse collapse {{ $sectionIndex == 0 ? 'show' : '' }}"
                                    aria-labelledby="heading{{ $sectionIndex }}"
                                    data-bs-parent="#curriculumAccordion"
                                >
                                    <div class="accordion-body cdp-acc-body">
                                        @if($section->description)
                                            <p class="cdp-acc-desc">{{ $section->description }}</p>
                                        @endif

                                        @if($section->lessons && $section->lessons->count() > 0)
                                            <ul class="cdp-lessons">
                                                @foreach($section->lessons as $lesson)
                                                <li class="cdp-lesson">
                                                    <span class="cdp-lesson__info">
                                                        @switch($lesson->type)
                                                            @case('video')
                                                                <i class="fas fa-play-circle"></i>
                                                                @break
                                                            @case('text')
                                                                <i class="fas fa-file-alt"></i>
                                                                @break
                                                            @case('file')
                                                                <i class="fas fa-file-download"></i>
                                                                @break
                                                            @case('quiz')
                                                                <i class="fas fa-question-circle"></i>
                                                                @break
                                                            @case('live')
                                                                <i class="fas fa-video"></i>
                                                                @break
                                                            @default
                                                                <i class="fas fa-circle"></i>
                                                        @endswitch
                                                        <span>{{ $lesson->title }}</span>
                                                    </span>
                                                    <span class="cdp-lesson__aside">
                                                        @if($lesson->duration)
                                                            <span class="cdp-lesson__dur"><i class="far fa-clock"></i> {{ $lesson->duration }} د</span>
                                                        @endif
                                                        @if($lesson->is_free)
                                                            <span class="cdp-lesson__free">معاينة مجانية</span>
                                                        @else
                                                            <i class="fas fa-lock cdp-lesson__lock"></i>
                                                        @endif
                                                    </span>
                                                </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="cdp-empty">لا توجد دروس في هذا المحور حالياً</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </article>
                    @endif

                    <article class="cdp-card">
                        <h2 class="cdp-card__title"><i class="fas fa-gem"></i> مميزات الكورس</h2>
                        <div class="cdp-features">
                            @if($course->certificate)
                                <div class="cdp-feature"><i class="fas fa-certificate"></i> شهادة إتمام معتمدة</div>
                            @endif
                            @if($course->lifetime_access)
                                <div class="cdp-feature"><i class="fas fa-infinity"></i> وصول مدى الحياة</div>
                            @endif
                            @if($course->downloadable_resources)
                                <div class="cdp-feature"><i class="fas fa-download"></i> موارد قابلة للتحميل</div>
                            @endif
                            <div class="cdp-feature"><i class="fas fa-mobile-screen"></i> متوفر على الجوال والكمبيوتر</div>
                        </div>
                    </article>

                    @if($reviews && $reviews->count() > 0)
                    <article class="cdp-card">
                        <h2 class="cdp-card__title"><i class="fas fa-comments"></i> آراء الطلاب</h2>

                        <div class="cdp-rating">
                            <div class="cdp-rating__score">
                                <div class="cdp-rating__num">{{ number_format($course->rating, 1) }}</div>
                                <div class="cdp-rating__stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($course->rating))
                                            <i class="fas fa-star"></i>
                                        @elseif($i - 0.5 <= $course->rating)
                                            <i class="fas fa-star-half-stroke"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="cdp-rating__count">{{ $course->reviews_count }} تقييم</div>
                            </div>
                            <div class="cdp-rating__bars">
                                @php
                                    $ratingDistribution = $reviews->groupBy('rating')->map->count();
                                    $totalReviews = $reviews->count();
                                @endphp
                                @for($star = 5; $star >= 1; $star--)
                                    @php
                                        $count = $ratingDistribution->get($star, 0);
                                        $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                                    @endphp
                                    <div class="cdp-bar">
                                        <span>{{ $star }}</span>
                                        <div class="cdp-bar__track"><div class="cdp-bar__fill" style="width: {{ $percentage }}%"></div></div>
                                        <span>{{ number_format($percentage, 0) }}%</span>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="cdp-reviews">
                            @foreach($reviews as $review)
                            <div class="cdp-review">
                                <div class="cdp-review__head">
                                    <div class="cdp-review__who">
                                        <div class="cdp-review__avatar">
                                            @if($review->student_image)
                                                <img src="{{ asset('storage/' . $review->student_image) }}" alt="" width="48" height="48" loading="lazy">
                                            @else
                                                <span>{{ mb_substr($review->student_name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ $review->student_name }}</strong>
                                            @if($review->student_position)
                                                <p>{{ $review->student_position }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="cdp-review__meta">
                                        <span class="cdp-review__stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                            @endfor
                                        </span>
                                        <time>{{ $review->created_at->diffForHumans() }}</time>
                                    </div>
                                </div>
                                <p class="cdp-review__text">{{ $review->review_text }}</p>
                            </div>
                            @endforeach
                        </div>
                    </article>
                    @endif

                </div>

                {{-- Sidebar --}}
                <aside class="cdp-side">
                    <div class="cdp-side__sticky">
                        <div class="cdp-buy">
                            @if($course->is_free)
                                <div class="cdp-buy__price cdp-buy__price--free">مجاني</div>
                            @elseif($course->has_discount)
                                <div class="cdp-buy__price-row">
                                    <span class="cdp-buy__price">{{ $course->discount_price }} {{ $course->currency }}</span>
                                    <span class="cdp-buy__old">{{ $course->price }} {{ $course->currency }}</span>
                                    <span class="cdp-buy__sale">خصم {{ $course->discount_percentage }}%</span>
                                </div>
                            @else
                                <div class="cdp-buy__price">{{ $course->price }} {{ $course->currency }}</div>
                            @endif

                            <button type="button" class="cdp-buy__btn">
                                <i class="fas fa-graduation-cap"></i> سجل الآن
                            </button>

                            @auth
                                @php $user = auth()->user(); @endphp
                                @if(method_exists($user, 'hasRole') && $user->hasRole('admin'))
                                    <a href="{{ route('admin.frontend-courses.edit', $course->id) }}" class="cdp-buy__admin">
                                        <i class="fas fa-pen-to-square"></i> تعديل الكورس (لوحة التحكم)
                                    </a>
                                @endif
                            @endauth

                            <div class="cdp-includes">
                                <h3>يتضمن هذا الكورس</h3>
                                <ul>
                                    <li><i class="fas fa-video"></i> {{ $course->lessons_count }} درس فيديو</li>
                                    <li><i class="fas fa-clock"></i> {{ $course->duration }} ساعة من المحتوى</li>
                                    @if($course->certificate)
                                        <li><i class="fas fa-certificate"></i> شهادة إتمام</li>
                                    @endif
                                    @if($course->lifetime_access)
                                        <li><i class="fas fa-infinity"></i> وصول مدى الحياة</li>
                                    @endif
                                    @if($course->downloadable_resources)
                                        <li><i class="fas fa-download"></i> موارد قابلة للتحميل</li>
                                    @endif
                                    <li><i class="fas fa-mobile"></i> الوصول عبر الجوال</li>
                                </ul>
                            </div>
                        </div>

                        <div class="cdp-share">
                            <h3>شارك هذا الكورس</h3>
                            @php
                                $courseUrl = urlencode(request()->url());
                                $courseTitle = urlencode($course->title);
                            @endphp
                            <div class="cdp-share__row">
                                @if(!empty($socialLinks))
                                    @foreach($socialLinks as $social)
                                        @if(($social['enabled'] ?? true) && !empty($social['url']))
                                            @php
                                                $platform = strtolower($social['platform'] ?? '');
                                                $shareUrl = '#';
                                                $iconClass = $social['icon'] ?? 'fa-link';
                                                $btnClass = 'copy';

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
                                            <a href="{{ $shareUrl }}" class="cdp-share__btn {{ $btnClass }}" target="_blank" rel="noopener noreferrer" title="{{ $social['label'] ?? 'شارك' }}">
                                                <i class="fab {{ $iconClass }}"></i>
                                            </a>
                                        @endif
                                    @endforeach
                                @else
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $courseUrl }}" class="cdp-share__btn facebook" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                                    <a href="https://twitter.com/intent/tweet?url={{ $courseUrl }}&text={{ $courseTitle }}" class="cdp-share__btn twitter" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter"></i></a>
                                    <a href="https://wa.me/?text={{ $courseTitle }}%20{{ $courseUrl }}" class="cdp-share__btn whatsapp" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i></a>
                                    <a href="https://t.me/share/url?url={{ $courseUrl }}&text={{ $courseTitle }}" class="cdp-share__btn telegram" target="_blank" rel="noopener noreferrer"><i class="fab fa-telegram-plane"></i></a>
                                @endif
                                <button type="button" class="cdp-share__btn copy" onclick="copyToClipboard('{{ request()->url() }}')" title="نسخ الرابط">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if($relatedCourses->count() > 0)
    <section class="section-padding related-courses-section" style="background: var(--clr-bg);">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <span class="section-badge">الكورسات</span>
                <h2>كورسات ذات صلة</h2>
                <p>دورات أخرى قد تناسب اهتمامك في نفس المجال</p>
            </div>
            <div class="ccards-grid">
                @foreach($relatedCourses->take(3) as $relatedCourse)
                <a href="{{ route('frontend.courses.show', $relatedCourse->slug) }}" class="ccard animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}">
                    <div class="ccard__media">
                        <img
                            src="{{ $relatedCourse->thumbnail ? course_image_url($relatedCourse->thumbnail) : asset('frontend2/assets/images/course-webdev.svg') }}"
                            alt="{{ $relatedCourse->title }}"
                            width="800"
                            height="450"
                            loading="lazy"
                        >
                        @if($relatedCourse->is_free)
                            <span class="ccard__badge ccard__badge--free">مجاني</span>
                        @elseif($relatedCourse->hasDiscount())
                            <span class="ccard__badge ccard__badge--sale">خصم {{ $relatedCourse->discount_percentage }}%</span>
                        @endif
                    </div>
                    <div class="ccard__body">
                        @if($relatedCourse->category)
                            <span class="ccard__cat"><i class="fa {{ $relatedCourse->category->icon ?? 'fa-folder' }}"></i> {{ $relatedCourse->category->name }}</span>
                        @endif
                        <h3 class="ccard__title">{{ $relatedCourse->title }}</h3>
                        <p class="ccard__excerpt">{{ Str::limit(strip_tags($relatedCourse->subtitle ?? $relatedCourse->description ?? ''), 90) }}</p>
                    </div>
                    <div class="ccard__footer">
                        @if($relatedCourse->is_free)
                            <span class="ccard__price ccard__price--free">مجاني</span>
                        @elseif($relatedCourse->hasDiscount())
                            <span class="ccard__price-wrap">
                                <span class="ccard__old">{{ number_format((float) $relatedCourse->price, 0) }} {{ $relatedCourse->currency ?? 'ر.س' }}</span>
                                <span class="ccard__price">{{ number_format((float) $relatedCourse->discount_price, 0) }} {{ $relatedCourse->currency ?? 'ر.س' }}</span>
                            </span>
                        @else
                            <span class="ccard__price">{{ number_format((float) $relatedCourse->price, 0) }} {{ $relatedCourse->currency ?? 'ر.س' }}</span>
                        @endif
                        <span class="ccard__more">عرض <i class="fas fa-arrow-left"></i></span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const copyBtn = document.querySelector('.cdp-share__btn.copy');
        if (copyBtn) {
            const originalIcon = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i>';
            copyBtn.classList.add('is-copied');
            setTimeout(function() {
                copyBtn.innerHTML = originalIcon;
                copyBtn.classList.remove('is-copied');
            }, 2000);
        }
    }).catch(function() {
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
