@extends('admin.layouts.master')

@section('page-title')
    تسجيل جماعي - {{ $course->title }}
@stop

@push('head')
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
@endpush

@section('css')
<style data-version="3.0">
    /* Enhanced Design v3.0 - Cache Busted {{ now()->timestamp }} */
    .upload-area {
        border: 3px dashed #ddd;
        border-radius: 16px;
        padding: 3rem;
        text-align: center;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.02) 0%, rgba(118, 75, 162, 0.02) 100%);
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .upload-area::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .upload-area:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        transform: translateY(-3px);
    }

    .upload-area:hover::before {
        opacity: 1;
    }

    .upload-area.dragging {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.25);
        transform: scale(1.02);
    }

    .upload-area.dragging::before {
        opacity: 1;
    }

    .step-indicator-wrapper {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }

    .step-indicator-wrapper:hover {
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.1);
        border-color: rgba(102, 126, 234, 0.3);
    }

    .step-indicator {
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-weight: 800;
        font-size: 1.5rem;
        position: relative;
        z-index: 2;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .step.active .step-circle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        transform: scale(1.1);
    }

    .step.completed .step-circle {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    }

    .step-label {
        font-weight: 700;
        font-size: 0.95rem;
        color: #6c757d;
        transition: all 0.3s;
    }

    .step.active .step-label {
        color: #667eea;
        font-size: 1rem;
    }

    .step::before {
        content: '';
        position: absolute;
        top: 30px;
        left: 50%;
        right: -50%;
        height: 4px;
        background: #e9ecef;
        z-index: 1;
        border-radius: 2px;
        transition: all 0.3s;
    }

    .step:last-child::before {
        display: none;
    }

    .step.completed::before {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .template-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .template-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: scale(0);
        transition: transform 0.6s ease-out;
    }

    .template-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
    }

    .template-card:hover::before {
        transform: scale(1);
    }

    .template-icon {
        width: 70px;
        height: 70px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .template-card:hover .template-icon {
        transform: scale(1.1) rotate(5deg);
        background: rgba(255, 255, 255, 0.3);
    }

    .template-card h5 {
        font-weight: 800;
        margin-bottom: 0.5rem;
        font-size: 1.4rem;
    }

    .template-card p {
        opacity: 0.95;
        margin-bottom: 1.5rem;
        font-size: 1rem;
    }

    .info-card {
        border-radius: 16px;
        border: 2px solid #e9ecef;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        position: relative;
        overflow: hidden;
    }

    .info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .info-card:hover {
        border-color: #667eea;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        transform: translateY(-3px);
    }

    .info-card:hover::before {
        opacity: 1;
    }

    .upload-placeholder-icon {
        transition: all 0.4s ease;
    }

    .upload-area:hover .upload-placeholder-icon {
        transform: translateY(-10px) scale(1.1);
        color: #667eea !important;
    }

    .btn-download-template {
        background: white;
        color: #667eea;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.05rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .btn-download-template::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.1);
        transform: translate(-50%, -50%);
        transition: width 0.4s, height 0.4s;
    }

    .btn-download-template:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .btn-download-template:hover::before {
        width: 300px;
        height: 300px;
    }

    .example-table-card {
        border-radius: 16px;
        border: 2px solid #e9ecef;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        position: relative;
        overflow: hidden;
    }

    .example-table-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .example-table-card:hover {
        border-color: #667eea;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
    }

    .example-table-card:hover::before {
        opacity: 1;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تسجيل جماعي من Excel/CSV</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $course->id) }}">{{ $course->title }}</a></li>
                            <li class="breadcrumb-item active">تسجيل جماعي</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator-wrapper">
                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-circle">1</div>
                        <div class="step-label">تحميل القالب</div>
                    </div>
                    <div class="step">
                        <div class="step-circle">2</div>
                        <div class="step-label">رفع الملف</div>
                    </div>
                    <div class="step">
                        <div class="step-circle">3</div>
                        <div class="step-label">المعاينة</div>
                    </div>
                    <div class="step">
                        <div class="step-circle">4</div>
                        <div class="step-label">التنفيذ</div>
                    </div>
                </div>
            </div>

            <!-- Template Download Card -->
            <div class="template-card">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="template-icon">
                            <i class="fas fa-download"></i>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h5 class="mb-2">الخطوة 1: تحميل قالب Excel</h5>
                        <p class="mb-0">
                            قم بتحميل القالب الجاهز، املأه ببيانات الطلاب، ثم ارفعه في الخطوة التالية
                        </p>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="{{ route('courses.enrollments.download-template') }}" class="btn btn-download-template">
                            <i class="fas fa-file-excel me-2"></i>تحميل القالب
                        </a>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card custom-card info-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        تعليمات مهمة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">
                                <i class="fas fa-check-circle me-2"></i>افعل:
                            </h6>
                            <ul class="mb-0">
                                <li>استخدم القالب المقدم فقط</li>
                                <li>تأكد من صحة عناوين البريد الإلكتروني</li>
                                <li>احفظ الملف بصيغة .xlsx أو .csv</li>
                                <li>تأكد من عدم وجود صفوف فارغة</li>
                                <li>استخدم الأرقام الطلابية الموجودة في النظام</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger">
                                <i class="fas fa-times-circle me-2"></i>لا تفعل:
                            </h6>
                            <ul class="mb-0">
                                <li>لا تغير أسماء الأعمدة</li>
                                <li>لا تحذف صف العناوين</li>
                                <li>لا تضف أعمدة إضافية</li>
                                <li>لا ترفع ملفات بحجم أكبر من 5MB</li>
                                <li>لا تستخدم أحرف خاصة في الأسماء</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card custom-card info-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-upload me-2"></i>
                        الخطوة 2: رفع ملف Excel
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('courses.enrollments.bulk.process', $course->id) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          id="bulkUploadForm">
                        @csrf

                        <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
                            <input type="file"
                                   name="file"
                                   id="fileInput"
                                   class="d-none"
                                   accept=".xlsx,.xls,.csv"
                                   required>

                            <div id="uploadPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-5x text-muted mb-3 opacity-50 upload-placeholder-icon"></i>
                                <h5 class="mb-2">اسحب الملف وأفلته هنا</h5>
                                <p class="text-muted mb-3">أو انقر لاختيار الملف من جهازك</p>
                                <p class="text-muted mb-0">
                                    <small>الصيغ المدعومة: .xlsx, .xls, .csv (حد أقصى: 5MB)</small>
                                </p>
                            </div>

                            <div id="uploadPreview" class="d-none">
                                <i class="fas fa-file-excel fa-5x text-success mb-3"></i>
                                <h5 id="fileName" class="mb-2"></h5>
                                <p id="fileSize" class="text-muted mb-3"></p>
                                <button type="button" class="btn btn-outline-danger" onclick="clearFile()">
                                    <i class="fas fa-times me-2"></i>إزالة الملف
                                </button>
                            </div>
                        </div>

                        @error('file')
                            <div class="alert alert-danger mt-3">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                            </div>
                        @enderror

                        <div class="mt-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="send_notifications" id="sendNotifications" checked>
                                <label class="form-check-label" for="sendNotifications">
                                    إرسال إشعارات البريد الإلكتروني للطلاب
                                </label>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skipDuplicates" checked>
                                <label class="form-check-label" for="skipDuplicates">
                                    تخطي الطلاب المسجلين مسبقاً
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('courses.enrollments.index', $course->id) }}" class="btn btn-light">
                                <i class="fas fa-arrow-right me-2"></i>رجوع
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-upload me-2"></i>رفع ومعاينة
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Example Data -->
            <div class="card custom-card example-table-card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        مثال على البيانات المطلوبة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>student_id</th>
                                    <th>email</th>
                                    <th>name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ST001</td>
                                    <td>student1@example.com</td>
                                    <td>أحمد محمد</td>
                                </tr>
                                <tr>
                                    <td>ST002</td>
                                    <td>student2@example.com</td>
                                    <td>فاطمة علي</td>
                                </tr>
                                <tr>
                                    <td>ST003</td>
                                    <td>student3@example.com</td>
                                    <td>محمود حسن</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>ملاحظة:</strong> يمكنك استخدام أي من الحقول الثلاثة للتعرف على الطالب (ID، البريد، أو الرقم الطلابي)
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const uploadPreview = document.getElementById('uploadPreview');
    const submitBtn = document.getElementById('submitBtn');

    // Drag & Drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.add('dragging');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
            uploadArea.classList.remove('dragging');
        });
    });

    uploadArea.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    });

    // File Input Change
    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        const file = fileInput.files[0];
        if (file) {
            // Validate file type
            const validTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'text/csv'
            ];

            if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/)) {
                alert('نوع الملف غير مدعوم. يرجى رفع ملف Excel أو CSV');
                clearFile();
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الملف كبير جداً. الحد الأقصى 5MB');
                clearFile();
                return;
            }

            // Show preview
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            uploadPlaceholder.classList.add('d-none');
            uploadPreview.classList.remove('d-none');
            submitBtn.disabled = false;
        }
    }

    function clearFile() {
        fileInput.value = '';
        uploadPlaceholder.classList.remove('d-none');
        uploadPreview.classList.add('d-none');
        submitBtn.disabled = true;
        event.stopPropagation();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Form Submit
    document.getElementById('bulkUploadForm').addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الرفع...';
    });
</script>
@stop
