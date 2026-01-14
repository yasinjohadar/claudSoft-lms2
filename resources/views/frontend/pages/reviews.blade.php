@extends('frontend.layouts.master')

@php
    $pageTitle = 'آراء وتقييمات الطلاب - ' . config('app.name');
    $pageDescription = 'اطلع على آراء وتقييمات طلابنا حول المنصة والكورسات والدورات التدريبية المقدمة. تقييمات حقيقية من طلاب حقيقيين';
    $pageKeywords = 'آراء الطلاب, تقييمات, مراجعات, شهادات, تجارب';
    $canonicalUrl = route('frontend.reviews.index');
    $ogImage = asset('frontend/assets/img/default-course.jpg');
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $pageKeywords)

@push('head')
    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="ar_SA">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Robots Meta --}}
    <meta name="robots" content="index, follow">

    {{-- Breadcrumb Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "الرئيسية",
                "item": "{{ route('frontend.home') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "آراء الطلاب",
                "item": "{{ $canonicalUrl }}"
            }
        ]
    }
    </script>

    {{-- CollectionPage Schema --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "{{ $pageTitle }}",
        "description": "{{ $pageDescription }}",
        "url": "{{ $canonicalUrl }}"
    }
    </script>
@endpush

@section('content')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">آراء الطلاب حول المنصة</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">آراء الطلاب</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Reviews Section -->
<section class="reviews-section py-5" style="background-color: #eee;">
    <div class="container">

        <!-- Add Review Button (Only for authenticated students) -->
        @auth
        <div class="text-center mb-4">
            <a href="{{ route('frontend.reviews.create') }}" class="btn btn-lg" style="background: var(--secondary-Color); color: white; border: none;">
                <i class="fa-solid fa-plus"></i> أضف تقييمك ورأيك حول المنصة
            </a>
        </div>
        @endauth

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

        <div class="all-reviews">
            <div class="inner-testimonials row row-cols-2 row-cols-sm-2 row-cols-md-3 gap-3 text-center">
                @forelse($reviews as $review)
                <div class="col testimonial review-card"
                     data-name="{{ $review->student_name }}"
                     data-position="{{ $review->student_position ?? 'طالب' }}"
                     data-rating="{{ $review->rating }}"
                     data-text="{{ $review->review_text }}">
                    <div class="testimonial-info">
                        @if($review->student_image)
                            <img src="{{ asset('storage/' . $review->student_image) }}" alt="{{ $review->student_name }}">
                        @elseif($review->user && $review->user->avatar)
                            <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="{{ $review->student_name }}">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($review->student_name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="name">
                            <h3>{{ $review->student_name }}</h3>
                            <p>{{ $review->student_position ?? 'طالب' }}</p>
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="fa-solid fa-star"></i>
                                    @else
                                        <i class="fa-regular fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>
                    <p>{{ Str::limit($review->review_text, 150) }}</p>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="fa-solid fa-comments fa-3x text-muted mb-3"></i>
                        <h4>لا توجد آراء متاحة حالياً</h4>
                        <p class="text-muted">كن أول من يضيف تقييماً للمنصة!</p>
                        @auth
                            <a href="{{ route('frontend.reviews.create') }}" class="btn mt-3" style="background: var(--secondary-Color); color: white; border: none;">
                                <i class="fa-solid fa-plus"></i> إضافة تقييمك
                            </a>
                        @endauth
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($reviews->hasPages())
            <div class="pagination-wrapper mt-5">
                <nav aria-label="Page navigation">
                    {{ $reviews->links('pagination::bootstrap-5') }}
                </nav>
            </div>
            @endif
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

/* Testimonial Cards */
.reviews-section .inner-testimonials {
    display: flex;
    justify-content: center;
}

.reviews-section .testimonial {
    background-color: white;
    border: 1px solid #dfdede;
    padding: 15px;
    border-radius: 7px;
    cursor: pointer;
    transition: 0.3s;
    min-height: 240px;
    position: relative;
}

.reviews-section .testimonial:hover {
    box-shadow: 3px 3px 3px #0556a25b;
    transform: translateY(-10px);
}

.reviews-section .testimonial img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 2px solid var(--main-Color);
    object-fit: cover;
}

.reviews-section .testimonial .stars i {
    color: #decb02;
}

.reviews-section .testimonial-info {
    display: flex;
    flex-direction: row-reverse;
    justify-content: space-between;
    margin-bottom: 10px;
    align-items: flex-start;
}

.reviews-section .testimonial-info img,
.reviews-section .testimonial-info .avatar-placeholder {
    flex-shrink: 0;
}

.reviews-section .testimonial-info .name {
    text-align: right;
}

.reviews-section .testimonial-info .name p {
    margin-bottom: 8px;
}

.reviews-section .testimonial-info div h3 {
    font-size: 20px;
    color: var(--secondary-Color);
    font-weight: 700;
}

.reviews-section .testimonial-info div p {
    font-size: 14px;
    color: var(--main-Color);
}

/* Avatar Placeholder */
.avatar-placeholder {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: var(--secondary-Color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: bold;
    border: 2px solid var(--main-Color);
}

.reviews-section .testimonial > p {
    color: #555;
    line-height: 1.8;
    font-size: 0.95rem;
    text-align: right;
}

/* Featured Badge */
.reviews-section .featured-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--secondary-Color);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.reviews-section .featured-badge i {
    margin-left: 5px;
}

/* Suggestion Badge */
.reviews-section .suggestion-badge {
    display: inline-block;
    background-color: #ffeaa7;
    color: #d63031;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    margin-top: 0.5rem;
}

.reviews-section .suggestion-badge i {
    margin-left: 0.25rem;
}

/* Empty State */
.empty-state {
    padding: 60px 20px;
}

.empty-state i {
    opacity: 0.3;
}

.empty-state h4 {
    color: #2c3e50;
    margin-bottom: 10px;
}

/* Rating Input Stars */
.rating-input {
    direction: rtl;
    font-size: 2.5rem;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input label {
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input[type="radio"]:checked ~ label {
    color: #ffc107;
}

/* Modal RTL Support (Review Modal Header Only) */
#reviewsPageModal .modal-header {
    display: flex;
    flex-direction: row-reverse; /* زر الإغلاق يسار، العنوان يمين */
    align-items: center;
}

#reviewsPageModal .modal-header .modal-title {
    margin-left: auto; /* يدفع العنوان لليمين */
}

#reviewsPageModal .modal-header .btn-close {
    margin: 0;
}

/* Make review cards clickable */
.review-card {
    cursor: pointer;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    gap: 5px;
}

.pagination-wrapper .page-link {
    color: var(--secondary-Color);
    border: 1px solid #dee2e6;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination-wrapper .page-link:hover {
    background: var(--main-Color);
    color: #ffffff;
    border-color: var(--main-Color);
}

.pagination-wrapper .page-item.active .page-link {
    background: var(--secondary-Color);
    border-color: var(--secondary-Color);
    color: #ffffff;
}

.pagination-wrapper .page-item.disabled .page-link {
    background: #f8f9fa;
    color: #6c757d;
    border-color: #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        padding: 60px 0 30px;
    }

    .page-title {
        font-size: 2rem;
    }

    .reviews-section .inner-testimonials {
        gap: 15px !important;
    }

    .reviews-section .testimonial img,
    .reviews-section .avatar-placeholder {
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
    }

    .reviews-section .testimonial-info .name h3 {
        font-size: 1rem;
    }

    .reviews-section .testimonial > p {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 1.5rem;
    }

}
</style>

<!-- Review Modal (Reviews Page) -->
<div class="modal fade" id="reviewsPageModal" tabindex="-1" aria-labelledby="reviewsPageModalLabel" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewsPageModalLabel">رأي الطالب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-row-reverse align-items-start gap-3 mb-3">
                    <div class="review-modal-avatar avatar-placeholder flex-shrink-0"></div>
                    <div class="text-end flex-grow-1">
                        <h5 class="review-modal-name mb-1"></h5>
                        <p class="review-modal-position text-muted mb-2"></p>
                        <div class="review-modal-stars text-warning mb-2"></div>
                    </div>
                </div>
                <p class="review-modal-text mb-0" style="line-height: 1.8;"></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('reviewsPageModal');
    if (!modalElement || typeof bootstrap === 'undefined') return;

    const modal = new bootstrap.Modal(modalElement);
    const nameEl = modalElement.querySelector('.review-modal-name');
    const positionEl = modalElement.querySelector('.review-modal-position');
    const starsEl = modalElement.querySelector('.review-modal-stars');
    const textEl = modalElement.querySelector('.review-modal-text');
    const avatarEl = modalElement.querySelector('.review-modal-avatar');

    document.querySelectorAll('.review-card').forEach(function (card) {
        card.setAttribute('title', 'اضغط لقراءة كامل الرأي');
        card.addEventListener('click', function () {
            const name = this.dataset.name || '';
            const position = this.dataset.position || '';
            const rating = parseInt(this.dataset.rating || '0', 10);
            const text = this.dataset.text || '';

            if (nameEl) nameEl.textContent = name;
            if (positionEl) positionEl.textContent = position;
            if (textEl) textEl.textContent = text;

            if (avatarEl) {
                avatarEl.textContent = name ? name.trim().charAt(0).toUpperCase() : '';
            }

            if (starsEl) {
                starsEl.innerHTML = '';
                for (let i = 1; i <= 5; i++) {
                    const icon = document.createElement('i');
                    icon.className = i <= rating ? 'fa-solid fa-star' : 'fa-regular fa-star';
                    starsEl.appendChild(icon);
                }
            }

            modal.show();
        });
    });
});
</script>

@endsection
