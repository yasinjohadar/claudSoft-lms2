@php
    $currentTier = request('tier', 'all');
    $currentStatus = request('status', 'all');

    $tierFilters = [
        ['key' => 'all', 'label' => 'كل المستويات', 'icon' => 'fe-grid', 'params' => array_filter(['status' => request('status') !== 'all' ? request('status') : null])],
        ['key' => 'bronze', 'label' => 'برونزي', 'icon' => 'fe-shield', 'params' => array_filter(['tier' => 'bronze', 'status' => request('status') !== 'all' ? request('status') : null])],
        ['key' => 'silver', 'label' => 'فضي', 'icon' => 'fe-star', 'params' => array_filter(['tier' => 'silver', 'status' => request('status') !== 'all' ? request('status') : null])],
        ['key' => 'gold', 'label' => 'ذهبي', 'icon' => 'fe-award', 'params' => array_filter(['tier' => 'gold', 'status' => request('status') !== 'all' ? request('status') : null])],
        ['key' => 'platinum', 'label' => 'بلاتيني', 'icon' => 'fe-zap', 'params' => array_filter(['tier' => 'platinum', 'status' => request('status') !== 'all' ? request('status') : null])],
        ['key' => 'diamond', 'label' => 'ماسي', 'icon' => 'fe-aperture', 'params' => array_filter(['tier' => 'diamond', 'status' => request('status') !== 'all' ? request('status') : null])],
    ];
@endphp

<div class="student-achievements-filters mb-4">
    <div class="student-achievements-filters__group">
        <span class="student-achievements-filters__label">المستوى</span>
        <div class="student-my-courses-filters student-achievements-filters__pills">
            @foreach ($tierFilters as $filter)
                <a href="{{ route('gamification.achievements.index', $filter['params']) }}"
                   class="student-my-courses-filter student-achievements-tier-tab student-achievements-tier-tab--{{ $filter['key'] }} {{ $currentTier === $filter['key'] || ($filter['key'] === 'all' && !request('tier')) ? 'is-active' : '' }}">
                    <i class="fe {{ $filter['icon'] }}"></i>
                    <span>{{ $filter['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="student-achievements-filters__group mt-3">
        <span class="student-achievements-filters__label">الحالة</span>
        <div class="student-my-courses-filters student-achievements-filters__pills" id="achievementStatusFilters">
            @foreach ([
                ['key' => 'all', 'label' => 'الكل', 'icon' => 'fe-grid'],
                ['key' => 'completed', 'label' => 'مكتمل', 'icon' => 'fe-check-circle'],
                ['key' => 'in_progress', 'label' => 'قيد التقدم', 'icon' => 'fe-loader'],
                ['key' => 'not_started', 'label' => 'لم يبدأ', 'icon' => 'fe-lock'],
            ] as $filter)
                <button type="button"
                    class="student-my-courses-filter student-achievements-status-tab {{ $currentStatus === $filter['key'] || ($filter['key'] === 'all' && !request('status')) ? 'is-active' : '' }}"
                    data-status-filter="{{ $filter['key'] }}">
                    <i class="fe {{ $filter['icon'] }}"></i>
                    <span>{{ $filter['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
