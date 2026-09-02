@extends('admin.layouts.master')

@section('page-title', 'إنشاء مقال بالذكاء الاصطناعي')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" />
@include('admin.docs.pages.partials.ai-page-styles')
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
                    <li class="breadcrumb-item active">توليد بالذكاء الاصطناعي</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-zap me-1"></i>
                        مساعد المدونة
                    </span>
                    <h2 class="group-show-hero__title mb-2">إنشاء مقال بالذكاء الاصطناعي</h2>
                    <p class="group-show-hero__desc mb-2">
                        اكتب الموضوع، اختر التصنيف والإعدادات، ثم ولّد المقال وراجعه قبل الحفظ — بنفس آلية توليد صفحات التوثيق (مخطط ثم أقسام لو كان طويلاً).
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @if(!empty($blogEngineChoiceAvailable))
                            <span class="doc-ai-badge"><i class="fe fe-layers"></i>محركان متاحان</span>
                        @elseif(!empty($useLaravelAiEngine))
                            <span class="doc-ai-badge"><i class="fe fe-cpu"></i>Laravel AI SDK</span>
                        @else
                            <span class="doc-ai-badge"><i class="fe fe-database"></i>بنك الموديلات</span>
                        @endif
                        <span class="doc-ai-badge"><i class="fe fe-file-text"></i>مقال جاهز للنشر مع SEO كامل</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>قائمة المقالات
                        </a>
                        <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-outline-primary">
                            <i class="fe fe-edit me-1"></i>إضافة يدوية
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.blog.ai-posts.store') }}" method="POST" enctype="multipart/form-data" id="aiBlogPostForm">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">

                    <!-- AI Generation Settings -->
                    <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-zap"></i></span>
                                إعدادات التوليد بالذكاء الاصطناعي
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-4">
                                <label class="form-label" for="topic">الموضوع أو الكلمة المفتاحية <span class="text-danger">*</span></label>
                                <input type="text" id="topic" class="form-control doc-ai-topic-input" placeholder="مثال: الذكاء الاصطناعي في التعليم">
                                <p class="doc-ai-hint mb-0">أدخل الموضوع الذي تريد إنشاء مقال عنه — يُستخدم للتوليد فقط ولا يُحفظ مع المقال.</p>
                            </div>

                            @if(!empty($blogEngineChoiceAvailable))
                                <div class="mb-4">
                                    <label class="form-label d-block">محرك التوليد</label>
                                    <div class="doc-ai-engine-pills">
                                        <div class="doc-ai-engine-pill">
                                            <input class="form-check-input" type="radio" name="blog_engine" id="blog_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                            <label for="blog_engine_laravel_ai">
                                                <i class="fe fe-cpu"></i>
                                                Laravel AI SDK
                                            </label>
                                        </div>
                                        <div class="doc-ai-engine-pill">
                                            <input class="form-check-input" type="radio" name="blog_engine" id="blog_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                            <label for="blog_engine_legacy">
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
                                            <div id="blog_engine_laravel_wrap" class="docs-engine-model-wrap" style="{{ !empty($blogEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                                <label class="form-label" for="laravel_ai_model_id">موديل Laravel AI SDK</label>
                                                <select id="laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                                                    <option value="">افتراضي (أولوية + قدرة blog.generate)</option>
                                                    @foreach($laravelAiModels as $lmodel)
                                                        <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                                    @endforeach
                                                </select>
                                                <p class="doc-ai-hint mb-0">إدارة الموديلات من: موديلات Laravel AI SDK</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($blogEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                        <div class="col-md-6">
                                            <div id="blog_engine_legacy_wrap" class="docs-engine-model-wrap" style="{{ !empty($blogEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                                <label class="form-label" for="ai_model_id">موديل AI (بنك الموديلات)</label>
                                                <select id="ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                                                    <option value="">استخدام الموديل الافتراضي</option>
                                                    @foreach($models as $model)
                                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-4">
                                        <label class="form-label" for="content_length">طول المحتوى</label>
                                        <select id="content_length" class="form-select">
                                            <option value="short">قصير (500-800 كلمة)</option>
                                            <option value="medium" selected>متوسط (1000-1500 كلمة)</option>
                                            <option value="long">طويل (2000-3000 كلمة)</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">الطويل/المتوسط يُولَّد على مراحل (مخطط ثم أقسام ثم تجميع) عبر الموديل المختار.</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="tone">الأسلوب</label>
                                        <select id="tone" class="form-select">
                                            <option value="professional" selected>احترافي</option>
                                            <option value="friendly">ودود</option>
                                            <option value="technical">تقني</option>
                                            <option value="casual">عادي</option>
                                            <option value="formal">رسمي</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="language">اللغة</label>
                                        <select id="language" class="form-select">
                                            <option value="ar" selected>العربية</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label d-block">خيارات SEO</label>
                                <div class="border rounded p-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="generate_seo" value="1" checked>
                                        <label class="form-check-label" for="generate_seo">
                                            توليد حقول SEO الأساسية (Meta Title, Description, Keywords)
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="generate_og" value="1" checked>
                                        <label class="form-check-label" for="generate_og">
                                            توليد Open Graph Tags
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="generate_twitter" value="1" checked>
                                        <label class="form-check-label" for="generate_twitter">
                                            توليد Twitter Card Tags
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="generate_schema" value="1" checked>
                                        <label class="form-check-label" for="generate_schema">
                                            توليد Schema.org Markup
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="generate_keyword_synonyms" value="1" checked>
                                        <label class="form-check-label" for="generate_keyword_synonyms">
                                            توليد مرادفات الكلمة المفتاحية
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="doc-ai-generate-bar">
                                <p class="doc-ai-hint mb-0">
                                    <i class="fe fe-info me-1"></i>
                                    المحتوى المتوسط/الطويل يُولَّد على مراحل مع شريط تقدم — لا حاجة للانتظار الصامت.
                                </p>
                                <button type="button" class="doc-ai-generate-btn" id="generateBtn">
                                    <span class="loading-spinner spinner-border spinner-border-sm" role="status"></span>
                                    <i class="fe fe-zap"></i>
                                    <span class="btn-text">توليد المقال</span>
                                </button>
                            </div>
                            <div id="blogAiProgressWrap" class="mt-3" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted" id="blogAiProgressLabel">جاري التوليد…</small>
                                    <small class="fw-semibold" id="blogAiProgressPct">0%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="blogAiProgressBar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <div id="blogAiResumeWrap" class="alert alert-warning mt-3 mb-0" style="display:none;">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="fe fe-pause-circle mt-1"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold" id="blogAiResumeTitle">التوليد متوقف مؤقتاً</div>
                                        <div class="small text-muted mt-1" id="blogAiResumeMsg"></div>
                                        <div class="small mt-1" id="blogAiResumeMissing"></div>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="button" class="btn btn-sm btn-primary" id="blogAiResumeBtn">
                                                <i class="fe fe-play me-1"></i> متابعة التوليد
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="blogAiPartialBtn">
                                                <i class="fe fe-eye me-1"></i> عرض المحتوى الجزئي
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Info -->
                    <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-file-text"></i></span>
                                المعلومات الأساسية
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label" for="title">عنوان المقال <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="slug">الرابط (Slug) <span class="text-danger">*</span></label>
                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                       value="{{ old('slug') }}" required>
                                <p class="doc-ai-hint mb-0">رابط المقال في الموقع</p>
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="excerpt">المقتطف</label>
                                <textarea name="excerpt" id="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt') }}</textarea>
                                <p class="doc-ai-hint mb-0">نبذة مختصرة عن المقال</p>
                                @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label" for="content">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="15">{{ old('content') }}</textarea>
                                @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--seo"><i class="fe fe-search"></i></span>
                                إعدادات SEO
                                <button type="button" class="btn btn-sm btn-outline-secondary float-end" data-bs-toggle="collapse" data-bs-target="#seoCollapse">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h6>
                        </div>
                        <div class="card-body collapse show pt-2" id="seoCollapse">
                            <div class="mb-3">
                                <label class="form-label" for="meta_title">عنوان SEO (Meta Title)</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" maxlength="255">
                                <p class="doc-ai-hint mb-0">50-60 حرف (سيتم استخدام عنوان المقال إذا تُرك فارغاً)</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="meta_description">وصف SEO (Meta Description)</label>
                                <textarea name="meta_description" id="meta_description" rows="2" class="form-control"></textarea>
                                <p class="doc-ai-hint mb-0">150-160 حرف</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="meta_keywords">الكلمات المفتاحية (Meta Keywords)</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" class="form-control">
                                <p class="doc-ai-hint mb-0">افصل الكلمات بفاصلة</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="focus_keyword">الكلمة المفتاحية الرئيسية (Focus Keyword)</label>
                                <input type="text" name="focus_keyword" id="focus_keyword" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="focus_keyword_synonyms">مرادفات الكلمة المفتاحية</label>
                                <input type="text" name="focus_keyword_synonyms" id="focus_keyword_synonyms" class="form-control">
                                <p class="doc-ai-hint mb-0">افصل المرادفات بفواصل</p>
                            </div>

                            <div class="mb-0">
                                <label class="form-label" for="canonical_url">Canonical URL</label>
                                <input type="url" name="canonical_url" id="canonical_url" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Open Graph -->
                    <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--meta"><i class="fab fa-facebook"></i></span>
                                Open Graph
                                <button type="button" class="btn btn-sm btn-outline-secondary float-end" data-bs-toggle="collapse" data-bs-target="#ogCollapse">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h6>
                        </div>
                        <div class="card-body collapse pt-2" id="ogCollapse">
                            <div class="mb-3">
                                <label class="form-label" for="og_title">OG Title</label>
                                <input type="text" name="og_title" id="og_title" class="form-control" maxlength="255">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="og_description">OG Description</label>
                                <textarea name="og_description" id="og_description" rows="2" class="form-control"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="og_type">OG Type</label>
                                    <select name="og_type" id="og_type" class="form-select">
                                        <option value="article" selected>Article</option>
                                        <option value="website">Website</option>
                                        <option value="blog">Blog</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="og_locale">OG Locale</label>
                                    <input type="text" name="og_locale" id="og_locale" class="form-control" value="ar_SA">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Twitter Card -->
                    <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fab fa-twitter"></i></span>
                                Twitter Card
                                <button type="button" class="btn btn-sm btn-outline-secondary float-end" data-bs-toggle="collapse" data-bs-target="#twitterCollapse">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h6>
                        </div>
                        <div class="card-body collapse pt-2" id="twitterCollapse">
                            <div class="mb-3">
                                <label class="form-label" for="twitter_card">Twitter Card Type</label>
                                <select name="twitter_card" id="twitter_card" class="form-select">
                                    <option value="summary">Summary</option>
                                    <option value="summary_large_image" selected>Summary Large Image</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="twitter_title">Twitter Title</label>
                                <input type="text" name="twitter_title" id="twitter_title" class="form-control" maxlength="255">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="twitter_description">Twitter Description</label>
                                <textarea name="twitter_description" id="twitter_description" rows="2" class="form-control"></textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label" for="twitter_creator">Twitter Creator</label>
                                <input type="text" name="twitter_creator" id="twitter_creator" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Schema.org -->
                    <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fas fa-code"></i></span>
                                Schema.org
                                <button type="button" class="btn btn-sm btn-outline-secondary float-end" data-bs-toggle="collapse" data-bs-target="#schemaCollapse">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h6>
                        </div>
                        <div class="card-body collapse pt-2" id="schemaCollapse">
                            <div class="mb-3">
                                <label class="form-label" for="schema_type">Schema Type</label>
                                <input type="text" name="schema_type" id="schema_type" class="form-control" value="Article">
                                <p class="doc-ai-hint mb-0">مثال: Article, BlogPosting, NewsArticle</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="schema_headline">Schema Headline</label>
                                <input type="text" name="schema_headline" id="schema_headline" class="form-control" maxlength="255">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="schema_description">Schema Description</label>
                                <textarea name="schema_description" id="schema_description" rows="2" class="form-control"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="schema_image">Schema Image (رابط)</label>
                                <input type="text" name="schema_image" id="schema_image" class="form-control">
                                <p class="doc-ai-hint mb-0">اتركه فارغاً لاستخدام الصورة البارزة</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="schema_author_name">Schema Author Name</label>
                                <input type="text" name="schema_author_name" id="schema_author_name" class="form-control">
                            </div>

                            <div class="mb-0">
                                <label class="form-label" for="schema_author_url">Schema Author URL</label>
                                <input type="url" name="schema_author_url" id="schema_author_url" class="form-control">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="doc-ai-sidebar-sticky">

                        <!-- Publish Settings -->
                        <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--meta"><i class="fe fe-layers"></i></span>
                                    إعدادات النشر
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                <div class="mb-3">
                                    <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="draft" selected>مسودة</option>
                                        <option value="published">منشور</option>
                                        <option value="scheduled">مجدول</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">تاريخ النشر</label>
                                    <input type="datetime-local" name="published_at" class="form-control">
                                    <p class="doc-ai-hint mb-0">اتركه فارغاً للنشر الفوري</p>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured">
                                    <label class="form-check-label" for="is_featured">مقال مميز</label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_comments" value="1" id="allow_comments" checked>
                                    <label class="form-check-label" for="allow_comments">السماح بالتعليقات</label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_indexable" value="1" id="is_indexable" checked>
                                    <label class="form-check-label" for="is_indexable">قابل للفهرسة (Index)</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_followable" value="1" id="is_followable" checked>
                                    <label class="form-check-label" for="is_followable">قابل للمتابعة (Follow)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-folder"></i></span>
                                    التصنيف
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                <select name="category_id" class="form-select" required>
                                    <option value="">اختر التصنيف</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-tag"></i></span>
                                    الوسوم
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                <div class="tags-container" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($tags as $tag)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="tags[]"
                                               value="{{ $tag->id }}" id="tag{{ $tag->id }}">
                                        <label class="form-check-label" for="tag{{ $tag->id }}">
                                            {{ $tag->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Featured Image -->
                        <div class="card custom-card doc-ai-panel doc-ai-animate mb-4">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--seo"><i class="fe fe-image"></i></span>
                                    الصورة البارزة
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                <div class="mb-3">
                                    <input type="file" name="featured_image" class="form-control" accept="image/*" id="featuredImage">
                                </div>

                                <div id="imagePreview" class="mb-3" style="display: none;">
                                    <img src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">نص بديل للصورة (Alt Text)</label>
                                    <input type="text" name="featured_image_alt" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fe fe-save me-1"></i>حفظ المقال
                                </button>
                                <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-light border w-100">
                                    إلغاء
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@include('admin.blog.ai-posts.partials.ai-job-poller')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Prism.js for Syntax Highlighting -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>

<!-- TinyMCE Editor -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
document.documentElement.classList.add('loaded');

// Wait for all libraries to load before initializing TinyMCE
function initTinyMCE() {
    // Check if TinyMCE is loaded
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE failed to load');
        setTimeout(initTinyMCE, 100); // Retry after 100ms
        return;
    }

    // TinyMCE Editor - Simplified configuration
    tinymce.init({
        selector: '#content',
        height: 600,
        directionality: 'rtl',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
        promotion: false,
        branding: false,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code codesample fullscreen insertdatetime media table help wordcount emoticons directionality',
        toolbar: 'undo redo | blocks | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image media table | codesample code | fullscreen | help',
        menubar: 'file edit view insert format tools table help',
        menu: {
            file: { title: 'ملف', items: 'newdocument restoredraft | preview | print' },
            edit: { title: 'تحرير', items: 'undo redo | cut copy paste | selectall | searchreplace' },
            view: { title: 'عرض', items: 'code | visualaid visualchars visualblocks | preview fullscreen' },
            insert: { title: 'إدراج', items: 'image link media codesample | charmap emoticons hr | pagebreak nonbreaking anchor | insertdatetime' },
            format: { title: 'تنسيق', items: 'bold italic underline strikethrough | formats blockformats fontformats fontsizes align | forecolor backcolor | removeformat' },
            tools: { title: 'أدوات', items: 'code wordcount' },
            table: { title: 'جدول', items: 'inserttable | cell row column | tableprops deletetable' },
            help: { title: 'تعليمات', items: 'help' }
        },
        content_style: 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; font-size: 14px; direction: rtl; }',
        elementpath: true,
        resize: true,
        contextmenu: 'link image table',
        paste_as_text: false,
        paste_data_images: true,
        relative_urls: false,
        remove_script_host: false,
        image_advtab: true,
        image_uploadtab: true,
        automatic_uploads: true,
        images_upload_url: '/upload',
        media_live_embeds: true,
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'C++', value: 'cpp' },
            { text: 'C#', value: 'csharp' },
            { text: 'SQL', value: 'sql' },
            { text: 'JSON', value: 'json' },
            { text: 'Bash/Shell', value: 'bash' },
            { text: 'TypeScript', value: 'typescript' },
            { text: 'Ruby', value: 'ruby' },
            { text: 'Go', value: 'go' },
            { text: 'Swift', value: 'swift' },
            { text: 'Kotlin', value: 'kotlin' },
            { text: 'Dart', value: 'dart' },
            { text: 'Rust', value: 'rust' }
        ],
        codesample_global_prismjs: true,
    }).catch(function(error) {
        console.error('TinyMCE initialization error:', error);
        alert('حدث خطأ في تهيئة محرر النصوص. يرجى تحديث الصفحة.');
    });
}

(function () {
    const useLaravelAiEngine = @json(!empty($useLaravelAiEngine));
    const blogEngineChoiceAvailable = @json(!empty($blogEngineChoiceAvailable));
    const STORAGE_KEY = 'blog_ai_generate_job_uuid';
    let pausedUuid = null;

    function setProgress(job) {
        const wrap = document.getElementById('blogAiProgressWrap');
        const bar = document.getElementById('blogAiProgressBar');
        const label = document.getElementById('blogAiProgressLabel');
        const pct = document.getElementById('blogAiProgressPct');
        if (!wrap) return;
        wrap.style.display = '';
        const p = Math.max(0, Math.min(100, parseInt(job.progress || 0, 10)));
        if (bar) bar.style.width = p + '%';
        if (pct) pct.textContent = p + '%';
        if (label) {
            let text = job.stage_label || 'جاري التوليد…';
            if (job.queue_hint && job.status === 'queued') {
                text = 'في الطابور — تأكد أن عامل الطابور يعمل (php artisan queue:work)';
            }
            label.textContent = text;
        }
    }

    function hideProgress() {
        const wrap = document.getElementById('blogAiProgressWrap');
        if (wrap) wrap.style.display = 'none';
    }

    function hideResumePanel() {
        const wrap = document.getElementById('blogAiResumeWrap');
        if (wrap) wrap.style.display = 'none';
        pausedUuid = null;
    }

    /** Paused means every finished section is already stored server-side. */
    function showResumePanel(job) {
        pausedUuid = job.uuid;
        const wrap = document.getElementById('blogAiResumeWrap');
        const msg = document.getElementById('blogAiResumeMsg');
        const missing = document.getElementById('blogAiResumeMissing');
        const title = document.getElementById('blogAiResumeTitle');
        const partialBtn = document.getElementById('blogAiPartialBtn');
        if (!wrap) return;

        const s = job.sections || null;
        if (title) {
            title.textContent = s
                ? 'تم توليد ' + s.done + ' من ' + s.planned + ' قسماً وحُفظت'
                : 'التوليد متوقف مؤقتاً';
        }
        if (msg) msg.textContent = job.error_message || '';
        if (missing) {
            const headings = (s && s.failed_headings) ? s.failed_headings : [];
            missing.textContent = headings.length
                ? 'الأقسام الناقصة: ' + headings.join('، ')
                : '';
        }
        if (partialBtn) {
            partialBtn.style.display = job.partial_content_available ? '' : 'none';
        }

        wrap.style.display = '';
        hideProgress();
        resetGenerateBtn();
    }

    function resetGenerateBtn() {
        const btn = document.getElementById('generateBtn');
        if (!btn) return;
        btn.disabled = false;
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.loading-spinner');
        if (btnText) btnText.textContent = 'توليد المقال';
        if (spinner) spinner.classList.remove('active');
    }

    // Fill form fields with generated data
    function fillFormFields(data) {
        if (data.title) document.getElementById('title').value = data.title;
        if (data.slug) document.getElementById('slug').value = data.slug;
        if (data.excerpt) document.getElementById('excerpt').value = data.excerpt;
        if (data.content) {
            const editor = tinymce.get('content');
            if (editor) {
                editor.setContent(data.content);
            } else {
                setTimeout(function() {
                    const ed = tinymce.get('content');
                    if (ed) ed.setContent(data.content);
                }, 500);
            }
        }

        // SEO fields
        if (data.meta_title) document.getElementById('meta_title').value = data.meta_title;
        if (data.meta_description) document.getElementById('meta_description').value = data.meta_description;
        if (data.meta_keywords) document.getElementById('meta_keywords').value = data.meta_keywords;
        if (data.focus_keyword) document.getElementById('focus_keyword').value = data.focus_keyword;
        if (data.focus_keyword_synonyms) document.getElementById('focus_keyword_synonyms').value = data.focus_keyword_synonyms;
        if (data.canonical_url) document.getElementById('canonical_url').value = data.canonical_url;

        // Open Graph
        if (data.og_title) document.getElementById('og_title').value = data.og_title;
        if (data.og_description) document.getElementById('og_description').value = data.og_description;
        if (data.og_type) document.getElementById('og_type').value = data.og_type;
        if (data.og_locale) document.getElementById('og_locale').value = data.og_locale;

        // Twitter Card
        if (data.twitter_card) document.getElementById('twitter_card').value = data.twitter_card;
        if (data.twitter_title) document.getElementById('twitter_title').value = data.twitter_title;
        if (data.twitter_description) document.getElementById('twitter_description').value = data.twitter_description;
        if (data.twitter_creator) document.getElementById('twitter_creator').value = data.twitter_creator;

        // Schema.org
        if (data.schema_type) document.getElementById('schema_type').value = data.schema_type;
        if (data.schema_headline) document.getElementById('schema_headline').value = data.schema_headline;
        if (data.schema_description) document.getElementById('schema_description').value = data.schema_description;
    }

    function startPolling(uuid) {
        const btn = document.getElementById('generateBtn');
        if (btn) btn.disabled = true;
        window.BlogAiJobPoller.poll({
            uuid: uuid,
            storageKey: STORAGE_KEY,
            onProgress: setProgress,
            onComplete: function (result) {
                fillFormFields(result || {});
                document.getElementById('previewCard') && (document.getElementById('previewCard').style.display = 'block');
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'تم التوليد بنجاح!', text: 'تم توليد المقال وجميع حقول SEO بنجاح. يمكنك مراجعته وتعديله قبل الحفظ.', timer: 2800 });
                }
            },
            onPaused: showResumePanel,
            onError: function (msg) {
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg || 'فشل التوليد' });
                } else {
                    alert(msg || 'فشل التوليد');
                }
            }
        });
    }

    function syncBlogEngineModelVisibility() {
        if (!blogEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('blog_engine_laravel_ai')?.checked;
        const wL = document.getElementById('blog_engine_laravel_wrap');
        const wG = document.getElementById('blog_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initTinyMCE, 200);

        syncBlogEngineModelVisibility();
        document.querySelectorAll('input[name="blog_engine"]').forEach(function (el) {
            el.addEventListener('change', syncBlogEngineModelVisibility);
        });

        // Resume unfinished job after leave/return
        window.BlogAiJobPoller.resumeIfAny(STORAGE_KEY, {
            onProgress: function (job) {
                setProgress(job);
                const btn = document.getElementById('generateBtn');
                if (btn) {
                    btn.disabled = true;
                    const btnText = btn.querySelector('.btn-text');
                    const spinner = btn.querySelector('.loading-spinner');
                    if (btnText) btnText.textContent = 'جاري التوليد...';
                    if (spinner) spinner.classList.add('active');
                }
            },
            onComplete: function (result) {
                fillFormFields(result || {});
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'تم التوليد', text: 'اكتملت المهمة أثناء غيابك — راجع المحتوى.', timer: 3200 });
                }
            },
            onPaused: showResumePanel,
            onError: function (msg) {
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg || 'فشل التوليد' });
                }
            }
        });

        const resumeBtn = document.getElementById('blogAiResumeBtn');
        if (resumeBtn) {
            resumeBtn.addEventListener('click', function () {
                if (!pausedUuid) return;
                resumeBtn.disabled = true;
                window.BlogAiJobPoller.resume(pausedUuid)
                    .then(function (data) {
                        const uuid = (data.job && data.job.uuid) ? data.job.uuid : pausedUuid;
                        hideResumePanel();
                        const btn = document.getElementById('generateBtn');
                        if (btn) {
                            btn.disabled = true;
                            const btnText = btn.querySelector('.btn-text');
                            const spinner = btn.querySelector('.loading-spinner');
                            if (btnText) btnText.textContent = 'جاري التوليد...';
                            if (spinner) spinner.classList.add('active');
                        }
                        setProgress({ progress: 1, stage_label: 'استئناف التوليد…', status: 'queued' });
                        startPolling(uuid);
                    })
                    .catch(function (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'خطأ', text: err.message || 'تعذّر استئناف التوليد' });
                        } else {
                            alert(err.message || 'تعذّر استئناف التوليد');
                        }
                    })
                    .finally(function () {
                        resumeBtn.disabled = false;
                    });
            });
        }

        const partialBtn = document.getElementById('blogAiPartialBtn');
        if (partialBtn) {
            partialBtn.addEventListener('click', function () {
                if (!pausedUuid) return;
                partialBtn.disabled = true;
                window.BlogAiJobPoller.partial(pausedUuid)
                    .then(function (result) {
                        fillFormFields(result || {});
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'info', title: 'محتوى جزئي', text: 'طُبِّقت الأقسام المكتملة على النموذج — لا يزال بإمكانك متابعة التوليد لإكمال الباقي.', timer: 3500 });
                        }
                    })
                    .catch(function (err) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'خطأ', text: err.message || 'تعذّر جلب المحتوى الجزئي' });
                        } else {
                            alert(err.message || 'تعذّر جلب المحتوى الجزئي');
                        }
                    })
                    .finally(function () {
                        partialBtn.disabled = false;
                    });
            });
        }

        // Generate button click
        document.getElementById('generateBtn').addEventListener('click', function() {
            const topic = document.getElementById('topic').value.trim();
            if (!topic) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'يرجى إدخال الموضوع أو الكلمة المفتاحية' });
                } else {
                    alert('يرجى إدخال الموضوع أو الكلمة المفتاحية');
                }
                return;
            }

            const btn = this;
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.loading-spinner');
            btn.disabled = true;
            btnText.textContent = 'جاري التوليد...';
            spinner.classList.add('active');
            setProgress({ progress: 1, stage_label: 'بدء المهمة…', status: 'queued' });

            let engine = useLaravelAiEngine ? 'laravel_ai' : 'legacy';
            if (blogEngineChoiceAvailable) {
                const r = document.querySelector('input[name="blog_engine"]:checked');
                if (r) engine = r.value;
            }

            // Collect form data
            const formData = {
                topic: topic,
                blog_engine: engine,
                content_length: document.getElementById('content_length').value,
                tone: document.getElementById('tone').value,
                language: document.getElementById('language').value,
                category_id: document.querySelector('select[name="category_id"]').value,
                generate_seo: document.getElementById('generate_seo').checked,
                generate_og: document.getElementById('generate_og').checked,
                generate_twitter: document.getElementById('generate_twitter').checked,
                generate_schema: document.getElementById('generate_schema').checked,
                generate_keyword_synonyms: document.getElementById('generate_keyword_synonyms').checked,
                _token: '{{ csrf_token() }}'
            };
            if (engine === 'laravel_ai') {
                const el = document.getElementById('laravel_ai_model_id');
                formData.laravel_ai_model_id = el ? el.value : '';
            } else {
                const el = document.getElementById('ai_model_id');
                formData.ai_model_id = el ? el.value : '';
            }

            // Start the job — returns immediately with a job uuid; the actual
            // generation (outline + sections for medium/long) runs in the queue.
            fetch('{{ route("admin.blog.ai-posts.generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.job && data.job.uuid) {
                    startPolling(data.job.uuid);
                } else {
                    hideProgress();
                    resetGenerateBtn();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: data.message || 'تعذر بدء التوليد' });
                    } else {
                        alert(data.message || 'تعذر بدء التوليد');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'حدث خطأ أثناء الاتصال بالخادم' });
                }
            });
        });

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            if (!document.getElementById('slug').value || document.getElementById('slug').value === '') {
                const title = this.value;
                const slug = title.toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^؀-ۿa-z0-9-]/g, '')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                document.getElementById('slug').value = slug;
            }
        });

        // Image preview
        document.getElementById('featuredImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.querySelector('img').src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });
})();
</script>
@endsection
