<div class="testimonials">
    <div class="all-testimonial container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">آراء الطلاب</h2>
            <div class="d-flex gap-3">
                @auth
                    <a href="{{ route('frontend.reviews.create') }}" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> إضافة تقييمك
                    </a>
                @endauth
                <a href="{{ route('frontend.reviews.index') }}" class="btn btn-outline-primary">عرض المزيد</a>
            </div>
        </div>
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
                <p class="text-muted">لا توجد آراء متاحة حالياً</p>
                @auth
                    <a href="{{ route('frontend.reviews.create') }}" class="btn btn-primary mt-3">
                        <i class="fa-solid fa-plus"></i> كن أول من يضيف تقييماً
                    </a>
                @endauth
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
/* Avatar Placeholder */
.avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--secondary-Color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    margin: 0 auto;
}

/* Testimonial Image Fix */
.testimonial-info {
    display: flex;
    flex-direction: row-reverse;
    justify-content: space-between;
    margin-bottom: 10px;
    align-items: flex-start;
}

.testimonial-info img,
.testimonial-info .avatar-placeholder {
    flex-shrink: 0;
}

.testimonial-info img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.testimonial-info .avatar-placeholder {
    margin: 0;
}

.testimonial-info .name {
    text-align: right;
}
</style>
