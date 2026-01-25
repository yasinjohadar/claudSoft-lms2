@extends('admin.layouts.master')

@section('page-title')
    إضافة قالب بريد إلكتروني جديد
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">
                    <i class="fas fa-plus me-2"></i>
                    إضافة قالب بريد إلكتروني جديد
                </h5>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>
                    رجوع
                </a>
            </div>
        </div>

        @include('admin.components.alerts')

        <div class="row">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <form action="{{ route('admin.email-templates.store') }}" method="POST" id="templateForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">اسم القالب (إنجليزي)</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">اسم القالب (عربي)</label>
                                    <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" 
                                           value="{{ old('name_ar') }}">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label required">موضوع البريد</label>
                                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                           value="{{ old('subject') }}" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">يمكنك استخدام المتغيرات مثل: @{{student_name}}, @{{group_name}}</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required">نوع القالب</label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="registration_welcome" {{ old('type') == 'registration_welcome' ? 'selected' : '' }}>
                                            ترحيب بالتسجيل
                                        </option>
                                        <option value="enrollment_confirmation" {{ old('type') == 'enrollment_confirmation' ? 'selected' : '' }}>
                                            تأكيد التسجيل
                                        </option>
                                        <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>
                                            مخصص
                                        </option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- المتغيرات المتاحة -->
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">
                                        <i class="fas fa-code me-2"></i>
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
                                <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror" rows="15">{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>تفعيل القالب</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    حفظ القالب
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

    // إدراج المتغيرات
    document.querySelectorAll('.insert-variable').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const varName = this.getAttribute('data-variable');
            const variable = '{' + '{' + varName + '}' + '}';
            const editor = tinymce.get('body');
            const subjectInput = document.querySelector('input[name="subject"]');
            
            // إذا كان التركيز على حقل الموضوع
            if (document.activeElement === subjectInput) {
                const start = subjectInput.selectionStart;
                const end = subjectInput.selectionEnd;
                subjectInput.value = subjectInput.value.substring(0, start) + variable + subjectInput.value.substring(end);
                subjectInput.setSelectionRange(start + variable.length, start + variable.length);
            } else if (editor) {
                // إدراج في محرر TinyMCE
                editor.insertContent(variable);
            }
        });
    });

    // معالج إرسال النموذج
    const templateForm = document.getElementById('templateForm');
    if (templateForm) {
        templateForm.addEventListener('submit', function(e) {
            const editor = tinymce.get('body');
            if (editor) {
                // حفظ محتوى TinyMCE في textarea المخفي
                editor.save();
                
                // التحقق من أن المحتوى غير فارغ
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
