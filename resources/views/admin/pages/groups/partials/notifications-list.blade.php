@php
    $typeStyles = [
        'success' => ['badge' => 'bg-success-transparent text-success', 'label' => 'نجاح'],
        'info' => ['badge' => 'bg-info-transparent text-info', 'label' => 'معلومة'],
        'warning' => ['badge' => 'bg-warning-transparent text-warning', 'label' => 'تنبيه'],
        'error' => ['badge' => 'bg-danger-transparent text-danger', 'label' => 'خطأ'],
    ];
@endphp

@if($notifications->isEmpty())
    <div class="group-show-empty">
        <div class="group-show-empty__icon">
            <i class="fe fe-bell-off"></i>
        </div>
        <h4 class="group-show-empty__title">لا توجد إشعارات مُرسَلة بعد</h4>
        <p class="text-muted mb-0">استخدم النموذج أعلاه لإرسال أول إشعار لهذه المجموعة.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover text-nowrap dashboard-table mb-0">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>النوع</th>
                    <th>تاريخ الإرسال</th>
                    <th>القراءة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $notification)
                    @php $style = $typeStyles[$notification->type] ?? $typeStyles['info']; @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $notification->title }}</div>
                            <small class="d-block text-muted text-truncate" style="max-width: 320px;">
                                {{ Str::limit($notification->message, 70) }}
                            </small>
                        </td>
                        <td>
                            <span class="badge {{ $style['badge'] }}">{{ $style['label'] }}</span>
                            @if($notification->is_message)
                                <span class="badge bg-primary-transparent text-primary">
                                    <i class="fe fe-mail me-1"></i>رسالة
                                </span>
                            @endif
                        </td>
                        <td>{{ $notification->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-secondary js-view-recipients"
                                    data-notification-id="{{ $notification->id }}">
                                <i class="fe fe-eye me-1"></i>
                                قرأه {{ $notification->read_count }} من {{ $notification->recipients_count }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger-light js-delete-notification"
                                    data-notification-id="{{ $notification->id }}" title="حذف">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $notifications->links() }}
    </div>
@endif
