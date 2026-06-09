@php
    $student = $report->student;
    $displayPhone = $student->full_phone
        ?? (($student->country_code && $student->phone) ? $student->country_code . $student->phone : null)
        ?? $student->phone;
    $statusLabels = [
        'draft' => ['label' => 'مسودة', 'class' => 'bg-secondary-transparent text-secondary'],
        'submitted' => ['label' => 'مرسل', 'class' => 'bg-primary-transparent text-primary'],
        'reviewed' => ['label' => 'مراجع', 'class' => 'bg-info-transparent text-info'],
        'closed' => ['label' => 'مغلق', 'class' => 'bg-warning-transparent text-warning'],
    ];
    $status = $statusLabels[$report->status] ?? ['label' => $report->status, 'class' => 'bg-secondary-transparent text-secondary'];
@endphp

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">بيانات الطالب</h4>
                <p class="fs-12 text-muted mb-0">معلومات الاتصال والانتماء للكورس والمجموعة.</p>
            </div>
            <div class="card-body pt-3">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-start gap-2">
                        <span class="text-muted fs-13">الاسم</span>
                        <span class="text-end fw-semibold">
                            {{ $student->name_ar ?? $student->name ?? '—' }}
                            @if($student->name_ar && $student->name)
                                <small class="d-block text-muted fw-normal">{{ $student->name }}</small>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">البريد</span>
                        <span>{{ $student->email ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">الهاتف</span>
                        <span>{{ $displayPhone ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">الكورس</span>
                        <span>{{ $report->targetCourse?->title ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">المجموعة</span>
                        <span>{{ $report->targetGroup?->name ?? '—' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">بيانات التقرير</h4>
                <p class="fs-12 text-muted mb-0">الحالة والتواريخ ومن أنشأ التقرير.</p>
            </div>
            <div class="card-body pt-3">
                <div class="mb-3">
                    <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">تاريخ الإنشاء</span>
                        <span>{{ $report->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">الموعد النهائي</span>
                        <span>{{ $report->due_at?->format('Y-m-d H:i') ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">وقت الإرسال</span>
                        <span>{{ $report->submitted_at?->format('Y-m-d H:i') ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">وقت المراجعة</span>
                        <span>{{ $report->reviewed_at?->format('Y-m-d H:i') ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">وقت الإغلاق</span>
                        <span>{{ $report->closed_at?->format('Y-m-d H:i') ?? '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                        <span class="text-muted fs-13">أنشأه</span>
                        <span>{{ $report->createdByAdmin?->name_ar ?? $report->createdByAdmin?->name ?? '—' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
