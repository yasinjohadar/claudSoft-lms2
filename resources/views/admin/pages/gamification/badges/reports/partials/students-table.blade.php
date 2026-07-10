@forelse ($students as $student)
    <tr>
        <td>{{ $students->firstItem() + $loop->index }}</td>
        <td class="text-start">
            <div class="fw-semibold">{{ $student->name }}</div>
            @if ($student->name_ar)
                <small class="text-muted d-block">{{ $student->name_ar }}</small>
            @endif
            <small class="text-muted">{{ $student->email }}</small>
        </td>
        <td>
            <span class="badge bg-primary-transparent">{{ $student->earned_count ?? 0 }}</span>
            <span class="text-muted">/ {{ $student->active_badges_total ?? 0 }}</span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="progress flex-fill" style="height: 8px; min-width: 80px;">
                    <div class="progress-bar bg-success" style="width: {{ min(100, (float) ($student->completion_rate ?? 0)) }}%"></div>
                </div>
                <span class="fw-semibold fs-12">{{ number_format($student->completion_rate ?? 0, 1) }}%</span>
            </div>
        </td>
        <td class="text-center">
            <button type="button"
                class="btn btn-sm btn-outline-primary badge-student-detail-btn"
                data-student-id="{{ $student->id }}"
                data-student-name="{{ $student->name }}"
                title="تفاصيل الشارات">
                <i class="fe fe-award me-1"></i>التفاصيل
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center text-muted py-5">
            <i class="fe fe-inbox fs-24 d-block mb-2"></i>
            لا يوجد طلاب مطابقون للفلتر
        </td>
    </tr>
@endforelse
