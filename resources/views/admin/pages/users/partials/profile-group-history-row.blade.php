<tr class="admin-users-table__row profile-group-history-row" data-history-id="{{ $history->id }}">
    <td class="profile-group-history-row__number">{{ $rowNumber }}</td>
    <td class="fw-semibold">{{ $history->group->name ?? '—' }}</td>
    <td>
        <span class="group-show-chip group-show-chip--sm">
            {{ $history->role === 'leader' ? 'قائد' : 'عضو' }}
        </span>
    </td>
    <td><small class="text-muted">{{ optional($history->joined_at)->format('Y-m-d H:i') }}</small></td>
    <td>
        @if($history->left_at)
            <small class="text-muted">{{ $history->left_at->format('Y-m-d H:i') }}</small>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td><small>{{ Str::limit($history->join_reason, 80) ?: '—' }}</small></td>
    <td><small>{{ Str::limit($history->leave_reason, 80) ?: '—' }}</small></td>
    <td><small class="text-muted">{{ $history->joinedByUser->name ?? '—' }}</small></td>
    <td><small class="text-muted">{{ $history->removedByUser->name ?? '—' }}</small></td>
    <td><small class="text-muted">{{ $history->source_label }}</small></td>
    <td>
        @if($history->isActive())
            <span class="group-show-chip group-show-chip--sm text-success">نشط</span>
        @else
            <span class="group-show-chip group-show-chip--sm">منتهي</span>
        @endif
    </td>
</tr>
