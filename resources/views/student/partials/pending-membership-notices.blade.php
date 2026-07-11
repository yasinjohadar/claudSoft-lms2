@if(!empty($pendingMembershipNotices))
    <div class="d-flex flex-column gap-3 mb-4">
        @foreach($pendingMembershipNotices as $notice)
            <div class="student-pending-review-banner" role="status" aria-live="polite">
                <div class="student-pending-review-banner__icon" aria-hidden="true">
                    <i class="fe fe-clock"></i>
                </div>
                <div class="student-pending-review-banner__body">
                    <span class="student-pending-review-banner__badge">انتظار موافقة الإدارة</span>
                    <h3 class="student-pending-review-banner__title">
                        طلبكم قيد المعالجة حالياً
                        @if(!empty($notice['diploma_name']) || !empty($notice['group_name']))
                            <span class="student-pending-review-banner__group">
                                — {{ $notice['diploma_name'] ?: $notice['group_name'] }}
                            </span>
                        @endif
                    </h3>
                    <p class="student-pending-review-banner__text">
                        {{ $notice['message'] }}
                    </p>
                    <p class="student-pending-review-banner__hint">
                        لا حاجة لأي إجراء منك الآن. ستظهر الكورسات تلقائياً بعد قبول طلب الانضمام.
                    </p>
                </div>
            </div>
        @endforeach
    </div>
@endif
