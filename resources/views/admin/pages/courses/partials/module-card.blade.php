@php
    $groupNames = $module->accessRestrictions && $module->accessRestrictions->count() > 0
        ? $module->accessRestrictions->pluck('group.name')->filter()->unique()->values()
        : collect();
    $displayGroups = $groupNames->take(3);
    $moreCount = max($groupNames->count() - $displayGroups->count(), 0);
    $hasRestrictions = $module->accessRestrictions && $module->accessRestrictions->count() > 0;

    $isResourceUrl = false;
    if ($module->module_type == 'resource' && $module->modulable) {
        $isResourceUrl = $module->modulable->resource_source == 'url';
    }

    $moduleTypeMeta = match ($module->module_type) {
        'lesson' => ['icon' => 'fe-book-open', 'label' => 'درس', 'tone' => 'primary'],
        'video' => ['icon' => 'fe-play-circle', 'label' => 'فيديو', 'tone' => 'danger'],
        'quiz' => ['icon' => 'fe-help-circle', 'label' => 'اختبار', 'tone' => 'success'],
        'assignment' => ['icon' => 'fe-check-square', 'label' => 'واجب', 'tone' => 'warning'],
        'question_module' => ['icon' => 'fe-layers', 'label' => 'وحدة أسئلة', 'tone' => 'info'],
        default => ['icon' => 'fe-file-text', 'label' => 'مورد', 'tone' => 'secondary'],
    };
@endphp

<div id="module-container-{{ $module->id }}" class="admin-course-module-card">
    <div class="admin-course-module-card__row">
        <div class="admin-course-module-card__main">
            <div class="form-check admin-course-module-card__check flex-shrink-0">
                <input type="checkbox"
                       class="form-check-input js-module-bulk-check"
                       value="{{ $module->id }}"
                       id="module-bulk-check-{{ $module->id }}"
                       data-section-id="{{ $section->id }}"
                       title="تحديد للقيود الجماعية"
                       aria-label="تحديد الوحدة">
            </div>

            <span class="admin-course-module-card__icon admin-course-module-card__icon--{{ $moduleTypeMeta['tone'] }}">
                <i class="fe {{ $moduleTypeMeta['icon'] }}"></i>
            </span>

            <div class="admin-course-module-card__content min-w-0">
                <div class="admin-course-module-card__title-row">
                    <h6 class="admin-course-module-card__title mb-0">{{ $module->title }}</h6>
                    @if($isResourceUrl)
                        <i class="fe fe-link text-info" title="رابط خارجي"></i>
                    @endif
                    <span class="group-show-chip group-show-chip--sm text-muted">#{{ $loop->iteration }}</span>
                </div>

                <div class="admin-course-module-card__chips">
                    <span class="group-show-chip group-show-chip--sm admin-course-module-card__type-chip">
                        {{ $moduleTypeMeta['label'] }}
                    </span>

                    <span id="module-main-badge-{{ $module->id }}"
                          class="group-show-chip group-show-chip--sm text-warning"
                          style="display: {{ $hasRestrictions ? 'inline-flex' : 'none' }};"
                          @if($hasRestrictions && $groupNames->isNotEmpty())
                              title="هذه الوحدة مقيدة على المجموعات: {{ $groupNames->implode('، ') }}"
                          @elseif($hasRestrictions)
                              title="هذه الوحدة لها قيود وصول"
                          @endif>
                        <i class="fe fe-lock me-1"></i>قيود
                    </span>

                    <span id="module-groups-container-{{ $module->id }}" class="d-inline-flex flex-wrap gap-1">
                        @if($hasRestrictions && $displayGroups->isNotEmpty())
                            @foreach($displayGroups as $groupName)
                                <span class="group-show-chip group-show-chip--sm module-group-badge"
                                      data-module-id="{{ $module->id }}"
                                      data-group-name="{{ $groupName }}">
                                    <i class="fe fe-users me-1"></i>{{ $groupName }}
                                </span>
                            @endforeach
                            @if($moreCount > 0)
                                <span class="group-show-chip group-show-chip--sm text-muted" id="module-more-badge-{{ $module->id }}">
                                    +{{ $moreCount }}
                                </span>
                            @endif
                        @endif
                    </span>

                    @if($module->module_type == 'question_module' && $module->modulable)
                        <span class="group-show-chip group-show-chip--sm text-info">
                            {{ $module->modulable->questions->count() }} سؤال
                        </span>
                        @if($module->modulable->getTotalGrade() > 0)
                            <span class="group-show-chip group-show-chip--sm text-success">
                                {{ $module->modulable->getTotalGrade() }} نقطة
                            </span>
                        @endif
                    @endif

                    <span id="module-visibility-badge-{{ $module->id }}"
                          class="group-show-chip group-show-chip--sm {{ $module->is_visible ? 'text-success' : 'text-muted' }}">
                        <i class="fe fe-eye{{ $module->is_visible ? '' : '-off' }} me-1"></i>
                        {{ $module->is_visible ? 'ظاهر' : 'مخفي' }}
                    </span>

                    @if($module->is_required)
                        <span class="group-show-chip group-show-chip--sm text-danger" title="مطلوب">
                            <i class="fe fe-asterisk"></i>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-course-module-card__actions">
            <button type="button"
                    class="btn btn-sm btn-warning-light rounded-pill manage-restrictions-btn"
                    data-type="module"
                    data-id="{{ $module->id }}"
                    data-title="{{ $module->title }}"
                    title="قيود المجموعات">
                <i class="fe fe-lock"></i><span class="admin-course-module-card__action-text">قيود</span>
            </button>

            <a href="{{ route('courses.modules.completions', ['course' => $course->id, 'module' => $module->id]) }}"
               class="btn btn-sm btn-success-light rounded-pill"
               title="تقدم الطلاب">
                <i class="fe fe-user-check"></i><span class="admin-course-module-card__action-text">التقدم</span>
            </a>

            @if($module->module_type == 'assignment' && $module->modulable_id)
                <a href="{{ route('assignments.show', $module->modulable_id) }}" class="btn btn-sm btn-info-light rounded-pill" title="معاينة">
                    <i class="fe fe-eye"></i><span class="admin-course-module-card__action-text">معاينة</span>
                </a>
            @elseif($module->module_type == 'quiz' && $module->modulable_id)
                <a href="{{ route('quizzes.show', $module->modulable_id) }}" class="btn btn-sm btn-info-light rounded-pill" title="معاينة">
                    <i class="fe fe-eye"></i><span class="admin-course-module-card__action-text">معاينة</span>
                </a>
            @elseif($module->module_type == 'question_module' && $module->modulable_id)
                <a href="{{ route('question-modules.show', $module->modulable_id) }}" class="btn btn-sm btn-info-light rounded-pill" title="معاينة">
                    <i class="fe fe-eye"></i><span class="admin-course-module-card__action-text">معاينة</span>
                </a>
            @else
                <a href="{{ route('sections.modules.show', [$section->id, $module->id]) }}" class="btn btn-sm btn-info-light rounded-pill" title="معاينة">
                    <i class="fe fe-eye"></i><span class="admin-course-module-card__action-text">معاينة</span>
                </a>
            @endif

            @if($module->module_type == 'assignment' && $module->modulable_id)
                <a href="{{ route('assignments.edit', $module->modulable_id) }}" class="btn btn-sm btn-primary-light rounded-pill" title="تحرير">
                    <i class="fe fe-edit-2"></i><span class="admin-course-module-card__action-text">تحرير</span>
                </a>
            @elseif($module->module_type == 'quiz' && $module->modulable_id)
                <a href="{{ route('quizzes.edit', $module->modulable_id) }}" class="btn btn-sm btn-primary-light rounded-pill" title="تحرير">
                    <i class="fe fe-edit-2"></i><span class="admin-course-module-card__action-text">تحرير</span>
                </a>
            @elseif($module->module_type == 'question_module' && $module->modulable_id)
                <a href="{{ route('question-modules.manage-questions', $module->modulable_id) }}" class="btn btn-sm btn-primary-light rounded-pill" title="تحرير">
                    <i class="fe fe-edit-2"></i><span class="admin-course-module-card__action-text">تحرير</span>
                </a>
            @elseif($module->module_type == 'video' && $module->modulable_id)
                <a href="{{ route('videos.edit', $module->modulable_id) }}" class="btn btn-sm btn-primary-light rounded-pill" title="تعديل الفيديو">
                    <i class="fe fe-video"></i><span class="admin-course-module-card__action-text">الفيديو</span>
                </a>
                <a href="{{ route('sections.modules.edit', [$section->id, $module->id]) }}" class="btn btn-sm btn-primary-light rounded-pill" title="تحرير الوحدة">
                    <i class="fe fe-edit-2"></i><span class="admin-course-module-card__action-text">الوحدة</span>
                </a>
            @else
                <a href="{{ route('sections.modules.edit', [$section->id, $module->id]) }}" class="btn btn-sm btn-primary-light rounded-pill" title="تحرير">
                    <i class="fe fe-edit-2"></i><span class="admin-course-module-card__action-text">تحرير</span>
                </a>
            @endif

            <button type="button"
                    class="btn btn-sm btn-secondary-light rounded-pill module-visibility-btn"
                    id="module-visibility-btn-{{ $module->id }}"
                    onclick="toggleVisibility('module', {{ $module->id }})"
                    title="{{ $module->is_visible ? 'إخفاء' : 'إظهار' }}">
                <i class="fe fe-eye{{ $module->is_visible ? '-off' : '' }}"></i>
                <span class="admin-course-module-card__action-text">{{ $module->is_visible ? 'إخفاء' : 'إظهار' }}</span>
            </button>

            <button type="button"
                    class="btn btn-sm btn-danger-light rounded-pill delete-module-btn"
                    id="delete-module-btn-{{ $module->id }}"
                    data-section-id="{{ $section->id }}"
                    data-module-id="{{ $module->id }}"
                    data-module-title="{{ $module->title }}"
                    title="حذف">
                <i class="fe fe-trash-2"></i><span class="admin-course-module-card__action-text">حذف</span>
            </button>
        </div>
    </div>

    @if($module->module_type == 'question_module' && $module->modulable && $module->modulable->questions->count() > 0)
        <div class="admin-course-module-card__questions">
            <h6 class="admin-course-module-card__questions-title">
                <i class="fe fe-list me-1"></i>الأسئلة ({{ $module->modulable->questions->count() }})
            </h6>
            <div class="admin-course-module-card__questions-list">
                @foreach($module->modulable->questions as $index => $question)
                    <div class="admin-course-module-card__question-item">
                        <div class="min-w-0 flex-fill">
                            <span class="group-show-chip group-show-chip--sm me-1">{{ $index + 1 }}</span>
                            <span>{!! Str::limit(strip_tags($question->question_text), 80) !!}</span>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <span class="group-show-chip group-show-chip--sm text-info">{{ $question->questionType->display_name }}</span>
                            <span class="group-show-chip group-show-chip--sm text-success">{{ $question->pivot->question_grade }} نقطة</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<form id="delete-form-{{ $module->id }}"
      action="{{ route('sections.modules.destroy', [$section->id, $module->id]) }}"
      method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
