<tr class="admin-users-table__row profile-group-row" data-group-id="{{ $member->group_id }}">
    <td>{{ $rowNumber }}</td>
    <td class="fw-semibold">{{ $member->group->name ?? '—' }}</td>
    <td>
        <span class="group-show-chip group-show-chip--sm">
            {{ $member->role === 'leader' ? 'قائد' : 'عضو' }}
        </span>
    </td>
    <td>
        @if(optional($member->group)->courses->isNotEmpty())
            <div class="d-flex flex-wrap gap-1">
                @foreach($member->group->courses as $course)
                    <span class="group-show-chip group-show-chip--sm">{{ Str::limit($course->title, 24) }}</span>
                @endforeach
            </div>
        @else
            <span class="text-muted">—</span>
        @endif
    </td>
    <td><small class="text-muted">{{ optional($member->joined_at)->format('Y-m-d') }}</small></td>
    <td>
        @if($member->group)
            <button type="button"
                    class="btn btn-sm btn-danger-light profile-remove-group-btn"
                    data-group-id="{{ $member->group_id }}"
                    data-group-name="{{ $member->group->name }}"
                    title="إلغاء الانضمام من المجموعة">
                <i class="fe fe-user-minus"></i>
            </button>
        @endif
    </td>
</tr>
