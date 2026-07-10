@forelse ($badges as $badge)
    <tr>
        <td>{{ $badges->firstItem() + $loop->index }}</td>
        <td>
            @if ($badge->icon)
                <span style="font-size: 22px;">{{ $badge->icon }}</span>
            @else
                <i class="fe fe-award text-warning"></i>
            @endif
        </td>
        <td class="text-start">
            <div class="fw-semibold">{{ $badge->name }}</div>
            @if ($badge->description)
                <small class="text-muted">{{ Str::limit($badge->description, 50) }}</small>
            @endif
        </td>
        <td>
            @include('admin.pages.gamification.badges.reports.partials.rarity-badge', ['rarity' => $badge->rarity])
        </td>
        <td>
            <span class="badge bg-primary-transparent">{{ number_format($badge->earners_count ?? 0) }}</span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="progress flex-fill" style="height: 8px; min-width: 80px;">
                    <div class="progress-bar bg-success" style="width: {{ min(100, (float) ($badge->award_rate ?? 0)) }}%"></div>
                </div>
                <span class="fw-semibold fs-12">{{ number_format($badge->award_rate ?? 0, 1) }}%</span>
            </div>
        </td>
        <td>{{ $badge->points_value ?? 0 }}</td>
        <td class="text-center">
            <a href="{{ route('admin.gamification.badges.show', $badge) }}" class="btn btn-sm btn-icon btn-outline-primary" title="تفاصيل الشارة">
                <i class="fe fe-eye"></i>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-muted py-5">
            <i class="fe fe-inbox fs-24 d-block mb-2"></i>
            لا توجد شارات مطابقة للفلتر
        </td>
    </tr>
@endforelse
