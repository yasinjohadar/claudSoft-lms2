@extends('admin.layouts.master')

@section('page-title')
توليد كورس واجهة بالذكاء الاصطناعي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">توليد كورس للواجهة الأمامية بالذكاء الاصطناعي</h4>
                <p class="mb-0 text-muted">اختر التصنيف والمدرب والمستوى، اكتب موضوع الكورس، ثم ولّد المحتوى والمحاور ومراجعة الحقول قبل الحفظ</p>
            </div>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <a href="{{ route('admin.frontend-courses.index') }}" class="btn btn-secondary">قائمة الكورسات</a>
                <a href="{{ route('admin.frontend-courses.create') }}" class="btn btn-outline-primary">إنشاء يدوي</a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card custom-card mb-4">
            <div class="card-header bg-primary text-white">
                <div class="card-title mb-0"><i class="fas fa-robot me-2"></i>إعدادات التوليد</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">موضوع أو فكرة الكورس <span class="text-danger">*</span></label>
                        <input type="text" id="ai_topic" class="form-control" placeholder="مثال: دورة Laravel للمبتدئين، أساسيات التصميم UX">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">موديل AI</label>
                        <select id="ai_model_id" class="form-select">
                            <option value="">الافتراضي</option>
                            @foreach($models as $model)
                            <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">أسلوب الكتابة</label>
                        <select id="ai_tone" class="form-select">
                            <option value="professional" selected>احترافي</option>
                            <option value="friendly">ودود</option>
                            <option value="technical">تقني</option>
                            <option value="casual">عادي</option>
                            <option value="formal">رسمي</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">لغة النصوص</label>
                        <select id="ai_language" class="form-select">
                            <option value="ar" selected>العربية</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">التصنيف <span class="text-danger">*</span></label>
                        <select id="ai_category_id" class="form-select" required>
                            <option value="">— اختر —</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">المدرب <span class="text-danger">*</span></label>
                        <select id="ai_instructor_id" class="form-select" required>
                            <option value="">— اختر —</option>
                            @foreach($instructors as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">مستوى الكورس <span class="text-danger">*</span></label>
                        <select id="ai_level" class="form-select">
                            <option value="beginner" selected>مبتدئ</option>
                            <option value="intermediate">متوسط</option>
                            <option value="advanced">متقدم</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">عدد المحاور (تقريباً)</label>
                        <input type="number" id="ai_target_sections" class="form-control" value="4" min="2" max="12">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">دروس/محور (تقريباً)</label>
                        <input type="number" id="ai_lessons_hint" class="form-control" value="3" min="1" max="8">
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ai_generate_advanced_seo" checked>
                            <label class="form-check-label" for="ai_generate_advanced_seo">توليد SEO متقدم (OG، Twitter، …)</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-success" id="aiGenerateBtn">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="aiGenSpinner" role="status"></span>
                            <span id="aiGenBtnText">توليد المحتوى بالذكاء الاصطناعي</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.frontend-courses.store') }}" method="POST" enctype="multipart/form-data" id="aiFrontendCourseForm">
            @csrf
            @include('admin.frontend-courses._form', ['showAiExtras' => true])
        </form>
    </div>
</div>

<script>
(function () {
    const generateUrl = @json(route('admin.frontend-courses.ai.generate'));
    const csrf = @json(csrf_token());

    function setVal(id, v) {
        const el = document.getElementById(id);
        if (el) el.value = v != null ? v : '';
    }

    function syncHiddenFromAiPanel() {
        const cat = document.getElementById('ai_category_id');
        const ins = document.getElementById('ai_instructor_id');
        const formCat = document.querySelector('#aiFrontendCourseForm select[name="category_id"]');
        const formIns = document.querySelector('#aiFrontendCourseForm select[name="instructor_id"]');
        const formLev = document.querySelector('#aiFrontendCourseForm select[name="level"]');
        if (formCat && cat && cat.value) formCat.value = cat.value;
        if (formIns && ins && ins.value) formIns.value = ins.value;
        if (formLev) formLev.value = document.getElementById('ai_level').value;
    }

    function clearWhatYouLearn() {
        const c = document.getElementById('what-you-learn-inputs');
        if (c) c.innerHTML = '';
    }

    function fillWhatYouLearn(items) {
        clearWhatYouLearn();
        if (!Array.isArray(items)) return;
        items.forEach(function (t) {
            if (typeof addWhatYouLearnRow === 'function') addWhatYouLearnRow(t);
        });
    }

    function fillSectionsFromAi(sections) {
        const container = document.getElementById('sections-container');
        if (!container || typeof addSection !== 'function') return;
        container.innerHTML = '';
        if (typeof sectionIndex !== 'undefined') sectionIndex = 0;
        if (!Array.isArray(sections) || sections.length === 0) return;

        sections.forEach(function (sec) {
            addSection();
            const items = container.querySelectorAll('.section-item');
            const sectionEl = items[items.length - 1];
            const titleInp = sectionEl.querySelector('input[name*="[title]"]');
            if (titleInp) titleInp.value = sec.title || '';
            const descTa = sectionEl.querySelector('textarea[name*="[description]"]');
            if (descTa) descTa.value = sec.description || '';

            (sec.lessons || []).forEach(function (les) {
                const addBtn = sectionEl.querySelector('.lessons-container .btn-success');
                if (addBtn && typeof addLesson === 'function') addLesson(addBtn);
                const lessonEls = sectionEl.querySelectorAll('.lesson-item');
                const lessonEl = lessonEls[lessonEls.length - 1];
                if (!lessonEl) return;
                const lt = lessonEl.querySelector('input[name$="[title]"]');
                if (lt) lt.value = les.title || '';
                const typeSel = lessonEl.querySelector('select[name*="[type]"]');
                if (typeSel && les.type) typeSel.value = les.type;
                const dur = lessonEl.querySelector('input[name*="[duration]"]');
                if (dur && les.duration != null) dur.value = les.duration;
                const ld = lessonEl.querySelector('textarea[name*="[description]"]');
                if (ld) ld.value = les.description || '';
            });
        });
        if (typeof renumberSections === 'function') renumberSections();
    }

    document.getElementById('ai_category_id').addEventListener('change', syncHiddenFromAiPanel);
    document.getElementById('ai_instructor_id').addEventListener('change', syncHiddenFromAiPanel);
    document.getElementById('ai_level').addEventListener('change', syncHiddenFromAiPanel);

    document.getElementById('aiGenerateBtn').addEventListener('click', function () {
        const topic = document.getElementById('ai_topic').value.trim();
        const categoryId = document.getElementById('ai_category_id').value;
        const instructorId = document.getElementById('ai_instructor_id').value;
        if (!topic) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'أدخل موضوع الكورس' });
            else alert('أدخل موضوع الكورس');
            return;
        }
        if (!categoryId || !instructorId) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'اختر التصنيف والمدرب' });
            else alert('اختر التصنيف والمدرب');
            return;
        }

        syncHiddenFromAiPanel();

        const btn = this;
        const sp = document.getElementById('aiGenSpinner');
        const tx = document.getElementById('aiGenBtnText');
        btn.disabled = true;
        sp.classList.remove('d-none');
        tx.textContent = 'جاري التوليد...';

        fetch(generateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                topic: topic,
                ai_model_id: document.getElementById('ai_model_id').value || null,
                tone: document.getElementById('ai_tone').value,
                language: document.getElementById('ai_language').value,
                category_id: categoryId,
                instructor_id: instructorId,
                level: document.getElementById('ai_level').value,
                target_sections: parseInt(document.getElementById('ai_target_sections').value, 10) || null,
                lessons_per_section_hint: parseInt(document.getElementById('ai_lessons_hint').value, 10) || null,
                generate_advanced_seo: document.getElementById('ai_generate_advanced_seo').checked,
                _token: csrf
            })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            if (!res.body.success || !res.body.data) {
                const msg = res.body.message || 'فشل التوليد';
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                else alert(msg);
                return;
            }
            const d = res.body.data;
            const setName = function (name, v) {
                const el = document.querySelector('[name="' + name + '"]');
                if (el) el.value = v != null ? v : '';
            };
            setName('title', d.title);
            setName('subtitle', d.subtitle);
            setName('description', d.description);
            setName('requirements', d.requirements);
            setName('meta_title', d.meta_title);
            setName('meta_description', d.meta_description);
            setName('meta_keywords', d.meta_keywords);
            setVal('field_duration', d.duration != null ? d.duration : '');
            setVal('field_og_title', d.og_title);
            setVal('field_og_description', d.og_description);
            setVal('field_og_type', d.og_type || 'website');
            setVal('field_twitter_title', d.twitter_title);
            setVal('field_twitter_description', d.twitter_description);
            setVal('field_focus_keyword', d.focus_keyword);
            setVal('field_reading_time', d.reading_time != null ? d.reading_time : '');
            setVal('field_robots', d.robots);
            setVal('field_author', d.author);
            fillWhatYouLearn(d.what_you_learn);
            fillSectionsFromAi(d.sections);
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'تم', text: 'راجع الحقول والصورة ثم احفظ الكورس.', timer: 2800 });
        })
        .catch(function () {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الاتصال' });
            else alert('فشل الاتصال');
        })
        .finally(function () {
            btn.disabled = false;
            sp.classList.add('d-none');
            tx.textContent = 'توليد المحتوى بالذكاء الاصطناعي';
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        syncHiddenFromAiPanel();
    });
})();
</script>
@endsection
