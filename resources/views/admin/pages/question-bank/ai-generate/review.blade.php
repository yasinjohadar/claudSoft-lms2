@extends('admin.layouts.master')

@section('page-title')
    مراجعة الأسئلة المولدة
@stop

@section('styles')
    @include('admin.pages.question-bank.partials.page-styles')
    <style>
        .qb-ai-review-card--saved { border-color: #198754 !important; opacity: 0.92; }
        .qb-ai-review-card--saved .card-header { background: rgba(25, 135, 84, 0.08) !important; }
    </style>
@stop

@section('content')
@php
    $rawQuestions = $generation->generated_questions;
    if (is_string($rawQuestions)) {
        $rawQuestions = json_decode($rawQuestions, true);
    }
    $questions = is_array($rawQuestions) ? $rawQuestions : [];
    $questionsCount = count($questions);
    $savedIndices = $generation->getSavedIndices();
    $unsavedCount = count(array_filter(array_keys($questions), fn ($i) => ! in_array($i, $savedIndices, true)));

    $aiTypeLabels = [
        'single_choice' => 'اختيار من متعدد (إجابة واحدة)',
        'multiple_choice' => 'اختيار من متعدد (إجابات متعددة)',
        'true_false' => 'صح / خطأ',
        'short_answer' => 'إجابة قصيرة',
        'essay' => 'مقالي',
        'matching' => 'مطابقة',
        'ordering' => 'ترتيب',
        'fill_blanks' => 'ملء الفراغات',
        'numerical' => 'إجابة رقمية',
        'calculated' => 'محسوب',
    ];
@endphp

<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb qb-page-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.index') }}">بنك الأسئلة</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('question-bank.ai-generate.create') }}">توليد AI</a></li>
                    <li class="breadcrumb-item active">مراجعة #{{ $generation->id }}</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h2 class="mb-1">مراجعة الأسئلة المولدة</h2>
                <p class="text-muted mb-0">راجع الأسئلة ثم احفظ الكل أو كل سؤال على حدة في بنك الأسئلة.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($generation->status === 'completed' || $generation->status === 'failed')
                    <form action="{{ route('question-bank.ai-generate.regenerate', $generation) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('إعادة التوليد ستحذف حالة الحفظ الحالية. متابعة؟')">
                            <i class="fe fe-refresh-cw me-1"></i> إعادة التوليد
                        </button>
                    </form>
                @endif
                <a href="{{ route('question-bank.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fe fe-database me-1"></i> بنك الأسئلة
                </a>
            </div>
        </div>

        @if($generation->status === 'completed' && $generation->error_message && str_contains($generation->error_message, 'سؤال'))
            <div class="alert alert-warning">
                <i class="fe fe-alert-triangle me-1"></i>{{ $generation->error_message }}
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card custom-card group-show-members-card">
                    <div class="card-header border-0 pb-0">
                        <h6 class="card-title mb-0">معلومات الطلب</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">الحالة</td>
                                <td>
                                    @if($generation->status === 'completed')
                                        <span class="badge bg-success">مكتمل</span>
                                    @elseif($generation->status === 'failed')
                                        <span class="badge bg-danger">فشل</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $generation->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($generation->course)
                                <tr><td class="text-muted">الكورس</td><td>{{ $generation->course->title }}</td></tr>
                            @endif
                            @if($generation->lesson_name || $generation->lesson)
                                <tr><td class="text-muted">الدرس</td><td>{{ $generation->lesson_name ?? $generation->lesson?->title }}</td></tr>
                            @endif
                            @if($generation->programmingLanguage)
                                <tr>
                                    <td class="text-muted">اللغة</td>
                                    <td>
                                        @include('admin.pages.question-bank.partials.programming-language-chips', [
                                            'languages' => collect([$generation->programmingLanguage]),
                                        ])
                                    </td>
                                </tr>
                            @endif
                            <tr><td class="text-muted">الصعوبة</td><td>{{ \App\Models\AIQuestionGeneration::DIFFICULTIES[$generation->difficulty_level] ?? $generation->difficulty_level }}</td></tr>
                            <tr><td class="text-muted">الدرجة</td><td>{{ number_format((float) $generation->default_grade, 2) }}</td></tr>
                            <tr><td class="text-muted">المطلوب</td><td>{{ $generation->number_of_questions }}</td></tr>
                            <tr><td class="text-muted">المُولَّد</td><td>{{ $questionsCount }}</td></tr>
                            <tr><td class="text-muted">المحفوظ</td><td>{{ count($savedIndices) }}</td></tr>
                            <tr><td class="text-muted">بانتظار الحفظ</td><td>{{ $unsavedCount }}</td></tr>
                        </table>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card mt-3">
                    <div class="card-header border-0 pb-0">
                        <h6 class="card-title mb-0">المحتوى المصدر</h6>
                    </div>
                    <div class="card-body">
                        <div class="bg-light rounded p-3 small" style="max-height: 200px; overflow-y: auto;">{{ $generation->source_content }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @if($generation->status === 'completed' && $questionsCount > 0)
                    <div class="card custom-card group-show-members-card mb-3">
                        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <strong>{{ $questionsCount }}</strong> سؤال —
                                <span class="text-success">{{ count($savedIndices) }} محفوظ</span> /
                                <span class="text-warning">{{ $unsavedCount }} بانتظار الحفظ</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="selectAll()">تحديد الكل</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">إلغاء التحديد</button>
                                <form action="{{ route('question-bank.ai-generate.save-selected', $generation) }}" method="POST" id="saveSelectedForm" class="d-inline" onsubmit="return saveSelected()">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fe fe-check-square me-1"></i> حفظ المحدد (<span id="selectedCount">0</span>)
                                    </button>
                                </form>
                                @if($unsavedCount > 0)
                                    <form action="{{ route('question-bank.ai-generate.save-all', $generation) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('حفظ كل الأسئلة غير المحفوظة في بنك الأسئلة؟')">
                                            <i class="fe fe-save me-1"></i> حفظ الكل
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    @foreach($questions as $index => $question)
                        @php
                            $isSaved = $generation->isIndexSaved($index);
                            $bankId = $generation->getSavedQuestionBankId($index);
                            $typeKey = $question['type'] ?? 'single_choice';
                            $typeLabel = $aiTypeLabels[$typeKey] ?? $typeKey;
                            $diffKey = $question['difficulty'] ?? 'medium';
                            $diffLabel = \App\Models\AIQuestionGeneration::DIFFICULTIES[$diffKey] ?? $diffKey;
                        @endphp
                        <div class="card custom-card group-show-members-card mb-3 border-start border-3 border-primary qb-ai-review-card {{ $isSaved ? 'qb-ai-review-card--saved' : '' }}" data-index="{{ $index }}">
                            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    @if(! $isSaved)
                                        <input class="form-check-input question-checkbox" type="checkbox" value="{{ $index }}" id="q_cb_{{ $index }}" onchange="updateSelectedCount()" checked>
                                    @endif
                                    <span class="badge bg-primary">#{{ $index + 1 }}</span>
                                    <span class="fw-semibold">{{ $typeLabel }}</span>
                                    @if($isSaved)
                                        <span class="badge bg-success"><i class="fe fe-check me-1"></i>محفوظ</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary">{{ $diffLabel }}</span>
                                    @if($isSaved && $bankId)
                                        <a href="{{ route('question-bank.preview', $bankId) }}" class="btn btn-outline-primary btn-sm" target="_blank">عرض في البنك</a>
                                    @elseif(! $isSaved)
                                        <form action="{{ route('question-bank.ai-generate.save-one', [$generation, $index]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fe fe-save me-1"></i> حفظ هذا السؤال
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="fw-semibold fs-6 mb-3">{{ $question['question'] ?? '—' }}</p>

                                @if(!empty($question['options']) && is_array($question['options']))
                                    <ul class="list-group list-group-flush mb-3">
                                        @foreach($question['options'] as $optIndex => $option)
                                            @php
                                                $correctAnswer = $question['correct_answer'] ?? '';
                                                $isCorrect = is_array($correctAnswer)
                                                    ? in_array($option, $correctAnswer)
                                                    : trim((string) $option) === trim((string) $correctAnswer);
                                            @endphp
                                            <li class="list-group-item {{ $isCorrect ? 'list-group-item-success' : '' }}">
                                                <span class="badge bg-secondary me-1">{{ chr(65 + $optIndex) }}</span>
                                                {{ $option }}
                                                @if($isCorrect)<i class="fe fe-check text-success ms-1"></i>@endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if(!empty($question['items']) && is_array($question['items']))
                                    <div class="mb-3">
                                        <strong class="text-muted small">عناصر الترتيب:</strong>
                                        <ol class="mb-0 mt-1">
                                            @foreach($question['items'] as $item)
                                                <li>{{ $item }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endif

                                @if(!empty($question['pairs']) && is_array($question['pairs']))
                                    <div class="mb-3">
                                        <strong class="text-muted small">أزواج المطابقة:</strong>
                                        <ul class="mb-0 mt-1">
                                            @foreach($question['pairs'] as $pair)
                                                <li>{{ $pair['question'] ?? '' }} → {{ $pair['answer'] ?? '' }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="row g-2">
                                    @if(isset($question['correct_answer']) && $question['correct_answer'] !== '')
                                        <div class="col-md-6">
                                            <div class="small bg-success bg-opacity-10 rounded p-2">
                                                <strong>الإجابة الصحيحة:</strong>
                                                {{ is_array($question['correct_answer']) ? implode('، ', $question['correct_answer']) : $question['correct_answer'] }}
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($question['explanation']))
                                        <div class="col-md-6">
                                            <div class="small bg-info bg-opacity-10 rounded p-2">
                                                <strong>الشرح:</strong> {{ $question['explanation'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @elseif($generation->status === 'failed')
                    <div class="card custom-card group-show-members-card">
                        <div class="card-body text-center py-5">
                            <i class="fe fe-x-circle text-danger" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">فشل التوليد</h5>
                            <p class="text-danger">{{ $generation->error_message ?? 'حدث خطأ غير معروف' }}</p>
                            <a href="{{ route('question-bank.ai-generate.create') }}" class="btn btn-primary">العودة للنموذج</a>
                        </div>
                    </div>
                @else
                    <div class="card custom-card group-show-members-card">
                        <div class="card-body text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <h5>جاري المعالجة...</h5>
                            <button class="btn btn-outline-primary btn-sm mt-2" onclick="location.reload()">تحديث</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
function updateSelectedCount() {
    const count = document.querySelectorAll('.question-checkbox:checked').length;
    const el = document.getElementById('selectedCount');
    if (el) el.textContent = count;
}

function selectAll() {
    document.querySelectorAll('.question-checkbox').forEach(cb => { cb.checked = true; });
    updateSelectedCount();
}

function deselectAll() {
    document.querySelectorAll('.question-checkbox').forEach(cb => { cb.checked = false; });
    updateSelectedCount();
}

function saveSelected() {
    const selected = Array.from(document.querySelectorAll('.question-checkbox:checked')).map(cb => parseInt(cb.value, 10));
    if (selected.length === 0) {
        alert('يرجى تحديد سؤال واحد على الأقل');
        return false;
    }
    const form = document.getElementById('saveSelectedForm');
    form.querySelectorAll('input[name="selected_questions[]"]').forEach(i => i.remove());
    selected.forEach(index => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_questions[]';
        input.value = index;
        form.appendChild(input);
    });
    return confirm('حفظ ' + selected.length + ' سؤال في بنك الأسئلة؟');
}

document.addEventListener('DOMContentLoaded', updateSelectedCount);
</script>
@stop
