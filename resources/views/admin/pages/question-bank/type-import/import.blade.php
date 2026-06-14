@extends('admin.layouts.master')

@section('page-title')
    استيراد {{ $format === 'excel' ? 'Excel' : 'JSON' }} — {{ $questionType->display_name }}
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
                    <li class="breadcrumb-item">
                        <a href="{{ route('question-bank.import.type.select', $format) }}">اختر النوع</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $questionType->display_name }}</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in qb-import-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-{{ $format === 'excel' ? 'file-text' : 'code' }} me-1"></i>
                        استيراد حسب النوع
                    </span>
                    <h2 class="group-show-hero__title mb-2">
                        {{ $questionType->display_name }}
                    </h2>
                    <p class="group-show-hero__desc mb-0">
                        استيراد أسئلة من نوع <strong>{{ $questionType->display_name }}</strong> فقط عبر ملف {{ $format === 'excel' ? 'Excel' : 'JSON' }} مخصص.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="group-show-actions">
                        <a href="{{ route('question-bank.import.type.template', ['format' => $format, 'type' => $questionType->name]) }}" class="group-show-action group-show-action--primary">
                            <span class="group-show-action__icon"><i class="fe fe-download"></i></span>
                            <span class="group-show-action__text">تحميل القالب</span>
                        </a>
                        <a href="{{ route('question-bank.import.type.select', $format) }}" class="group-show-action">
                            <span class="group-show-action__icon"><i class="fe fe-grid"></i></span>
                            <span class="group-show-action__text">تغيير النوع</span>
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
                <h4 class="card-title mb-1">رفع ملف {{ $format === 'excel' ? 'Excel' : 'JSON' }}</h4>
                <p class="fs-12 text-muted mb-0">الملف يجب أن يحتوي على أسئلة من نوع {{ $questionType->display_name }} فقط.</p>
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
                            <strong><i class="fe fe-tag me-1"></i>النوع المختار</strong>
                            {{ $questionType->display_name }} — لا تخلط أنواعاً أخرى في نفس الملف.
                        </div>
                        @if($format === 'excel')
                            <div class="qb-import-tip">
                                <strong><i class="fe fe-file me-1"></i>صيغة الملف</strong>
                                .xlsx أو .xls — الحد الأقصى 10 ميجابايت. القالب يحتوي ورقتي دليل وأسئلة.
                            </div>
                        @else
                            <div class="qb-import-tip">
                                <strong><i class="fe fe-code me-1"></i>صيغة الملف</strong>
                                .json — الحد الأقصى 10 ميجابايت. يدعم القالب المنظم أو صيغة مسطحة.
                            </div>
                        @endif
                        <div class="qb-import-tip">
                            <strong><i class="fe fe-book me-1"></i>الكورس واللغة</strong>
                            اختياريان في الملف إذا حددتهما من القوائم أدناه. قيم الملف لها الأولوية عند وجودها.
                        </div>
                    </div>

                    <form id="upload-form" enctype="multipart/form-data" onsubmit="return false;">
                        @csrf

                        <label class="qb-import-dropzone" id="import-dropzone" for="import_file">
                            <input type="file"
                                   class="d-none"
                                   id="import_file"
                                   name="import_file"
                                   accept="{{ $format === 'excel' ? '.xlsx,.xls' : '.json' }}"
                                   required>
                            <div class="qb-import-dropzone__icon">
                                <i class="fe fe-upload-cloud"></i>
                            </div>
                            <p class="mb-1 fw-semibold">اسحب الملف هنا أو انقر للاختيار</p>
                            <small class="text-muted" id="import-filename" data-default-hint="{{ $format === 'excel' ? '.xlsx, .xls — حتى 10MB' : '.json — حتى 10MB' }}">
                                {{ $format === 'excel' ? '.xlsx, .xls — حتى 10MB' : '.json — حتى 10MB' }}
                            </small>
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
                                    <th>نص السؤال</th>
                                    <th>اسم الدرس</th>
                                    <th>الخيارات / إضافي</th>
                                    <th>الإجابة</th>
                                    <th>الدرجة</th>
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
                        <button type="button" class="btn btn-success" id="import-btn" onclick="processImport()">
                            <i class="fe fe-upload me-1"></i>استيراد البيانات
                        </button>
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

document.addEventListener('DOMContentLoaded', function() {
    initImportDropzone('import_file', 'import-dropzone', 'import-filename');

    document.getElementById('preview-btn').addEventListener('click', function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('import_file');
        if (!fileInput.files.length) {
            alert('يرجى اختيار ملف');
            return;
        }

        document.getElementById('upload-section').style.display = 'none';
        document.getElementById('preview-section').style.display = 'none';
        document.getElementById('loading-spinner').style.display = 'block';

        const formData = new FormData();
        formData.append('import_file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        appendImportDefaults(formData);

        fetch('{{ route("question-bank.import.type.preview", ["format" => $format, "type" => $questionType->name]) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(result => {
            document.getElementById('loading-spinner').style.display = 'none';
            if (result.success) {
                previewData = result.data;
                displayPreview(result);
            } else {
                alert(result.message || Object.values(result.errors || {}).flat().join(', '));
                resetForm();
            }
        })
        .catch(err => {
            document.getElementById('loading-spinner').style.display = 'none';
            alert('حدث خطأ: ' + err.message);
            resetForm();
        });
    });
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

    result.data.forEach(row => {
        const hasError = result.errors.some(e => e.row === row.row_number);
        const tr = document.createElement('tr');
        tr.className = hasError ? 'preview-row-error' : 'preview-row-valid';

        const options = [];
        for (let i = 1; i <= 6; i++) {
            if (row['option_' + i]) options.push(`${i}. ${row['option_' + i]}`);
        }
        const extra = [];
        if (row.accepted_answers) extra.push('مقبولة: ' + row.accepted_answers);
        if (row.matching_pairs_raw) extra.push('مطابقة: ' + row.matching_pairs_raw);

        tr.innerHTML = `
            <td>${row.row_number}</td>
            <td>${row.question_text ? (row.question_text.length > 50 ? row.question_text.substring(0,50)+'...' : row.question_text) : '<span class="text-danger">مفقود</span>'}</td>
            <td>${row.lesson_name || '<span class="text-muted">—</span>'}</td>
            <td>${options.length ? options.join('<br>') : (extra.length ? extra.join('<br>') : '<span class="text-muted">—</span>')}</td>
            <td>${row.correct_answer || '<span class="text-muted">—</span>'}</td>
            <td>${row.points || '1'}</td>
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
    const filenameEl = document.getElementById('import-filename');
    if (filenameEl) filenameEl.textContent = filenameEl.dataset.defaultHint || '';
    previewData = [];
}

async function processImport() {
    if (!previewData.length) {
        alert('لا توجد بيانات للاستيراد');
        return;
    }
    if (!confirm('هل أنت متأكد من استيراد البيانات؟')) return;

    const btn = document.getElementById('import-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الاستيراد...';

    const formData = new FormData();
    formData.append('questions_data', JSON.stringify(previewData));
    formData.append('_token', '{{ csrf_token() }}');
    appendImportDefaults(formData);

    try {
        const response = await fetch('{{ route("question-bank.import.type.process", ["format" => $format, "type" => $questionType->name]) }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (result.success && result.imported > 0) {
            window.location.href = '{{ route("question-bank.index") }}';
        } else {
            alert(result.message || 'لم يتم استيراد أي سؤال');
            btn.disabled = false;
            btn.innerHTML = '<i class="fe fe-upload me-1"></i>استيراد البيانات';
        }
    } catch (e) {
        alert('حدث خطأ: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fe fe-upload me-1"></i>استيراد البيانات';
    }
}
</script>
@endsection
