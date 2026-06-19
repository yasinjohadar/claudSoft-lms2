@php
    $status = $enrollment->enrollment_status;
    $chips = [
        'active' => ['label' => 'نشط', 'class' => 'admin-enrollment-status--active'],
        'completed' => ['label' => 'مكتمل', 'class' => 'admin-enrollment-status--completed'],
        'suspended' => ['label' => 'معلق', 'class' => 'admin-enrollment-status--suspended'],
        'pending' => ['label' => 'قيد الانتظار', 'class' => 'admin-enrollment-status--pending'],
        'cancelled' => ['label' => 'ملغي', 'class' => 'admin-enrollment-status--cancelled'],
    ];
    $chip = $chips[$status] ?? ['label' => $status, 'class' => 'admin-enrollment-status--default'];
@endphp
<span class="admin-enrollment-status {{ $chip['class'] }}">
    {{ $chip['label'] }}
</span>
