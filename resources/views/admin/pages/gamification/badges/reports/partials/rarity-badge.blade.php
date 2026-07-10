@php
    $labels = [
        'common' => ['class' => 'secondary', 'label' => 'عادي'],
        'rare' => ['class' => 'info', 'label' => 'نادر'],
        'epic' => ['class' => 'primary', 'label' => 'ملحمي'],
        'legendary' => ['class' => 'warning', 'label' => 'أسطوري'],
        'mythic' => ['class' => 'danger', 'label' => 'خرافي'],
    ];
    $meta = $labels[$rarity ?? ''] ?? ['class' => 'secondary', 'label' => $rarity ?? '—'];
@endphp

<span class="badge bg-{{ $meta['class'] }}-transparent text-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
