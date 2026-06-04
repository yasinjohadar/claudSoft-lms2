@php
    $currentStatus = request('status', 'all');
    $filters = [
        ['key' => 'all', 'label' => 'جميع الحالات', 'icon' => 'fe-grid', 'params' => []],
        ['key' => 'completed', 'label' => 'مكتملة', 'icon' => 'fe-check-circle', 'params' => ['status' => 'completed']],
        ['key' => 'pending', 'label' => 'قيد الانتظار', 'icon' => 'fe-clock', 'params' => ['status' => 'pending']],
        ['key' => 'failed', 'label' => 'فاشلة', 'icon' => 'fe-x-circle', 'params' => ['status' => 'failed']],
    ];
@endphp

<div class="student-my-courses-filters mb-4">
    @foreach ($filters as $filter)
        <a href="{{ route('student.payments.index', $filter['params']) }}"
           class="student-my-courses-filter {{ $currentStatus === $filter['key'] || ($filter['key'] === 'all' && !request('status')) ? 'is-active' : '' }}">
            <i class="fe {{ $filter['icon'] }}"></i>
            <span>{{ $filter['label'] }}</span>
        </a>
    @endforeach
</div>
