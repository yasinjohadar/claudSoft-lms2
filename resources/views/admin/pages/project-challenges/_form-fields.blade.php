@php
    $diffLabels = ['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب', 'expert' => 'خبير'];
    $typeLabels = [
        'team_project' => 'مشروع فريق',
        'open_challenge' => 'تحدي مفتوح',
        'hackathon' => 'هاكاثون',
        'capstone' => 'مشروع تخرج',
    ];
    $approvalLabels = [
        'auto' => 'تلقائي',
        'admin_approval' => 'موافقة المشرف',
        'leader_approval' => 'موافقة القائد',
        'hybrid' => 'هجين',
    ];
    $selectedSkills = old('skill_ids', isset($challenge) ? $challenge->skills->pluck('id')->toArray() : []);
    $selectedTech = old('technology_ids', isset($challenge) ? $challenge->technologies->pluck('id')->toArray() : []);
@endphp

<div class="row">
    <div class="col-xl-8">
        <div class="card custom-card">
            <div class="card-header"><div class="card-title">معلومات التحدي</div></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">العنوان <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $challenge->title ?? '') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">الملخص</label>
                    <textarea name="summary" id="project_challenge_summary" class="form-control">{{ old('summary', $challenge->summary ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">الوصف</label>
                    <textarea name="description" id="project_challenge_description" class="form-control">{{ old('description', $challenge->description ?? '') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">نوع المشروع <span class="text-danger">*</span></label>
                        <select name="project_type" class="form-select" required>
                            @foreach($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('project_type', $challenge->project_type ?? 'team_project') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">الصعوبة <span class="text-danger">*</span></label>
                        <select name="difficulty" class="form-select" required>
                            @foreach($diffLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('difficulty', $challenge->difficulty ?? 'medium') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">المهارات</label>
                    <select name="skill_ids[]" class="form-select" multiple size="5">
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}" @selected(in_array($skill->id, $selectedSkills))>{{ $skill->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">اضغط Ctrl للاختيار المتعدد</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">التقنيات</label>
                    <select name="technology_ids[]" class="form-select" multiple size="5">
                        @foreach($technologies as $tech)
                            <option value="{{ $tech->id }}" @selected(in_array($tech->id, $selectedTech))>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card custom-card">
            <div class="card-header"><div class="card-title">الإعدادات</div></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">النقاط الإجمالية</label>
                    <input type="number" name="points_total" class="form-control" min="0" value="{{ old('points_total', $challenge->points_total ?? 0) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">المدة المتوقعة</label>
                    <input type="text" name="expected_duration" class="form-control" value="{{ old('expected_duration', $challenge->expected_duration ?? '') }}" placeholder="مثال: 4 أسابيع">
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ البداية</label>
                    <input type="datetime-local" name="starts_at" class="form-control"
                           value="{{ old('starts_at', isset($challenge?->starts_at) ? $challenge->starts_at->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ النهاية</label>
                    <input type="datetime-local" name="ends_at" class="form-control"
                           value="{{ old('ends_at', isset($challenge?->ends_at) ? $challenge->ends_at->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">الحد الأقصى للفرق</label>
                    <input type="number" name="max_teams" class="form-control" min="1" value="{{ old('max_teams', $challenge->max_teams ?? '') }}">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">أقل أعضاء <span class="text-danger">*</span></label>
                        <input type="number" name="min_members" class="form-control" min="1" required
                               value="{{ old('min_members', $challenge->min_members ?? 1) }}">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">أقصى أعضاء <span class="text-danger">*</span></label>
                        <input type="number" name="max_members" class="form-control" min="1" required
                               value="{{ old('max_members', $challenge->max_members ?? 4) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">وضع الموافقة على الفريق <span class="text-danger">*</span></label>
                    <select name="team_approval_mode" class="form-select" required>
                        @foreach($approvalLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('team_approval_mode', $challenge->team_approval_mode ?? 'auto') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">حد نشر العرض (%)</label>
                    <input type="number" name="showcase_threshold" class="form-control" min="0" max="100"
                           value="{{ old('showcase_threshold', $challenge->showcase_threshold ?? 100) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">اللغة</label>
                    <input type="text" name="language" class="form-control" maxlength="5" value="{{ old('language', $challenge->language ?? 'ar') }}">
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="allow_late_join" id="allow_late_join" value="1"
                           @checked(old('allow_late_join', $challenge->allow_late_join ?? false))>
                    <label class="form-check-label" for="allow_late_join">السماح بالانضمام المتأخر</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                           @checked(old('is_featured', $challenge->is_featured ?? false))>
                    <label class="form-check-label" for="is_featured">مميز</label>
                </div>
            </div>
        </div>
    </div>
</div>
