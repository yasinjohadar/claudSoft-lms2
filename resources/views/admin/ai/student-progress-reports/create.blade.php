@extends('admin.layouts.master')

@section('page-title')
    تقارير الدراسة (AI)
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إنشاء تقارير الدراسة (مجموعة / كورس)</h5>
                @if(!empty($useLaravelAi))
                    <p class="mb-0 text-muted small"><span class="badge bg-info text-dark">Laravel AI SDK</span> — تقرير الدراسة يجمع الكورس والاختبارات والتقدم؛ يُفضّل موديل بقدرة «تقارير تقدم الطلاب» أو «نص عام».</p>
                @else
                    <p class="mb-0 text-danger small">لا يوجد مسار Laravel AI مفعّل لهذه الميزة. فعّل <code>AI_APPLICATION_ENGINE=laravel_ai</code> أو <code>AI_REPORTS_ENGINE=laravel_ai</code> أو أضف موديلاً نشطاً في Laravel AI SDK.</p>
                @endif
            </div>
            <div>
                <a href="{{ route('admin.ai.student-progress-reports.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        @include('admin.components.alerts')

        <div class="row">
            <div class="col-lg-10">
                @include('admin.ai.student-progress-reports._queue-worker-card')

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header">معاينة (طالب واحد)</div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.ai.student-progress-reports.preview') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                    <select name="course_id" id="preview_course_id" class="form-select" required>
                                        <option value="">—</option>
                                        @foreach($courses as $c)
                                            <option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>{{ $c->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الطالب <span class="text-danger">*</span></label>
                                    <select name="student_id" id="preview_student_id" class="form-select" required disabled>
                                        <option value="">اختر الكورس أولاً</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">استراتيجية المحاولة</label>
                                    <select name="attempt_strategy" class="form-select">
                                        <option value="best" @selected(old('attempt_strategy', 'best') === 'best')>أفضل نتيجة لكل اختبار</option>
                                        <option value="latest" @selected(old('attempt_strategy') === 'latest')>آخر محاولة</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">من تاريخ (اختياري)</label>
                                    <input type="date" name="since" class="form-control" value="{{ old('since') }}">
                                </div>
                                @if(!empty($useLaravelAi))
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">موديل Laravel AI</label>
                                    <select name="laravel_ai_model_id" class="form-select">
                                        <option value="">افتراضي (قدرة التقارير ثم النص العام)</option>
                                        @foreach($laravelAiModels as $m)
                                            <option value="{{ $m->id }}" @selected((string) old('laravel_ai_model_id') === (string) $m->id)>{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-outline-primary" @if(empty($useLaravelAi)) disabled @endif>معاينة تقرير الدراسة</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header">جدولة تقارير الدراسة (على مستوى المجموعات)</div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.ai.student-progress-reports.dispatch') }}" id="batch_dispatch_form" onsubmit="return confirm('سيتم جدولة تقرير دراسة لكل طالب مؤهل ضمن نطاق المجموعة/المجموعات. المتابعة؟');">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                <select name="course_id" id="batch_course_id" class="form-select" required>
                                    <option value="">—</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>{{ $c->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-block">نطاق الإرسال <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="batch_scope" id="batch_scope_single" value="single_group" @checked(old('batch_scope', 'single_group') === 'single_group')>
                                    <label class="form-check-label" for="batch_scope_single">مجموعة واحدة</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="batch_scope" id="batch_scope_all" value="all_groups_in_course" @checked(old('batch_scope') === 'all_groups_in_course')>
                                    <label class="form-check-label" for="batch_scope_all">كل مجموعات هذا الكورس</label>
                                </div>
                            </div>
                            <div class="mb-3" id="batch_single_group_wrap">
                                <label class="form-label">المجموعة <span class="text-danger">*</span></label>
                                <select name="course_group_id" id="batch_group_id" class="form-select">
                                    <option value="">اختر الكورس أولاً</option>
                                </select>
                                <small class="text-muted">يُرسل فقط لطلاب مسجّلين في الكورس <strong>و</strong> أعضاء في المجموعة.</small>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">استراتيجية المحاولة</label>
                                    <select name="attempt_strategy" class="form-select">
                                        <option value="best" @selected(old('attempt_strategy', 'best') === 'best')>أفضل نتيجة</option>
                                        <option value="latest" @selected(old('attempt_strategy') === 'latest')>آخر محاولة</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">من تاريخ (اختياري)</label>
                                    <input type="date" name="since" class="form-control" value="{{ old('since') }}">
                                </div>
                                @if(!empty($useLaravelAi))
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">موديل Laravel AI</label>
                                    <select name="laravel_ai_model_id" class="form-select">
                                        <option value="">افتراضي</option>
                                        @foreach($laravelAiModels as $m)
                                            <option value="{{ $m->id }}" @selected((string) old('laravel_ai_model_id') === (string) $m->id)>{{ $m->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary" @if(empty($useLaravelAi)) disabled @endif>جدولة التقارير</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('preview_course_id');
    const studentSelect = document.getElementById('preview_student_id');
    const enrolledUrl = @json(route('admin.ai.student-progress-reports.enrolled-students'));

    function loadStudents(courseId) {
        if (!courseId) {
            studentSelect.innerHTML = '<option value="">اختر الكورس أولاً</option>';
            studentSelect.disabled = true;
            return;
        }
        studentSelect.disabled = true;
        studentSelect.innerHTML = '<option value="">جاري التحميل...</option>';
        fetch(enrolledUrl + '?course_id=' + encodeURIComponent(courseId), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                studentSelect.innerHTML = '<option value="">اختر الطالب</option>';
                (data || []).forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.id;
                    o.textContent = s.name + (s.email ? ' — ' + s.email : '');
                    studentSelect.appendChild(o);
                });
                studentSelect.disabled = false;
            })
            .catch(() => {
                studentSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
            });
    }

    courseSelect.addEventListener('change', function() { loadStudents(this.value); });
    if (courseSelect.value) {
        loadStudents(courseSelect.value);
    }

    const batchCourse = document.getElementById('batch_course_id');
    const batchGroup = document.getElementById('batch_group_id');
    const batchScopeSingle = document.getElementById('batch_scope_single');
    const batchScopeAll = document.getElementById('batch_scope_all');
    const batchSingleWrap = document.getElementById('batch_single_group_wrap');
    const groupsUrl = @json(route('admin.ai.student-progress-reports.course-groups'));

    function syncBatchScope() {
        const single = batchScopeSingle && batchScopeSingle.checked;
        if (batchGroup) {
            batchGroup.disabled = !single;
            batchGroup.required = single;
            if (!single) {
                batchGroup.value = '';
            }
        }
        if (batchSingleWrap) {
            batchSingleWrap.style.opacity = single ? '1' : '0.6';
        }
    }

    function loadBatchGroups(courseId) {
        if (!batchGroup) return;
        if (!courseId) {
            batchGroup.innerHTML = '<option value="">اختر الكورس أولاً</option>';
            return;
        }
        batchGroup.disabled = true;
        batchGroup.innerHTML = '<option value="">جاري التحميل...</option>';
        fetch(groupsUrl + '?course_id=' + encodeURIComponent(courseId), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                batchGroup.innerHTML = '<option value="">— اختر المجموعة —</option>';
                (data || []).forEach(g => {
                    const o = document.createElement('option');
                    o.value = g.id;
                    o.textContent = g.name;
                    batchGroup.appendChild(o);
                });
                const oldGid = @json(old('course_group_id'));
                if (oldGid) {
                    batchGroup.value = String(oldGid);
                }
                syncBatchScope();
                if (batchScopeSingle && batchScopeSingle.checked) {
                    batchGroup.disabled = false;
                }
            })
            .catch(() => {
                batchGroup.innerHTML = '<option value="">خطأ في التحميل</option>';
            });
    }

    if (batchCourse) {
        batchCourse.addEventListener('change', function() { loadBatchGroups(this.value); });
        if (batchCourse.value) {
            loadBatchGroups(batchCourse.value);
        }
    }
    if (batchScopeSingle) batchScopeSingle.addEventListener('change', syncBatchScope);
    if (batchScopeAll) batchScopeAll.addEventListener('change', syncBatchScope);
    syncBatchScope();
});
</script>
@endpush
@stop
