@extends('admin.layouts.master')

@section('page-title')
    تعديل قالب بريد إلكتروني
@stop

@section('content')
    <div class="main-content app-content admin-email-templates-page">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.email-templates.index') }}">قوالب البريد</a></li>
                        <li class="breadcrumb-item active">تعديل القالب</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start gap-3">
                            <span class="admin-group-form-page__icon"><i class="fe fe-edit-2"></i></span>
                            <div>
                                <span class="group-show-hero__eyebrow">
                                    <i class="fe fe-file-text me-1"></i>
                                    تعديل القالب
                                </span>
                                <h2 class="group-show-hero__title mb-2">{{ $emailTemplate->name_ar ?: $emailTemplate->name }}</h2>
                                <p class="group-show-hero__desc mb-0">عدّل بيانات القالب والمحتوى ثم احفظ التغييرات.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('admin.email-templates.show', $emailTemplate) }}" class="group-show-action group-show-action--info">
                                <span class="group-show-action__icon"><i class="fe fe-eye"></i></span>
                                <span class="group-show-action__text">عرض القالب</span>
                            </a>
                            <a href="{{ route('admin.email-templates.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                                <span class="group-show-action__text">رجوع للقائمة</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">بيانات القالب</h4>
                    <p class="fs-12 text-muted mb-0">عدّل الاسم والموضوع والمحتوى، ثم فعّل أو عطّل القالب.</p>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ route('admin.email-templates.update', $emailTemplate) }}" method="POST" id="templateForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">اسم القالب (إنجليزي)</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $emailTemplate->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">اسم القالب (عربي)</label>
                                <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                                       value="{{ old('name_ar', $emailTemplate->name_ar) }}">
                                @error('name_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label required">موضوع البريد</label>
                                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject', $emailTemplate->subject) }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">يمكنك استخدام المتغيرات مثل: @{{student_name}}, @{{group_name}}</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">نوع القالب</label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="registration_welcome" {{ old('type', $emailTemplate->type) == 'registration_welcome' ? 'selected' : '' }}>ترحيب بالتسجيل</option>
                                    <option value="enrollment_confirmation" {{ old('type', $emailTemplate->type) == 'enrollment_confirmation' ? 'selected' : '' }}>تأكيد التسجيل</option>
                                    <option value="custom" {{ old('type', $emailTemplate->type) == 'custom' ? 'selected' : '' }}>مخصص</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="card bg-light mb-3 border-0 admin-email-templates-page__variables-card">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="fe fe-code me-2"></i>
                                    المتغيرات المتاحة:
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-2 insert-variable" data-variable="student_name">
                                            @{{student_name}}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-2 insert-variable" data-variable="student_name_en">
                                            @{{student_name_en}}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-2 insert-variable" data-variable="group_name">
                                            @{{group_name}}
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-2 insert-variable" data-variable="email">
                                            @{{email}}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary mb-2 insert-variable" data-variable="phone">
                                            @{{phone}}
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">اضغط على المتغير لإدراجه في الموضوع أو المحتوى</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">محتوى البريد</label>
                            <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror" rows="15">{{ old('body', $emailTemplate->body) }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                       {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>تفعيل القالب</strong>
                                </label>
                            </div>
                        </div>

                        <div class="admin-email-templates-page__form-actions d-flex flex-wrap gap-2 justify-content-md-end mt-4 pt-3 border-top">
                            <a href="{{ route('admin.email-templates.index') }}" class="btn btn-light">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i>حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@stop

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" rel="preload" as="script">
<style>
    .required::after {
        content: " *";
        color: red;
    }
</style>
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
function initTinyMCE() {
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE failed to load');
        setTimeout(initTinyMCE, 100);
        return;
    }

    tinymce.init({
        selector: '#body',
        height: 500,
        directionality: 'rtl',
        language: 'ar',
        language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
        promotion: false,
        branding: false,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount emoticons directionality',
        toolbar: 'undo redo | blocks | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link image media table | code | fullscreen | help',
        menubar: 'file edit view insert format tools table help',
        menu: {
            file: { title: 'ملف', items: 'newdocument restoredraft | preview | print' },
            edit: { title: 'تحرير', items: 'undo redo | cut copy paste | selectall | searchreplace' },
            view: { title: 'عرض', items: 'code | visualaid visualchars visualblocks | preview fullscreen' },
            insert: { title: 'إدراج', items: 'image link media | charmap emoticons hr | pagebreak nonbreaking anchor | insertdatetime' },
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
        setup: function(editor) {
            editor.on('init', function() {
                console.log('TinyMCE initialized successfully');
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initTinyMCE, 200);

    document.querySelectorAll('.insert-variable').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const varName = this.getAttribute('data-variable');
            const variable = '{' + '{' + varName + '}' + '}';
            const editor = tinymce.get('body');
            const subjectInput = document.querySelector('input[name="subject"]');

            if (document.activeElement === subjectInput) {
                const start = subjectInput.selectionStart;
                const end = subjectInput.selectionEnd;
                subjectInput.value = subjectInput.value.substring(0, start) + variable + subjectInput.value.substring(end);
                subjectInput.setSelectionRange(start + variable.length, start + variable.length);
            } else if (editor) {
                editor.insertContent(variable);
            }
        });
    });

    const templateForm = document.getElementById('templateForm');
    if (templateForm) {
        templateForm.addEventListener('submit', function(e) {
            const editor = tinymce.get('body');
            if (editor) {
                editor.save();

                const bodyContent = document.getElementById('body').value.trim();
                if (!bodyContent) {
                    e.preventDefault();
                    alert('يرجى إدخال محتوى البريد الإلكتروني');
                    editor.focus();
                    return false;
                }
            }
        });
    }
});
</script>
@stop
