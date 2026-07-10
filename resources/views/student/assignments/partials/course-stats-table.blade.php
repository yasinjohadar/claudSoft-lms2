<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 group-show-table">
        <thead>
        <tr>
            <th>الكورس</th>
            <th>إجمالي</th>
            <th>مقيّم</th>
            <th>بانتظار التقييم</th>
            <th>لم يُسلّم</th>
            <th>المتوسط</th>
            <th>النقاط</th>
        </tr>
        </thead>
        <tbody>
        @foreach($courseStats as $courseStat)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent">
                            <i class="fe fe-book text-primary"></i>
                        </span>
                        <span class="fw-semibold">{{ $courseStat['course']->title }}</span>
                    </div>
                </td>
                <td><span class="badge bg-primary-transparent text-primary">{{ $courseStat['total'] }}</span></td>
                <td><span class="badge bg-success-transparent text-success">{{ $courseStat['graded'] }}</span></td>
                <td><span class="badge bg-warning-transparent text-warning">{{ $courseStat['submitted'] }}</span></td>
                <td><span class="badge bg-secondary-transparent text-secondary">{{ $courseStat['pending'] }}</span></td>
                <td>
                    @if($courseStat['average_grade'] > 0)
                        <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                            <div class="progress flex-fill" style="height: 8px;">
                                <div class="progress-bar {{ $courseStat['average_grade'] >= 60 ? 'bg-success' : 'bg-danger' }}"
                                     style="width: {{ min(100, $courseStat['average_grade']) }}%"></div>
                            </div>
                            <span class="fs-12 fw-semibold">{{ $courseStat['average_grade'] }}%</span>
                        </div>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <span class="fw-semibold">{{ $courseStat['earned_points'] }} / {{ $courseStat['total_points'] }}</span>
                </td>
            </tr>
        @endforeach
        <tr class="table-active fw-semibold">
            <td><i class="fe fe-layers me-1"></i>المجموع الكلي</td>
            <td><span class="badge bg-primary-transparent text-primary">{{ $stats['total'] }}</span></td>
            <td><span class="badge bg-success-transparent text-success">{{ $stats['graded'] }}</span></td>
            <td><span class="badge bg-warning-transparent text-warning">{{ $stats['submitted'] }}</span></td>
            <td><span class="badge bg-secondary-transparent text-secondary">{{ $stats['pending'] }}</span></td>
            <td>
                @if($stats['average_grade'] > 0)
                    <span class="fs-12 fw-semibold">{{ $stats['average_grade'] }}%</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </td>
            <td><span class="text-primary fw-bold">{{ $stats['earned_points'] }} / {{ $stats['total_points'] }}</span></td>
        </tr>
        </tbody>
    </table>
</div>
