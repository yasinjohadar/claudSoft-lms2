@php
    $categoryIcons = [
        'project' => 'fe-code',
        'assignment' => 'fe-file-text',
        'creative' => 'fe-image',
        'research' => 'fe-search',
        'other' => 'fe-folder',
    ];
    $cat = $categories[$work->category] ?? ['name' => $work->category, 'color' => 'secondary'];
    $status = $statuses[$work->status] ?? ['name' => $work->status, 'color' => 'secondary'];
    $catIcon = $categoryIcons[$work->category] ?? 'fe-folder';
@endphp

<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 45 }}ms">
    <article class="student-course-card student-work-card h-100">
        <div class="student-course-card__media">
            <div class="student-course-card__placeholder {{ $work->image ? 'student-course-card__placeholder--under' : '' }}">
                <span class="student-course-card__placeholder-icon">
                    <i class="fe {{ $catIcon }}"></i>
                </span>
            </div>
            @if($work->image)
                <img src="{{ $work->image_url }}"
                     alt="{{ $work->title }}"
                     class="student-course-card__img"
                     loading="lazy"
                     onerror="this.style.display='none'">
            @endif

            <span class="student-course-card__badge bg-{{ $status['color'] }}-transparent text-{{ $status['color'] }}">
                <i class="fe fe-{{ $work->status === 'approved' ? 'check-circle' : ($work->status === 'pending' ? 'clock' : ($work->status === 'rejected' ? 'x-circle' : 'edit-3')) }} me-1"></i>
                {{ $status['name'] }}
            </span>

            <span class="student-work-card__category bg-{{ $cat['color'] }}-transparent text-{{ $cat['color'] }}">
                <i class="fe {{ $catIcon }} me-1"></i>{{ $cat['name'] }}
            </span>
        </div>

        <div class="student-course-card__body">
            <h5 class="student-course-card__title mb-2">
                <a href="{{ route('student.works.show', $work) }}">{{ $work->title }}</a>
            </h5>

            @if($work->course)
                <p class="text-muted fs-12 mb-2">
                    <i class="fe fe-book me-1"></i>{{ $work->course->title }}
                </p>
            @endif

            @if($work->description)
                <p class="text-muted fs-13 mb-3">{{ Str::limit($work->description, 100) }}</p>
            @endif

            @if($work->isApproved() && $work->rating)
                <div class="student-work-card__rating mb-3">
                    <span><i class="fe fe-star me-1"></i>التقييم</span>
                    <strong>{{ $work->rating }} / 10</strong>
                </div>
            @endif

            @if($work->status === 'rejected' && $work->admin_feedback)
                <div class="student-work-card__feedback mb-3">
                    <i class="fe fe-message-square me-1"></i>{{ Str::limit($work->admin_feedback, 80) }}
                </div>
            @endif

            @if($work->tags && count($work->tags) > 0)
                <div class="student-work-card__tags mb-3">
                    @foreach(array_slice($work->tags, 0, 3) as $tag)
                        <span class="student-work-card__tag">#{{ $tag }}</span>
                    @endforeach
                    @if(count($work->tags) > 3)
                        <span class="text-muted fs-11">+{{ count($work->tags) - 3 }}</span>
                    @endif
                </div>
            @endif

            <div class="student-course-card__meta">
                @if($work->completion_date)
                    <span><i class="fe fe-calendar me-1"></i>{{ $work->completion_date->format('Y/m/d') }}</span>
                @endif
                <span><i class="fe fe-eye me-1"></i>{{ $work->views_count }}</span>
            </div>

            <div class="student-course-card__actions">
                <a href="{{ route('student.works.show', $work) }}" class="btn btn-sm btn-primary-light rounded-pill">
                    <i class="fe fe-eye me-1"></i>عرض
                </a>

                @can('update', $work)
                    <a href="{{ route('student.works.edit', $work) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <i class="fe fe-edit me-1"></i>تعديل
                    </a>
                @endcan

                @if($work->status === 'draft')
                    <form action="{{ route('student.works.submit', $work) }}" method="POST" class="d-inline flex-fill">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success rounded-pill w-100"
                                onclick="return confirm('هل أنت متأكد من تقديم هذا العمل للمراجعة؟')">
                            <i class="fe fe-send me-1"></i>تقديم
                        </button>
                    </form>
                @endif

                @can('delete', $work)
                    <form action="{{ route('student.works.destroy', $work) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"
                                onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟')"
                                title="حذف">
                            <i class="fe fe-trash-2"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </article>
</div>
