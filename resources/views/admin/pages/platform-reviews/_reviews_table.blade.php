<div class="table-responsive">
    <table class="table table-hover dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 48px;">#</th>
                <th style="min-width: 180px;">اسم الطالب</th>
                <th style="min-width: 120px;">المنصب</th>
                <th style="width: 110px;">التقييم</th>
                <th>النص</th>
                <th style="width: 100px;">الحالة</th>
                <th style="width: 90px;">التميز</th>
                <th style="width: 110px;">التاريخ</th>
                <th style="width: 150px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
                <tr class="platform-reviews-table-row">
                    <td>{{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            @if($review->student_image)
                                <img src="{{ asset('storage/' . $review->student_image) }}" alt="{{ $review->student_name }}" class="avatar avatar-sm rounded-circle flex-shrink-0">
                            @else
                                <div class="avatar avatar-sm rounded-circle bg-primary-transparent flex-shrink-0">
                                    <span class="fw-bold">{{ mb_substr($review->student_name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate" title="{{ $review->student_name }}">{{ $review->student_name }}</div>
                                @if($review->user)
                                    <small class="text-muted d-block text-truncate" title="{{ $review->user->email }}">{{ $review->user->email }}</small>
                                @elseif($review->student_email)
                                    <small class="text-muted d-block text-truncate" title="{{ $review->student_email }}">{{ $review->student_email }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($review->student_position)
                            <span class="platform-reviews-position-chip" title="{{ $review->student_position }}">{{ $review->student_position }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="platform-reviews-stars" title="{{ $review->rating }}/5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fe fe-star" style="opacity: {{ $i <= $review->rating ? '1' : '0.25' }};"></i>
                            @endfor
                        </span>
                        <small class="text-muted ms-1">({{ $review->rating }})</small>
                    </td>
                    <td>
                        <div class="platform-reviews-text-preview" title="{{ $review->review_text }}">
                            {{ $review->review_text }}
                        </div>
                    </td>
                    <td>
                        @if($review->is_active)
                            <span class="assignments-status-chip assignments-status-chip--published">مقبول</span>
                        @else
                            <span class="assignments-status-chip assignments-status-chip--pending">في الانتظار</span>
                        @endif
                    </td>
                    <td>
                        @if($review->is_featured)
                            <span class="assignments-status-chip assignments-status-chip--graded"><i class="fe fe-heart me-1"></i>مميز</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted d-block">{{ $review->created_at->format('Y-m-d') }}</small>
                        <small class="text-muted">{{ $review->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1 platform-reviews-actions">
                            <a href="{{ route('admin.platform-reviews.show', $review->id) }}" class="btn btn-primary-light btn-sm" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            @if(!$review->is_active)
                                <form action="{{ route('admin.platform-reviews.approve', $review->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success-light btn-sm" title="موافقة" onclick="return confirm('هل أنت متأكد من الموافقة على هذا التقييم؟');">
                                        <i class="fe fe-check"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.platform-reviews.reject', $review->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning-light btn-sm" title="رفض" onclick="return confirm('هل أنت متأكد من رفض هذا التقييم؟');">
                                        <i class="fe fe-x"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.platform-reviews.toggle-featured', $review->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info-light btn-sm" title="{{ $review->is_featured ? 'إلغاء التميز' : 'تمييز' }}">
                                    <i class="fe fe-heart"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.platform-reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-light btn-sm" title="حذف">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-star"></i></span>
                        <p class="text-muted mb-0">لا توجد تقييمات للمنصة</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
