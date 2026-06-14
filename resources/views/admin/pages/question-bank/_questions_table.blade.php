<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 40px;">
                    <input type="checkbox" id="select-all-questions-table" class="form-check-input">
                </th>
                <th style="width: 48px;">#</th>
                <th>السؤال</th>
                <th>النوع</th>
                <th>اللغات</th>
                <th>الكورس / الدرس</th>
                <th>الصعوبة</th>
                <th>الدرجة</th>
                <th>الاستخدام</th>
                <th style="width: 140px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($questions as $question)
                <tr id="question-row-{{ $question->id }}" class="qb-table-row">
                    <td>
                        <input type="checkbox" class="form-check-input question-row-checkbox" value="{{ $question->id }}">
                    </td>
                    <td>{{ $loop->iteration + ($questions->currentPage() - 1) * $questions->perPage() }}</td>
                    <td>
                        <div class="qb-question-preview text-truncate fw-semibold" title="{{ strip_tags($question->question_text) }}">
                            {{ Str::limit(strip_tags($question->question_text), 80) }}
                        </div>
                        <small class="text-muted">
                            <i class="fe fe-user me-1"></i>{{ $question->creator->name ?? 'غير محدد' }}
                        </small>
                    </td>
                    <td>
                        <span class="qb-type-chip">
                            <i class="{{ $question->questionType->icon ?? 'fe fe-help-circle' }}"></i>
                            {{ $question->questionType->display_name }}
                        </span>
                    </td>
                    <td>
                        @if($question->programmingLanguages->count() > 0)
                            @foreach($question->programmingLanguages as $lang)
                                <span class="qb-lang-chip" style="background-color: {{ $lang->color ?? '#6c757d' }};">
                                    <i class="{{ $lang->icon ?? 'fe fe-code' }}"></i>{{ $lang->name }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($question->course)
                            <div class="fw-semibold text-truncate" style="max-width: 160px;" title="{{ $question->course->title }}">
                                {{ $question->course->title }}
                            </div>
                        @else
                            <span class="text-muted">عام</span>
                        @endif
                        @php
                            $lessonLabel = $question->lesson_name ?? ($question->metadata['lesson_name'] ?? null);
                        @endphp
                        @if($lessonLabel)
                            <small class="text-muted d-block text-truncate" style="max-width: 160px;" title="{{ $lessonLabel }}">
                                {{ $lessonLabel }}
                            </small>
                        @endif
                    </td>
                    <td>
                        @php
                            $diffClass = match($question->difficulty_level) {
                                'easy' => 'easy',
                                'medium' => 'medium',
                                'hard' => 'hard',
                                default => 'expert',
                            };
                            $diffLabel = match($question->difficulty_level) {
                                'easy' => 'سهل',
                                'medium' => 'متوسط',
                                'hard' => 'صعب',
                                default => 'خبير',
                            };
                        @endphp
                        <span class="qb-difficulty-chip qb-difficulty-chip--{{ $diffClass }}">{{ $diffLabel }}</span>
                    </td>
                    <td>
                        <span class="group-show-chip group-show-chip--sm">{{ $question->default_grade ?? 0 }}</span>
                    </td>
                    <td>
                        <span class="group-show-chip group-show-chip--sm" title="عدد مرات الاستخدام">
                            {{ $question->quizQuestions()->count() }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1 qb-actions">
                            <a href="{{ route('question-bank.show', $question->id) }}"
                               class="btn btn-sm btn-info-light" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="{{ route('question-bank.edit', $question->id) }}"
                               class="btn btn-sm btn-primary-light" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                            <form action="{{ route('question-bank.duplicate', $question->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary-light" title="نسخ">
                                    <i class="fe fe-copy"></i>
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-danger-light remove-question"
                                    data-question-id="{{ $question->id }}"
                                    data-question-text="{{ Str::limit(strip_tags($question->question_text), 50) }}"
                                    title="حذف">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="mb-3">
                            <i class="fe fe-help-circle fs-48 text-muted"></i>
                        </div>
                        <p class="text-muted fs-16 mb-3">لا توجد أسئلة تطابق البحث</p>
                        <a href="{{ route('question-bank.create') }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-plus me-1"></i>إضافة سؤال جديد
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($questions->hasPages())
    <div class="mt-3 pt-2 border-top qb-pagination">
        {{ $questions->withQueryString()->links() }}
    </div>
@endif
