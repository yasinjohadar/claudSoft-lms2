@php
    $moduleTypeMeta = [
        'lesson' => ['icon' => 'fe-book-open', 'label' => 'درس', 'color' => 'primary'],
        'video' => ['icon' => 'fe-play-circle', 'label' => 'فيديو', 'color' => 'danger'],
        'quiz' => ['icon' => 'fe-help-circle', 'label' => 'اختبار', 'color' => 'success'],
        'assignment' => ['icon' => 'fe-clipboard', 'label' => 'واجب', 'color' => 'warning'],
        'question_module' => ['icon' => 'fe-file-text', 'label' => 'اختبار', 'color' => 'info'],
    ];
@endphp

<div class="card custom-card group-show-members-card dashboard-fade-in">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
        <h6 class="group-show-members-card__title mb-0">
            <i class="fe fe-list me-2 text-primary"></i>محتوى الكورس
        </h6>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @include('student.components.sidebar-layout-toggle', ['showLabel' => 'القائمة'])
            <span class="group-show-members-card__count">
                {{ $stats['total_sections'] ?? 0 }} قسم • {{ $stats['total_modules'] ?? 0 }} درس
            </span>
        </div>
    </div>
    <div class="card-body pt-3 px-0 pb-0">
        @if($course->sections->count() > 0)
            <div class="accordion student-course-curriculum-accordion" id="courseCurriculumAccordion">
                @foreach($course->sections->where('is_visible', true) as $index => $section)
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="heading-{{ $section->id }}">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse-{{ $section->id }}"
                                    aria-expanded="false"
                                    aria-controls="collapse-{{ $section->id }}">
                                <span class="student-learn-sidebar-section-title student-course-section-title">
                                    <span class="student-learn-sidebar-section-icon" aria-hidden="true">
                                        <i class="ri-folder-3-fill student-learn-sidebar-section-icon__closed"></i>
                                        <i class="ri-folder-open-fill student-learn-sidebar-section-icon__open"></i>
                                    </span>
                                    <span class="student-learn-sidebar-section-title__text">{{ $section->title }}</span>
                                </span>
                                <span class="badge bg-primary-transparent text-primary student-learn-sidebar-section-count student-course-section-count">
                                    {{ $section->modules->count() }} {{ $section->modules->count() == 1 ? 'درس' : 'دروس' }}
                                </span>
                            </button>
                        </h2>
                        <div id="collapse-{{ $section->id }}"
                             class="accordion-collapse collapse"
                             aria-labelledby="heading-{{ $section->id }}"
                             data-bs-parent="#courseCurriculumAccordion">
                            <div class="accordion-body pt-2 pb-3">
                                @if($section->description)
                                    <p class="text-muted fs-12 mb-3 px-1">{{ $section->description }}</p>
                                @endif
                                @forelse($section->modules->where('is_visible', true) as $module)
                                    @php
                                        $meta = $moduleTypeMeta[$module->module_type] ?? ['icon' => 'fe-file', 'label' => 'محتوى', 'color' => 'secondary'];
                                        $moduleUrl = route('student.learn.module', ['moduleId' => $module->id]);
                                        $isCompleted = $enrollment && isset($completedModules) && in_array($module->id, $completedModules);
                                    @endphp
                                    <a href="{{ $moduleUrl }}"
                                       class="student-course-module-link student-course-module-row">
                                        <span class="student-course-module-row__icon bg-{{ $meta['color'] }}-transparent text-{{ $meta['color'] }}">
                                            <i class="fe {{ $meta['icon'] }}"></i>
                                        </span>
                                        <span class="student-course-module-row__body min-w-0">
                                            <span class="student-course-module-row__title">{{ $module->title }}</span>
                                            <span class="student-course-module-row__meta">
                                                <span class="badge bg-light text-default">{{ $meta['label'] }}</span>
                                                @if($module->module_type === 'question_module' && $module->modulable)
                                                    <span class="badge bg-info-transparent text-info">{{ $module->modulable->questions->count() }} سؤال</span>
                                                @endif
                                                @if($module->duration)
                                                    <span><i class="fe fe-clock me-1"></i>{{ $module->duration }} د</span>
                                                @endif
                                            </span>
                                        </span>
                                        <span class="student-course-module-row__status">
                                            @if($module->is_preview)
                                                <span class="badge bg-info-transparent text-info">معاينة</span>
                                            @elseif($enrollment)
                                                @if($isCompleted)
                                                    <span class="badge bg-success-transparent text-success"><i class="fe fe-check me-1"></i>مكتمل</span>
                                                @else
                                                    <span class="badge bg-secondary-transparent text-secondary">غير مكتمل</span>
                                                @endif
                                            @else
                                                <i class="fe fe-lock text-muted"></i>
                                            @endif
                                        </span>
                                    </a>
                                @empty
                                    <div class="group-show-empty py-3">
                                        <p class="text-muted fs-13 mb-0">لا توجد دروس في هذا القسم</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="group-show-empty py-5">
                <i class="fe fe-book-open group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لم يُضف محتوى بعد</h5>
                <p class="group-show-empty__desc mb-0">سيتم إضافة المحتوى قريباً.</p>
            </div>
        @endif
    </div>
</div>
