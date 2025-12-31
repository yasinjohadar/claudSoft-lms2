@extends('admin.layouts.master')

@section('page-title')
    إنشاء الأسئلة بالذكاء الاصطناعي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء الأسئلة بالذكاء الاصطناعي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الذكاء الاصطناعي</li>
                            <li class="breadcrumb-item active">إنشاء الأسئلة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Generator Form -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إعدادات إنشاء الأسئلة</div>
                        </div>
                        <div class="card-body">
                            <form id="generateQuestionsForm">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الكورس</label>
                                        <select name="course_id" id="course_id" class="form-select">
                                            <option value="">اختر الكورس (اختياري)</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الدرس</label>
                                        <select name="lesson_id" id="lesson_id" class="form-select" disabled>
                                            <option value="">اختر الدرس (اختياري)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">عدد الأسئلة <span class="text-danger">*</span></label>
                                        <input type="number" name="count" class="form-control" value="5" min="1" max="50" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الصعوبة <span class="text-danger">*</span></label>
                                        <select name="difficulty" class="form-select" required>
                                            <option value="easy">سهل</option>
                                            <option value="medium" selected>متوسط</option>
                                            <option value="hard">صعب</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">أنواع الأسئلة <span class="text-danger">*</span></label>
                                        <div class="row">
                                            @foreach($questionTypes as $type)
                                                <div class="col-md-3 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="question_types[]" value="{{ $type->name }}" id="type_{{ $type->id }}">
                                                        <label class="form-check-label" for="type_{{ $type->id }}">
                                                            {{ $type->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">مقدم الخدمة</label>
                                        <select name="provider_name" class="form-select">
                                            <option value="">استخدام الافتراضي</option>
                                            @foreach($aiProviders ?? [] as $provider)
                                                <option value="{{ $provider->name }}">{{ $provider->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="save_immediately" id="save_immediately" value="1">
                                            <label class="form-check-label" for="save_immediately">حفظ تلقائياً بعد الإنشاء</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="async" id="async" value="1">
                                            <label class="form-check-label" for="async">تشغيل في الخلفية</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-magic me-2"></i>إنشاء الأسئلة
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="row mt-4" id="resultsContainer" style="display: none;">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الأسئلة المولدة</div>
                        </div>
                        <div class="card-body" id="resultsContent">
                            <!-- Results will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        // Load lessons when course is selected
        document.getElementById('course_id').addEventListener('change', function() {
            const courseId = this.value;
            const lessonSelect = document.getElementById('lesson_id');
            
            if (courseId) {
                lessonSelect.disabled = false;
                fetch(`/admin/ai/courses/${courseId}/lessons`)
                    .then(response => response.json())
                    .then(lessons => {
                        lessonSelect.innerHTML = '<option value="">اختر الدرس (اختياري)</option>';
                        lessons.forEach(lesson => {
                            lessonSelect.innerHTML += `<option value="${lesson.id}">${lesson.title}</option>`;
                        });
                    });
            } else {
                lessonSelect.disabled = true;
                lessonSelect.innerHTML = '<option value="">اختر الدرس (اختياري)</option>';
            }
        });

        // Handle form submission
        document.getElementById('generateQuestionsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الإنشاء...';

            fetch('{{ route("admin.ai.question-generator.generate") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('resultsContainer').style.display = 'block';
                    document.getElementById('resultsContent').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>تم إنشاء ${data.questions.length} سؤال بنجاح
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>السؤال</th>
                                        <th>النوع</th>
                                        <th>الصعوبة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.questions.map((q, index) => `
                                        <tr>
                                            <td>${q.question_text || 'N/A'}</td>
                                            <td>${q.question_type || 'N/A'}</td>
                                            <td>${q.difficulty || 'N/A'}</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-question" data-index="${index}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    alert('خطأ: ' + data.message);
                }
            })
            .catch(error => {
                alert('حدث خطأ: ' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    </script>
    @endpush
@stop

