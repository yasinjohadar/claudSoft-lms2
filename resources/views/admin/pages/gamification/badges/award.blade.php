@extends('admin.layouts.master')

@section('page-title')
    منح شارة يدوياً
@stop

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h5 class="mb-0 fw-bold">منح شارة يدوياً</h5>
                            <a class="btn btn-sm btn-secondary" href="{{ $badge ? route('admin.gamification.badges.show', $badge) : route('admin.gamification.badges.index') }}">
                                <i class="fas fa-arrow-right me-1"></i> رجوع
                            </a>
                        </div>

                        <div class="card-body">
                            @php $targetType = old('target_type', 'single'); @endphp
                            <form method="POST" action="{{ route('admin.gamification.badges.award.store') }}" id="badge-award-form">
                                @csrf

                                <div class="mb-4">
                                    <label for="badge_id" class="form-label">الشارة <span class="text-danger">*</span></label>
                                    <select class="form-select" name="badge_id" id="badge_id" required {{ $badge ? 'disabled' : '' }}>
                                        <option value="">اختر الشارة</option>
                                        @foreach($badges as $item)
                                            <option value="{{ $item->id }}" {{ (old('badge_id', $badge?->id) == $item->id) ? 'selected' : '' }}>
                                                {{ $item->icon }} {{ $item->name }} ({{ $item->rarity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($badge)
                                        <input type="hidden" name="badge_id" value="{{ $badge->id }}">
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <label class="form-label d-block">نوع الاستهداف <span class="text-danger">*</span></label>
                                    @foreach([
                                        'single' => 'طالب واحد',
                                        'multiple' => 'عدة طلاب',
                                        'group' => 'مجموعة كاملة',
                                        'course' => 'كورس كامل (كل المسجّلين)',
                                        'course_group' => 'كورس + مجموعة',
                                    ] as $value => $label)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input target-type-radio" type="radio" name="target_type" id="target_{{ $value }}" value="{{ $value }}" {{ $targetType === $value ? 'checked' : '' }}>
                                            <label class="form-check-label" for="target_{{ $value }}">{{ $label }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'single' ? '' : 'd-none' }}" data-target="single">
                                    <label for="user_id" class="form-label">الطالب</label>
                                    <select name="user_id" id="user_id" class="form-select student-search-select" style="width:100%"></select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'multiple' ? '' : 'd-none' }}" data-target="multiple">
                                    <label for="user_ids" class="form-label">الطلاب</label>
                                    <select name="user_ids[]" id="user_ids" class="form-select student-search-select-multiple" multiple style="width:100%"></select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'group' ? '' : 'd-none' }}" data-target="group">
                                    <label for="group_id_only" class="form-label">المجموعة</label>
                                    <select name="group_id" id="group_id_only" class="form-select">
                                        <option value="">اختر المجموعة</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'course' ? '' : 'd-none' }}" data-target="course">
                                    <label for="course_id_only" class="form-label">الكورس</label>
                                    <select name="course_id" id="course_id_only" class="form-select">
                                        <option value="">اختر الكورس</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="target-panel mb-3 {{ $targetType === 'course_group' ? '' : 'd-none' }}" data-target="course_group">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="course_id" class="form-label">الكورس</label>
                                            <select name="course_id" id="course_id" class="form-select">
                                                <option value="">اختر الكورس</option>
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="group_id" class="form-label">المجموعة</label>
                                            <select name="group_id" id="group_id" class="form-select">
                                                <option value="">اختر المجموعة</option>
                                                @foreach($groups as $group)
                                                    <option
                                                        value="{{ $group->id }}"
                                                        data-course-ids="{{ $group->courses->pluck('id')->implode(',') }}"
                                                        {{ old('group_id') == $group->id ? 'selected' : '' }}
                                                    >{{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                            <div id="group-empty-hint" class="form-text text-warning d-none">
                                                لا توجد مجموعات مرتبطة بالكورس المحدد.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="reason" class="form-label">سبب المنح (اختياري)</label>
                                    <textarea class="form-control" name="reason" id="reason" rows="2" placeholder="مثال: تقديراً للتميز في المشروع">{{ old('reason') }}</textarea>
                                </div>

                                <div class="alert alert-info d-none" id="award-preview-box">
                                    <strong>معاينة:</strong>
                                    <span id="award-preview-text">—</span>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-primary" id="preview-btn">
                                        <i class="fas fa-eye me-1"></i> معاينة
                                    </button>
                                    <button type="submit" class="btn btn-success" id="submit-btn">
                                        <i class="fas fa-award me-1"></i> تأكيد المنح
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            const previewUrl = @json(route('admin.gamification.badges.award.preview'));
            const studentsUrl = @json(route('admin.gamification.badges.award.students'));
            const form = document.getElementById('badge-award-form');
            const previewBox = document.getElementById('award-preview-box');
            const previewText = document.getElementById('award-preview-text');
            const courseSelect = document.getElementById('course_id');
            const groupSelect = document.getElementById('group_id');
            const groupEmptyHint = document.getElementById('group-empty-hint');

            const getTargetType = () => document.querySelector('input[name="target_type"]:checked')?.value || 'single';

            const togglePanels = () => {
                const type = getTargetType();
                document.querySelectorAll('.target-panel').forEach((panel) => {
                    panel.classList.toggle('d-none', panel.dataset.target !== type);
                });
                disableInactiveFields(type);
            };

            const disableInactiveFields = (activeType) => {
                const map = {
                    single: ['user_id'],
                    multiple: ['user_ids'],
                    group: ['group_id_only'],
                    course: ['course_id_only'],
                    course_group: ['course_id', 'group_id'],
                };

                document.querySelectorAll('.target-panel input, .target-panel select, .target-panel textarea').forEach((el) => {
                    el.disabled = true;
                });

                (map[activeType] || []).forEach((id) => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.disabled = false;
                    }
                });

                if (activeType === 'group') {
                    document.getElementById('group_id_only').disabled = false;
                }
                if (activeType === 'course') {
                    document.getElementById('course_id_only').disabled = false;
                }
            };

            const applyGroupFilter = () => {
                if (!courseSelect || !groupSelect) return;

                const selectedCourseId = courseSelect.value;
                let hasMatching = false;

                Array.from(groupSelect.options).forEach((option, index) => {
                    if (index === 0) return;

                    const courseIds = (option.dataset.courseIds || '').split(',').map((id) => id.trim()).filter(Boolean);
                    const isMatch = selectedCourseId && courseIds.includes(selectedCourseId);
                    option.disabled = !isMatch;
                    option.hidden = !isMatch;

                    if (isMatch) hasMatching = true;
                    else if (option.selected) option.selected = false;
                });

                if (!selectedCourseId) {
                    groupSelect.value = '';
                    groupEmptyHint?.classList.add('d-none');
                } else if (!hasMatching) {
                    groupSelect.value = '';
                    groupEmptyHint?.classList.remove('d-none');
                } else {
                    groupEmptyHint?.classList.add('d-none');
                }
            };

            const buildPreviewParams = () => {
                const params = new URLSearchParams();
                const badgeId = form.querySelector('[name="badge_id"]')?.value;
                const targetType = getTargetType();

                params.set('badge_id', badgeId || '');
                params.set('target_type', targetType);

                if (targetType === 'single') {
                    params.set('user_id', document.getElementById('user_id')?.value || '');
                }
                if (targetType === 'multiple') {
                    const selected = $('#user_ids').val() || [];
                    selected.forEach((id) => params.append('user_ids[]', id));
                }
                if (targetType === 'group') {
                    params.set('group_id', document.getElementById('group_id_only')?.value || '');
                }
                if (targetType === 'course') {
                    params.set('course_id', document.getElementById('course_id_only')?.value || '');
                }
                if (targetType === 'course_group') {
                    params.set('course_id', courseSelect?.value || '');
                    params.set('group_id', groupSelect?.value || '');
                }

                return params;
            };

            const runPreview = async () => {
                try {
                    const response = await fetch(previewUrl + '?' + buildPreviewParams().toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        previewBox.classList.remove('d-none');
                        previewText.textContent = 'يرجى إكمال الحقول المطلوبة أولاً.';
                        return;
                    }

                    previewBox.classList.remove('d-none');
                    previewText.textContent = `إجمالي المستهدفين: ${data.total} | سيُمنح: ${data.will_award} | يملكونها مسبقاً: ${data.already_have}`;
                } catch (e) {
                    previewBox.classList.remove('d-none');
                    previewText.textContent = 'تعذر تحميل المعاينة.';
                }
            };

            document.querySelectorAll('.target-type-radio').forEach((radio) => {
                radio.addEventListener('change', () => {
                    togglePanels();
                    previewBox.classList.add('d-none');
                });
            });

            courseSelect?.addEventListener('change', () => {
                applyGroupFilter();
                previewBox.classList.add('d-none');
            });

            document.getElementById('preview-btn')?.addEventListener('click', runPreview);

            form?.addEventListener('submit', (e) => {
                if (!confirm('هل أنت متأكد من منح هذه الشارة للمستهدفين المحددين؟')) {
                    e.preventDefault();
                }
            });

            jQuery(function ($) {
                const select2Ajax = {
                    url: studentsUrl,
                    dataType: 'json',
                    delay: 300,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    data: (params) => ({ search: params.term }),
                    processResults: (data) => data,
                    cache: true,
                };

                $('#user_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'ابحث عن طالب…',
                    allowClear: true,
                    dir: 'rtl',
                    minimumInputLength: 2,
                    ajax: select2Ajax,
                });

                $('#user_ids').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'ابحث واختر عدة طلاب…',
                    allowClear: true,
                    dir: 'rtl',
                    minimumInputLength: 2,
                    ajax: select2Ajax,
                });
            });

            togglePanels();
            applyGroupFilter();
        })();
    </script>
@endpush
