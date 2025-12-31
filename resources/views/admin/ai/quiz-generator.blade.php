@extends('admin.layouts.master')

@section('page-title')
    إنشاء الاختبارات بالذكاء الاصطناعي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إنشاء الاختبارات بالذكاء الاصطناعي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الذكاء الاصطناعي</li>
                            <li class="breadcrumb-item active">إنشاء الاختبارات</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Generator Form -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">إعدادات إنشاء الاختبار</div>
                        </div>
                        <div class="card-body">
                            <form id="generateQuizForm">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الكورس <span class="text-danger">*</span></label>
                                        <select name="course_id" class="form-select" required>
                                            <option value="">اختر الكورس</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">عدد الأسئلة <span class="text-danger">*</span></label>
                                        <input type="number" name="total_questions" class="form-control" value="10" min="5" max="100" required>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">أنواع الأسئلة <span class="text-danger">*</span></label>
                                        <div class="row">
                                            @foreach($questionTypes as $type)
                                                <div class="col-md-3 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="question_types[]" value="{{ $type->name }}" id="quiz_type_{{ $type->id }}" checked>
                                                        <label class="form-check-label" for="quiz_type_{{ $type->id }}">
                                                            {{ $type->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الصعوبة <span class="text-danger">*</span></label>
                                        <select name="difficulty" class="form-select" required>
                                            <option value="easy">سهل</option>
                                            <option value="medium" selected>متوسط</option>
                                            <option value="hard">صعب</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الوقت المقترح (دقيقة)</label>
                                        <input type="number" name="time_limit" class="form-control" value="60" min="5" max="300">
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
                                            <input class="form-check-input" type="checkbox" name="save_immediately" id="save_quiz_immediately" value="1">
                                            <label class="form-check-label" for="save_quiz_immediately">حفظ تلقائياً بعد الإنشاء</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" name="async" id="async_quiz" value="1">
                                            <label class="form-check-label" for="async_quiz">تشغيل في الخلفية</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-magic me-2"></i>إنشاء الاختبار
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="row mt-4" id="quizResultsContainer" style="display: none;">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الاختبار المولد</div>
                        </div>
                        <div class="card-body" id="quizResultsContent">
                            <!-- Results will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('generateQuizForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الإنشاء...';

            fetch('{{ route("admin.ai.quiz-generator.generate") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('quizResultsContainer').style.display = 'block';
                    document.getElementById('quizResultsContent').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>تم إنشاء الاختبار بنجاح
                            ${data.quiz_id ? `<br><a href="/admin/quizzes/${data.quiz_id}" class="btn btn-sm btn-primary mt-2">عرض الاختبار</a>` : ''}
                        </div>
                        <h5>${data.quiz.quiz_title || 'اختبار جديد'}</h5>
                        <p>${data.quiz.quiz_description || ''}</p>
                        <p><strong>عدد الأسئلة:</strong> ${data.quiz.questions?.length || 0}</p>
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

