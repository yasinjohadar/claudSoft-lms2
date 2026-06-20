@if($enrollment)
    <div class="card custom-card student-learn-completion-card dashboard-fade-in"
         id="module-completion-card"
         data-module-id="{{ $module->id }}"
         data-url-complete="{{ route('student.learn.module.mark-complete', $module->id) }}"
         data-url-incomplete="{{ route('student.learn.module.mark-incomplete', $module->id) }}">
        <div class="card-body">
            <div class="student-learn-completion-card__inner">
                <div class="student-learn-completion-card__info">
                    <span class="student-learn-completion-card__icon-wrap">
                        <i class="fe fe-award"></i>
                    </span>
                    <div class="min-w-0">
                        <h6 class="student-learn-completion-title mb-1">هل أكملت هذا الدرس؟</h6>
                        <p class="student-learn-completion-sub mb-0">قم بتحديده كمكتمل لمتابعة تقدمك في الكورس</p>
                    </div>
                    <div id="module-completion-badge"
                         class="student-learn-completion-card__done-badge {{ $isCompleted ? '' : 'd-none' }}">
                        <i class="fe fe-check-circle"></i>
                        <span>مكتمل</span>
                    </div>
                </div>
                <div id="module-completion-actions" class="student-learn-completion-card__actions">
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm rounded-pill js-module-completion-btn student-learn-completion-btn {{ $isCompleted ? '' : 'd-none' }}"
                            data-action="incomplete"
                            data-url="{{ route('student.learn.module.mark-incomplete', $module->id) }}">
                        <i class="fe fe-x me-1"></i>إلغاء الإكمال
                    </button>
                    <button type="button"
                            class="btn btn-primary btn-sm rounded-pill js-module-completion-btn student-learn-completion-btn {{ $isCompleted ? 'd-none' : '' }}"
                            data-action="complete"
                            data-url="{{ route('student.learn.module.mark-complete', $module->id) }}">
                        <i class="fe fe-check me-1"></i>تحديد كمكتمل
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
