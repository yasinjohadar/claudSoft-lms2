@php
    $currentRarity = request('rarity', 'all');
    $filters = [
        ['key' => 'all', 'label' => 'الكل', 'icon' => 'fe-grid', 'params' => array_filter(['type' => request('type')])],
        ['key' => 'common', 'label' => 'عادية', 'icon' => 'fe-circle', 'params' => array_filter(['rarity' => 'common', 'type' => request('type')])],
        ['key' => 'rare', 'label' => 'نادرة', 'icon' => 'fe-star', 'params' => array_filter(['rarity' => 'rare', 'type' => request('type')])],
        ['key' => 'epic', 'label' => 'ملحمية', 'icon' => 'fe-zap', 'params' => array_filter(['rarity' => 'epic', 'type' => request('type')])],
        ['key' => 'legendary', 'label' => 'أسطورية', 'icon' => 'fe-award', 'params' => array_filter(['rarity' => 'legendary', 'type' => request('type')])],
    ];
@endphp

<div class="student-my-courses-filters mb-4">
    @foreach ($filters as $filter)
        <a href="{{ route('gamification.badges.index', $filter['params']) }}"
           class="student-my-courses-filter {{ $currentRarity === $filter['key'] || ($filter['key'] === 'all' && !request('rarity')) ? 'is-active' : '' }}">
            <i class="fe {{ $filter['icon'] }}"></i>
            <span>{{ $filter['label'] }}</span>
        </a>
    @endforeach
</div>
