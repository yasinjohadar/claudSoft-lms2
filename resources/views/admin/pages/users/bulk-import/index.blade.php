@extends('admin.layouts.master')

@section('page-title')
    رفع مستخدمين من Excel
@stop

@section('css')
    <style>
        .upload-card {
            margin-top: 30px;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #007bff;
            background: #e7f3ff;
        }
        .upload-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .file-info {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .instructions-card {
            margin-top: 20px;
        }
        .instruction-item {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .instruction-item:last-child {
            border-bottom: none;
        }
    </style>
@stop

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">رفع مستخدمين من ملف Excel</h1>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                            <li class="breadcrumb-item active" aria-current="page">رفع جماعي</li>
                        </ol>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>نجح!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>خطأ!</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>خطأ!</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-xl-8">
                        <!-- Upload Card -->
                        <div class="card upload-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">رفع الملف</h3>
                                <a href="{{ route('users.bulk-import.template') }}" class="btn btn-success btn-sm">
                                    <i class="fa fa-download me-2"></i>تحميل القالب
                                </a>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('users.bulk-import.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                                    @csrf

                                    <div class="upload-area" id="uploadArea">
                                        <div class="upload-icon">
                                            <i class="fa fa-cloud-upload-alt"></i>
                                        </div>
                                        <h4>اسحب وأفلت الملف هنا أو اضغط للاختيار</h4>
                                        <p class="text-muted">الصيغ المدعومة: Excel (.xlsx, .xls) أو CSV</p>
                                        <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv" required>
                                        <button type="button" class="btn btn-primary mt-3" onclick="document.getElementById('fileInput').click()">
                                            <i class="fa fa-folder-open me-2"></i>اختر ملف
                                        </button>
                                    </div>

                                    <div class="file-info d-none" id="fileInfo">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fa fa-file-excel text-success fa-2x me-3"></i>
                                                <strong id="fileName"></strong>
                                                <br>
                                                <small class="text-muted" id="fileSize"></small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeFile()">
                                                <i class="fa fa-times"></i> إزالة
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fa fa-info-circle me-1"></i>
                                            الحد الأقصى لحجم الملف: 10 MB
                                        </small>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                                            <i class="fa fa-upload me-2"></i>رفع الملف والمتابعة
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <!-- Instructions Card -->
                        <div class="card instructions-card">
                            <div class="card-header">
                                <h3 class="card-title">تعليمات الرفع</h3>
                            </div>
                            <div class="card-body">
                                <div class="instruction-item">
                                    <i class="fa fa-download text-success me-2"></i>
                                    <strong>قم بتحميل القالب</strong>
                                    <p class="text-muted mb-0">استخدم الزر أعلاه لتحميل قالب Excel الجاهز</p>
                                </div>

                                <div class="instruction-item">
                                    <i class="fa fa-edit text-primary me-2"></i>
                                    <strong>املأ البيانات</strong>
                                    <p class="text-muted mb-0">أدخل بيانات الطلاب في القالب حسب الأعمدة المحددة</p>
                                </div>

                                <div class="instruction-item">
                                    <i class="fa fa-upload text-info me-2"></i>
                                    <strong>ارفع الملف</strong>
                                    <p class="text-muted mb-0">ارفع الملف المعبأ باستخدام النموذج</p>
                                </div>

                                <div class="instruction-item">
                                    <i class="fa fa-check-circle text-success me-2"></i>
                                    <strong>راجع وأكد</strong>
                                    <p class="text-muted mb-0">راجع البيانات قبل البدء بالمعالجة</p>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="card mt-3">
                            <div class="card-header bg-warning-transparent">
                                <h3 class="card-title">ملاحظات مهمة</h3>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li class="mb-2">البريد الإلكتروني يجب أن يكون فريداً</li>
                                    <li class="mb-2">رقم الهاتف يكتب كاملاً بدون + أو صفر</li>
                                    <li class="mb-2">سيتم تحديث بيانات المستخدمين الموجودين</li>
                                    <li class="mb-2">يمكن رفع 500-1000 طالب في المرة الواحدة</li>
                                    <li class="mb-0">تأكد من مطابقة أسماء الكورسات والمجموعات</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop

@section('script')
    <script>
        const fileInput = document.getElementById('fileInput');
        const uploadArea = document.getElementById('uploadArea');
        const fileInfo = document.getElementById('fileInfo');
        const submitBtn = document.getElementById('submitBtn');

        // File input change
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                displayFileInfo(this.files[0]);
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#007bff';
            this.style.background = '#e7f3ff';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#dee2e6';
            this.style.background = '#f8f9fa';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#dee2e6';
            this.style.background = '#f8f9fa';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                   'application/vnd.ms-excel',
                                   'text/csv'];

                if (validTypes.includes(file.type)) {
                    fileInput.files = files;
                    displayFileInfo(file);
                } else {
                    alert('الرجاء اختيار ملف Excel أو CSV');
                }
            }
        });

        function displayFileInfo(file) {
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);

            fileInfo.classList.remove('d-none');
            submitBtn.disabled = false;
        }

        function removeFile() {
            fileInput.value = '';
            fileInfo.classList.add('d-none');
            submitBtn.disabled = true;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>
@stop
