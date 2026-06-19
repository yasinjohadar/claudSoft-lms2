@if ($user->hasRole('student'))
    @php
        $pc = $user->profile_completion_data;
        $pct = (int) ($pc['percentage'] ?? 0);
        $tooltip = $pct >= 100
            ? 'الملف مكتمل'
            : 'ناقص: ' . implode('، ', $pc['missing_fields'] ?? []);
    @endphp
    <div class="admin-users-profile-pct" title="{{ $tooltip }}">
        <div class="admin-users-profile-pct__bar" role="progressbar"
             aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"
             aria-label="نسبة اكتمال البروفايل">
            <div class="admin-users-profile-pct__fill {{ $pct >= 100 ? 'is-complete' : '' }}"
                 style="width: {{ max(0, min(100, $pct)) }}%"></div>
        </div>
        <span class="admin-users-profile-pct__label {{ $pct >= 100 ? 'is-complete' : '' }}">{{ $pct }}%</span>
    </div>
@else
    <span class="text-muted">—</span>
@endif
