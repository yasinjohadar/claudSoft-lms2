@extends('admin.layouts.master')

@section('page-title')
    تصحيح المحاولة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تصحيح: {{ $attempt->questionModule->title ?? 'غير معروف' }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.question-module-grading.index') }}">تصحيح اختبارات الكورسات</a></li>
                            <li class="breadcrumb-item active">تصحيح المحاولة</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Student Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-primary-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>معلومات الطالب
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>الاسم:</strong> {{ $attempt->student->name ?? 'غير معروف' }}</p>
                                    <p class="mb-2"><strong>البريد الإلكتروني:</strong> {{ $attempt->student->email ?? '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>المحاولة رقم:</strong> #{{ $attempt->attempt_number }}</p>
                                    <p class="mb-2"><strong>تاريخ الإنهاء:</strong> {{ $attempt->completed_at ? $attempt->completed_at->format('Y-m-d H:i') : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grading Form -->
                    @if($responsesNeedingGrading->count() > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            يوجد {{ $responsesNeedingGrading->count() }} سؤال يحتاج تصحيح يدوي
                        </div>
                    @endif

                    @foreach($allResponses as $index => $response)
                        @php
                            $question = $response->question;
                            $questionNumber = $index + 1;
                            $questionTypeName = $question->questionType->name ?? '';
                            $requiresManualGrading = in_array($questionTypeName, ['short_answer', 'essay']);
                            // Only show grading section for manual grading questions
                            $needsGrading = $requiresManualGrading && ($response->is_correct === null || $response->score_obtained === null);
                        @endphp

                        <div class="card custom-card mb-4 {{ $needsGrading ? 'border-warning' : '' }}">
                            <div class="card-header {{ $needsGrading ? 'bg-warning-transparent' : 'bg-success-transparent' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">
                                            <span class="badge bg-primary me-2">سؤال {{ $questionNumber }}</span>
                                            <span class="badge bg-info">{{ $question->questionType->display_name ?? 'غير معروف' }}</span>
                                            @if($needsGrading)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>بانتظار التصحيح
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>مُصحح
                                                </span>
                                            @endif
                                        </h6>
                                    </div>
                                    <div>
                                        <span class="badge bg-secondary">الدرجة القصوى: {{ $response->max_score }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Question Text -->
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-2">نص السؤال:</h6>
                                    <div>{!! $question->question_text !!}</div>
                                    @if($question->question_image)
                                        <img src="{{ asset('storage/' . $question->question_image) }}" alt="صورة السؤال" class="img-fluid rounded mt-2" style="max-width: 400px;">
                                    @endif
                                </div>

                                <!-- Student Answer -->
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-2">إجابة الطالب:</h6>
                                    <div class="p-3 bg-light rounded">
                                        @php
                                            $studentAnswer = $response->student_answer;
                                            $questionTypeName = $question->questionType->name ?? '';
                                            
                                            // Debug output for troubleshooting
                                            $debugInfo = [
                                                'response_id' => $response->id,
                                                'question_id' => $question->id,
                                                'question_type' => $questionTypeName,
                                                'answer_type' => gettype($studentAnswer),
                                                'answer_value' => is_array($studentAnswer) ? json_encode($studentAnswer, JSON_UNESCAPED_UNICODE) : $studentAnswer,
                                                'is_null' => $studentAnswer === null,
                                                'is_empty_string' => $studentAnswer === '',
                                                'is_empty_array' => is_array($studentAnswer) && empty($studentAnswer),
                                            ];
                                        @endphp
                                        
                                        @if($studentAnswer === null || $studentAnswer === '' || (is_array($studentAnswer) && empty($studentAnswer)))
                                            <span class="text-muted">لم يتم الإجابة</span>
                                            @if(config('app.debug') && !empty($studentAnswer))
                                                <div class="mt-2">
                                                    <small class="text-muted d-block">Debug Info:</small>
                                                    <pre class="small bg-light p-2 rounded">{{ json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                        @elseif($questionTypeName === 'multiple_choice_single')
                                            @php
                                                // Handle different formats: direct ID, string ID, or array with key
                                                $optionId = null;
                                                if (is_array($studentAnswer)) {
                                                    $optionId = $studentAnswer['selected_option'] ?? $studentAnswer['answer'] ?? null;
                                                    // If still null, try to get first numeric value
                                                    if ($optionId === null) {
                                                        foreach ($studentAnswer as $key => $val) {
                                                            if (is_numeric($val)) {
                                                                $optionId = (int)$val;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    // Direct value - could be string or number
                                                    $optionId = is_numeric($studentAnswer) ? (int)$studentAnswer : null;
                                                }
                                                
                                                if ($optionId) {
                                                    $selectedOption = $question->options->find($optionId);
                                                } else {
                                                    $selectedOption = null;
                                                }
                                            @endphp
                                            @if($selectedOption)
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-check-circle me-2"></i>{!! $selectedOption->option_text !!}
                                                </div>
                                            @else
                                                <span class="text-danger">الخيار المحدد غير موجود (ID: {{ $optionId ?? 'null' }})</span>
                                                <pre class="mt-2 small">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        @elseif($questionTypeName === 'true_false')
                                            @php
                                                // Handle true/false answers - could be 'true'/'false' string or option ID
                                                $answerValue = null;
                                                $displayText = null;
                                                
                                                if (is_array($studentAnswer)) {
                                                    $answerValue = $studentAnswer['answer'] ?? $studentAnswer['selected_option'] ?? null;
                                                    if ($answerValue === null && !empty($studentAnswer)) {
                                                        $answerValue = array_values($studentAnswer)[0] ?? null;
                                                    }
                                                } else {
                                                    $answerValue = $studentAnswer;
                                                }
                                                
                                                // Convert to display text
                                                if (is_numeric($answerValue)) {
                                                    // It's an option ID
                                                    $selectedOption = $question->options->find($answerValue);
                                                    if ($selectedOption) {
                                                        $displayText = $selectedOption->option_text;
                                                    }
                                                } else {
                                                    // It's a string 'true' or 'false'
                                                    $answerStr = strtolower(trim((string)$answerValue));
                                                    if ($answerStr === 'true' || $answerStr === '1' || $answerStr === 'صح') {
                                                        $displayText = 'صحيح';
                                                    } elseif ($answerStr === 'false' || $answerStr === '0' || $answerStr === 'خطأ') {
                                                        $displayText = 'خطأ';
                                                    } else {
                                                        $displayText = $answerValue; // Fallback
                                                    }
                                                }
                                            @endphp
                                            @if($displayText)
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-{{ $displayText === 'صحيح' ? 'check' : 'times' }}-circle me-2"></i>{{ $displayText }}
                                                </div>
                                            @else
                                                <span class="text-danger">الإجابة غير معروفة</span>
                                                <pre class="mt-2 small">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        @elseif($questionTypeName === 'multiple_choice_multiple')
                                            @php
                                                $optionIds = [];
                                                if (is_array($studentAnswer)) {
                                                    if (isset($studentAnswer['selected_options'])) {
                                                        $optionIds = array_map('intval', (array)$studentAnswer['selected_options']);
                                                    } else {
                                                        // Direct array of IDs
                                                        foreach ($studentAnswer as $val) {
                                                            if (is_numeric($val)) {
                                                                $optionIds[] = (int)$val;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    // Single value - shouldn't happen for multiple choice, but handle it
                                                    if (is_numeric($studentAnswer)) {
                                                        $optionIds = [(int)$studentAnswer];
                                                    }
                                                }
                                            @endphp
                                            @if(!empty($optionIds))
                                                <ul class="mb-0">
                                                    @foreach($question->options as $option)
                                                        @if(in_array((int)$option->id, $optionIds))
                                                            <li>{!! $option->option_text !!}</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                                <pre class="mt-2 small">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        @elseif($questionTypeName === 'short_answer' || $questionTypeName === 'essay')
                                            @php
                                                $answerText = '';
                                                if (is_array($studentAnswer)) {
                                                    $answerText = $studentAnswer['answer'] ?? $studentAnswer['text'] ?? null;
                                                    // If still null, try to get first non-numeric value
                                                    if ($answerText === null) {
                                                        foreach ($studentAnswer as $key => $val) {
                                                            if (!is_numeric($val) && !is_numeric($key)) {
                                                                $answerText = $val;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    // Direct string value
                                                    $answerText = (string)$studentAnswer;
                                                }
                                            @endphp
                                            @if($answerText && trim($answerText) !== '')
                                                <p class="mb-0">{!! nl2br(e($answerText)) !!}</p>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                                <pre class="mt-2 small">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        @elseif($questionTypeName === 'fill_blanks')
                                            @if(is_array($studentAnswer) && !empty($studentAnswer))
                                                <div class="mb-0">
                                                    @foreach($studentAnswer as $index => $blankAnswer)
                                                        <div class="mb-2">
                                                            <strong>الفراغ {{ $index + 1 }}:</strong> 
                                                            <span class="badge bg-secondary">{{ e($blankAnswer) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                                <pre class="mt-2 small">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        @elseif($questionTypeName === 'matching')
                                            @if(is_array($studentAnswer) && !empty($studentAnswer))
                                                <ul class="mb-0">
                                                    @foreach($studentAnswer as $optionId => $matchedValue)
                                                        @php $option = $question->options->find($optionId); @endphp
                                                        <li class="mb-2">
                                                            <strong>{!! $option ? $option->option_text : 'Option #' . $optionId !!}:</strong> 
                                                            <span class="badge bg-info">{{ e($matchedValue) }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                                <pre class="mt-2 small">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        @elseif($questionTypeName === 'ordering')
                                            @if(is_array($studentAnswer) && !empty($studentAnswer))
                                                <ol class="mb-0">
                                                    @foreach($studentAnswer as $order => $optionId)
                                                        @php 
                                                            // Handle both indexed arrays [0 => id, 1 => id] and direct arrays [id, id]
                                                            $actualOptionId = is_numeric($optionId) ? (int)$optionId : (int)$order;
                                                            $option = $question->options->find($actualOptionId);
                                                        @endphp
                                                        <li>{!! $option ? $option->option_text : 'Option #' . $actualOptionId !!}</li>
                                                    @endforeach
                                                </ol>
                                            @else
                                                <span class="text-muted">لم يتم الإجابة</span>
                                                <pre class="mt-2 small">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        @else
                                            {{-- Fallback: display as JSON for debugging --}}
                                            <div class="alert alert-warning">
                                                <strong>تنسيق غير معروف:</strong>
                                                <pre class="mb-0 small mt-2">{{ json_encode($studentAnswer, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                            @if(config('app.debug'))
                                                <div class="mt-2">
                                                    <small class="text-muted d-block">Debug Info:</small>
                                                    <pre class="small bg-light p-2 rounded">{{ json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Correct Answer (if available) -->
                                @php
                                    $correctOptions = $question->options->where('is_correct', true);
                                @endphp
                                @if($correctOptions->count() > 0)
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-2 text-success">
                                            <i class="fas fa-lightbulb me-1"></i>الإجابة الصحيحة:
                                        </h6>
                                        <div class="alert alert-success mb-0">
                                            @foreach($correctOptions as $option)
                                                <div><i class="fas fa-check-circle me-2"></i>{{ $option->option_text }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Current Grade Status -->
                                @if(!$needsGrading && ($response->is_correct !== null || $response->score_obtained !== null))
                                    <div class="mb-3">
                                        <div class="alert alert-{{ $response->is_correct === true ? 'success' : ($response->is_correct === false ? 'danger' : 'info') }}">
                                            <strong>الدرجة المحصلة:</strong> {{ number_format($response->score_obtained ?? 0, 2) }} / {{ number_format($response->max_score ?? 0, 2) }}
                                            @if($response->is_correct === true)
                                                <span class="badge bg-success ms-2">صحيح</span>
                                            @elseif($response->is_correct === false)
                                                <span class="badge bg-danger ms-2">خطأ</span>
                                            @else
                                                <span class="badge bg-info ms-2">مُصحح تلقائياً</span>
                                            @endif
                                        </div>
                                        @if($response->feedback)
                                            <div class="alert alert-info">
                                                <strong>الملاحظات:</strong> {{ $response->feedback }}
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Grading Section -->
                                @if($needsGrading)
                                    <div class="row g-3 mt-3 p-3 bg-warning-transparent rounded">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">الدرجة المحصلة <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   class="form-control score-input"
                                                   id="score_{{ $response->id }}"
                                                   data-response-id="{{ $response->id }}"
                                                   data-max-score="{{ $response->max_score }}"
                                                   min="0"
                                                   max="{{ $response->max_score }}"
                                                   step="0.01"
                                                   value="{{ $response->score_obtained ?? 0 }}"
                                                   required>
                                            <small class="text-muted">الحد الأقصى: {{ $response->max_score }}</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">صحيح/خطأ</label>
                                            <select class="form-select is-correct-input"
                                                    id="is_correct_{{ $response->id }}"
                                                    data-response-id="{{ $response->id }}">
                                                <option value="">اختر...</option>
                                                <option value="1" {{ $response->is_correct === true ? 'selected' : '' }}>صحيح</option>
                                                <option value="0" {{ $response->is_correct === false ? 'selected' : '' }}>خطأ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">الملاحظات (اختياري)</label>
                                            <textarea class="form-control feedback-input"
                                                      id="feedback_{{ $response->id }}"
                                                      data-response-id="{{ $response->id }}"
                                                      rows="2"
                                                      placeholder="أضف ملاحظات للطالب...">{{ $response->feedback }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="button"
                                                    class="btn btn-success btn-sm save-response-btn"
                                                    data-response-id="{{ $response->id }}">
                                                <i class="fas fa-save me-1"></i>حفظ التصحيح
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if($responsesNeedingGrading->count() == 0)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            جميع الأسئلة تم تصحيحها!
                        </div>
                    @endif

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Summary -->
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-info-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>ملخص التصحيح
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">إجمالي الأسئلة:</span>
                                    <strong>{{ $allResponses->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">مُصحح تلقائياً:</span>
                                    <strong class="text-success">{{ $allResponses->where('is_correct', '!=', null)->where('score_obtained', '!=', null)->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">يحتاج تصحيح:</span>
                                    <strong class="text-danger" id="pending-count">{{ $responsesNeedingGrading->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">تم التصحيح:</span>
                                    <strong class="text-primary" id="graded-count">{{ $allResponses->where('score_obtained', '!=', null)->count() }}</strong>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">الدرجة الحالية:</span>
                                        <strong class="text-primary" id="current-score">{{ number_format($attempt->total_score ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">الدرجة القصوى:</span>
                                        <strong class="text-secondary">{{ number_format($attempt->responses->sum('max_score') ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">النسبة المئوية:</span>
                                        <strong class="text-{{ ($attempt->percentage ?? 0) >= ($attempt->questionModule->pass_percentage ?? 60) ? 'success' : 'danger' }}" id="percentage-score">
                                            {{ number_format($attempt->percentage ?? 0, 1) }}%
                                        </strong>
                                    </div>
                            </div>

                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-{{ ($attempt->percentage ?? 0) >= ($attempt->questionModule->pass_percentage ?? 60) ? 'success' : 'danger' }}"
                                     id="score-progress"
                                     style="width: {{ min($attempt->percentage ?? 0, 100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header bg-warning-transparent">
                            <div class="card-title mb-0">
                                <i class="fas fa-cog me-2"></i>الإجراءات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($responsesNeedingGrading->count() == 0)
                                    <form action="{{ route('admin.question-module-grading.complete', $attempt->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-check-circle me-2"></i>إنهاء التصحيح
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.question-module-grading.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
                                </a>
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
    // Save individual response
    $('.save-response-btn').click(function() {
        const responseId = $(this).data('response-id');
        const score = parseFloat($(`#score_${responseId}`).val()) || 0;
        const maxScore = parseFloat($(`#score_${responseId}`).data('max-score'));
        const isCorrect = $(`#is_correct_${responseId}`).val();
        const feedback = $(`#feedback_${responseId}`).val();

        if (score > maxScore) {
            alert('الدرجة المدخلة أكبر من الدرجة القصوى!');
            return;
        }

        if (!isCorrect) {
            alert('يرجى تحديد ما إذا كانت الإجابة صحيحة أم خاطئة');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>جاري الحفظ...');

        $.ajax({
            url: '{{ route("admin.question-module-grading.grade-response", ":id") }}'.replace(':id', responseId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                score_obtained: score,
                is_correct: isCorrect, // Send as '1' or '0' string, controller will handle conversion
                feedback: feedback
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to update all data
                    location.reload();
                } else {
                    alert('حدث خطأ: ' + (response.message || 'حاول مرة أخرى'));
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>حفظ التصحيح');
                }
            },
            error: function(xhr) {
                alert('حدث خطأ: ' + (xhr.responseJSON?.message || 'حاول مرة أخرى'));
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>حفظ التصحيح');
            }
        });
    });

    // Auto-update is_correct based on score
    $('.score-input').on('input', function() {
        const responseId = $(this).data('response-id');
        const score = parseFloat($(this).val()) || 0;
        const maxScore = parseFloat($(this).data('max-score'));
        const isCorrectSelect = $(`#is_correct_${responseId}`);
        
        if (score >= maxScore) {
            isCorrectSelect.val('1');
        } else if (score > 0) {
            // Leave as is or set to 0
            if (isCorrectSelect.val() === '') {
                isCorrectSelect.val('0');
            }
        }
    });
});
</script>
@stop

