<div class="student-badge-detail">
    <div class="mb-4">
        <h5 class="mb-1">{{ $student->name }}</h5>
        <p class="text-muted mb-2">{{ $student->email }}</p>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-success-transparent">مكتسبة: {{ $earned_count }}</span>
            <span class="badge bg-primary-transparent">من أصل {{ $active_badges_total }}</span>
            <span class="badge bg-info-transparent">نسبة الإكمال: {{ number_format($completion_rate, 1) }}%</span>
            @if (($overall_progress ?? 0) > 0)
                <span class="badge bg-warning-transparent">متوسط التقدم: {{ number_format($overall_progress, 1) }}%</span>
            @endif
        </div>
    </div>

    <div class="mb-4">
        <h6 class="mb-3"><i class="fe fe-check-circle text-success me-1"></i>الشارات المكتسبة ({{ $earned->count() }})</h6>
        @if ($earned->isEmpty())
            <p class="text-muted mb-0">لم يحصل هذا الطالب على شارات بعد ضمن النطاق الحالي.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>الشارة</th>
                            <th>الندرة</th>
                            <th>تاريخ المنح</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($earned as $userBadge)
                            <tr>
                                <td>
                                    <span class="me-2">{{ $userBadge->badge->icon ?? '🏅' }}</span>
                                    {{ $userBadge->badge->name }}
                                </td>
                                <td>
                                    @include('admin.pages.gamification.badges.reports.partials.rarity-badge', ['rarity' => $userBadge->badge->rarity])
                                </td>
                                <td>{{ optional($userBadge->awarded_at)->format('Y-m-d H:i') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>
        <h6 class="mb-3"><i class="fe fe-trending-up text-primary me-1"></i>قيد التقدم ({{ $in_progress->count() }})</h6>
        @if ($in_progress->isEmpty())
            <p class="text-muted mb-0">لا يوجد تقدم ملحوظ نحو شارات غير مكتسبة.</p>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach ($in_progress as $row)
                    @php
                        $badge = $row['badge'];
                        $progressPct = (float) ($row['progress']['progress'] ?? 0);
                    @endphp
                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <span class="me-2">{{ $badge->icon ?? '🏅' }}</span>
                                <strong>{{ $badge->name }}</strong>
                            </div>
                            @include('admin.pages.gamification.badges.reports.partials.rarity-badge', ['rarity' => $badge->rarity])
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-fill" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ min(100, $progressPct) }}%"></div>
                            </div>
                            <span class="fs-12 fw-semibold">{{ number_format($progressPct, 0) }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
