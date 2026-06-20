@if($work->image)
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 overflow-hidden">
        <div class="student-work-show__cover">
            <img src="{{ $work->image_url }}" alt="{{ $work->title }}" class="student-work-show__cover-img">
        </div>
    </div>
@endif

<div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">
            <i class="fe fe-file-text me-2 text-primary"></i>الوصف
        </h6>
    </div>
    <div class="card-body pt-3">
        @if($work->description)
            <p class="student-work-show__description mb-0">{{ $work->description }}</p>
        @else
            <p class="text-muted mb-0">لا يوجد وصف متاح لهذا العمل.</p>
        @endif
    </div>
</div>

@if($work->technologies)
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">
                <i class="fe fe-code me-2 text-primary"></i>التقنيات المستخدمة
            </h6>
        </div>
        <div class="card-body pt-3">
            <div class="student-work-card__tags">
                @foreach(explode(',', $work->technologies) as $tech)
                    @if(trim($tech) !== '')
                        <span class="student-work-card__tag">{{ trim($tech) }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif

@if($work->github_url || $work->demo_url || $work->website_url || $work->video_url)
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">
                <i class="fe fe-link me-2 text-primary"></i>الروابط والمصادر
            </h6>
        </div>
        <div class="card-body pt-3">
            <div class="row g-3">
                @if($work->github_url)
                    <div class="col-md-6">
                        <a href="{{ $work->github_url }}" target="_blank" rel="noopener noreferrer"
                           class="student-work-show__link student-work-show__link--dark">
                            <span class="student-work-show__link-icon"><i class="fe fe-github"></i></span>
                            <span class="student-work-show__link-text">
                                <strong>GitHub</strong>
                                <small>عرض الكود المصدري</small>
                            </span>
                            <i class="fe fe-external-link student-work-show__link-arrow"></i>
                        </a>
                    </div>
                @endif
                @if($work->demo_url)
                    <div class="col-md-6">
                        <a href="{{ $work->demo_url }}" target="_blank" rel="noopener noreferrer"
                           class="student-work-show__link student-work-show__link--primary">
                            <span class="student-work-show__link-icon"><i class="fe fe-globe"></i></span>
                            <span class="student-work-show__link-text">
                                <strong>التجربة الحية</strong>
                                <small>Demo مباشر</small>
                            </span>
                            <i class="fe fe-external-link student-work-show__link-arrow"></i>
                        </a>
                    </div>
                @endif
                @if($work->website_url)
                    <div class="col-md-6">
                        <a href="{{ $work->website_url }}" target="_blank" rel="noopener noreferrer"
                           class="student-work-show__link student-work-show__link--info">
                            <span class="student-work-show__link-icon"><i class="fe fe-link"></i></span>
                            <span class="student-work-show__link-text">
                                <strong>زيارة الموقع</strong>
                                <small>الموقع الرسمي</small>
                            </span>
                            <i class="fe fe-external-link student-work-show__link-arrow"></i>
                        </a>
                    </div>
                @endif
                @if($work->video_url)
                    <div class="col-md-6">
                        <a href="{{ $work->video_url }}" target="_blank" rel="noopener noreferrer"
                           class="student-work-show__link student-work-show__link--danger">
                            <span class="student-work-show__link-icon"><i class="fe fe-play-circle"></i></span>
                            <span class="student-work-show__link-text">
                                <strong>مشاهدة الفيديو</strong>
                                <small>عرض توضيحي</small>
                            </span>
                            <i class="fe fe-external-link student-work-show__link-arrow"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

@if($work->admin_feedback)
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 student-work-show__feedback-card {{ $work->status === 'approved' ? 'is-approved' : 'is-rejected' }}">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">
                <i class="fe fe-message-square me-2"></i>ملاحظات المدرس
            </h6>
        </div>
        <div class="card-body pt-3">
            <p class="student-work-show__description mb-3">{{ $work->admin_feedback }}</p>
            @if($work->approver)
                <div class="d-flex align-items-center gap-2 text-muted fs-12">
                    <span class="avatar avatar-xs bg-primary-transparent">
                        <i class="fe fe-user text-primary"></i>
                    </span>
                    <span>{{ $work->approver->name }}</span>
                    @if($work->approved_at)
                        <span>•</span>
                        <span>{{ $work->approved_at->format('Y/m/d h:i A') }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
