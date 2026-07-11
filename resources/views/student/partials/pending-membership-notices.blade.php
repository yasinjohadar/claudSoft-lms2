@if(!empty($pendingMembershipNotices))
    <div class="d-flex flex-column gap-3 mb-3">
        @foreach($pendingMembershipNotices as $notice)
            <div class="alert alert-warning border-0 mb-0 d-flex align-items-start gap-3" role="status">
                <i class="fe fe-clock fs-4 mt-1 flex-shrink-0"></i>
                <div>
                    <h6 class="mb-1">
                        طلبكم قيد المعالجة
                        @if(!empty($notice['diploma_name']) || !empty($notice['group_name']))
                            —
                            {{ $notice['diploma_name'] ?: $notice['group_name'] }}
                        @endif
                    </h6>
                    <p class="mb-0 fs-13">{{ $notice['message'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
@endif
