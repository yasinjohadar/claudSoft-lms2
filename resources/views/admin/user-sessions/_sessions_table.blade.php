@if($sessions->count() > 0)
    <form id="bulkDeleteForm" action="{{ route('admin.user-sessions.bulk-delete') }}" method="POST">
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
                        <th>تاريخ البدء</th>
                        <th>المدة</th>
                        <th>الجهاز</th>
                        <th>الموقع</th>
                        <th>الحالة</th>
                        <th>الأنشطة</th>
                        <th style="width: 80px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr class="us-table-row">
                            <td>
                                <input type="checkbox" class="form-check-input session-checkbox" value="{{ $session->id }}" onchange="updateBulkActionBar()">
                            </td>
                            <td>{{ $sessions->firstItem() + $loop->index }}</td>
                            <td>
                                @if($session->user)
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <span class="us-user-avatar">
                                            @if($session->user->avatar)
                                                <img src="{{ asset('storage/' . $session->user->avatar) }}" alt="">
                                            @else
                                                {{ mb_substr($session->user->name, 0, 1) }}
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <span class="fw-semibold d-block text-truncate" style="max-width: 200px;" title="{{ $session->user->name }}">
                                                {{ $session->user->name }}
                                            </span>
                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;">{{ $session->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted d-block">{{ $session->started_at->format('Y-m-d') }}</small>
                                <small class="fw-semibold">{{ $session->started_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="us-duration-chip">
                                    <i class="fe fe-clock"></i>{{ $session->duration_formatted }}
                                </span>
                            </td>
                            <td>
                                <div class="min-w-0" style="max-width: 180px;">
                                    <span class="d-flex align-items-center gap-1 small">
                                        <i class="fe fe-monitor text-muted"></i>
                                        <span class="text-truncate" title="{{ $session->device_info }}">{{ $session->device_info }}</span>
                                    </span>
                                    @if($session->ip_address)
                                        <small class="text-muted"><i class="fe fe-globe me-1"></i>{{ $session->ip_address }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 140px;" title="{{ $session->location_formatted }}">
                                    {{ $session->location_formatted }}
                                </small>
                            </td>
                            <td>
                                @if($session->status == 'active')
                                    <span class="us-status-chip us-status-chip--active"><i class="fe fe-radio me-1"></i>نشطة</span>
                                @elseif($session->status == 'completed')
                                    <span class="us-status-chip us-status-chip--completed">مكتملة</span>
                                @elseif($session->status == 'disconnected')
                                    <span class="us-status-chip us-status-chip--disconnected">منفصلة</span>
                                @else
                                    <span class="us-status-chip us-status-chip--timeout">انتهت</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.user-sessions.show', $session->id) }}" class="us-activities-chip">
                                    <i class="fe fe-activity"></i>{{ $session->activities_count }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.user-sessions.show', $session->id) }}"
                                   class="btn btn-info-light btn-sm assignments-actions__btn"
                                   title="عرض التفاصيل">
                                    <i class="fe fe-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-3">
        {{ $sessions->withQueryString()->links() }}
    </div>
@else
    <div class="group-show-empty py-5">
        <i class="fe fe-monitor group-show-empty__icon"></i>
        <h5 class="group-show-empty__title">لا توجد جلسات</h5>
        <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو إعادة تعيين البحث.</p>
    </div>
@endif
