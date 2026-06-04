@php
    $currentStatus = request('status', 'all');
    $filters = [
        ['key' => 'all', 'label' => 'جميع الحالات', 'icon' => 'fe-grid', 'params' => []],
        ['key' => 'issued', 'label' => 'صادرة', 'icon' => 'fe-send', 'params' => ['status' => 'issued']],
        ['key' => 'partial', 'label' => 'جزئياً', 'icon' => 'fe-pie-chart', 'params' => ['status' => 'partial']],
        ['key' => 'paid', 'label' => 'مدفوعة', 'icon' => 'fe-check-circle', 'params' => ['status' => 'paid']],
    ];
@endphp

<div class="student-my-courses-filters mb-4">
    @foreach ($filters as $filter)
        <a href="{{ route('student.invoices.index', $filter['params']) }}"
           class="student-my-courses-filter {{ $currentStatus === $filter['key'] || ($filter['key'] === 'all' && !request('status')) ? 'is-active' : '' }}">
            <i class="fe {{ $filter['icon'] }}"></i>
            <span>{{ $filter['label'] }}</span>
        </a>
    @endforeach
</div>
