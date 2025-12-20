@extends('admin.layouts.master')

@section('page-title')
    معاينة البيانات - رفع المستخدمين
@stop

@section('css')
    <style>
        .mapping-row {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }
        .column-header {
            font-weight: bold;
            color: #495057;
        }
        .mapping-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .preview-table {
            overflow-x: auto;
        }
        .preview-table table {
            white-space: nowrap;
        }
        .stats-box {
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-number {
            font-size: 2em;
            font-weight: bold;
        }
        .option-checkbox {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
@stop

@section('content')
    <div class="main-content app-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">معاينة البيانات وربط الأعمدة</h1>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">المستخدمون</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.bulk-import.index') }}">رفع جماعي</a></li>
                            <li class="breadcrumb-item active" aria-current="page">معاينة</li>
                        </ol>
                    </div>
                </div>

                <form action="{{ route('users.bulk-import.process') }}" method="POST" id="processForm">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $sessionId }}">

                    <div class="row">
                        <!-- Stats -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="stats-box bg-primary-transparent">
                                                <div class="stats-number text-primary">{{ count($headers) }}</div>
                                                <div class="text-muted">عدد الأعمدة</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stats-box bg-success-transparent">
                                                <div class="stats-number text-success">{{ count($previewRows) }}</div>
                                                <div class="text-muted">صفوف المعاينة</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stats-box bg-info-transparent">
                                                <div class="stats-number text-info">
                                                    <i class="fa fa-arrow-left"></i>
                                                </div>
                                                <div class="text-muted">جاهز للمعالجة</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Column Mapping -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">ربط الأعمدة</h3>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="resetMapping()">
                                        <i class="fa fa-redo"></i> إعادة تعيين
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">
                                        <i class="fa fa-info-circle me-1"></i>
                                        حدد كيفية ربط كل عمود من ملف Excel مع حقول قاعدة البيانات
                                    </p>

                                    @foreach($headers as $index => $header)
                                        <div class="mapping-row">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <span class="badge bg-secondary">عمود {{ chr(65 + $index) }}</span>
                                                    <div class="column-header mt-1">{{ $header }}</div>
                                                </div>
                                                <div class="col-md-1 text-center">
                                                    <i class="fa fa-arrow-left text-muted"></i>
                                                </div>
                                                <div class="col-md-7">
                                                    <select name="mapping[{{ $index }}]" class="mapping-select" required>
                                                        <option value="skip" {{ ($suggestedMapping[$index] ?? 'skip') === 'skip' ? 'selected' : '' }}>
                                                            تخطي هذا العمود
                                                        </option>
                                                        <option value="name" {{ ($suggestedMapping[$index] ?? '') === 'name' ? 'selected' : '' }}>
                                                            الاسم (name) - مطلوب
                                                        </option>
                                                        <option value="email" {{ ($suggestedMapping[$index] ?? '') === 'email' ? 'selected' : '' }}>
                                                            البريد الإلكتروني (email) - مطلوب
                                                        </option>
                                                        <option value="password" {{ ($suggestedMapping[$index] ?? '') === 'password' ? 'selected' : '' }}>
                                                            كلمة المرور (password) - مطلوب
                                                        </option>
                                                        <option value="full_phone" {{ ($suggestedMapping[$index] ?? '') === 'full_phone' ? 'selected' : '' }}>
                                                            رقم الهاتف (full_phone) - اختياري
                                                        </option>
                                                        <option value="course_name" {{ ($suggestedMapping[$index] ?? '') === 'course_name' ? 'selected' : '' }}>
                                                            اسم الكورس (course_name) - اختياري
                                                        </option>
                                                        <option value="group_name" {{ ($suggestedMapping[$index] ?? '') === 'group_name' ? 'selected' : '' }}>
                                                            اسم المجموعة (group_name) - اختياري
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Processing Options -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">خيارات المعالجة</h3>
                                </div>
                                <div class="card-body">
                                    <div class="option-checkbox">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="update_existing"
                                                   id="updateExisting" checked>
                                            <label class="form-check-label" for="updateExisting">
                                                <strong>تحديث بيانات المستخدمين الموجودين</strong>
                                                <p class="text-muted mb-0 small">إذا كان البريد الإلكتروني موجوداً، سيتم تحديث البيانات</p>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="option-checkbox">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="skip_errors"
                                                   id="skipErrors" checked>
                                            <label class="form-check-label" for="skipErrors">
                                                <strong>تخطي الأخطاء والمتابعة</strong>
                                                <p class="text-muted mb-0 small">متابعة المعالجة حتى لو حدثت أخطاء في بعض الصفوف</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">معاينة البيانات (أول 5 صفوف)</h3>
                                </div>
                                <div class="card-body preview-table">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-primary">
                                                <tr>
                                                    <th width="50">#</th>
                                                    @foreach($headers as $header)
                                                        <th>{{ $header }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($previewRows as $rowIndex => $row)
                                                    <tr>
                                                        <td>{{ $rowIndex + 1 }}</td>
                                                        @foreach($row as $cell)
                                                            <td>{{ $cell }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <i class="fa fa-info-circle me-1"></i>
                                        هذه معاينة فقط لأول 5 صفوف. سيتم معالجة جميع الصفوف عند الضغط على "بدء المعالجة"
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('users.bulk-import.index') }}" class="btn btn-light">
                                            <i class="fa fa-arrow-right me-2"></i>رجوع
                                        </a>
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fa fa-check-circle me-2"></i>بدء المعالجة
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@stop

@section('script')
    <script>
        function resetMapping() {
            const selects = document.querySelectorAll('.mapping-select');
            selects.forEach(select => {
                select.selectedIndex = 0; // Reset to first option (skip)
            });
        }

        // Validate before submit
        document.getElementById('processForm').addEventListener('submit', function(e) {
            const mappings = Array.from(document.querySelectorAll('.mapping-select'));
            const hasName = mappings.some(select => select.value === 'name');
            const hasEmail = mappings.some(select => select.value === 'email');
            const hasPassword = mappings.some(select => select.value === 'password');

            if (!hasName) {
                e.preventDefault();
                alert('يجب ربط عمود "الاسم" على الأقل');
                return false;
            }

            if (!hasEmail) {
                e.preventDefault();
                alert('يجب ربط عمود "البريد الإلكتروني" على الأقل');
                return false;
            }

            if (!hasPassword) {
                e.preventDefault();
                alert('يجب ربط عمود "كلمة المرور" على الأقل');
                return false;
            }

            return confirm('هل أنت متأكد من بدء معالجة الملف؟ هذه العملية قد تستغرق بعض الوقت.');
        });
    </script>
@stop
