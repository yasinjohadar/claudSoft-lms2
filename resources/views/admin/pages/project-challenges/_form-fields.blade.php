@php
    $challenge = $challenge ?? null;
    $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
    $diffIcons = ['easy' => 'fe-smile', 'medium' => 'fe-minus-circle', 'hard' => 'fe-zap', 'expert' => 'fe-award'];
    $typeLabels = [
        'team_project' => 'مشروع فريق',
        'open_challenge' => 'تحدي مفتوح',
        'hackathon' => 'هاكاثون',
        'capstone' => 'مشروع تخرج',
    ];
    $typeIcons = [
        'team_project' => 'fe-users',
        'open_challenge' => 'fe-target',
        'hackathon' => 'fe-code',
        'capstone' => 'fe-award',
    ];
    $approvalLabels = [
        'auto' => 'تلقائي',
        'admin_approval' => 'موافقة المشرف',
        'leader_approval' => 'موافقة القائد',
        'hybrid' => 'هجين',
    ];
    $selectedSkills = old('skill_ids', isset($challenge) ? $challenge->skills->pluck('id')->toArray() : []);
    $selectedTech = old('technology_ids', isset($challenge) ? $challenge->technologies->pluck('id')->toArray() : []);
    $currentType = old('project_type', $challenge?->project_type ?? 'team_project');
    $currentDiff = old('difficulty', $challenge?->difficulty ?? 'medium');
@endphp

<div class="row g-3">
    {{-- العمود الرئيسي --}}
    <div class="col-xl-8">
        {{-- أساسيات --}}
        <div class="pc-form-panel">
            <div class="pc-form-panel__head">
                <span class="pc-form-panel__icon"><i class="fe fe-edit-3"></i></span>
                <div>
                    <h2 class="pc-form-panel__title">معلومات التحدي</h2>
                    <p class="pc-form-panel__sub">العنوان، النوع، ومستوى الصعوبة</p>
                </div>
            </div>

            <div class="mb-4">
                <label class="pc-form-label">العنوان <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control pc-form-input form-control-lg"
                       value="{{ old('title', $challenge?->title ?? '') }}" required
                       placeholder="مثال: بناء منصة تعليمية تفاعلية">
            </div>

            <div class="mb-4">
                <label class="pc-form-label">نوع المشروع <span class="text-danger">*</span></label>
                <div class="row g-2">
                    @foreach($typeLabels as $value => $label)
                        <div class="col-6 col-md-3">
                            <label class="pc-type-card">
                                <input type="radio" name="project_type" value="{{ $value }}"
                                       @checked($currentType === $value) required>
                                <span class="pc-type-card__icon"><i class="fe {{ $typeIcons[$value] ?? 'fe-folder' }}"></i></span>
                                <span class="pc-type-card__name">{{ $label }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="pc-form-label">مستوى الصعوبة <span class="text-danger">*</span></label>
                <div class="pc-pill-group">
                    @foreach($diffLabels as $value => $label)
                        <label class="pc-pill pc-pill--{{ $value }}">
                            <input type="radio" name="difficulty" value="{{ $value }}"
                                   @checked($currentDiff === $value) required>
                            <i class="fe {{ $diffIcons[$value] ?? 'fe-circle' }}"></i>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- المحتوى --}}
        <div class="pc-form-panel">
            <div class="pc-form-panel__head">
                <span class="pc-form-panel__icon"><i class="fe fe-file-text"></i></span>
                <div>
                    <h2 class="pc-form-panel__title">الملخص والوصف</h2>
                    <p class="pc-form-panel__sub">محتوى يظهر للطلاب عند التسجيل</p>
                </div>
            </div>
            <div class="mb-3">
                <label class="pc-form-label">الملخص</label>
                <textarea name="summary" id="project_challenge_summary" class="form-control">{{ old('summary', $challenge?->summary ?? '') }}</textarea>
            </div>
            <div>
                <label class="pc-form-label">الوصف التفصيلي</label>
                <textarea name="description" id="project_challenge_description" class="form-control">{{ old('description', $challenge?->description ?? '') }}</textarea>
            </div>
        </div>

        {{-- المهارات والتقنيات --}}
        <div class="pc-form-panel">
            <div class="pc-form-panel__head">
                <span class="pc-form-panel__icon"><i class="fe fe-layers"></i></span>
                <div>
                    <h2 class="pc-form-panel__title">المهارات والتقنيات</h2>
                    <p class="pc-form-panel__sub">اختر ما ينطبق على هذا التحدي</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="pc-form-label">المهارات</label>
                    <div class="pc-chip-grid">
                        @forelse($skills as $skill)
                            <label class="pc-chip">
                                <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}"
                                       @checked(in_array($skill->id, $selectedSkills))>
                                {{ $skill->name }}
                            </label>
                        @empty
                            <span class="text-muted small">لا توجد مهارات</span>
                        @endforelse
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="pc-form-label">التقنيات</label>
                    <div class="pc-chip-grid">
                        @forelse($technologies as $tech)
                            <label class="pc-chip">
                                <input type="checkbox" name="technology_ids[]" value="{{ $tech->id }}"
                                       @checked(in_array($tech->id, $selectedTech))>
                                {{ $tech->name }}
                            </label>
                        @empty
                            <span class="text-muted small">لا توجد تقنيات</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- الشريط الجانبي --}}
    <div class="col-xl-4">
        <div class="pc-sidebar-sticky">
            <div class="pc-form-panel">
                <div class="pc-form-panel__head">
                    <span class="pc-form-panel__icon"><i class="fe fe-settings"></i></span>
                    <div>
                        <h2 class="pc-form-panel__title">الإعدادات</h2>
                        <p class="pc-form-panel__sub">النقاط، التوقيت، والفرق</p>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="pc-form-label">النقاط</label>
                        <input type="number" name="points_total" class="form-control pc-form-input" min="0"
                               value="{{ old('points_total', $challenge?->points_total ?? 0) }}">
                    </div>
                    <div class="col-6">
                        <label class="pc-form-label">حد العرض %</label>
                        <input type="number" name="showcase_threshold" class="form-control pc-form-input" min="0" max="100"
                               value="{{ old('showcase_threshold', $challenge?->showcase_threshold ?? 100) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="pc-form-label">المدة المتوقعة</label>
                    <input type="text" name="expected_duration" class="form-control pc-form-input"
                           value="{{ old('expected_duration', $challenge?->expected_duration ?? '') }}"
                           placeholder="مثال: 4 أسابيع">
                </div>

                <div class="mb-3">
                    <label class="pc-form-label"><i class="fe fe-calendar me-1 text-primary"></i> تاريخ البداية</label>
                    <input type="datetime-local" name="starts_at" class="form-control pc-form-input"
                           value="{{ old('starts_at', isset($challenge?->starts_at) ? $challenge->starts_at->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="mb-3">
                    <label class="pc-form-label"><i class="fe fe-calendar me-1 text-danger"></i> تاريخ النهاية</label>
                    <input type="datetime-local" name="ends_at" class="form-control pc-form-input"
                           value="{{ old('ends_at', isset($challenge?->ends_at) ? $challenge->ends_at->format('Y-m-d\TH:i') : '') }}">
                </div>

                <hr class="my-3 opacity-25">

                <div class="mb-3">
                    <label class="pc-form-label">الحد الأقصى للفرق</label>
                    <input type="number" name="max_teams" class="form-control pc-form-input" min="1"
                           value="{{ old('max_teams', $challenge?->max_teams ?? '') }}" placeholder="بدون حد">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="pc-form-label">أقل أعضاء <span class="text-danger">*</span></label>
                        <input type="number" name="min_members" class="form-control pc-form-input" min="1" required
                               value="{{ old('min_members', $challenge?->min_members ?? 1) }}">
                    </div>
                    <div class="col-6">
                        <label class="pc-form-label">أقصى أعضاء <span class="text-danger">*</span></label>
                        <input type="number" name="max_members" class="form-control pc-form-input" min="1" required
                               value="{{ old('max_members', $challenge?->max_members ?? 4) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="pc-form-label">وضع الموافقة <span class="text-danger">*</span></label>
                    <select name="team_approval_mode" class="form-select pc-form-input" required>
                        @foreach($approvalLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('team_approval_mode', $challenge?->team_approval_mode ?? 'auto') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="pc-form-label">اللغة</label>
                    <input type="text" name="language" class="form-control pc-form-input" maxlength="5"
                           value="{{ old('language', $challenge?->language ?? 'ar') }}">
                </div>

                <div class="pc-setting-row {{ old('allow_late_join', $challenge?->allow_late_join ?? false) ? 'on' : '' }}" data-switch-row>
                    <div>
                        <p class="pc-setting-row__label">الانضمام المتأخر</p>
                        <p class="pc-setting-row__hint">السماح للطلاب بالانضمام بعد البداية</p>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="allow_late_join" id="allow_late_join" value="1"
                               @checked(old('allow_late_join', $challenge?->allow_late_join ?? false))>
                    </div>
                </div>
                <div class="pc-setting-row {{ old('is_featured', $challenge?->is_featured ?? false) ? 'on' : '' }}" data-switch-row>
                    <div>
                        <p class="pc-setting-row__label">تحدي مميز</p>
                        <p class="pc-setting-row__hint">يظهر في المقدمة للطلاب</p>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                               @checked(old('is_featured', $challenge?->is_featured ?? false))>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
