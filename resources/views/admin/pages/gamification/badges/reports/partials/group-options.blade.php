@php
    $selectedGroupId = (int) request('group_id', 0);
@endphp

<option value="">كل المجموعات</option>
@foreach ($allGroups ?? [] as $group)
    <option value="{{ $group->id }}" {{ $selectedGroupId === (int) $group->id ? 'selected' : '' }}>
        {{ $group->name }}
    </option>
@endforeach
