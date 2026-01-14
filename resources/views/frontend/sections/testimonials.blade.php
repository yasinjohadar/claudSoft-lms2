<div class="testimonials">
    <div class="all-testimonial container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">آراء الطلاب</h2>
            <div class="d-flex gap-3">
                @auth
                    <a href="{{ route('frontend.reviews.create') }}" class="btn" style="background: var(--secondary-Color); color: white; border: none;">
                        <i class="fa-solid fa-plus"></i> إضافة تقييمك
                    </a>
                @endauth
                <a href="{{ route('frontend.reviews.index') }}" class="btn" style="background: var(--secondary-Color); color: white; border: none;">عرض المزيد</a>
            </div>
        </div>
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
                <p class="text-muted">لا توجد آراء متاحة حالياً</p>
                @auth
                    <a href="{{ route('frontend.reviews.create') }}" class="btn mt-3" style="background: var(--secondary-Color); color: white; border: none;">
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

/* Modal RTL Support (Review Modal Header Only) */
#homepageReviewModal .modal-header {
    display: flex;
    flex-direction: row-reverse; /* زر الإغلاق يسار، العنوان يمين */
    align-items: center;
}

#homepageReviewModal .modal-header .modal-title {
    margin-left: auto; /* يدفع العنوان لليمين */
}

#homepageReviewModal .modal-header .btn-close {
    margin: 0;
}
</style>

<!-- Review Modal (Homepage Testimonials) -->
<div class="modal fade" id="homepageReviewModal" tabindex="-1" aria-labelledby="homepageReviewModalLabel" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="homepageReviewModalLabel">رأي الطالب</h5>
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
    const modalElement = document.getElementById('homepageReviewModal');
    if (!modalElement || typeof bootstrap === 'undefined') return;

    const modal = new bootstrap.Modal(modalElement);
    const nameEl = modalElement.querySelector('.review-modal-name');
    const positionEl = modalElement.querySelector('.review-modal-position');
    const starsEl = modalElement.querySelector('.review-modal-stars');
    const textEl = modalElement.querySelector('.review-modal-text');
    const avatarEl = modalElement.querySelector('.review-modal-avatar');

    document.querySelectorAll('.review-card').forEach(function (card) {
        card.style.cursor = 'pointer';
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
