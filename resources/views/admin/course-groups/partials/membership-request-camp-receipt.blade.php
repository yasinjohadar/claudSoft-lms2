{{-- إيصال دفع طلب الانضمام (معسكر / مجموعة مدفوعة) — مستقل عن وصل التسجيل الخارجي --}}
@if($membershipRequest->hasReceipt())
    @php
        $campReceiptUrl = route('courses.groups.membership-requests.receipt', [$course->id, $group->id, $membershipRequest->id]);
        $campReceiptDownloadUrl = route('courses.groups.membership-requests.receipt', [
            $course->id,
            $group->id,
            $membershipRequest->id,
            'download' => 1,
        ]);
        $campReceiptExtension = strtolower(pathinfo($membershipRequest->receipt_path, PATHINFO_EXTENSION));
        $campReceiptIsImage = in_array($campReceiptExtension, ['jpg', 'jpeg', 'png', 'webp'], true);
    @endphp
    <div class="card custom-card group-show-members-card mb-4">
        <div class="card-header border-0 pb-0 d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h6 class="group-show-members-card__title mb-1">
                    <i class="fe fe-paperclip me-1 text-warning"></i>إيصال دفع المعسكر
                </h6>
                <p class="fs-12 text-muted mb-0">
                    الملف الذي رفعه الطالب مع طلب التسجيل على المعسكر (من داخل المنصة).
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ $campReceiptUrl }}" target="_blank" rel="noopener"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fe fe-eye me-1"></i>عرض
                </a>
                <a href="{{ $campReceiptDownloadUrl }}" class="btn btn-sm btn-primary">
                    <i class="fe fe-download me-1"></i>تحميل
                </a>
            </div>
        </div>
        <div class="card-body pt-3">
            @if($campReceiptIsImage)
                <a href="{{ $campReceiptUrl }}" target="_blank" rel="noopener" class="d-inline-block">
                    <img src="{{ $campReceiptUrl }}"
                         alt="إيصال دفع المعسكر"
                         class="img-fluid rounded border"
                         style="max-height: 320px;">
                </a>
            @elseif($campReceiptExtension === 'pdf')
                <object data="{{ $campReceiptUrl }}" type="application/pdf" width="100%" height="360">
                    <p class="mb-0">
                        <a href="{{ $campReceiptUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                            فتح ملف PDF
                        </a>
                    </p>
                </object>
            @else
                <a href="{{ $campReceiptUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                    <i class="fe fe-file me-1"></i>فتح الملف
                </a>
            @endif
        </div>
    </div>
@elseif($group->is_camp && ($group->require_payment_receipt ?? true))
    <div class="alert alert-warning border mb-4">
        <i class="fe fe-alert-triangle me-1"></i>
        لم يتم إرفاق إيصال دفع مع طلب التسجيل على المعسكر.
    </div>
@endif
