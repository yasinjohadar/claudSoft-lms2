@extends('admin.layouts.master')

@section('page-title')
    تفاصيل مجموعة الأسئلة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $pool->name }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-pools.index') }}">مجموعات الأسئلة</a></li>
                            <li class="breadcrumb-item active">تفاصيل المجموعة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('question-pools.edit', $pool->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('question-pools.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-primary-transparent me-3">
                                    <i class="fas fa-question-circle fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">إجمالي الأسئلة</p>
                                    <h4 class="mb-0 fw-bold">{{ $pool->questions->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-success-transparent me-3">
                                    <i class="fas fa-check-circle fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">الأسئلة النشطة</p>
                                    <h4 class="mb-0 fw-bold">{{ $pool->questions->where('is_active', true)->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-warning-transparent me-3">
                                    <i class="fas fa-star fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">إجمالي الدرجات</p>
                                    <h4 class="mb-0 fw-bold">{{ $pool->questions->sum('points') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg bg-info-transparent me-3">
                                    <i class="fas fa-clipboard-list fs-24"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted fs-12">الاختبارات المستخدمة</p>
                                    <h4 class="mb-0 fw-bold">{{ $pool->quizzes->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Pool Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-primary-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>معلومات المجموعة
                                </div>
                                <div>
                                    @if($pool->is_active)
                                        <span class="badge bg-success">نشطة</span>
                                    @else
                                        <span class="badge bg-danger">غير نشطة</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">اسم المجموعة:</h6>
                                <h5 class="mb-0">{{ $pool->name }}</h5>
                            </div>
                            @if($pool->description)
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">الوصف:</h6>
                                    <p class="mb-0">{{ $pool->description }}</p>
                                </div>
                            @endif
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">الكورس:</small>
                                    <span class="badge bg-primary-transparent fs-14">
                                        <i class="fas fa-book me-1"></i>{{ $pool->course->title }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">عدد الأسئلة لكل اختبار:</small>
                                    <span class="badge bg-info-transparent fs-14">
                                        {{ $pool->questions_per_quiz ?? 'جميع الأسئلة' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="card-title mb-0">
                                    <i class="fas fa-list me-2 text-success"></i>الأسئلة ({{ $pool->questions->count() }})
                                </div>
                                <div>
                                    <select id="filter-difficulty" class="form-select form-select-sm">
                                        <option value="">جميع المستويات</option>
                                        <option value="easy">سهل</option>
                                        <option value="medium">متوسط</option>
                                        <option value="hard">صعب</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($pool->questions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>نص السؤال</th>
                                                <th>النوع</th>
                                                <th>الصعوبة</th>
                                                <th>الدرجة</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pool->questions->sortBy('pivot.order') as $index => $question)
                                                <tr class="question-row" data-difficulty="{{ $question->difficulty }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <a href="{{ route('question-bank.show', $question->id) }}" class="text-primary">
                                                            {{ Str::limit($question->question_text, 60) }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info-transparent">
                                                            {{ $question->questionType->display_name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($question->difficulty == 'easy')
                                                            <span class="badge bg-success">سهل</span>
                                                        @elseif($question->difficulty == 'medium')
                                                            <span class="badge bg-warning">متوسط</span>
                                                        @else
                                                            <span class="badge bg-danger">صعب</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ $question->points }}</span>
                                                    </td>
                                                    <td>
                                                        @if($question->is_active)
                                                            <span class="badge bg-success-transparent">نشط</span>
                                                        @else
                                                            <span class="badge bg-danger-transparent">غير نشط</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('question-bank.show', $question->id) }}"
                                                           class="btn btn-sm btn-info"
                                                           data-bs-toggle="tooltip"
                                                           title="عرض">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('question-bank.edit', $question->id) }}"
                                                           class="btn btn-sm btn-warning"
                                                           data-bs-toggle="tooltip"
                                                           title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="card-body text-center py-5">
                                    <div class="avatar avatar-xl bg-secondary-transparent mx-auto mb-3">
                                        <i class="fas fa-question-circle fs-40"></i>
                                    </div>
                                    <h5 class="mb-2">لا توجد أسئلة في هذه المجموعة</h5>
                                    <p class="text-muted mb-3">أضف أسئلة من بنك الأسئلة</p>
                                    <a href="{{ route('question-pools.edit', $pool->id) }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>إضافة أسئلة
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Questions by Type -->
                    @if($pool->questions->count() > 0)
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-chart-pie me-2 text-info"></i>توزيع الأسئلة حسب النوع
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @php
                                        $questionsByType = $pool->questions->groupBy('question_type_id');
                                    @endphp
                                    @foreach($questionsByType as $typeId => $questions)
                                        @php
                                            $type = $questions->first()->questionType;
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="mb-1 fw-semibold">{{ $type->display_name }}</p>
                                                        <small class="text-muted">{{ $questions->count() }} سؤال</small>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-info fs-16">{{ $questions->count() }}</span>
                                                    </div>
                                                </div>
                                                <div class="progress mt-2" style="height: 6px;">
                                                    <div class="progress-bar bg-info"
                                                         style="width: {{ ($questions->count() / $pool->questions->count()) * 100 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-success-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2 text-success"></i>إحصائيات سريعة
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">سهل:</span>
                                    <strong class="text-success">{{ $pool->questions->where('difficulty', 'easy')->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">متوسط:</span>
                                    <strong class="text-warning">{{ $pool->questions->where('difficulty', 'medium')->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">صعب:</span>
                                    <strong class="text-danger">{{ $pool->questions->where('difficulty', 'hard')->count() }}</strong>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">أقل درجة:</span>
                                    <strong>{{ $pool->questions->min('points') ?? 0 }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">أعلى درجة:</span>
                                    <strong>{{ $pool->questions->max('points') ?? 0 }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">متوسط الدرجات:</span>
                                    <strong>{{ number_format($pool->questions->avg('points') ?? 0, 1) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quizzes Using This Pool -->
                    @if($pool->quizzes->count() > 0)
                        <div class="card custom-card mb-4">
                            <div class="card-header bg-warning-transparent">
                                <div class="card-title mb-0">
                                    <i class="fas fa-clipboard-list me-2 text-warning"></i>الاختبارات المستخدمة
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    @foreach($pool->quizzes->take(5) as $quiz)
                                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>{{ $quiz->title }}</span>
                                                <span class="badge bg-secondary">{{ $quiz->max_score }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                                @if($pool->quizzes->count() > 5)
                                    <div class="text-center mt-2">
                                        <small class="text-muted">و {{ $pool->quizzes->count() - 5 }} اختبار آخر</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-info-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-calendar me-2 text-info"></i>التواريخ
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">تاريخ الإنشاء:</small>
                                <p class="mb-0">{{ $pool->created_at->format('Y-m-d H:i') }}</p>
                                <small class="text-muted">{{ $pool->created_at->diffForHumans() }}</small>
                            </div>
                            <div>
                                <small class="text-muted d-block">آخر تعديل:</small>
                                <p class="mb-0">{{ $pool->updated_at->format('Y-m-d H:i') }}</p>
                                <small class="text-muted">{{ $pool->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header bg-danger-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-cog me-2 text-danger"></i>الإجراءات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('question-pools.edit', $pool->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit me-2"></i>تعديل المجموعة
                                </a>
                                <button type="button" class="btn btn-info" onclick="exportPool()">
                                    <i class="fas fa-download me-2"></i>تصدير الأسئلة
                                </button>
                                <hr>
                                <form action="{{ route('question-pools.destroy', $pool->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟ سيتم الاحتفاظ بالأسئلة في بنك الأسئلة.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف المجموعة
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

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Filter by difficulty
    $('#filter-difficulty').change(function() {
        const difficulty = $(this).val();

        if (difficulty === '') {
            $('.question-row').show();
        } else {
            $('.question-row').each(function() {
                if ($(this).data('difficulty') === difficulty) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
});

function exportPool() {
    alert('ميزة التصدير قيد التطوير');
}
</script>
@endsection
