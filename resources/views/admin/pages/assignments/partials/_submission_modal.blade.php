<div class="modal fade" id="submissionModal{{ $submission->id }}" tabindex="-1" aria-labelledby="submissionModalLabel{{ $submission->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="submissionModalLabel{{ $submission->id }}">
                    تسليم {{ $submission->student->name }} — المحاولة #{{ $submission->attempt_number }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="mb-4">
                    <h6 class="mb-3">معلومات التسليم</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-1 fw-semibold">تاريخ التسليم</p>
                            <p class="text-muted mb-0">{{ $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : 'لم يتم التسليم' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 fw-semibold">الحالة</p>
                            @if($submission->is_late)
                                <span class="assignments-status-chip assignments-status-chip--expired">متأخر</span>
                            @else
                                <span class="assignments-status-chip assignments-status-chip--active">في الموعد</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($submission->submission_text)
                    <div class="mb-4">
                        <h6 class="mb-3">نص التسليم</h6>
                        <div class="alert alert-light mb-0">{{ $submission->submission_text }}</div>
                    </div>
                @endif

                @if($submission->submitted_links && is_array($submission->submitted_links) && count($submission->submitted_links) > 0)
                    <div class="mb-4">
                        <h6 class="mb-3">الروابط المرسلة</h6>
                        <ul class="list-group">
                            @foreach($submission->submitted_links as $link)
                                <li class="list-group-item">
                                    <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="text-primary">
                                        <i class="fe fe-external-link me-2"></i>{{ $link }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($submission->submitted_files && is_array($submission->submitted_files) && count($submission->submitted_files) > 0)
                    <div class="mb-4">
                        <h6 class="mb-3">الملفات المرسلة</h6>
                        <div class="row g-2">
                            @foreach($submission->submitted_files as $file)
                                <div class="col-md-6">
                                    <div class="assignments-attachment-card d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <i class="fe fe-file me-2 text-primary"></i>{{ $file['name'] }}
                                            <br>
                                            <small class="text-muted">{{ number_format($file['size'] / 1024, 2) }} KB</small>
                                        </div>
                                        <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-info-light btn-sm assignments-actions__btn">
                                            <i class="fe fe-download"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($submission->grade !== null)
                    <div class="alert alert-success mb-4">
                        <h6 class="mb-2">التقييم الحالي</h6>
                        <p class="mb-1"><strong>الدرجة:</strong> {{ $submission->grade }} / {{ $assignment->max_grade }}</p>
                        @if($submission->is_late && $assignment->late_penalty_percentage > 0)
                            <p class="mb-1 text-danger">
                                <strong>بعد خصم التأخير ({{ $assignment->late_penalty_percentage }}%):</strong>
                                {{ $submission->getFinalGrade() }} / {{ $assignment->max_grade }}
                            </p>
                        @endif
                        @if($submission->feedback)
                            <p class="mb-1"><strong>الملاحظات:</strong></p>
                            <p class="mb-0">{{ $submission->feedback }}</p>
                        @endif
                        <small class="text-muted">
                            تم التقييم بواسطة {{ $submission->grader->name ?? 'غير محدد' }}
                            في {{ $submission->graded_at ? $submission->graded_at->format('Y-m-d H:i') : '-' }}
                        </small>
                    </div>
                @endif

                @if($submission->status !== 'draft')
                    <div class="card bg-light mb-3 border-0">
                        <div class="card-body">
                            <h6 class="mb-3">{{ $submission->grade !== null ? 'تعديل التقييم' : 'إضافة تقييم' }}</h6>
                            <form action="{{ route('submissions.grade', $submission->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">الدرجة (من {{ $assignment->max_grade }})</label>
                                    <input type="number" name="grade" class="form-control"
                                           value="{{ $submission->grade }}"
                                           min="0" max="{{ $assignment->max_grade }}"
                                           step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الملاحظات</label>
                                    <textarea name="feedback" class="form-control" rows="3">{{ $submission->feedback }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fe fe-check me-1"></i>حفظ التقييم
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($assignment->allow_resubmission && $submission->status === 'graded')
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="mb-3 text-info">
                                    <i class="fe fe-rotate-cw me-2"></i>إدارة إعادة التسليم
                                </h6>
                                <p class="text-muted small mb-3">
                                    يمكنك منح الطالب محاولة إضافية لإعادة تسليم الواجب بعد مراجعة التقييم والملاحظات.
                                </p>

                                @php
                                    $studentId = $submission->student_id;
                                    $canResubmit = $assignment->canResubmit($studentId);
                                    $remaining = $assignment->getRemainingResubmissions($studentId);
                                    $totalAttempts = $assignment->submissions()
                                        ->where('student_id', $studentId)
                                        ->count();
                                    $extraAttempts = $assignment->getExtraAttemptsForStudent($studentId);
                                @endphp

                                <div class="mb-3">
                                    <small class="d-block mb-1">
                                        <strong>عدد المحاولات الحالية:</strong> {{ $totalAttempts }}
                                    </small>
                                    @if($assignment->max_resubmissions !== null)
                                        <small class="d-block mb-1">
                                            <strong>الحد الأقصى الأساسي:</strong> {{ $assignment->max_resubmissions + 1 }}
                                        </small>
                                        @if($extraAttempts > 0)
                                            <small class="d-block mb-1 text-info">
                                                <strong>محاولات إضافية ممنوحة:</strong> {{ $extraAttempts }}
                                                <i class="fe fe-package ms-1"></i>
                                            </small>
                                            <small class="d-block mb-1">
                                                <strong>الإجمالي المسموح:</strong> {{ $assignment->max_resubmissions + 1 + $extraAttempts }}
                                            </small>
                                        @endif
                                        <small class="d-block">
                                            <strong>المحاولات المتبقية:</strong>
                                            @if($canResubmit)
                                                <span class="text-success">{{ $remaining ?? 'غير محدود' }}</span>
                                            @else
                                                <span class="text-danger">0 (استُنفدت جميع المحاولات)</span>
                                            @endif
                                        </small>
                                    @else
                                        <small class="d-block">
                                            <strong>المحاولات المتبقية:</strong> <span class="text-success">غير محدود</span>
                                        </small>
                                    @endif
                                </div>

                                @if($canResubmit)
                                    <div class="alert alert-success alert-sm mb-0">
                                        <i class="fe fe-check-circle me-2"></i>
                                        الطالب يمكنه إعادة التسليم حالياً
                                    </div>
                                @elseif($assignment->max_resubmissions !== null)
                                    <form action="{{ route('submissions.grant-resubmission', $submission->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من منح الطالب محاولة إضافية؟')">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm">
                                            <i class="fe fe-plus-circle me-1"></i>
                                            منح محاولة إضافية
                                        </button>
                                        <small class="d-block text-muted mt-2">
                                            سيتم زيادة الحد الأقصى للمحاولات المسموحة بمقدار واحد
                                        </small>
                                    </form>
                                @else
                                    <div class="alert alert-warning alert-sm mb-0">
                                        <i class="fe fe-info me-2"></i>
                                        إعادة التسليم مسموحة بدون حد أقصى
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
