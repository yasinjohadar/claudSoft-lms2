@extends('admin.layouts.master')

@section('page-title')
    تفاصيل السؤال
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تفاصيل السؤال</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                            <li class="breadcrumb-item active">تفاصيل السؤال</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('question-bank.edit', $question->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('question-bank.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Question Details -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-primary-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="card-title mb-0">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>تفاصيل السؤال
                                </div>
                                <div>
                                    @if($question->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-danger">غير نشط</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Question Text -->
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">نص السؤال:</h6>
                                <div class="p-3 bg-light rounded">
                                    <p class="mb-0 fs-16">{{ $question->question_text }}</p>
                                </div>
                            </div>

                            <!-- Media -->
                            @if($question->media_url)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">الوسائط المرفقة:</h6>
                                    @if($question->media_type == 'image')
                                        <img src="{{ $question->media_url }}" alt="صورة السؤال" class="img-fluid rounded shadow-sm" style="max-width: 500px;">
                                    @elseif($question->media_type == 'video')
                                        <video controls class="w-100 rounded" style="max-width: 500px;">
                                            <source src="{{ $question->media_url }}" type="video/mp4">
                                        </video>
                                    @elseif($question->media_type == 'audio')
                                        <audio controls class="w-100">
                                            <source src="{{ $question->media_url }}" type="audio/mpeg">
                                        </audio>
                                    @endif
                                </div>
                            @endif

                            <!-- Options -->
                            @if($question->options && $question->options->count() > 0)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-3">خيارات الإجابة:</h6>
                                    <div class="options-list">
                                        @foreach($question->options->sortBy('option_order') as $index => $option)
                                            <div class="option-item mb-3 p-3 rounded border {{ $option->is_correct ? 'border-success bg-success-transparent' : 'border-secondary bg-light' }}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                                            <strong>{{ $option->option_text }}</strong>
                                                            @if($option->is_correct)
                                                                <i class="fas fa-check-circle text-success ms-2 fs-18"></i>
                                                            @endif
                                                        </div>
                                                        @if($option->feedback)
                                                            <small class="text-muted">
                                                                <i class="fas fa-comment me-1"></i>{{ $option->feedback }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="text-muted d-block">الوزن: {{ $option->score_weight }}</small>
                                                        <small class="text-muted d-block">الترتيب: {{ $option->option_order }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Explanation -->
                            @if($question->explanation)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">شرح الإجابة:</h6>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-lightbulb me-2"></i>{{ $question->explanation }}
                                    </div>
                                </div>
                            @endif

                            <!-- Tags -->
                            @if($question->tags && count($question->tags) > 0)
                                <div>
                                    <h6 class="text-muted mb-2">الوسوم:</h6>
                                    <div>
                                        @foreach($question->tags as $tag)
                                            <span class="badge bg-warning-transparent me-1 mb-1">
                                                <i class="fas fa-tag me-1"></i>{{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-chart-bar me-2 text-info"></i>إحصائيات الاستخدام
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-primary-transparent rounded text-center">
                                        <i class="fas fa-clipboard-list fs-24 text-primary mb-2"></i>
                                        <h4 class="mb-0">{{ $question->quizQuestions ? $question->quizQuestions->count() : 0 }}</h4>
                                        <small class="text-muted">عدد الاختبارات المستخدمة فيها</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-success-transparent rounded text-center">
                                        <i class="fas fa-users fs-24 text-success mb-2"></i>
                                        <h4 class="mb-0">{{ $question->responses ? $question->responses->count() : 0 }}</h4>
                                        <small class="text-muted">عدد الإجابات المسجلة</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-warning-transparent rounded text-center">
                                        <i class="fas fa-percentage fs-24 text-warning mb-2"></i>
                                        <h4 class="mb-0">
                                            @if($question->responses && $question->responses->count() > 0)
                                                {{ number_format(($question->responses->where('is_correct', true)->count() / $question->responses->count()) * 100, 1) }}%
                                            @else
                                                -
                                            @endif
                                        </h4>
                                        <small class="text-muted">نسبة الإجابات الصحيحة</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-info-transparent rounded text-center">
                                        <i class="fas fa-star fs-24 text-info mb-2"></i>
                                        <h4 class="mb-0">
                                            @if($question->responses && $question->responses->count() > 0)
                                                {{ number_format($question->responses->avg('score_obtained'), 1) }}
                                            @else
                                                -
                                            @endif
                                        </h4>
                                        <small class="text-muted">متوسط الدرجات المحصلة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Basic Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-success-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-info-circle me-2 text-success"></i>المعلومات الأساسية
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">الكورس:</small>
                                @if($question->course)
                                    <strong>{{ $question->course->title }}</strong>
                                @else
                                    <span class="text-muted">عام (غير مرتبط بكورس)</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">نوع السؤال:</small>
                                <span class="badge bg-info">{{ $question->questionType->display_name }}</span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">مستوى الصعوبة:</small>
                                @if($question->difficulty == 'easy')
                                    <span class="badge bg-success">سهل</span>
                                @elseif($question->difficulty == 'medium')
                                    <span class="badge bg-warning">متوسط</span>
                                @else
                                    <span class="badge bg-danger">صعب</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">الدرجة:</small>
                                <span class="badge bg-primary fs-15">{{ $question->default_grade }} نقطة</span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">قابل لإعادة الاستخدام:</small>
                                @if($question->is_reusable)
                                    <span class="badge bg-success">نعم</span>
                                @else
                                    <span class="badge bg-danger">لا</span>
                                @endif
                            </div>
                            <hr>
                            <div class="mb-2">
                                <small class="text-muted">تاريخ الإنشاء:</small>
                                <p class="mb-0">{{ $question->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            <div>
                                <small class="text-muted">آخر تعديل:</small>
                                <p class="mb-0">{{ $question->updated_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Question Pools -->
                    @if($question->questionPools && $question->questionPools->count() > 0)
                        <div class="card custom-card mb-4">
                            <div class="card-header bg-warning-transparent">
                                <div class="card-title mb-0">
                                    <i class="fas fa-layer-group me-2 text-warning"></i>المجموعات
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    @foreach($question->questionPools as $pool)
                                        <a href="{{ route('question-pools.show', $pool->id) }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-layer-group me-2 text-warning"></i>{{ $pool->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header bg-danger-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-cog me-2 text-danger"></i>الإجراءات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('question-bank.edit', $question->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>تعديل السؤال
                                </a>
                                <button type="button" class="btn btn-info" onclick="duplicateQuestion()">
                                    <i class="fas fa-copy me-2"></i>نسخ السؤال
                                </button>
                                <hr>
                                <form action="{{ route('question-bank.destroy', $question->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف السؤال
                                    </button>
                                </form>
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
function duplicateQuestion() {
    if (confirm('هل تريد إنشاء نسخة من هذا السؤال؟')) {
        window.location.href = "{{ route('question-bank.create') }}?duplicate={{ $question->id }}";
    }
}
</script>
@stop
