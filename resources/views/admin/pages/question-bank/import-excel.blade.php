@extends('admin.layouts.master')

@section('page-title')
    استيراد الأسئلة من Excel
@stop

@section('styles')
    @include('admin.pages.question-bank.partials.import-ui-styles')
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb qb-import-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                    <li class="breadcrumb-item active">استيراد من Excel</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in qb-import-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-file-text me-1"></i>
                        استيراد جماعي
                    </span>
                    <h2 class="group-show-hero__title mb-2">استيراد الأسئلة من Excel</h2>
                    <p class="group-show-hero__desc mb-0">
                        ارفع ملف Excel يحتوي على أنواع متعددة من الأسئلة. حمّل القالب أولاً، ثم ارفع الملف ومعاينته قبل الاستيراد النهائي.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('question-bank.export.template') }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-download"></i></span>
                            <span class="group-show-action__text">تحميل القالب</span>
                        </a>
                        <a href="{{ route('question-bank.import.type.select', 'excel') }}" class="group-show-action group-show-action--warning">
                            <span class="group-show-action__icon"><i class="fe fe-grid"></i></span>
                            <span class="group-show-action__text">استيراد حسب النوع</span>
                        </a>
                        <a href="{{ route('question-bank.index') }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-arrow-right"></i></span>
                            <span class="group-show-action__text">رجوع</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in qb-import-animate">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">رفع ملف Excel</h4>
                <p class="fs-12 text-muted mb-0">اتبع الخطوات أدناه لاستيراد الأسئلة بأمان.</p>
            </div>
            <div class="card-body pt-3">

                <div class="qb-import-steps">
                    <span class="qb-import-step"><span class="qb-import-step__num">1</span> تحميل القالب</span>
                    <span class="qb-import-step qb-import-step--muted"><span class="qb-import-step__num">2</span> رفع الملف</span>
                    <span class="qb-import-step qb-import-step--muted"><span class="qb-import-step__num">3</span> معاينة</span>
                    <span class="qb-import-step qb-import-step--muted"><span class="qb-import-step__num">4</span> استيراد</span>
                </div>

                <div id="upload-section">
                    <div class="qb-import-tips">
                        <div class="qb-import-tip">
                            <strong><i class="fe fe-file me-1"></i>صيغة الملف</strong>
                            Excel (.xlsx أو .xls) — الحد الأقصى 10 ميجابايت
                        </div>
                        <div class="qb-import-tip">
                            <strong><i class="fe fe-layers me-1"></i>هيكل القالب</strong>
                            ورقتان: <strong>دليل</strong> و<strong>أسئلة</strong>. القالب القديم (13 عموداً) ما زال مدعوماً.
                        </div>
                        <div class="qb-import-tip">
                            <strong><i class="fe fe-book me-1"></i>الكورس واللغة</strong>
                            اختياريان في الملف إذا حددتهما من القوائم أدناه. قيم الملف لها الأولوية عند وجودها.
                        </div>
                        <div class="qb-import-tip">
                            <strong><i class="fe fe-list me-1"></i>الأنواع المدعومة</strong>
                            اختيار من متعدد، صح/خطأ، إجابة قصيرة، مقالي، مطابقة، ملء فراغات، ترتيب، رقمي، محسوب.
                        </div>
                    </div>

                    <form id="upload-form" enctype="multipart/form-data" onsubmit="return false;">
                        @csrf

                        <label class="qb-import-dropzone" id="excel-dropzone" for="excel_file">
                            <input type="file" class="d-none" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                            <div class="qb-import-dropzone__icon"><i class="fe fe-upload-cloud"></i></div>
                            <p class="mb-1 fw-semibold">اسحب ملف Excel هنا أو انقر للاختيار</p>
                            <small class="text-muted" id="excel-filename" data-default-hint=".xlsx, .xls — حتى 10 ميجابايت">.xlsx, .xls — حتى 10 ميجابايت</small>
                        </label>

                        @include('admin.pages.question-bank.partials.import-defaults-fields')

                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <a href="{{ route('question-bank.index') }}" class="btn btn-light">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </a>
                            <button type="button" class="btn btn-primary" id="preview-btn">
                                <i class="fe fe-eye me-1"></i>معاينة البيانات
                            </button>
                        </div>
                    </form>
                </div>

                <div id="preview-section" style="display: none;">
                    <div class="alert alert-warning d-flex align-items-start gap-2" id="preview-alert" style="display: none;">
                        <i class="fe fe-alert-triangle mt-1"></i>
                        <span id="preview-alert-text"></span>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h6 class="mb-0 fw-semibold">معاينة البيانات</h6>
                        <div class="qb-import-preview-stats">
                            <span class="qb-import-stat-chip qb-import-stat-chip--success" id="valid-rows-badge">0 صحيح</span>
                            <span class="qb-import-stat-chip qb-import-stat-chip--danger" id="error-rows-badge">0 خطأ</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover dashboard-table mb-0" id="preview-table">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>نوع السؤال</th>
                                    <th>نص السؤال</th>
                                    <th>اسم الدرس</th>
                                    <th>الخيارات</th>
                                    <th>الإجابة / إضافي</th>
                                    <th>الدرجة</th>
                                    <th>الصعوبة</th>
                                    <th>الكورس</th>
                                    <th>اللغة</th>
                                    <th width="90">الحالة</th>
                                </tr>
                            </thead>
                            <tbody id="preview-tbody"></tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <button type="button" class="btn btn-light" onclick="resetForm()">
                            <i class="fe fe-rotate-cw me-1"></i>اختيار ملف آخر
                        </button>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-light" onclick="resetForm()">
                                <i class="fe fe-x me-1"></i>إلغاء
                            </button>
                            <button type="button" class="btn btn-success" id="import-btn" onclick="processImport()">
                                <i class="fe fe-upload me-1"></i>استيراد البيانات
                            </button>
                        </div>
                    </div>
                </div>

                <div id="loading-spinner" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="mt-3 text-muted mb-0">جاري معالجة الملف...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('admin.pages.question-bank.partials.import-dropzone-script')
@include('admin.pages.question-bank.partials.import-defaults-script')
<script>
let previewData = [];
let excelFile = null;

document.addEventListener('DOMContentLoaded', function() {
    initImportDropzone('excel_file', 'excel-dropzone', 'excel-filename');

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

            document.getElementById('upload-section').style.display = 'none';
            document.getElementById('preview-section').style.display = 'none';
            document.getElementById('loading-spinner').style.display = 'block';

            const formData = new FormData();
            formData.append('excel_file', excelFile);
            formData.append('_token', '{{ csrf_token() }}');
            appendImportDefaults(formData);

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

    document.getElementById('valid-rows-badge').textContent = result.valid_rows + ' صحيح';
    document.getElementById('error-rows-badge').textContent = result.errors.length + ' خطأ';

    const alertEl = document.getElementById('preview-alert');
    if (result.errors.length > 0) {
        alertEl.style.display = 'flex';
        document.getElementById('preview-alert-text').textContent =
            'تم اكتشاف ' + result.errors.length + ' سطر به أخطاء. سيتم تخطيها أثناء الاستيراد.';
    } else {
        alertEl.style.display = 'none';
    }

    result.data.forEach((row) => {
        const hasError = result.errors.some(e => e.row === row.row_number);
        const tr = document.createElement('tr');
        tr.className = hasError ? 'preview-row-error' : 'preview-row-valid';

        const options = [];
        for (let i = 1; i <= 6; i++) {
            if (row['option_' + i]) {
                options.push(`${i}. ${row['option_' + i]}`);
            }
        }
        const extraBits = [];
        if (row.accepted_answers) extraBits.push('مقبولة: ' + row.accepted_answers);
        if (row.matching_pairs_raw) extraBits.push('مطابقة: ' + (row.matching_pairs_raw.length > 40 ? row.matching_pairs_raw.substring(0, 40) + '…' : row.matching_pairs_raw));
        const answerCell = row.correct_answer
            ? row.correct_answer + (extraBits.length ? '<br><small class="text-muted">' + extraBits.join('<br>') + '</small>' : '')
            : (extraBits.length ? '<small class="text-muted">' + extraBits.join('<br>') + '</small>' : '<span class="text-muted">—</span>');

        tr.innerHTML = `
            <td>${row.row_number}</td>
            <td>${row.question_type || '<span class="text-danger">مفقود</span>'}</td>
            <td>${row.question_text ? (row.question_text.length > 50 ? row.question_text.substring(0, 50) + '...' : row.question_text) : '<span class="text-danger">مفقود</span>'}</td>
            <td>${row.lesson_name ? row.lesson_name : '<span class="text-muted">—</span>'}</td>
            <td>${options.length > 0 ? options.join('<br>') : '<span class="text-muted">لا توجد</span>'}</td>
            <td>${answerCell}</td>
            <td>${row.points || '1'}</td>
            <td>${row.difficulty || 'medium'}</td>
            <td>${formatImportDefaultCell(row.course, row.course_from_default)}</td>
            <td>${formatImportDefaultCell(row.language, row.language_from_default, '<span class="text-muted">—</span>')}</td>
            <td>${hasError ? '<span class="badge bg-danger">خطأ</span>' : '<span class="badge bg-success">صحيح</span>'}</td>
        `;

        tbody.appendChild(tr);
    });

    document.getElementById('preview-section').style.display = 'block';
}

function resetForm() {
    document.getElementById('upload-section').style.display = 'block';
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('loading-spinner').style.display = 'none';
    document.getElementById('upload-form').reset();
    const filenameEl = document.getElementById('excel-filename');
    if (filenameEl) filenameEl.textContent = filenameEl.dataset.defaultHint || '';
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

    const importBtn = document.getElementById('import-btn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الاستيراد...';

    const formData = new FormData();
    formData.append('excel_file', excelFile);
    formData.append('questions_data', JSON.stringify(previewData));
    formData.append('_token', '{{ csrf_token() }}');
    appendImportDefaults(formData);

    try {
        const response = await fetch('{{ route("question-bank.import.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('الخادم لم يرجع بيانات JSON. Status: ' + response.status);
        }

        const result = await response.json();

        if (result.success) {
            if (result.imported === 0) {
                let errorMsg = result.message || 'لم يتم استيراد أي سؤال';
                if (result.errors && result.errors.length > 0) {
                    const errorPreview = result.errors.slice(0, 5).join('\n');
                    const moreErrors = result.errors.length > 5 ? `\n... و ${result.errors.length - 5} أخطاء أخرى` : '';
                    errorMsg = 'لم يتم استيراد أي سؤال:\n\n' + errorPreview + moreErrors;
                }
                alert(errorMsg);
                importBtn.disabled = false;
                importBtn.innerHTML = '<i class="fe fe-upload me-1"></i>استيراد البيانات';
                return;
            }

            if (result.skipped > 0 && result.errors && result.errors.length > 0) {
                const errorPreview = result.errors.slice(0, 3).join('\n');
                const moreErrors = result.errors.length > 3 ? `\n... و ${result.errors.length - 3} أخطاء أخرى` : '';
                const warningMsg = result.message + '\n\nتم تخطي بعض الأسئلة:\n' + errorPreview + moreErrors;

                if (!confirm(warningMsg + '\n\nهل تريد المتابعة إلى صفحة الأسئلة؟')) {
                    importBtn.disabled = false;
                    importBtn.innerHTML = '<i class="fe fe-upload me-1"></i>استيراد البيانات';
                    return;
                }
            }

            window.location.href = '{{ route("question-bank.index") }}';
        } else {
            let errorMsg = result.message || 'خطأ غير معروف';
            if (result.errors) {
                errorMsg += ': ' + Object.values(result.errors).flat().join(', ');
            }
            alert('حدث خطأ: ' + errorMsg);
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fe fe-upload me-1"></i>استيراد البيانات';
        }
    } catch (error) {
        alert('حدث خطأ أثناء الاستيراد: ' + error.message);
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fe fe-upload me-1"></i>استيراد البيانات';
    }
}
</script>
@endsection
