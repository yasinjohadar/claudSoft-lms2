@php
    $tier = $tier ?? '';
    $tierConfig = [
        'bronze' => ['label' => 'برونزي', 'class' => 'bg-warning-transparent text-warning'],
        'silver' => ['label' => 'فضي', 'class' => 'bg-secondary-transparent text-secondary'],
        'gold' => ['label' => 'ذهبي', 'class' => 'bg-warning-transparent text-dark'],
        'platinum' => ['label' => 'بلاتيني', 'class' => 'bg-info-transparent text-info'],
        'diamond' => ['label' => 'ماسي', 'class' => 'bg-primary-transparent text-primary'],
    ];
    $config = $tierConfig[$tier] ?? ['label' => $tier, 'class' => 'bg-light text-dark'];
@endphp
<span class="badge {{ $config['class'] }}">{{ $config['label'] }}</span>
