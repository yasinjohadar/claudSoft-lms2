@extends('admin.layouts.master')

@section('page-title')
    استيراد الأسئلة من Excel
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        
        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">استيراد الأسئلة من Excel</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                        <li class="breadcrumb-item active">استيراد من Excel</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('question-bank.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-right me-2"></i>رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-file-excel me-2"></i>
                            رفع ملف Excel
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Upload Section -->
                        <div id="upload-section">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>تعليمات:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>يجب أن يكون الملف بصيغة Excel (.xlsx أو .xls)</li>
                                    <li>حجم الملف يجب أن يكون أقل من 10 ميجابايت</li>
                                    <li><strong class="text-danger">اسم الكورس مطلوب</strong> - يجب كتابة اسم الكورس في عمود "الكورس"</li>
                                    <li>اسم الكورس يجب أن يطابق اسم كورس موجود في النظام</li>
                                    <li>سيتم عرض معاينة للبيانات قبل الاستيراد</li>
                                    <li>يمكنك تحميل ملف قالب Excel كمرجع</li>
                                </ul>
                            </div>

                            <div class="text-center mb-4">
                                <a href="{{ route('question-bank.export.template') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-download me-2"></i>تحميل ملف القالب
                                </a>
                            </div>

                            <form id="upload-form" enctype="multipart/form-data" onsubmit="return false;">
                                @csrf
                                <div class="mb-3">
                                    <label for="excel_file" class="form-label">اختر ملف Excel <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                                    <div class="form-text">الصيغ المدعومة: .xlsx, .xls</div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-light" onclick="window.location.href='{{ route('question-bank.index') }}'">
                                        <i class="fas fa-times me-2"></i>إلغاء
                                    </button>
                                    <button type="button" class="btn btn-primary" id="preview-btn">
                                        <i class="fas fa-eye me-2"></i>معاينة البيانات
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Preview Section (Hidden initially) -->
                        <div id="preview-section" style="display: none;">
                            <div class="alert alert-warning" id="preview-alert" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="preview-alert-text"></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">معاينة البيانات</h6>
                                <div>
                                    <span class="badge bg-success" id="valid-rows-badge">0 صحيح</span>
                                    <span class="badge bg-danger" id="error-rows-badge">0 خطأ</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="preview-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">#</th>
                                            <th>نوع السؤال</th>
                                            <th>نص السؤال</th>
                                            <th>الخيارات</th>
                                            <th>الإجابة الصحيحة</th>
                                            <th>الدرجة</th>
                                            <th>الصعوبة</th>
                                            <th>الكورس</th>
                                            <th width="100">الحالة</th>
                                        </tr>
                                    </thead>
                                    <tbody id="preview-tbody">
                                        <!-- Data will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-light" onclick="resetForm()">
                                    <i class="fas fa-redo me-2"></i>اختيار ملف آخر
                                </button>
                                <div>
                                    <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                        <i class="fas fa-times me-2"></i>إلغاء
                                    </button>
                                    <button type="button" class="btn btn-success" id="import-btn" onclick="processImport()">
                                        <i class="fas fa-upload me-2"></i>استيراد البيانات
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Spinner -->
                        <div id="loading-spinner" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">جاري التحميل...</span>
                            </div>
                            <p class="mt-3 text-muted">جاري معالجة الملف...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .preview-row-error {
        background-color: #fff5f5 !important;
    }
    .preview-row-valid {
        background-color: #f0fff4 !important;
    }
    .status-badge {
        font-size: 0.75rem;
    }
</style>
@endpush

@section('scripts')
<script>
let previewData = [];
let excelFile = null;

// Handle preview button click
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('preview-btn');
    const fileInput = document.getElementById('excel_file');
    
    if (previewBtn) {
        previewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!fileInput || !fileInput.files.length) {
                alert('يرجى اختيار ملف Excel');
                return false;
            }

            excelFile = fileInput.files[0];
            
            // Show loading
            document.getElementById('upload-section').style.display = 'none';
            document.getElementById('preview-section').style.display = 'none';
            document.getElementById('loading-spinner').style.display = 'block';

            // Create FormData
            const formData = new FormData();
            formData.append('excel_file', excelFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("question-bank.import.preview") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Non-JSON response:', text);
                        throw new Error('الخادم لم يرجع بيانات JSON');
                    });
                }
                return response.json();
            })
            .then(result => {
                document.getElementById('loading-spinner').style.display = 'none';

                if (result.success) {
                    previewData = result.data;
                    displayPreview(result);
                } else {
                    let errorMsg = 'حدث خطأ';
                    if (result.errors) {
                        errorMsg += ': ' + Object.values(result.errors).flat().join(', ');
                    } else if (result.message) {
                        errorMsg += ': ' + result.message;
                    }
                    alert(errorMsg);
                    resetForm();
                }
            })
            .catch(error => {
                document.getElementById('loading-spinner').style.display = 'none';
                console.error('Error:', error);
                alert('حدث خطأ أثناء معالجة الملف: ' + error.message);
                resetForm();
            });
            
            return false;
        });
    }
});

function displayPreview(result) {
    const tbody = document.getElementById('preview-tbody');
    tbody.innerHTML = '';

    // Update badges
    document.getElementById('valid-rows-badge').textContent = result.valid_rows + ' صحيح';
    document.getElementById('error-rows-badge').textContent = result.errors.length + ' خطأ';

    // Show alert if there are errors
    if (result.errors.length > 0) {
        document.getElementById('preview-alert').style.display = 'block';
        document.getElementById('preview-alert-text').textContent = 
            'تم اكتشاف ' + result.errors.length + ' سطر به أخطاء. سيتم تخطيها أثناء الاستيراد.';
    } else {
        document.getElementById('preview-alert').style.display = 'none';
    }

    // Display data
    result.data.forEach((row, index) => {
        const hasError = result.errors.some(e => e.row === row.row_number);
        const tr = document.createElement('tr');
        tr.className = hasError ? 'preview-row-error' : 'preview-row-valid';

        // Get options text
        const options = [];
        for (let i = 1; i <= 4; i++) {
            if (row['option_' + i]) {
                options.push(`${i}. ${row['option_' + i]}`);
            }
        }

        tr.innerHTML = `
            <td>${row.row_number}</td>
            <td>${row.question_type || '<span class="text-danger">مفقود</span>'}</td>
            <td>${row.question_text ? (row.question_text.length > 50 ? row.question_text.substring(0, 50) + '...' : row.question_text) : '<span class="text-danger">مفقود</span>'}</td>
            <td>${options.length > 0 ? options.join('<br>') : '<span class="text-muted">لا توجد</span>'}</td>
            <td>${row.correct_answer || '<span class="text-danger">مفقود</span>'}</td>
            <td>${row.points || '1'}</td>
            <td>${row.difficulty || 'medium'}</td>
            <td>${row.course ? row.course : '<span class="text-danger">مطلوب</span>'}</td>
            <td>
                ${hasError ? 
                    '<span class="badge bg-danger status-badge">خطأ</span>' : 
                    '<span class="badge bg-success status-badge">صحيح</span>'
                }
            </td>
        `;

        tbody.appendChild(tr);
    });

    // Show preview section
    document.getElementById('preview-section').style.display = 'block';
}

function resetForm() {
    document.getElementById('upload-section').style.display = 'block';
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('loading-spinner').style.display = 'none';
    document.getElementById('upload-form').reset();
    previewData = [];
    excelFile = null;
}

async function processImport() {
    if (!excelFile || previewData.length === 0) {
        alert('لا توجد بيانات للاستيراد');
        return;
    }

    if (!confirm('هل أنت متأكد من استيراد البيانات؟')) {
        return;
    }

    // Show loading
    document.getElementById('import-btn').disabled = true;
    document.getElementById('import-btn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الاستيراد...';

    // Create FormData
    const formData = new FormData();
    formData.append('excel_file', excelFile);
    formData.append('questions_data', JSON.stringify(previewData));
    formData.append('_token', '{{ csrf_token() }}');

    try {
        const response = await fetch('{{ route("question-bank.import.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error('الخادم لم يرجع بيانات JSON. قد يكون هناك خطأ في الخادم.');
        }

        const result = await response.json();

        if (result.success) {
            // Redirect to index page
            window.location.href = '{{ route("question-bank.index") }}';
        } else {
            let errorMsg = result.message || 'خطأ غير معروف';
            if (result.errors) {
                errorMsg += ': ' + Object.values(result.errors).flat().join(', ');
            }
            alert('حدث خطأ: ' + errorMsg);
            document.getElementById('import-btn').disabled = false;
            document.getElementById('import-btn').innerHTML = '<i class="fas fa-upload me-2"></i>استيراد البيانات';
        }
    } catch (error) {
        console.error('Import error:', error);
        alert('حدث خطأ أثناء الاستيراد: ' + error.message);
        document.getElementById('import-btn').disabled = false;
        document.getElementById('import-btn').innerHTML = '<i class="fas fa-upload me-2"></i>استيراد البيانات';
    }
}
</script>
@endsection
