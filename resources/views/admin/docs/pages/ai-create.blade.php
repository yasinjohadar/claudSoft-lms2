@extends('admin.layouts.master')

@section('page-title', 'توليد صفحة توثيق بالذكاء الاصطناعي')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" />
<style>
    .loading-spinner { display: none; }
    .loading-spinner.active { display: inline-block; }
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">توليد صفحة توثيق بالذكاء الاصطناعي</h4>
                <p class="mb-0 text-muted">نفس آلية توليد المدونة: إعدادات ثم توليد ثم مراجعة وحفظ</p>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-secondary">قائمة الصفحات</a>
                <a href="{{ route('admin.docs.pages.create') }}" class="btn btn-outline-primary">إضافة يدوية</a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.docs.ai-pages.store') }}" method="POST" id="aiDocPageForm">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="card-title mb-0"><i class="fas fa-robot me-2"></i>إعدادات التوليد</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">الموضوع أو ما تريد شرحه <span class="text-danger">*</span></label>
                                <input type="text" id="topic" class="form-control" placeholder="مثال: التوجيه في Laravel، أنواع البيانات في PHP">
                                <small class="text-muted">للتوليد فقط — لن يُرسل مع الحفظ</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">موديل AI</label>
                                    <select id="ai_model_id" class="form-select">
                                        <option value="">الافتراضي</option>
                                        @foreach($models as $model)
                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">طول المحتوى</label>
                                    <select id="content_length" class="form-select">
                                        <option value="short">قصير</option>
                                        <option value="medium" selected>متوسط</option>
                                        <option value="long">طويل</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الأسلوب</label>
                                    <select id="tone" class="form-select">
                                        <option value="professional" selected>احترافي</option>
                                        <option value="friendly">ودود</option>
                                        <option value="technical">تقني</option>
                                        <option value="casual">عادي</option>
                                        <option value="formal">رسمي</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">اللغة</label>
                                    <select id="language" class="form-select">
                                        <option value="ar" selected>العربية</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="generate_meta" checked>
                                <label class="form-check-label" for="generate_meta">توليد meta_title و meta_description</label>
                            </div>
                            <button type="button" class="btn btn-success" id="generateBtn">
                                <span class="loading-spinner spinner-border spinner-border-sm me-1" role="status"></span>
                                <span class="btn-text">توليد المحتوى</span>
                            </button>
                        </div>
                    </div>

                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">المحتوى</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="doc_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الرابط (slug)</label>
                                <div class="input-group">
                                    <input type="text" name="slug" id="doc_slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}">
                                    <button type="button" class="btn btn-outline-secondary" id="doc_generate_slug">توليد</button>
                                </div>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">المقتطف</label>
                                <textarea name="excerpt" id="doc_excerpt" rows="2" class="form-control">{{ old('excerpt') }}</textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="content" id="doc_content" class="form-control @error('content') is-invalid @enderror" rows="12">{{ old('content') }}</textarea>
                                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">SEO</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">عنوان Meta</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title') }}" maxlength="255">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">وصف Meta</label>
                                <textarea name="meta_description" id="meta_description" rows="2" class="form-control">{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">التصنيف والهيكل</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">القسم <span class="text-danger">*</span></label>
                                <select name="documentation_category_id" id="doc_category_id" class="form-select @error('documentation_category_id') is-invalid @enderror" required>
                                    <option value="">— اختر —</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('documentation_category_id', $categoryId) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('documentation_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">صفحة أب (اختياري)</label>
                                <select name="parent_id" id="doc_parent_id" class="form-select">
                                    <option value="">— بدون —</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الترتيب</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>منشور</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تاريخ النشر</label>
                                <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at') }}">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_indexable" value="1" id="is_indexable" {{ old('is_indexable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_indexable">قابلة للفهرسة</label>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="bi bi-save me-2"></i>حفظ الصفحة</button>
                            <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-secondary w-100">إلغاء</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@include('admin.docs.partials.tinymce-doc')
<script>
(function () {
    const parentPages = @json($parentPagesJson);

    function refreshParentOptions() {
        const catId = document.getElementById('doc_category_id').value;
        const sel = document.getElementById('doc_parent_id');
        const current = sel.value;
        sel.innerHTML = '<option value="">— بدون —</option>';
        parentPages.filter(function (p) {
            return String(p.category_id) === String(catId);
        }).forEach(function (p) {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = p.label;
            sel.appendChild(o);
        });
        if (current) {
            sel.value = current;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshParentOptions();
        document.getElementById('doc_category_id').addEventListener('change', refreshParentOptions);
    });

    function slugify(t) {
        if (!t) return '';
        return t.toString().trim()
            .replace(/\s+/g, '-')
            .replace(/[^\u0600-\u06FFa-zA-Z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '') || ('page-' + Date.now());
    }

    document.addEventListener('DOMContentLoaded', function () {
        var titleEl = document.getElementById('doc_title');
        var slugEl = document.getElementById('doc_slug');
        var btn = document.getElementById('doc_generate_slug');
        if (titleEl && slugEl) {
            titleEl.addEventListener('input', function () {
                if (!slugEl.dataset.touched) slugEl.value = slugify(this.value);
            });
            slugEl.addEventListener('input', function () { this.dataset.touched = '1'; });
        }
        if (btn && titleEl && slugEl) {
            btn.addEventListener('click', function () {
                slugEl.value = slugify(titleEl.value);
                delete slugEl.dataset.touched;
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('generateBtn').addEventListener('click', function () {
            const topic = document.getElementById('topic').value.trim();
            const cat = document.getElementById('doc_category_id').value;
            if (!topic) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'تنبيه', text: 'يرجى إدخال الموضوع' });
                } else {
                    alert('يرجى إدخال الموضوع');
                }
                return;
            }
            if (!cat) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'تنبيه', text: 'يرجى اختيار قسم التوثيق أولاً' });
                } else {
                    alert('يرجى اختيار قسم التوثيق');
                }
                return;
            }

            const btn = this;
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.loading-spinner');
            btn.disabled = true;
            btnText.textContent = 'جاري التوليد...';
            spinner.classList.add('active');

            const payload = {
                topic: topic,
                ai_model_id: document.getElementById('ai_model_id').value || null,
                content_length: document.getElementById('content_length').value,
                tone: document.getElementById('tone').value,
                language: document.getElementById('language').value,
                documentation_category_id: cat,
                parent_id: document.getElementById('doc_parent_id').value || null,
                generate_meta: document.getElementById('generate_meta').checked,
                _token: '{{ csrf_token() }}'
            };

            fetch(@json(route('admin.docs.ai-pages.generate')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.data) {
                    const d = data.data;
                    if (d.title) document.getElementById('doc_title').value = d.title;
                    if (d.slug) {
                        document.getElementById('doc_slug').value = d.slug;
                        document.getElementById('doc_slug').dataset.touched = '1';
                    }
                    if (d.excerpt) document.getElementById('doc_excerpt').value = d.excerpt;
                    if (d.meta_title) document.getElementById('meta_title').value = d.meta_title;
                    if (d.meta_description) document.getElementById('meta_description').value = d.meta_description;
                    if (d.content) {
                        const ed = tinymce.get('doc_content');
                        if (ed) ed.setContent(d.content);
                        else document.getElementById('doc_content').value = d.content;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'تم التوليد', text: 'راجع المحتوى ثم احفظ.', timer: 2800 });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: data.message || 'فشل التوليد' });
                    } else {
                        alert(data.message || 'فشل التوليد');
                    }
                }
            })
            .catch(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الاتصال بالخادم' });
                } else {
                    alert('فشل الاتصال');
                }
            })
            .finally(function () {
                btn.disabled = false;
                btnText.textContent = 'توليد المحتوى';
                spinner.classList.remove('active');
            });
        });
    });
})();
</script>
@endsection
