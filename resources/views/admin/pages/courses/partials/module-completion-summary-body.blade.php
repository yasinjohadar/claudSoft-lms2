{{-- بطاقات الملخص + جداول الأقسام (يُحدَّث عبر Ajax) --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card custom-card mb-0">
            <div class="card-body">
                <p class="text-muted small mb-1">إجمالي الوحدات (الدروس)</p>
                <h4 class="fw-bold mb-0">{{ $totalModules }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card mb-0">
            <div class="card-body">
                <p class="text-muted small mb-1">
                    @if($groupFilterActive)
                        مسجّلون نشطون في الكورس وفي المجموعة
                    @else
                        المسجّلون النشطون
                    @endif
                </p>
                <h4 class="fw-bold mb-0">{{ $denominatorCount }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card mb-0">
            <div class="card-body">
                <p class="text-muted small mb-1">النسبة المعروضة</p>
                <p class="mb-0 small">
                    عدد المكمّلين ÷ المقام أعلاه (لكل وحدة)
                    @if($groupFilterActive)
                        <span class="d-block mt-1 text-primary">مقيّد بالمجموعة المختارة</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

@php $globalIndex = 0; @endphp
@forelse($course->sections as $section)
    <div class="card custom-card mb-3">
        <div class="card-header">
            <div class="card-title mb-0">
                <i class="fas fa-folder me-2 text-primary"></i>{{ $section->title }}
                <span class="badge bg-light text-default ms-2">{{ $section->modules->count() }} {{ $section->modules->count() === 1 ? 'وحدة' : 'وحدات' }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($section->modules->isEmpty())
                <p class="text-muted p-4 mb-0">لا توجد وحدات في هذا القسم.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الوحدة</th>
                            <th>النوع</th>
                            <th>المكمّلون</th>
                            <th>من أصل المقام</th>
                            <th>النسبة</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($section->modules as $module)
                            @php
                                $globalIndex++;
                                $completed = (int) ($completedByModule[$module->id] ?? 0);
                                $pct = $denominatorCount > 0
                                    ? round(($completed / $denominatorCount) * 100, 1)
                                    : null;
                                $detailUrl = route('courses.modules.completions', ['course' => $course->id, 'module' => $module->id]);
                                if ($groupFilterActive && $selectedGroupId) {
                                    $detailUrl .= '?group_id=' . $selectedGroupId;
                                }
                            @endphp
                            <tr>
                                <td>{{ $globalIndex }}</td>
                                <td class="fw-medium">{{ $module->title }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        @if($module->module_type == 'lesson') درس
                                        @elseif($module->module_type == 'video') فيديو
                                        @elseif($module->module_type == 'quiz') اختبار
                                        @elseif($module->module_type == 'assignment') واجب
                                        @elseif($module->module_type == 'question_module') وحدة أسئلة
                                        @elseif($module->module_type == 'resource') مورد
                                        @else {{ $module->module_type }}
                                        @endif
                                    </span>
                                </td>
                                <td><strong>{{ $completed }}</strong></td>
                                <td>
                                    @if($denominatorCount > 0)
                                        {{ $completed }} / {{ $denominatorCount }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pct !== null)
                                        {{ $pct }}%
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ $detailUrl }}"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-user-check me-1"></i>تقدم الطلاب
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="card custom-card">
        <div class="card-body text-muted">لا توجد أقسام في هذا الكورس.</div>
    </div>
@endforelse
