@extends('admin.layouts.master')

@section('page-title')
    إضافة درس جديد
@stop

@section('css')
<style>
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        border: 2px solid #e9ecef;
    }
    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }
    .section-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 1rem;
        font-size: 1.2rem;
    }
    .content-type-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .type-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .type-card:hover {
        border-color: #667eea;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.15);
    }
    .type-card.active {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }
    .type-card i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: #667eea;
    }
    .type-card h6 {
        margin: 0;
        font-weight: 600;
    }
    .content-area {
        display: none;
    }
    .content-area.active {
        display: block;
    }
    .sticky-actions {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 1.5rem;
        border-top: 2px solid #e9ecef;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.05);
        z-index: 100;
    }
    .video-preview {
        width: 100%;
        max-height: 400px;
        border-radius: 8px;
        margin-top: 1rem;
    }
    .file-upload-area {
        border: 2px dashed #667eea;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .file-upload-area:hover {
        background: rgba(102, 126, 234, 0.05);
    }
    .file-upload-area i {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 1rem;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إضافة درس جديد</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            @if($module && $module->section && $module->section->course)
                                <li class="breadcrumb-item"><a href="{{ route('courses.show', $module->section->course_id) }}">{{ $module->section->course->title }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('lessons.index', ['module' => $module->id]) }}">{{ $module->title }}</a></li>
                            @endif
                            <li class="breadcrumb-item active">إضافة درس</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form action="{{ route('lessons.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module_id" value="{{ $module->id }}">

                <!-- Basic Information -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h5 class="mb-0">المعلومات الأساسية</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">عنوان الدرس <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                   placeholder="مثال: مقدمة في البرمجة">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">المدة (بالدقائق)</label>
                            <input type="number" name="duration" class="form-control" min="1"
                                   placeholder="30">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">وصف مختصر</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="اكتب وصفاً مختصراً للدرس..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Content Type Selection -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h5 class="mb-0">نوع المحتوى <span class="text-danger">*</span></h5>
                    </div>

                    <div class="content-type-selector">
                        <div class="type-card" onclick="selectType('video')">
                            <i class="fas fa-video"></i>
                            <h6>فيديو</h6>
                            <small class="text-muted">رفع فيديو أو رابط يوتيوب</small>
                        </div>
                        <div class="type-card" onclick="selectType('reading')">
                            <i class="fas fa-book-open"></i>
                            <h6>قراءة</h6>
                            <small class="text-muted">محتوى نصي</small>
                        </div>
                        <div class="type-card" onclick="selectType('file')">
                            <i class="fas fa-file-download"></i>
                            <h6>ملف</h6>
                            <small class="text-muted">PDF أو ملفات أخرى</small>
                        </div>
                        <div class="type-card" onclick="selectType('quiz')">
                            <i class="fas fa-question-circle"></i>
                            <h6>اختبار</h6>
                            <small class="text-muted">اختبار تفاعلي</small>
                        </div>
                    </div>

                    <input type="hidden" name="type" id="contentType" required>

                    <!-- Video Content -->
                    <div id="videoContent" class="content-area">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">مصدر الفيديو</label>
                                <select name="video_source" class="form-select" onchange="toggleVideoSource(this.value)">
                                    <option value="upload">رفع فيديو</option>
                                    <option value="youtube">رابط يوتيوب</option>
                                    <option value="vimeo">رابط Vimeo</option>
                                    <option value="url">رابط مباشر</option>
                                </select>
                            </div>
                        </div>

                        <div id="uploadVideoArea">
                            <label class="form-label">رفع فيديو</label>
                            <div class="file-upload-area" onclick="document.getElementById('videoFile').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <h6>اضغط أو اسحب الفيديو هنا</h6>
                                <small class="text-muted">MP4, MOV, AVI (الحد الأقصى: 500MB)</small>
                            </div>
                            <input type="file" id="videoFile" name="video_file" accept="video/*" class="d-none" onchange="previewVideo(this)">
                            <video id="videoPreview" class="video-preview" controls style="display: none;"></video>
                        </div>

                        <div id="videoUrlArea" style="display: none;">
                            <label class="form-label">رابط الفيديو</label>
                            <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                        </div>
                    </div>

                    <!-- Reading Content -->
                    <div id="readingContent" class="content-area">
                        <label class="form-label">المحتوى</label>
                        <textarea name="content" id="editor" class="form-control" rows="15"></textarea>
                    </div>

                    <!-- File Content -->
                    <div id="fileContent" class="content-area">
                        <label class="form-label">رفع ملف</label>
                        <div class="file-upload-area" onclick="document.getElementById('lessonFile').click()">
                            <i class="fas fa-file-upload"></i>
                            <h6>اضغط أو اسحب الملف هنا</h6>
                            <small class="text-muted">PDF, DOC, DOCX, PPT, PPTX (الحد الأقصى: 50MB)</small>
                        </div>
                        <input type="file" id="lessonFile" name="file" class="d-none" onchange="showFileName(this)">
                        <div id="fileInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-file me-2"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn-close float-end" onclick="clearFile()"></button>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz Content -->
                    <div id="quizContent" class="content-area">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            سيتم توجيهك لصفحة إنشاء الاختبار بعد حفظ الدرس
                        </div>
                    </div>
                </div>

                <!-- Additional Settings -->
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5 class="mb-0">إعدادات إضافية</h5>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_free" id="isFree">
                                <label class="form-check-label" for="isFree">
                                    درس مجاني (متاح للجميع)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_visible" id="isVisible" checked>
                                <label class="form-check-label" for="isVisible">
                                    مرئي للطلاب
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="allow_download" id="allowDownload">
                                <label class="form-check-label" for="allowDownload">
                                    السماح بالتحميل
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Action Buttons -->
                <div class="sticky-actions">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('lessons.index', $module->id) }}" class="btn btn-light">
                            <i class="fas fa-arrow-right me-2"></i>إلغاء
                        </a>
                        <div>
                            <button type="submit" name="action" value="save" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-save me-2"></i>حفظ
                            </button>
                            <button type="submit" name="action" value="save_and_new" class="btn btn-success btn-lg">
                                <i class="fas fa-plus me-2"></i>حفظ وإضافة آخر
                            </button>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop

@section('script')
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    // Initialize CKEditor
    CKEDITOR.replace('editor', {
        language: 'ar',
        height: 400
    });

    // Select Content Type
    function selectType(type) {
        // Update type cards
        document.querySelectorAll('.type-card').forEach(card => card.classList.remove('active'));
        event.currentTarget.classList.add('active');

        // Update hidden input
        document.getElementById('contentType').value = type;

        // Show/hide content areas
        document.querySelectorAll('.content-area').forEach(area => area.classList.remove('active'));
        document.getElementById(type + 'Content').classList.add('active');
    }

    // Toggle Video Source
    function toggleVideoSource(source) {
        const uploadArea = document.getElementById('uploadVideoArea');
        const urlArea = document.getElementById('videoUrlArea');

        if (source === 'upload') {
            uploadArea.style.display = 'block';
            urlArea.style.display = 'none';
        } else {
            uploadArea.style.display = 'none';
            urlArea.style.display = 'block';
        }
    }

    // Preview Video
    function previewVideo(input) {
        const preview = document.getElementById('videoPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Show File Name
    function showFileName(input) {
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        if (input.files && input.files[0]) {
            fileName.textContent = input.files[0].name;
            fileInfo.style.display = 'block';
        }
    }

    // Clear File
    function clearFile() {
        document.getElementById('lessonFile').value = '';
        document.getElementById('fileInfo').style.display = 'none';
    }
</script>
@stop
