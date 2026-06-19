@foreach($enrollments as $enrollment)
    @if($enrollment->enrollment_status === 'pending')
        <div class="modal fade" id="approveModal{{ $enrollment->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="fe fe-check-circle me-2 text-success"></i>تأكيد قبول الطلب
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="group-show-empty__icon mx-auto mb-3" style="width:72px;height:72px;font-size:1.75rem;background:rgba(var(--success-rgb),0.12);color:rgb(var(--success-rgb));">
                            <i class="fe fe-user-check"></i>
                        </div>
                        <h5 class="mb-2">قبول طلب التسجيل</h5>
                        <p class="text-muted mb-1">
                            هل تريد قبول طلب تسجيل الطالب
                            <strong>{{ $enrollment->student->name ?? 'غير معروف' }}</strong>؟
                        </p>
                        <p class="text-muted mb-0">
                            في كورس: <strong>{{ $enrollment->course->title ?? 'غير معروف' }}</strong>
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('enrollments.approve', $enrollment->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fe fe-check me-1"></i>قبول الطلب
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rejectModal{{ $enrollment->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            <i class="fe fe-x-circle me-2 text-danger"></i>تأكيد رفض الطلب
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="group-show-empty__icon mx-auto mb-3" style="width:72px;height:72px;font-size:1.75rem;background:rgba(var(--danger-rgb),0.12);color:rgb(var(--danger-rgb));">
                            <i class="fe fe-user-x"></i>
                        </div>
                        <h5 class="mb-2">رفض طلب التسجيل</h5>
                        <p class="text-muted mb-1">
                            هل تريد رفض طلب تسجيل الطالب
                            <strong>{{ $enrollment->student->name ?? 'غير معروف' }}</strong>؟
                        </p>
                        <p class="text-muted mb-3">
                            في كورس: <strong>{{ $enrollment->course->title ?? 'غير معروف' }}</strong>
                        </p>
                        <div class="alert alert-warning d-flex align-items-start text-start mb-0 py-2">
                            <i class="fe fe-alert-triangle me-2 mt-1 flex-shrink-0"></i>
                            <small class="mb-0">سيتم إلغاء الطلب ولن يتمكن الطالب من الوصول للكورس.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('enrollments.reject', $enrollment->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fe fe-ban me-1"></i>رفض الطلب
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
