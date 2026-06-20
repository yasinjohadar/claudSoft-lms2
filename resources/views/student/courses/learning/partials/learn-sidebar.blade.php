@php
    $moduleTypeMeta = [
        'video' => ['icon' => 'fe-play-circle', 'label' => 'فيديو', 'color' => 'danger'],
        'lesson' => ['icon' => 'fe-book-open', 'label' => 'درس', 'color' => 'primary'],
        'assignment' => ['icon' => 'fe-clipboard', 'label' => 'واجب', 'color' => 'warning'],
        'quiz' => ['icon' => 'fe-help-circle', 'label' => 'اختبار', 'color' => 'success'],
        'question_module' => ['icon' => 'fe-file-text', 'label' => 'اختبار', 'color' => 'info'],
        'resource' => ['icon' => 'fe-link', 'label' => 'مورد', 'color' => 'secondary'],
    ];
    $currentMeta = $moduleTypeMeta[$module->module_type] ?? ['icon' => 'fe-file', 'label' => 'محتوى', 'color' => 'secondary'];
@endphp

<div class="student-learn-sidebar sticky-top">
    <div class="card custom-card group-show-members-card dashboard-fade-in student-learn-sidebar-card">
        <div class="student-learn-sidebar-hero">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <span class="badge bg-{{ $currentMeta['color'] }}-transparent text-{{ $currentMeta['color'] }}" id="js-learn-sidebar-type-content">
                    <i class="fe {{ $currentMeta['icon'] }} me-1"></i>{{ $currentMeta['label'] }}
                </span>
                <span id="js-learn-sidebar-completed-wrap" class="{{ $isCompleted ? '' : 'd-none' }}">
                    <span class="badge bg-success-transparent text-success">
                        <i class="fe fe-check-circle me-1"></i>مكتمل
                    </span>
                </span>
            </div>
            <h5 id="js-learn-sidebar-title" class="student-learn-sidebar-hero__title mb-1">{{ $module->title }}</h5>
            <p id="js-learn-sidebar-desc"
               class="student-learn-sidebar-hero__desc mb-0 {{ ($module->description ?? '') === '' ? 'd-none' : '' }}">
                {{ Str::limit($module->description ?? '', 80) }}
            </p>
        </div>

        @if($module->module_type != 'question_module' && $module->module_type != 'quiz')
            <div class="student-learn-sidebar-curriculum">
                <h6 class="student-learn-sidebar-curriculum__title">
                    <i class="fe fe-list me-2 text-primary"></i>محتوى الكورس
                </h6>
                <div class="student-learn-sidebar-curriculum__scroll">
                    <div class="accordion student-learn-sidebar-accordion" id="studentLearnSidebarAccordion">
                        @foreach($module->course->sections as $section)
                            @php
                                $sectionModuleCount = $section->modules->count();
                                $hasCurrentModule = $section->modules->contains('id', $module->id);
                            @endphp
                            <div class="accordion-item border-0 border-bottom">
                                <h2 class="accordion-header" id="sidebar-section-heading-{{ $section->id }}">
                                    <button class="accordion-button {{ $hasCurrentModule ? '' : 'collapsed' }} shadow-none"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#sidebar-section-collapse-{{ $section->id }}"
                                            aria-expanded="{{ $hasCurrentModule ? 'true' : 'false' }}"
                                            aria-controls="sidebar-section-collapse-{{ $section->id }}">
                                        <span class="student-learn-sidebar-section-title">
                                            <i class="fe fe-folder me-2 text-primary"></i>{{ $section->title }}
                                        </span>
                                        <span class="badge bg-primary-transparent text-primary student-learn-sidebar-section-count">
                                            {{ $sectionModuleCount }} {{ $sectionModuleCount === 1 ? 'درس' : 'دروس' }}
                                        </span>
                                    </button>
                                </h2>
                                <div id="sidebar-section-collapse-{{ $section->id }}"
                                     class="accordion-collapse collapse {{ $hasCurrentModule ? 'show' : '' }}"
                                     aria-labelledby="sidebar-section-heading-{{ $section->id }}"
                                     data-bs-parent="#studentLearnSidebarAccordion">
                                    <div class="accordion-body pt-1 pb-2 px-2">
                                        @foreach($section->modules as $mod)
                                            @php
                                                $modMeta = $moduleTypeMeta[$mod->module_type] ?? ['icon' => 'fe-file', 'label' => 'محتوى', 'color' => 'secondary'];
                                                $isCurrent = $mod->id == $module->id;
                                                $isModCompleted = in_array($mod->id, $completedModules);
                                            @endphp
                                            <div class="student-learn-sidebar-item"
                                                 data-sidebar-row-module-id="{{ $mod->id }}">
                                                <div class="student-learn-sidebar-lesson {{ $isCurrent ? 'is-active' : '' }} {{ $isModCompleted && ! $isCurrent ? 'is-completed' : '' }}">
                                                    <a href="{{ route('student.learn.module', $mod->id) }}"
                                                       data-learn-sidebar-nav="1"
                                                       data-sidebar-module-id="{{ $mod->id }}"
                                                       class="student-learn-sidebar-lesson__nav">
                                                        <span class="student-learn-sidebar-link__icon bg-{{ $modMeta['color'] }}-transparent text-{{ $modMeta['color'] }}">
                                                            <i class="fe {{ $modMeta['icon'] }}"></i>
                                                        </span>
                                                        <span class="student-learn-sidebar-link__title">{{ $mod->title }}</span>
                                                    </a>
                                                </div>
                                                @if($enrollment)
                                                    <div class="student-learn-sidebar-item__completion">
                                                        <button type="button"
                                                                class="student-learn-sidebar-completion-btn js-sidebar-completion-toggle {{ $isModCompleted ? 'is-done' : 'is-pending' }}"
                                                                data-module-id="{{ $mod->id }}"
                                                                data-is-completed="{{ $isModCompleted ? '1' : '0' }}"
                                                                data-url-complete="{{ route('student.learn.module.mark-complete', $mod->id) }}"
                                                                data-url-incomplete="{{ route('student.learn.module.mark-incomplete', $mod->id) }}"
                                                                title="{{ $isModCompleted ? 'إلغاء الإكمال' : 'تحديد كمكتمل' }}"
                                                                aria-label="{{ $isModCompleted ? 'إلغاء إكمال الدرس' : 'تحديد الدرس كمكتمل' }}">
                                                            @if($isModCompleted)
                                                                <i class="fe fe-check"></i><span>مكتمل</span>
                                                            @else
                                                                <i class="fe fe-circle"></i><span>غير مكتمل</span>
                                                            @endif
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
