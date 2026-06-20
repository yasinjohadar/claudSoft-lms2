@php
    $infoFields = [
        ['icon' => 'fe-activity', 'label' => 'الحالة', 'badge' => $currentStatus['name'], 'badgeColor' => $currentStatus['color']],
        ['icon' => $catIcon, 'label' => 'التصنيف', 'badge' => $currentCategory['name'], 'badgeColor' => $currentCategory['color']],
        ['icon' => 'fe-book', 'label' => 'الدورة التدريبية', 'value' => $work->course?->title],
        ['icon' => 'fe-calendar', 'label' => 'تاريخ الإنجاز', 'value' => $work->completion_date?->format('Y/m/d')],
        ['icon' => 'fe-plus-circle', 'label' => 'تاريخ الإنشاء', 'value' => $work->created_at->format('Y/m/d')],
        ['icon' => 'fe-refresh-cw', 'label' => 'آخر تحديث', 'value' => $work->updated_at->format('Y/m/d')],
        ['icon' => 'fe-check-circle', 'label' => 'تاريخ الاعتماد', 'value' => $work->approved_at?->format('Y/m/d')],
    ];
@endphp

<div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
    <div class="card-header border-0 pb-0">
        <h6 class="group-show-members-card__title mb-1">
            <i class="fe fe-info me-2 text-primary"></i>معلومات العمل
        </h6>
    </div>
    <div class="card-body pt-3">
        <div class="row g-3">
            @foreach($infoFields as $field)
                @php
                    $hasValue = !empty($field['value']) || !empty($field['badge']);
                @endphp
                @if($hasValue)
                    <div class="col-12">
                        <div class="admin-profile-detail-field">
                            <span class="admin-profile-detail-field__icon"><i class="fe {{ $field['icon'] }}"></i></span>
                            <div class="min-w-0 flex-fill">
                                <span class="admin-profile-detail-field__label">{{ $field['label'] }}</span>
                                @if(!empty($field['badge']))
                                    <span class="admin-profile-detail-field__value">
                                        <span class="badge bg-{{ $field['badgeColor'] }}-transparent text-{{ $field['badgeColor'] }}">
                                            {{ $field['badge'] }}
                                        </span>
                                    </span>
                                @else
                                    <span class="admin-profile-detail-field__value">{{ $field['value'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

@if($work->rating)
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">
                <i class="fe fe-star me-2 text-warning"></i>تقييم المدرس
            </h6>
        </div>
        <div class="card-body pt-3">
            <div class="student-work-show__rating">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fs-12">الدرجة من 10</span>
                    <strong class="fs-20 text-{{ $work->rating >= 7 ? 'success' : ($work->rating >= 5 ? 'warning' : 'danger') }}">
                        {{ $work->rating }}
                    </strong>
                </div>
                <div class="student-quizzes-result__track">
                    <div class="student-quizzes-result__bar {{ $work->rating >= 7 ? 'is-passed' : 'is-failed' }}"
                         style="width: {{ min(100, $work->rating * 10) }}%"
                         aria-valuenow="{{ $work->rating * 10 }}"></div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($work->tags && count($work->tags) > 0)
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
        <div class="card-header border-0 pb-0">
            <h6 class="group-show-members-card__title mb-1">
                <i class="fe fe-tag me-2 text-primary"></i>الوسوم
            </h6>
        </div>
        <div class="card-body pt-3">
            <div class="student-work-card__tags">
                @foreach($work->tags as $tag)
                    <span class="student-work-card__tag">#{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if($work->status === 'pending')
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 border-warning">
        <div class="card-body">
            <div class="d-flex gap-3">
                <span class="avatar avatar-md bg-warning-transparent flex-shrink-0">
                    <i class="fe fe-clock text-warning"></i>
                </span>
                <div>
                    <h6 class="mb-1">قيد المراجعة</h6>
                    <p class="text-muted fs-13 mb-0">
                        عملك الآن بانتظار مراجعة الإدارة. سيتم إخطارك بالنتيجة قريباً.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif

@if($work->status === 'rejected')
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 border-danger">
        <div class="card-body">
            <div class="d-flex gap-3">
                <span class="avatar avatar-md bg-danger-transparent flex-shrink-0">
                    <i class="fe fe-x-circle text-danger"></i>
                </span>
                <div>
                    <h6 class="mb-1">تم الرفض</h6>
                    <p class="text-muted fs-13 mb-3">
                        راجع ملاحظات المدرس وعدّل العمل ثم أعد التقديم.
                    </p>
                    @can('update', $work)
                        <a href="{{ route('student.works.edit', $work) }}" class="btn btn-sm btn-danger-light rounded-pill">
                            <i class="fe fe-edit me-1"></i>تعديل وإعادة التقديم
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endif

@if($work->status === 'approved')
    <div class="card custom-card group-show-members-card dashboard-fade-in mb-4 border-success">
        <div class="card-body">
            <div class="d-flex gap-3">
                <span class="avatar avatar-md bg-success-transparent flex-shrink-0">
                    <i class="fe fe-check-circle text-success"></i>
                </span>
                <div class="flex-fill">
                    <h6 class="mb-1">تم الاعتماد</h6>
                    <p class="text-muted fs-13 mb-3">
                        تهانينا! عملك معتمد ويظهر في بورتفوليوك العام.
                    </p>
                    <a href="{{ route('student.works.portfolio') }}" class="btn btn-sm btn-success rounded-pill w-100">
                        <i class="fe fe-image me-1"></i>عرض البورتفوليو
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
