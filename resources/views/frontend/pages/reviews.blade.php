@extends('frontend.layouts.master')

@section('title', 'آراء الطلاب')
@section('meta_description', 'اطلع على آراء وتقييمات طلابنا حول المنصة والكورسات والدورات التدريبية المقدمة')

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
            <a href="{{ route('frontend.reviews.create') }}" class="btn btn-primary btn-lg">
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
                <div class="col testimonial">
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
                            <a href="{{ route('frontend.reviews.create') }}" class="btn btn-primary mt-3">
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

/* Modal RTL Support */
.modal-header .btn-close {
    margin: 0;
    margin-left: auto;
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

@endsection
