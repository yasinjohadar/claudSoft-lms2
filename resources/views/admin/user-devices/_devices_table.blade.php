@php
    $deviceTypeIcons = [
        'mobile' => 'fe-smartphone',
        'tablet' => 'fe-tablet',
        'desktop' => 'fe-monitor',
    ];
    $deviceTypeLabels = [
        'mobile' => 'جوال',
        'tablet' => 'تابلت',
        'desktop' => 'سطح مكتب',
    ];
@endphp

@if($devices->count() > 0)
    <form id="bulkDeleteForm" action="{{ route('admin.user-devices.bulk-delete') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table table-hover text-nowrap dashboard-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        </th>
                        <th style="width: 48px;">#</th>
                        <th>المستخدم</th>
                        <th>معلومات الجهاز</th>
                        <th>عدد الدخول</th>
                        <th>أول استخدام</th>
                        <th>آخر استخدام</th>
                        <th>الموقع</th>
                        <th>الحالة</th>
                        <th style="width: 100px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $device)
                        @php
                            $typeIcon = $deviceTypeIcons[$device->device_type] ?? 'fe-hard-drive';
                        @endphp
                        <tr class="ud-table-row">
                            <td>
                                <input type="checkbox" class="form-check-input device-checkbox" value="{{ $device->id }}" onchange="updateBulkActionBar()">
                            </td>
                            <td>{{ $devices->firstItem() + $loop->index }}</td>
                            <td>
                                @if($device->user)
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="ud-user-avatar">
                                            @if($device->user->avatar)
                                                <img src="{{ asset('storage/' . $device->user->avatar) }}" alt="">
                                            @else
                                                {{ mb_substr($device->user->name, 0, 1) }}
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <span class="fw-semibold d-block text-truncate" style="max-width: 200px;" title="{{ $device->user->name }}">
                                                {{ $device->user->name }}
                                            </span>
                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;">{{ $device->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-start gap-2 min-w-0" style="max-width: 260px;">
                                    <span class="ud-device-icon"><i class="fe {{ $typeIcon }}"></i></span>
                                    <div class="min-w-0">
                                        @if($device->device_name)
                                            <span class="fw-semibold d-block text-truncate" title="{{ $device->device_name }}">{{ $device->device_name }}</span>
                                        @endif
                                        <small class="text-muted d-block text-truncate" title="{{ $device->device_info }}">{{ $device->device_info }}</small>
                                        @if($device->device_type)
                                            <small class="text-muted">{{ $deviceTypeLabels[$device->device_type] ?? ucfirst($device->device_type) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="ud-logins-chip">
                                    <i class="fe fe-log-in"></i>{{ number_format($device->total_logins) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted d-block">{{ $device->first_used_at?->format('Y-m-d') ?? '—' }}</small>
                                <small class="fw-semibold">{{ $device->first_used_at?->format('H:i') ?? '' }}</small>
                            </td>
                            <td>
                                <small class="fw-semibold d-block">{{ $device->last_used_human }}</small>
                                @if($device->last_used_at)
                                    <small class="text-muted">{{ $device->last_used_at->format('Y-m-d H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="d-block text-truncate" style="max-width: 140px;" title="{{ $device->location_formatted }}">
                                    <i class="fe fe-map-pin me-1 text-primary"></i>{{ $device->location_formatted }}
                                </small>
                                @if($device->last_ip_address)
                                    <small class="ud-ip-text d-block">{{ $device->last_ip_address }}</small>
                                @endif
                            </td>
                            <td>
                                @if($device->is_blocked)
                                    <span class="ud-status-chip ud-status-chip--blocked"><i class="fe fe-slash me-1"></i>محظور</span>
                                @elseif($device->is_trusted)
                                    <span class="ud-status-chip ud-status-chip--trusted"><i class="fe fe-shield me-1"></i>موثوق</span>
                                @elseif($device->total_logins === 0)
                                    <span class="ud-status-chip ud-status-chip--pending"><i class="fe fe-clock me-1"></i>بانتظار الموافقة</span>
                                @else
                                    <span class="ud-status-chip ud-status-chip--normal">عادي</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.user-devices.show', $device->id) }}"
                                       class="btn btn-info-light btn-sm assignments-actions__btn"
                                       title="عرض التفاصيل">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                    @if($device->is_blocked)
                                        <form action="{{ route('admin.user-devices.unblock', $device->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من إلغاء حظر هذا الجهاز؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-success-light btn-sm assignments-actions__btn" title="إلغاء الحظر">
                                                <i class="fe fe-unlock"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.user-devices.block', $device->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('هل أنت متأكد من حظر هذا الجهاز؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger-light btn-sm assignments-actions__btn" title="حظر">
                                                <i class="fe fe-slash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-3">
        {{ $devices->withQueryString()->links() }}
    </div>
@else
    <div class="group-show-empty py-5">
        <i class="fe fe-smartphone group-show-empty__icon"></i>
        <h5 class="group-show-empty__title">لا توجد أجهزة</h5>
        <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو إعادة تعيين البحث.</p>
    </div>
@endif
