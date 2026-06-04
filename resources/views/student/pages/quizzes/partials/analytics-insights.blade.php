@php
    $items = $items ?? collect();
    $variant = $variant ?? 'success';
    $icon = $variant === 'success' ? 'fe-thumbs-up' : 'fe-alert-triangle';
    $badgeClass = $variant === 'success' ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger';
@endphp

<div class="card custom-card student-quizzes-panel h-100">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-{{ $variant }}-transparent">
                <i class="fe {{ $icon }} text-{{ $variant }}"></i>
            </span>
            <h6 class="card-title mb-0">{{ $title }}</h6>
        </div>
    </div>
    <div class="card-body pt-3">
        @forelse($items as $item)
            <div class="student-quiz-analytics-insight d-flex justify-content-between align-items-center gap-2">
                <div class="min-w-0">
                    <p class="student-quiz-analytics-insight__name">{{ $item['name'] ?? 'غير محدد' }}</p>
                    @if(!empty($item['category']))
                        <span class="student-quiz-analytics-insight__category">{{ $item['category'] }}</span>
                    @endif
                </div>
                <span class="student-quiz-analytics-insight__badge badge {{ $badgeClass }}">
                    {{ number_format($item['accuracy'] ?? 0, 1) }}%
                </span>
            </div>
        @empty
            <div class="student-quiz-analytics-empty py-3">
                <div class="student-quiz-analytics-empty__icon"><i class="fe fe-inbox"></i></div>
                <p class="mb-0 fs-13">لا توجد بيانات كافية</p>
            </div>
        @endforelse
    </div>
</div>
