@php
    $quickNumbers = [
        ['icon' => 'fe-briefcase', 'color' => 'primary', 'label' => 'عدد المدرسين', 'value' => $userStats['instructors'] ?? 0],
        ['icon' => 'fe-shield', 'color' => 'danger', 'label' => 'عدد المشرفين (Admins)', 'value' => $userStats['admins'] ?? 0],
        ['icon' => 'fe-eye', 'color' => 'success', 'label' => 'الكورسات الظاهرة في الواجهة', 'value' => $courseStats['visible_courses'] ?? 0],
        ['icon' => 'fe-star', 'color' => 'warning', 'label' => 'إجمالي التقييمات على الكورسات', 'value' => $learningStats['course_reviews'] ?? 0],
        ['icon' => 'fe-zap', 'color' => 'info', 'label' => 'نقاط تكامل n8n (Endpoints)', 'value' => $integrationStats['n8n_endpoints'] ?? 0],
        ['icon' => 'fe-git-commit', 'color' => 'secondary', 'label' => 'إجمالي سجلات الويب هوكس', 'value' => $integrationStats['incoming_webhooks'] ?? 0],
    ];
@endphp

<div class="card custom-card dashboard-quick-numbers h-100">
    <div class="card-header border-0 pb-0">
        <h6 class="card-title fs-15 mb-1">أرقام سريعة</h6>
        <span class="d-block text-muted fs-12">نظرة عامة على بعض المؤشرات الأخرى في النظام.</span>
    </div>
    <div class="card-body pt-3">
        @foreach ($quickNumbers as $index => $item)
            <div class="dashboard-stat-row dashboard-stagger-item" style="--stagger-delay: {{ $index * 50 }}ms">
                <div class="d-flex align-items-center gap-3">
                    <span class="avatar avatar-sm bg-{{ $item['color'] }}-transparent">
                        <i class="fe {{ $item['icon'] }} text-{{ $item['color'] }}"></i>
                    </span>
                    <span class="flex-fill fs-13">{{ $item['label'] }}</span>
                    <span class="fw-semibold" data-countup="{{ $item['value'] }}">0</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
