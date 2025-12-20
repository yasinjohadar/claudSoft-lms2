@props(['course'])

@php
    $averageRating = $course->getAverageRating();
    $reviewsCount = $course->getReviewsCount();
    $distribution = $course->getRatingDistribution();
    $reviews = $course->approvedReviews()->with('student')->latest()->paginate(10);

    // Check if current user has reviewed
    $userReview = null;
    if (auth()->check() && auth()->user()->role === 'student') {
        $userReview = $course->reviews()->where('student_id', auth()->id())->first();
    }

    // Check if user is enrolled
    $isEnrolled = false;
    if (auth()->check() && auth()->user()->role === 'student') {
        $isEnrolled = $course->enrollments()->where('student_id', auth()->id())->exists();
    }
@endphp

<div class="card custom-card" id="reviews-section">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h5 class="card-title mb-0">
                <i class="ri-star-line me-2"></i>التقييمات والمراجعات
            </h5>
            @if($isEnrolled && !$userReview)
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="ri-add-line me-1"></i>أضف مراجعتك
                </button>
            @endif
        </div>
    </div>

    <div class="card-body">
        <!-- Overall Rating Summary -->
        <div class="row mb-4">
            <div class="col-md-4 text-center border-end">
                <div class="mb-2">
                    <h1 class="display-3 mb-0">{{ $averageRating }}</h1>
                    <div class="fs-20 text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($averageRating))
                                <i class="ri-star-fill"></i>
                            @elseif($i - $averageRating < 1 && $i - $averageRating > 0)
                                <i class="ri-star-half-fill"></i>
                            @else
                                <i class="ri-star-line"></i>
                            @endif
                        @endfor
                    </div>
                    <p class="text-muted mb-0">{{ $reviewsCount }} تقييم</p>
                </div>
            </div>

            <div class="col-md-8">
                <h6 class="mb-3">توزيع التقييمات</h6>
                @for($i = 5; $i >= 1; $i--)
                    @php
                        $count = $distribution[$i] ?? 0;
                        $percentage = $reviewsCount > 0 ? ($count / $reviewsCount) * 100 : 0;
                    @endphp
                    <div class="d-flex align-items-center mb-2">
                        <span class="me-2" style="min-width: 60px;">{{ $i }} نجوم</span>
                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: {{ $percentage }}%"
                                 aria-valuenow="{{ $percentage }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <span class="text-muted" style="min-width: 40px;">{{ $count }}</span>
                    </div>
                @endfor
            </div>
        </div>

        <!-- User's Review if exists -->
        @if($userReview)
            <div class="alert alert-{{ $userReview->status === 'approved' ? 'success' : ($userReview->status === 'pending' ? 'warning' : 'danger') }} mb-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="alert-heading">مراجعتك</h6>
                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $userReview->rating)
                                    <i class="ri-star-fill text-warning"></i>
                                @else
                                    <i class="ri-star-line text-warning"></i>
                                @endif
                            @endfor
                        </div>
                        @if($userReview->title)
                            <strong>{{ $userReview->title }}</strong>
                        @endif
                        <p class="mb-2">{{ $userReview->review }}</p>
                        <small class="text-muted">
                            الحالة:
                            @if($userReview->status === 'pending')
                                قيد المراجعة
                            @elseif($userReview->status === 'approved')
                                معتمدة
                            @else
                                مرفوضة
                            @endif
                        </small>
                        @if($userReview->admin_feedback)
                            <div class="mt-2">
                                <small class="text-danger">
                                    <strong>ملاحظات الإدارة:</strong> {{ $userReview->admin_feedback }}
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editReviewModal">
                            <i class="ri-edit-line"></i>
                        </button>
                        <form action="{{ route('student.courses.reviews.destroy', [$course, $userReview]) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('هل أنت متأكد من حذف مراجعتك؟')">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Reviews List -->
        <h6 class="mb-3">المراجعات ({{ $reviewsCount }})</h6>

        @forelse($reviews as $review)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-text bg-primary-transparent">
                                    {{ substr($review->student->name, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $review->student->name }}</h6>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-warning mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="ri-star-fill"></i>
                                    @else
                                        <i class="ri-star-line"></i>
                                    @endif
                                @endfor
                            </div>
                            @if($review->is_featured)
                                <span class="badge bg-warning-transparent">
                                    <i class="ri-star-fill me-1"></i>مراجعة مميزة
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($review->title)
                        <h6 class="mb-2">{{ $review->title }}</h6>
                    @endif

                    <p class="text-muted mb-3">{{ $review->review }}</p>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-light"
                                onclick="markReviewHelpful({{ $review->id }}, this)">
                            <i class="ri-thumb-up-line me-1"></i>
                            مفيدة (<span class="helpful-count">{{ $review->helpful_count }}</span>)
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <i class="ri-message-3-line fs-50 text-muted opacity-25"></i>
                <p class="text-muted mt-3">لا توجد مراجعات بعد. كن أول من يراجع هذا الكورس!</p>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Review Modal -->
@if($isEnrolled && !$userReview)
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.courses.reviews.store', $course) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">أضف مراجعتك</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">التقييم <span class="text-danger">*</span></label>
                        <div class="rating-input">
                            @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="rating" value="{{ $i }}" id="rating-{{ $i }}" required>
                                <label for="rating-{{ $i }}">
                                    <i class="ri-star-fill"></i>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">عنوان المراجعة (اختياري)</label>
                        <input type="text" name="title" class="form-control"
                               placeholder="مثال: كورس ممتاز للمبتدئين" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المراجعة <span class="text-danger">*</span></label>
                        <textarea name="review" class="form-control" rows="5"
                                  placeholder="شارك تجربتك مع هذا الكورس..."
                                  required minlength="10"></textarea>
                        <small class="text-muted">الحد الأدنى 10 أحرف</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إرسال المراجعة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Review Modal -->
@if($userReview)
<div class="modal fade" id="editReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.courses.reviews.update', [$course, $userReview]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title">تعديل مراجعتك</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">التقييم <span class="text-danger">*</span></label>
                        <div class="rating-input">
                            @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="rating" value="{{ $i }}"
                                       id="edit-rating-{{ $i }}"
                                       {{ $userReview->rating == $i ? 'checked' : '' }} required>
                                <label for="edit-rating-{{ $i }}">
                                    <i class="ri-star-fill"></i>
                                </label>
                            @endfor
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">عنوان المراجعة (اختياري)</label>
                        <input type="text" name="title" class="form-control"
                               value="{{ $userReview->title }}"
                               placeholder="مثال: كورس ممتاز للمبتدئين" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">المراجعة <span class="text-danger">*</span></label>
                        <textarea name="review" class="form-control" rows="5"
                                  required minlength="10">{{ $userReview->review }}</textarea>
                        <small class="text-muted">الحد الأدنى 10 أحرف</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 30px;
    color: #ddd;
    transition: color 0.2s;
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input:checked ~ label {
    color: #ffc107;
}
</style>

<script>
function markReviewHelpful(reviewId, button) {
    fetch(`/student/reviews/${reviewId}/helpful`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.querySelector('.helpful-count').textContent = data.helpful_count;
            button.disabled = true;
            button.classList.add('btn-success');
            button.classList.remove('btn-light');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
