<div class="row g-4" id="student-gifts-grid">
    @forelse ($recipients as $index => $recipient)
        @php $gift = $recipient->gift; @endphp
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index % 12) * 40 }}ms">
            <article class="student-gift-card h-100">
                <div class="student-gift-card__cover">
                    @if($gift?->cover_url)
                        <img src="{{ $gift->cover_url }}" alt="{{ $gift->name }}" class="student-gift-card__cover-img">
                    @else
                        <div class="student-gift-card__cover-placeholder">
                            <i class="ri ri-gift-line"></i>
                        </div>
                    @endif
                    <span class="student-gift-card__date badge bg-success-transparent">
                        <i class="fe fe-calendar me-1"></i>{{ $recipient->granted_at?->format('Y-m-d') }}
                    </span>
                </div>

                <div class="student-gift-card__body">
                    <h6 class="student-gift-card__title" title="{{ $gift?->name }}">{{ $gift?->name }}</h6>

                    @if($gift?->description)
                        <p class="student-gift-card__desc">{{ $gift->description }}</p>
                    @else
                        <p class="student-gift-card__desc text-muted">هدية مقدمة من الأكاديمية</p>
                    @endif

                    <div class="student-gift-card__meta">
                        @if($recipient->downloaded_at)
                            <span class="badge bg-primary-transparent fs-11"><i class="fe fe-check me-1"></i>تم التحميل</span>
                        @endif
                        @if($recipient->previewed_at)
                            <span class="badge bg-info-transparent fs-11"><i class="fe fe-eye me-1"></i>تمت المعاينة</span>
                        @endif
                    </div>
                </div>

                <div class="student-gift-card__footer">
                    @if($gift?->isExternalMode() ? $gift->preview_url : $gift?->preview_file_path)
                        <a href="{{ route('student.gifts.preview', $recipient) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="fe fe-eye me-1"></i>معاينة
                        </a>
                    @endif
                    @if($gift?->isExternalMode() ? $gift->download_url : $gift?->download_file_path)
                        <a href="{{ route('student.gifts.download', $recipient) }}" class="btn btn-primary btn-sm rounded-pill">
                            <i class="fe fe-download me-1"></i>تحميل
                        </a>
                    @endif
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="student-my-courses-empty text-center py-5">
                <div class="student-my-courses-empty__icon mb-4"><i class="ri ri-gift-line"></i></div>
                <h5 class="mb-2">لا توجد هدايا بعد</h5>
                <p class="text-muted mb-0">ستظهر هنا هدايا الأكاديمية التي يمنحك إياها المشرفون.</p>
            </div>
        </div>
    @endforelse
</div>
