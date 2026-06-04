@php
    $currentStatus = request('status', 'all');
    $filters = [
        ['key' => 'all', 'label' => 'جميع الحالات', 'icon' => 'fe-grid', 'params' => []],
        ['key' => 'pending', 'label' => 'قيد الانتظار', 'icon' => 'fe-clock', 'params' => ['status' => 'pending']],
        ['key' => 'approved', 'label' => 'مقبولة', 'icon' => 'fe-check-circle', 'params' => ['status' => 'approved']],
        ['key' => 'rejected', 'label' => 'مرفوضة', 'icon' => 'fe-x-circle', 'params' => ['status' => 'rejected']],
        ['key' => 'cancelled', 'label' => 'ملغاة', 'icon' => 'fe-slash', 'params' => ['status' => 'cancelled']],
    ];
@endphp

<div class="student-my-courses-filters mb-4">
    @foreach ($filters as $filter)
        <a href="{{ route('student.training-camps.my-enrollments', $filter['params']) }}"
           class="student-my-courses-filter {{ $currentStatus === $filter['key'] || ($filter['key'] === 'all' && !request('status')) ? 'is-active' : '' }}">
            <i class="fe {{ $filter['icon'] }}"></i>
            <span>{{ $filter['label'] }}</span>
        </a>
    @endforeach
</div>
