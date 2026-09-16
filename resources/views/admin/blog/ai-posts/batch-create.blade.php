@extends('admin.layouts.master')

@section('page-title', 'توليد دفعة مقالات بالذكاء الاصطناعي')

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.pages.partials.ai-page-styles')
@include('admin.docs.pages.partials.ai-batch-styles')
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb doc-ai-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.blog.posts.index') }}">المدونة</a></li>
                    <li class="breadcrumb-item active">توليد دفعة مقالات</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-layers me-1"></i>
                        مساعد المدونة
                    </span>
                    <h2 class="group-show-hero__title mb-2">توليد دفعة مقالات</h2>
                    <p class="group-show-hero__desc mb-2">
                        اكتب عدة مواضيع، اضبط الإعدادات المشتركة، ثم أضفها إلى الطابور — ستُعالَج المواضيع تباعاً ويُحفظ كل مقال <strong>كمسودة</strong> فور اكتماله، دون الحاجة لأي تدخل يدوي.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.blog.ai-posts.batch.index') }}" class="btn btn-light border">
                            <i class="fe fe-clock me-1"></i>سجل الدفعات
                        </a>
                        <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>قائمة المقالات
                        </a>
                        <a href="{{ route('admin.blog.ai-posts.create') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-zap me-1"></i>توليد مقال واحد
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($incompleteCount))
            <div class="alert alert-warning border-0 d-flex flex-wrap align-items-center gap-2 doc-ai-animate">
                <span class="flex-grow-1">
                    <i class="fe fe-alert-circle me-1"></i>
                    لديك <strong>{{ $incompleteCount }}</strong> مقالاً غير مكتمل من دفعات سابقة — الأقسام المكتملة محفوظة ويمكن إكمالها.
                </span>
                <a href="{{ route('admin.blog.ai-posts.batch.index', ['incomplete' => 1]) }}" class="btn btn-sm btn-warning">
                    <i class="fe fe-play-circle me-1"></i>عرضها ومتابعتها
                </a>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0">
                        <h6 class="doc-ai-panel__title">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-file-text"></i></span>
                            المواضيع
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <label class="form-label" for="batchTopics">المواضيع (افصل بين كل موضوع وآخر بسطر فارغ — اضغط Enter مرتين) <span class="text-danger">*</span></label>
                        <textarea id="batchTopics" class="form-control" rows="10" placeholder="أفضل ممارسات تحسين محركات البحث للمدونات العربية&#10;&#10;كيف تبني استراتيجية محتوى لمتجر إلكتروني&#10;&#10;دليل كتابة عناوين جذابة تزيد نسبة النقر"></textarea>
                        <p class="doc-ai-hint mb-0 mt-1"><span id="batchTopicsCount">0</span> موضوع مكتشف — الحد الأقصى 30 مقالاً لكل دفعة. يمكن أن يمتد شرح الموضوع الواحد على عدة أسطر متتالية؛ اترك سطراً فارغاً لتبدأ الموضوع التالي.</p>
                    </div>
                </div>

                <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0">
                        <h6 class="doc-ai-panel__title">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-zap"></i></span>
                            إعدادات التوليد
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        @if(!empty($blogEngineChoiceAvailable))
                            <div class="mb-4">
                                <label class="form-label d-block">محرك التوليد</label>
                                <div class="doc-ai-engine-pills">
                                    <div class="doc-ai-engine-pill">
                                        <input class="form-check-input" type="radio" name="batch_blog_engine" id="batch_blog_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                        <label for="batch_blog_engine_laravel_ai">
                                            <i class="fe fe-cpu"></i>
                                            Laravel AI SDK
                                        </label>
                                    </div>
                                    <div class="doc-ai-engine-pill">
                                        <input class="form-check-input" type="radio" name="batch_blog_engine" id="batch_blog_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                        <label for="batch_blog_engine_legacy">
                                            <i class="fe fe-database"></i>
                                            موديلات قديمة
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($models->isEmpty() && $laravelAiModels->isEmpty())
                            <div class="alert alert-warning border-0 mb-4">
                                <i class="fe fe-alert-triangle me-1"></i>
                                لا يوجد موديل نشط. أضف موديلاً من «إدارة موديلات AI» أو «موديلات Laravel AI SDK».
                            </div>
                        @else
                            <div class="row g-3 mb-4">
                                @if(!empty($blogEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
                                    <div class="col-md-6">
                                        <div id="batch_blog_engine_laravel_wrap" class="docs-engine-model-wrap" style="{{ !empty($blogEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label" for="batch_laravel_ai_model_id">موديل Laravel AI SDK</label>
                                            <select id="batch_laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                                                <option value="">افتراضي (أولوية + blog.generate)</option>
                                                @foreach($laravelAiModels as $lmodel)
                                                    <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($blogEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                    <div class="col-md-6">
                                        <div id="batch_blog_engine_legacy_wrap" class="docs-engine-model-wrap" style="{{ !empty($blogEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label" for="batch_ai_model_id">موديل AI (بنك الموديلات)</label>
                                            <select id="batch_ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                                                <option value="">الافتراضي</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-4">
                                    <label class="form-label" for="batch_content_length">طول المحتوى</label>
                                    <select id="batch_content_length" class="form-select">
                                        <option value="short">قصير (500-800 كلمة)</option>
                                        <option value="medium" selected>متوسط (1000-1500 كلمة)</option>
                                        <option value="long">طويل (2000-3000 كلمة)</option>
                                        <option value="very_long">طويل جداً (4000-6500 كلمة)</option>
                                    </select>
                                    <p class="doc-ai-hint mb-0 mt-1">«طويل جداً» يُكتب على 13 قسماً ويستغرق وقتاً أطول لكل مقال.</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="batch_tone">الأسلوب</label>
                                    <select id="batch_tone" class="form-select">
                                        <option value="professional" selected>احترافي</option>
                                        <option value="friendly">ودود</option>
                                        <option value="technical">تقني</option>
                                        <option value="casual">عادي</option>
                                        <option value="formal">رسمي</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="batch_language">اللغة</label>
                                    <select id="batch_language" class="form-select">
                                        <option value="ar" selected>العربية</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        <label class="form-label d-block">حقول تُولَّد تلقائياً لكل مقال</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="batch_generate_seo" checked>
                                    <label class="form-check-label" for="batch_generate_seo">حقول SEO (العنوان والوصف والكلمة المفتاحية)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="batch_generate_og" checked>
                                    <label class="form-check-label" for="batch_generate_og">Open Graph</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="batch_generate_twitter" checked>
                                    <label class="form-check-label" for="batch_generate_twitter">Twitter Card</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="batch_generate_schema" checked>
                                    <label class="form-check-label" for="batch_generate_schema">Schema.org</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="batch_generate_keyword_synonyms" checked>
                                    <label class="form-check-label" for="batch_generate_keyword_synonyms">مرادفات الكلمة المفتاحية</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="batch_generate_tags" checked>
                                    <label class="form-check-label" for="batch_generate_tags">الوسوم</label>
                                </div>
                            </div>
                        </div>
                        <p class="doc-ai-hint mb-0 mt-2">تُقترح الوسوم تلقائياً لكل مقال وتُنشأ إن لم تكن موجودة في قائمة الوسوم.</p>
                    </div>
                </div>

                <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0 d-flex justify-content-between align-items-center">
                        <h6 class="doc-ai-panel__title mb-0">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-activity"></i></span>
                            تقدّم الطابور
                        </h6>
                        @if(!empty($initialBatch))
                            <a href="{{ route('admin.blog.ai-posts.batch.show', $initialBatch['uuid']) }}" class="btn btn-sm btn-light border">
                                <i class="fe fe-external-link me-1"></i>صفحة الدفعة
                            </a>
                        @endif
                    </div>
                    <div class="card-body pt-2">
                        @include('admin.blog.ai-posts.partials.batch-progress')
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="doc-ai-sidebar-sticky">
                    <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--meta"><i class="fe fe-layers"></i></span>
                                التصنيف والحفظ
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label" for="batch_category_id">التصنيف <span class="text-danger">*</span></label>
                                <select id="batch_category_id" class="form-select" required>
                                    <option value="">— اختر التصنيف —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) $categoryId === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <p class="doc-ai-hint mb-0">يُطبَّق على كل مقالات هذه الدفعة.</p>
                            </div>

                            <div class="alert alert-info border-0 mb-0">
                                <i class="fe fe-info me-1"></i>
                                كل مقال يُحفظ <strong>كمسودة</strong> فور اكتماله — راجعه وانشره من قائمة المقالات.
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                        <div class="card-body">
                            <button type="button" class="btn btn-primary w-100 mb-2" id="batchSubmitBtn">
                                <span class="loading-spinner spinner-border spinner-border-sm" role="status" style="display:none;"></span>
                                <i class="fe fe-plus-circle me-1"></i>
                                <span class="btn-text">إضافة إلى الطابور</span>
                            </button>
                            <p class="doc-ai-hint mb-0">
                                كل موضوع يُولَّد ثم يُحفظ مقاله فوراً كمسودة. يمكنك مغادرة الصفحة والعودة لاحقاً؛ ستجد الدفعة كما تركتها.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
@include('admin.blog.ai-posts.partials.batch-scripts')
<script>
(function () {
    const blogEngineChoiceAvailable = @json(!empty($blogEngineChoiceAvailable));
    const storeUrl = @json(route('admin.blog.ai-posts.batch.store'));
    const csrfToken = @json(csrf_token());
    const initialBatch = @json($initialBatch);
    const initialSettings = @json($initialSettings);

    function setVal(id, value) {
        const el = document.getElementById(id);
        if (el && value !== null && value !== undefined && value !== '') el.value = value;
    }

    function setChecked(id, value) {
        const el = document.getElementById(id);
        if (el) el.checked = value !== false;
    }

    function syncEngineVisibility() {
        if (!blogEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('batch_blog_engine_laravel_ai')?.checked;
        const wL = document.getElementById('batch_blog_engine_laravel_wrap');
        const wG = document.getElementById('batch_blog_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    /** Put the form back the way the restored batch was configured. */
    function restoreSettings(s) {
        if (!s) return;
        setVal('batch_category_id', s.category_id);
        setVal('batch_content_length', s.content_length);
        setVal('batch_tone', s.tone);
        setVal('batch_language', s.language);
        setChecked('batch_generate_seo', s.generate_seo);
        setChecked('batch_generate_og', s.generate_og);
        setChecked('batch_generate_twitter', s.generate_twitter);
        setChecked('batch_generate_schema', s.generate_schema);
        setChecked('batch_generate_keyword_synonyms', s.generate_keyword_synonyms);
        setChecked('batch_generate_tags', s.generate_tags);
        if (blogEngineChoiceAvailable && s.blog_engine) {
            const radio = document.getElementById(
                s.blog_engine === 'laravel_ai' ? 'batch_blog_engine_laravel_ai' : 'batch_blog_engine_legacy'
            );
            if (radio) radio.checked = true;
            syncEngineVisibility();
        }
        setVal('batch_laravel_ai_model_id', s.laravel_ai_model_id);
        setVal('batch_ai_model_id', s.ai_model_id);
    }

    function parseTopics() {
        const raw = document.getElementById('batchTopics').value || '';
        const seen = {};
        // A comma (Arabic "،" or Latin ",") is ordinary punctuation inside a topic's
        // own explanation, so it can't be the separator. A blank line is: it never
        // occurs naturally inside one topic's wrapped text, only between topics.
        return raw.split(/\n\s*\n+/)
            .map(function (t) { return t.replace(/\s+/g, ' ').trim(); })
            .filter(function (t) {
                if (!t) return false;
                if (seen[t]) return false;
                seen[t] = true;
                return true;
            });
    }

    function updateTopicsCount() {
        document.getElementById('batchTopicsCount').textContent = parseTopics().length;
    }

    function setSubmitting(isSubmitting) {
        const btn = document.getElementById('batchSubmitBtn');
        const spinner = btn.querySelector('.loading-spinner');
        btn.disabled = isSubmitting;
        if (spinner) spinner.style.display = isSubmitting ? '' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncEngineVisibility();
        document.querySelectorAll('input[name="batch_blog_engine"]').forEach(function (el) {
            el.addEventListener('change', syncEngineVisibility);
        });
        document.getElementById('batchTopics').addEventListener('input', updateTopicsCount);
        updateTopicsCount();

        if (initialBatch && initialSettings) {
            restoreSettings(initialSettings);
        }

        // Restores the batch table (and resumes polling) after a refresh.
        window.BlogAiBatch.attach({ initialBatch: initialBatch });

        document.getElementById('batchSubmitBtn').addEventListener('click', function () {
            const topics = parseTopics();
            const categoryId = document.getElementById('batch_category_id').value;

            if (!topics.length) {
                window.BlogAiBatch.toast({ icon: 'warning', title: 'يرجى إدخال موضوع واحد على الأقل' });
                return;
            }
            if (topics.length > 30) {
                window.BlogAiBatch.toast({ icon: 'warning', title: 'الحد الأقصى 30 مقالاً لكل دفعة' });
                return;
            }
            const tooLong = topics.find(function (t) { return t.length > 3000; });
            if (tooLong) {
                window.BlogAiBatch.toast({ icon: 'warning', title: 'أحد المواضيع يتجاوز 3000 حرف', text: 'اختصر النص أو قسّمه.' });
                return;
            }
            if (!categoryId) {
                window.BlogAiBatch.toast({ icon: 'warning', title: 'يرجى اختيار تصنيف المدونة أولاً' });
                return;
            }

            let engine = blogEngineChoiceAvailable
                ? (document.querySelector('input[name="batch_blog_engine"]:checked')?.value || 'legacy')
                : @json(!empty($useLaravelAiEngine) ? 'laravel_ai' : 'legacy');

            const laravelEl = document.getElementById('batch_laravel_ai_model_id');
            const legacyEl = document.getElementById('batch_ai_model_id');

            const payload = {
                topics: topics,
                blog_engine: engine,
                ai_model_id: engine === 'legacy' ? (legacyEl ? (legacyEl.value || null) : null) : null,
                laravel_ai_model_id: engine === 'laravel_ai' ? (laravelEl ? (laravelEl.value || null) : null) : null,
                content_length: document.getElementById('batch_content_length').value,
                tone: document.getElementById('batch_tone').value,
                language: document.getElementById('batch_language').value,
                category_id: categoryId,
                generate_seo: document.getElementById('batch_generate_seo').checked,
                generate_og: document.getElementById('batch_generate_og').checked,
                generate_twitter: document.getElementById('batch_generate_twitter').checked,
                generate_schema: document.getElementById('batch_generate_schema').checked,
                generate_keyword_synonyms: document.getElementById('batch_generate_keyword_synonyms').checked,
                generate_tags: document.getElementById('batch_generate_tags').checked,
            };

            setSubmitting(true);
            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    setSubmitting(false);
                    if (res.body.success && res.body.batch) {
                        // A new batch replaces whatever was restored on screen.
                        window.BlogAiBatch.resetHistory();
                        window.BlogAiBatch.renderBatch(res.body.batch);
                        window.BlogAiBatch.startPolling(res.body.batch.uuid);
                        return;
                    }
                    window.BlogAiBatch.toast({
                        icon: 'error',
                        title: 'تعذر بدء الدفعة',
                        text: res.body.message || '',
                        timer: 7000,
                    });
                })
                .catch(function () {
                    setSubmitting(false);
                    window.BlogAiBatch.toast({
                        icon: 'error',
                        title: 'تعذر بدء الدفعة',
                        text: 'تحقق من الاتصال ثم أعد المحاولة.',
                    });
                });
        });
    });
})();
</script>
@endsection
