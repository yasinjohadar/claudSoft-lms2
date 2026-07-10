<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 40px;">
                    <input type="checkbox" id="select-all-import-questions" class="form-check-input">
                </th>
                <th style="width: 48px;">#</th>
                <th>السؤال</th>
                <th>النوع</th>
                <th>اللغات</th>
                <th>الكورس / الدرس</th>
                <th>الصعوبة</th>
                <th style="width: 100px;">الدرجة</th>
                <th style="width: 80px;">معاينة</th>
            </tr>
        </thead>
        <tbody id="import-questions-tbody">
            @forelse($availableQuestions as $question)
                @php
                    $lessonLabel = $question->lesson_name ?? ($question->metadata['lesson_name'] ?? null);
                @endphp
                <tr id="import-question-row-{{ $question->id }}" data-question-id="{{ $question->id }}">
                    <td>
                        <input type="checkbox"
                               class="form-check-input import-question-checkbox"
                               value="{{ $question->id }}"
                               data-default-grade="{{ $question->default_grade }}">
                    </td>
                    <td>{{ $loop->iteration + ($availableQuestions->currentPage() - 1) * $availableQuestions->perPage() }}</td>
                    <td>
                        @include('admin.pages.question-bank.partials.question-text-list', [
                            'text' => $question->question_text,
                            'clamp' => true,
                            'maxWidth' => '320px',
                        ])
                        <small class="text-muted">
                            <i class="fe fe-user me-1"></i>{{ $question->creator->name ?? 'غير محدد' }}
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-info-transparent">
                            {{ $question->questionType->display_name }}
                        </span>
                    </td>
                    <td>
                        @include('admin.pages.question-bank.partials.programming-language-chips', [
                            'languages' => $question->programmingLanguages,
                            'emptyText' => '—',
                        ])
                    </td>
                    <td>
                        @if($question->course)
                            <div class="fw-semibold text-truncate" style="max-width: 140px;" title="{{ $question->course->title }}">
                                {{ $question->course->title }}
                            </div>
                        @else
                            <span class="text-muted">عام</span>
                        @endif
                        @if($lessonLabel)
                            <small class="text-muted d-block text-truncate" style="max-width: 140px;" title="{{ $lessonLabel }}">
                                {{ $lessonLabel }}
                            </small>
                        @endif
                    </td>
                    <td>
                        @php
                            $diffBadge = match($question->difficulty_level) {
                                'easy' => 'bg-success-transparent text-success',
                                'medium' => 'bg-warning-transparent text-warning',
                                'hard' => 'bg-danger-transparent text-danger',
                                default => 'bg-dark-transparent text-dark',
                            };
                            $diffLabel = match($question->difficulty_level) {
                                'easy' => 'سهل',
                                'medium' => 'متوسط',
                                'hard' => 'صعب',
                                default => 'خبير',
                            };
                        @endphp
                        <span class="badge {{ $diffBadge }}">{{ $diffLabel }}</span>
                    </td>
                    <td>
                        <input type="number"
                               class="form-control form-control-sm import-question-grade"
                               value="{{ $question->default_grade }}"
                               min="0"
                               step="0.5"
                               style="width: 72px;"
                               data-question-id="{{ $question->id }}">
                    </td>
                    <td>
                        <a href="{{ route('question-bank.show', $question->id) }}"
                           class="btn btn-sm btn-info-light"
                           target="_blank"
                           rel="noopener"
                           title="عرض السؤال">
                            <i class="fe fe-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fe fe-inbox fs-1 d-block mb-2 opacity-50"></i>
                        لا توجد أسئلة متاحة للاستيراد — قد تكون جميعها مضافة مسبقاً أو لا تطابق الفلاتر.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($availableQuestions->hasPages())
    <div class="card-footer border-0 pt-3 qb-import-pagination">
        {{ $availableQuestions->links() }}
    </div>
@endif
