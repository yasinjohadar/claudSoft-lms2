@php
    $module = $module ?? null;
    $section = $section ?? null;
    $orderNumber = $orderNumber ?? 1;
    if (!$module || !$section) {
        return;
    }
    
    // Load access restrictions if not loaded
    if (!$module->relationLoaded('accessRestrictions')) {
        $module->load([
            'accessRestrictions' => function($query) {
                $query->where('restriction_type', 'group')
                      ->where('access_type', 'allow');
            },
            'accessRestrictions.group'
        ]);
    }
    
    // Load modulable if not loaded (for resource check)
    if (!$module->relationLoaded('modulable') && $module->module_type == 'resource') {
        $module->load('modulable');
    }
    
    // أسماء المجموعات المرتبطة بقيود هذه الوحدة
    $groupNames = $module->accessRestrictions && $module->accessRestrictions->count() > 0
        ? $module->accessRestrictions
            ->pluck('group.name')
            ->filter()
            ->unique()
            ->values()
        : collect();
    $displayGroups = $groupNames->take(3);
    $moreCount = max($groupNames->count() - $displayGroups->count(), 0);
    $hasRestrictions = $module->accessRestrictions && $module->accessRestrictions->count() > 0;
    
    // التحقق من أن المورد هو رابط
    $isResourceUrl = false;
    if ($module->module_type == 'resource' && $module->modulable) {
        $isResourceUrl = $module->modulable->resource_source == 'url';
    }
@endphp

<div id="module-container-{{ $module->id }}" class="mb-3 border rounded" style="transition: all 0.3s ease;">
    <div class="d-flex align-items-center justify-content-between p-3">
        <div class="d-flex align-items-center flex-grow-1">
            <span class="avatar avatar-md me-3
                {{ $module->module_type == 'lesson' ? 'bg-primary-transparent text-primary' : '' }}
                {{ $module->module_type == 'video' ? 'bg-danger-transparent text-danger' : '' }}
                {{ $module->module_type == 'quiz' ? 'bg-success-transparent text-success' : '' }}
                {{ $module->module_type == 'assignment' ? 'bg-warning-transparent text-warning' : '' }}
                {{ $module->module_type == 'question_module' ? 'bg-info-transparent text-info' : '' }}
                {{ $module->module_type == 'resource' ? 'bg-secondary-transparent text-secondary' : '' }}">
                @if($module->module_type == 'lesson')
                    <i class="fas fa-book-open"></i>
                @elseif($module->module_type == 'video')
                    <i class="fas fa-play"></i>
                @elseif($module->module_type == 'quiz')
                    <i class="fas fa-question-circle"></i>
                @elseif($module->module_type == 'assignment')
                    <i class="fas fa-tasks"></i>
                @elseif($module->module_type == 'question_module')
                    <i class="fas fa-clipboard-question"></i>
                @else
                    <i class="fas fa-file"></i>
                @endif
            </span>
            <div>
                <h6 class="mb-1 fw-semibold text-dark d-flex align-items-center gap-2 flex-wrap">
                    <span>{{ $module->title }}</span>
                    <span class="badge bg-secondary-transparent text-secondary">#{{ $orderNumber }}</span>
                    @if($isResourceUrl)
                        <i class="fas fa-link text-info" title="رابط خارجي"></i>
                    @endif
                </h6>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span id="module-main-badge-{{ $module->id }}" class="badge bg-warning text-dark" style="display: {{ $hasRestrictions ? 'inline-block' : 'none' }};"
                          @if($hasRestrictions && $groupNames->isNotEmpty())
                              title="هذه الوحدة مقيدة على المجموعات: {{ $groupNames->implode('، ') }}"
                          @elseif($hasRestrictions)
                              title="هذه الوحدة لها قيود وصول"
                          @endif
                    >
                        <i class="fas fa-lock me-1"></i>قيود
                    </span>
                    <span id="module-groups-container-{{ $module->id }}">
                        @if($hasRestrictions && $displayGroups->isNotEmpty())
                            @foreach($displayGroups as $index => $groupName)
                                <span class="badge bg-primary-transparent text-primary module-group-badge" data-module-id="{{ $module->id }}" data-group-name="{{ $groupName }}">
                                    <i class="fas fa-users me-1"></i>{{ $groupName }}
                                </span>
                            @endforeach
                            @if($moreCount > 0)
                                <span class="badge bg-light text-muted" id="module-more-badge-{{ $module->id }}">
                                    +{{ $moreCount }}
                                </span>
                            @endif
                        @endif
                    </span>
                </div>
                <small class="text-muted">
                    <span class="badge bg-light text-default me-1">
                        @if($module->module_type == 'lesson') درس
                        @elseif($module->module_type == 'video') فيديو
                        @elseif($module->module_type == 'quiz') اختبار
                        @elseif($module->module_type == 'assignment') واجب
                        @elseif($module->module_type == 'question_module') وحدة أسئلة
                        @elseif($module->module_type == 'resource') مورد
                        @endif
                    </span>
                    @if($module->module_type == 'question_module' && $module->modulable)
                        <span class="badge bg-info-transparent text-info badge-sm ms-1">
                            {{ $module->modulable->questions->count() }} سؤال
                        </span>
                        @if($module->modulable->getTotalGrade() > 0)
                            <span class="badge bg-success-transparent text-success badge-sm ms-1">
                                {{ $module->modulable->getTotalGrade() }} نقطة
                            </span>
                        @endif
                    @endif
                    <span id="module-visibility-badge-{{ $module->id }}" class="badge badge-sm ms-1 {{ $module->is_visible ? 'bg-success text-white' : 'bg-secondary' }}">
                        {{ $module->is_visible ? 'ظاهر' : 'مخفي' }}
                    </span>
                    @if($module->is_required)
                        <i class="fas fa-asterisk text-danger ms-1" style="font-size: 8px;" title="مطلوب"></i>
                    @endif
                </small>
            </div>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-outline-warning manage-restrictions-btn"
                    data-type="module"
                    data-id="{{ $module->id }}"
                    data-title="{{ $module->title }}"
                    title="إدارة القيود للمجموعات">
                <i class="fas fa-users-cog me-1"></i>قيود
            </button>
            @if($module->module_type == 'assignment' && $module->modulable_id)
                <a href="{{ route('assignments.show', $module->modulable_id) }}"
                   class="btn btn-sm btn-outline-info">
                    <i class="fas fa-eye me-1"></i>معاينة
                </a>
            @elseif($module->module_type == 'quiz' && $module->modulable_id)
                <a href="{{ route('quizzes.show', $module->modulable_id) }}"
                   class="btn btn-sm btn-outline-info">
                    <i class="fas fa-eye me-1"></i>معاينة
                </a>
            @elseif($module->module_type == 'question_module' && $module->modulable_id)
                <a href="{{ route('question-modules.show', $module->modulable_id) }}"
                   class="btn btn-sm btn-outline-info">
                    <i class="fas fa-eye me-1"></i>معاينة
                </a>
            @else
                <a href="{{ route('sections.modules.show', [$section->id, $module->id]) }}"
                   class="btn btn-sm btn-outline-info">
                    <i class="fas fa-eye me-1"></i>معاينة
                </a>
            @endif
            @if($module->module_type == 'assignment' && $module->modulable_id)
                <a href="{{ route('assignments.edit', $module->modulable_id) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>تحرير
                </a>
            @elseif($module->module_type == 'quiz' && $module->modulable_id)
                <a href="{{ route('quizzes.edit', $module->modulable_id) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>تحرير
                </a>
            @elseif($module->module_type == 'question_module' && $module->modulable_id)
                <a href="{{ route('question-modules.manage-questions', $module->modulable_id) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>تحرير
                </a>
            @elseif($module->module_type == 'video' && $module->modulable_id)
                <a href="{{ route('videos.edit', $module->modulable_id) }}"
                   class="btn btn-sm btn-outline-warning"
                   title="تعديل الفيديو مباشرة">
                    <i class="fas fa-video me-1"></i>تعديل الفيديو
                </a>
                <a href="{{ route('sections.modules.edit', [$section->id, $module->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>تحرير الوحدة
                </a>
            @else
                <a href="{{ route('sections.modules.edit', [$section->id, $module->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>تحرير
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary module-visibility-btn"
                    id="module-visibility-btn-{{ $module->id }}"
                    onclick="toggleVisibility('module', {{ $module->id }})">
                <i class="far fa-eye{{ $module->is_visible ? '' : '-slash' }} me-1"></i>
                {{ $module->is_visible ? 'إخفاء' : 'إظهار' }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger delete-module-btn"
                    id="delete-module-btn-{{ $module->id }}"
                    data-section-id="{{ $section->id }}"
                    data-module-id="{{ $module->id }}"
                    data-module-title="{{ $module->title }}">
                <i class="fas fa-trash me-1"></i>حذف
            </button>
        </div>
    </div>
</div>

