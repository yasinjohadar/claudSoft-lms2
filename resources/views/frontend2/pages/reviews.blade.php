@extends('frontend2.layouts.master')

@php
    $pageTitle = 'آراء وتقييمات الطلاب | ' . config('app.name');
    $pageDescription = 'اطلع على آراء وتقييمات طلابنا حول المنصة والكورسات. تقييمات حقيقية من طلاب حقيقيين - أكاديمية كلاودسوفت.';
    $canonicalUrl = route('frontend.reviews.index');
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
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            { "@@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "{{ route('frontend.home') }}" },
            { "@@type": "ListItem", "position": 2, "name": "آراء الطلاب", "item": "{{ $canonicalUrl }}" }
        ]
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "CollectionPage",
        "name": "{{ addslashes($pageTitle) }}",
        "description": "{{ addslashes($pageDescription) }}",
        "url": "{{ $canonicalUrl }}"
    }
    </script>
@endpush

@section('content')

    <!-- Page Banner -->
    <section class="page-banner page-banner-testimonials">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-comments"></i></div>
                <h1 class="page-banner-title">آراء <span>الطلاب</span></h1>
                <p class="page-banner-desc">تقييمات حقيقية من طلابنا حول المنصة والدورات التدريبية</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <span>آراء الطلاب</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding" style="padding-top: 30px;">
        <div class="container">
            @auth
            <div class="text-center mb-4 animate-on-scroll">
                <a href="{{ route('frontend.reviews.create') }}" class="btn-primary-custom">
                    <i class="fas fa-plus me-2"></i> أضف تقييمك ورأيك حول المنصة
                </a>
            </div>
            @endauth

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate-on-scroll" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate-on-scroll" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show animate-on-scroll" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row g-4">
                @forelse($reviews as $review)
                <div class="col-lg-4 col-md-6 review-card animate-on-scroll animate-delay-{{ min($loop->iteration, 3) }}"
                     data-name="{{ e($review->student_name ?? optional($review->user)->name ?? 'طالب') }}"
                     data-position="{{ e($review->student_position ?? '—') }}"
                     data-rating="{{ (int)($review->rating ?? 5) }}"
                     data-text="{{ e($review->review_text ?? '') }}"
                     data-avatar="{{ $review->student_image ? asset('storage/' . $review->student_image) : ($review->user && $review->user->avatar ? asset('storage/' . $review->user->avatar) : '') }}">
                    <div class="glass-panel testimonial-card h-100" style="cursor: pointer;" title="اضغط لقراءة كامل الرأي">
                        @if($review->is_featured)
                        <span class="position-absolute top-0 start-0 m-3 badge rounded-pill" style="background: var(--clr-secondary); color: #fff;"><i class="fas fa-star me-1"></i> مميز</span>
                        @endif
                        <div class="stars">
                            @php $rating = (int)($review->rating ?? 5); @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= $rating ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                        </div>
                        <p class="quote-text">"{{ Str::limit($review->review_text ?? '', 180) }}"</p>
                        <div class="student-info">
                            @if($review->student_image)
                                <img src="{{ asset('storage/' . $review->student_image) }}" alt="{{ $review->student_name }}" class="student-avatar" width="50" height="50" loading="lazy">
                            @elseif($review->user && $review->user->avatar)
                                <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="{{ $review->student_name ?? $review->user->name }}" class="student-avatar" width="50" height="50" loading="lazy">
                            @else
                                <div class="student-avatar-placeholder">{{ strtoupper(mb_substr($review->student_name ?? optional($review->user)->name ?? 'ط', 0, 1)) }}</div>
                            @endif
                            <div>
                                <div class="student-name">{{ $review->student_name ?? optional($review->user)->name ?? 'طالب' }}</div>
                                <div class="student-role">{{ $review->student_position ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="glass-panel text-center py-5 animate-on-scroll">
                        <i class="fas fa-comments fa-4x mb-3" style="color: var(--clr-text-muted);"></i>
                        <h4>لا توجد آراء متاحة حالياً</h4>
                        <p class="mb-0" style="color: var(--clr-text-muted);">كن أول من يضيف تقييماً للمنصة!</p>
                        @auth
                        <a href="{{ route('frontend.reviews.create') }}" class="btn-primary-custom mt-3"><i class="fas fa-plus me-2"></i> إضافة تقييمك</a>
                        @endauth
                    </div>
                </div>
                @endforelse
            </div>

            @if($reviews->hasPages())
            <div class="d-flex justify-content-center mt-5 animate-on-scroll">
                {{ $reviews->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container animate-on-scroll">
            <h2>شاركنا تجربتك</h2>
            <p>سجّل في أحد دوراتنا ثم أضف تقييمك لمساعدة الآخرين</p>
            <a href="{{ route('frontend.courses.index') }}" class="btn-light-custom">
                <i class="fas fa-graduation-cap"></i> تصفح الكورسات
            </a>
        </div>
    </section>

    <!-- Modal كامل الرأي -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true" dir="rtl">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content glass-panel" style="border: 1px solid var(--clr-border); background: var(--glass-bg);">
                <div class="modal-header border-bottom" style="border-color: var(--clr-border) !important;">
                    <h5 class="modal-title" id="reviewModalLabel" style="color: var(--clr-text);">رأي الطالب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="color: var(--clr-text);">
                    <div class="d-flex flex-row-reverse align-items-start gap-3 mb-3">
                        <div id="reviewModalAvatar" class="review-modal-avatar flex-shrink-0"></div>
                        <div class="text-end flex-grow-1">
                            <h5 id="reviewModalName" class="mb-1"></h5>
                            <p id="reviewModalPosition" class="small mb-2" style="color: var(--clr-text-muted);"></p>
                            <div id="reviewModalStars" class="text-warning"></div>
                        </div>
                    </div>
                    <p id="reviewModalText" class="mb-0" style="line-height: 1.8; color: var(--clr-text-secondary);"></p>
                </div>
            </div>
        </div>
    </div>

    <style>
    .student-avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-full);
        background: var(--clr-primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 700;
        border: 2px solid var(--clr-primary);
        flex-shrink: 0;
    }
    .testimonial-card { position: relative; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('reviewModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;
        var modal = new bootstrap.Modal(modalEl);
        var nameEl = document.getElementById('reviewModalName');
        var positionEl = document.getElementById('reviewModalPosition');
        var starsEl = document.getElementById('reviewModalStars');
        var textEl = document.getElementById('reviewModalText');
        var avatarEl = document.getElementById('reviewModalAvatar');

        document.querySelectorAll('.review-card').forEach(function (card) {
            if (!card.dataset.text) return;
            card.querySelector('.testimonial-card').addEventListener('click', function () {
                var name = card.dataset.name || '';
                var position = card.dataset.position || '';
                var rating = parseInt(card.dataset.rating || '0', 10);
                var text = card.dataset.text || '';
                var avatar = card.dataset.avatar || '';

                if (nameEl) nameEl.textContent = name;
                if (positionEl) positionEl.textContent = position;
                if (textEl) textEl.textContent = text;

                if (avatarEl) {
                    avatarEl.innerHTML = '';
                    avatarEl.className = 'flex-shrink-0';
                    if (avatar) {
                        var img = document.createElement('img');
                        img.src = avatar;
                        img.alt = name;
                        img.className = 'student-avatar';
                        img.style.width = '50px'; img.style.height = '50px'; img.style.borderRadius = '50%'; img.style.objectFit = 'cover';
                        avatarEl.appendChild(img);
                    } else {
                        var placeholder = document.createElement('div');
                        placeholder.className = 'student-avatar-placeholder';
                        placeholder.textContent = name ? name.trim().charAt(0).toUpperCase() : '؟';
                        avatarEl.appendChild(placeholder);
                    }
                }

                if (starsEl) {
                    starsEl.innerHTML = '';
                    for (var i = 1; i <= 5; i++) {
                        var icon = document.createElement('i');
                        icon.className = i <= rating ? 'fas fa-star' : 'far fa-star';
                        starsEl.appendChild(icon);
                    }
                }
                modal.show();
            });
        });
    });
    </script>
@endsection
