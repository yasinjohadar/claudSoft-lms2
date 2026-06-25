@extends('admin.layouts.master')

@section('page-title')
    مراحل التحدي — {{ $challenge->title }}
@stop

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/project-challenge.css') }}">
@endpush

@section('content')
    <div class="main-content app-content pc-page">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">مراحل: {{ $challenge->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.project-challenges.index') }}">تحديات المشاريع</a></li>
                            <li class="breadcrumb-item active">المراحل</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('admin.project-challenges.edit', $challenge->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fe fe-arrow-right me-1"></i>العودة للتحدي
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @php
                $linkTypes = config('project_challenges.link_types', []);
                $stagesData = old('stages', $challenge->stages->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'description' => $s->description,
                    'sort_order' => $s->sort_order,
                    'duration_days' => $s->duration_days,
                    'due_at' => $s->due_at?->format('Y-m-d\TH:i'),
                    'max_score' => $s->max_score,
                    'weight' => $s->weight,
                    'is_optional' => $s->is_optional,
                    'unlock_after_previous' => $s->unlock_after_previous,
                    'allowed_link_types' => $s->allowed_link_types ?? [],
                    'status' => $s->status,
                ])->values()->toArray());
                if (empty($stagesData)) {
                    $stagesData = [['title' => '', 'sort_order' => 0, 'max_score' => 100, 'weight' => 1, 'status' => 'open']];
                }
            @endphp

            <form action="{{ route('admin.project-challenges.update-stages', $challenge->id) }}" method="POST" id="stages-form">
                @csrf @method('PUT')
                <div id="stages-container">
                    @foreach($stagesData as $index => $stage)
                        <div class="pc-stage-row" data-stage-index="{{ $index }}">
                            <div class="pc-stage-row__header">
                                <h6 class="mb-0">المرحلة #<span class="stage-number">{{ $index + 1 }}</span></h6>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-stage-btn" @if(count($stagesData) <= 1) disabled @endif>
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </div>
                            @if(!empty($stage['id']))
                                <input type="hidden" name="stages[{{ $index }}][id]" value="{{ $stage['id'] }}">
                            @endif
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                    <input type="text" name="stages[{{ $index }}][title]" class="form-control" value="{{ $stage['title'] ?? '' }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">الترتيب <span class="text-danger">*</span></label>
                                    <input type="number" name="stages[{{ $index }}][sort_order]" class="form-control" min="0" value="{{ $stage['sort_order'] ?? $index }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الوصف</label>
                                <textarea name="stages[{{ $index }}][description]" class="form-control" rows="2">{{ $stage['description'] ?? '' }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">الدرجة القصوى <span class="text-danger">*</span></label>
                                    <input type="number" name="stages[{{ $index }}][max_score]" class="form-control" min="0" step="0.01" value="{{ $stage['max_score'] ?? 100 }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">الوزن <span class="text-danger">*</span></label>
                                    <input type="number" name="stages[{{ $index }}][weight]" class="form-control" min="0" step="0.01" value="{{ $stage['weight'] ?? 1 }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">مدة (أيام)</label>
                                    <input type="number" name="stages[{{ $index }}][duration_days]" class="form-control" min="0" value="{{ $stage['duration_days'] ?? '' }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">الموعد النهائي</label>
                                    <input type="datetime-local" name="stages[{{ $index }}][due_at]" class="form-control" value="{{ $stage['due_at'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="stages[{{ $index }}][is_optional]" value="1" id="optional_{{ $index }}"
                                               @checked(!empty($stage['is_optional']))>
                                        <label class="form-check-label" for="optional_{{ $index }}">مرحلة اختيارية</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="stages[{{ $index }}][unlock_after_previous]" value="1" id="unlock_{{ $index }}"
                                               @checked($stage['unlock_after_previous'] ?? true)>
                                        <label class="form-check-label" for="unlock_{{ $index }}">فتح بعد المرحلة السابقة</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">الحالة</label>
                                    <select name="stages[{{ $index }}][status]" class="form-select">
                                        <option value="locked" @selected(($stage['status'] ?? '') === 'locked')>مقفلة</option>
                                        <option value="open" @selected(($stage['status'] ?? 'open') === 'open')>مفتوحة</option>
                                        <option value="closed" @selected(($stage['status'] ?? '') === 'closed')>مغلقة</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">أنواع الروابط المسموحة</label>
                                <div class="pc-link-types-grid">
                                    @foreach($linkTypes as $typeKey => $typeLabel)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="stages[{{ $index }}][allowed_link_types][]"
                                                   value="{{ $typeKey }}"
                                                   id="link_{{ $index }}_{{ $typeKey }}"
                                                   @checked(in_array($typeKey, $stage['allowed_link_types'] ?? []))>
                                            <label class="form-check-label" for="link_{{ $index }}_{{ $typeKey }}">{{ $typeLabel }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-primary" id="add-stage-btn">
                        <i class="fe fe-plus me-1"></i>إضافة مرحلة
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>حفظ المراحل
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="stage-row-template">
        <div class="pc-stage-row" data-stage-index="__INDEX__">
            <div class="pc-stage-row__header">
                <h6 class="mb-0">المرحلة #<span class="stage-number"></span></h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-stage-btn">
                    <i class="fe fe-trash-2"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">العنوان <span class="text-danger">*</span></label>
                    <input type="text" name="stages[__INDEX__][title]" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">الترتيب <span class="text-danger">*</span></label>
                    <input type="number" name="stages[__INDEX__][sort_order]" class="form-control" min="0" value="__INDEX__" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">الوصف</label>
                <textarea name="stages[__INDEX__][description]" class="form-control" rows="2"></textarea>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">الدرجة القصوى <span class="text-danger">*</span></label>
                    <input type="number" name="stages[__INDEX__][max_score]" class="form-control" min="0" step="0.01" value="100" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">الوزن <span class="text-danger">*</span></label>
                    <input type="number" name="stages[__INDEX__][weight]" class="form-control" min="0" step="0.01" value="1" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">مدة (أيام)</label>
                    <input type="number" name="stages[__INDEX__][duration_days]" class="form-control" min="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">الموعد النهائي</label>
                    <input type="datetime-local" name="stages[__INDEX__][due_at]" class="form-control">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="stages[__INDEX__][is_optional]" value="1" id="optional___INDEX__">
                        <label class="form-check-label" for="optional___INDEX__">مرحلة اختيارية</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="stages[__INDEX__][unlock_after_previous]" value="1" id="unlock___INDEX__" checked>
                        <label class="form-check-label" for="unlock___INDEX__">فتح بعد المرحلة السابقة</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">الحالة</label>
                    <select name="stages[__INDEX__][status]" class="form-select">
                        <option value="locked">مقفلة</option>
                        <option value="open" selected>مفتوحة</option>
                        <option value="closed">مغلقة</option>
                    </select>
                </div>
            </div>
            <div class="mb-0">
                <label class="form-label">أنواع الروابط المسموحة</label>
                <div class="pc-link-types-grid">
                    @foreach($linkTypes as $typeKey => $typeLabel)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="stages[__INDEX__][allowed_link_types][]"
                                   value="{{ $typeKey }}"
                                   id="link___INDEX____{{ $typeKey }}">
                            <label class="form-check-label" for="link___INDEX____{{ $typeKey }}">{{ $typeLabel }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </template>
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('stages-container');
    const template = document.getElementById('stage-row-template');
    const addBtn = document.getElementById('add-stage-btn');

    function reindexStages() {
        container.querySelectorAll('.pc-stage-row').forEach((row, i) => {
            row.dataset.stageIndex = i;
            row.querySelector('.stage-number').textContent = i + 1;
            row.querySelectorAll('[name^="stages["]').forEach(el => {
                el.name = el.name.replace(/stages\[\d+\]/, 'stages[' + i + ']');
            });
            row.querySelectorAll('[id]').forEach(el => {
                if (el.id.includes('optional_') || el.id.includes('unlock_') || el.id.includes('link_')) {
                    el.id = el.id.replace(/_\d+_/, '_' + i + '_').replace(/optional_\d+/, 'optional_' + i).replace(/unlock_\d+/, 'unlock_' + i);
                }
            });
            row.querySelectorAll('label[for]').forEach(el => {
                const forAttr = el.getAttribute('for');
                if (forAttr && (forAttr.includes('optional_') || forAttr.includes('unlock_') || forAttr.includes('link_'))) {
                    el.setAttribute('for', forAttr.replace(/_\d+_/, '_' + i + '_').replace(/optional_\d+/, 'optional_' + i).replace(/unlock_\d+/, 'unlock_' + i));
                }
            });
        });
        const rows = container.querySelectorAll('.pc-stage-row');
        rows.forEach(row => {
            row.querySelector('.remove-stage-btn').disabled = rows.length <= 1;
        });
    }

    addBtn.addEventListener('click', function () {
        const index = container.querySelectorAll('.pc-stage-row').length;
        const html = template.innerHTML.replace(/__INDEX__/g, index);
        container.insertAdjacentHTML('beforeend', html);
        reindexStages();
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-stage-btn')) {
            const rows = container.querySelectorAll('.pc-stage-row');
            if (rows.length <= 1) return;
            e.target.closest('.pc-stage-row').remove();
            reindexStages();
        }
    });
});
</script>
@endpush
